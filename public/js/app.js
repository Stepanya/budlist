/* ===========================================================================
   Budlist SPA — jQuery + AJAX, optimistic UI with rollback, SortableJS reorder.
   The page loads once; every action hits a small JSON endpoint.
   =========================================================================== */
(function ($) {
    'use strict';

    // --- CSRF for every AJAX request (meta tag + $.ajaxSetup) -----------------
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // If the session expires (or CSRF token goes stale), the server replies 401/419.
    // Bounce to the login page rather than failing every action silently.
    $(document).ajaxError(function (event, jqxhr) {
        if (jqxhr.status === 401 || jqxhr.status === 419) {
            window.location.href = '/login';
        }
    });

    // --- app state ------------------------------------------------------------
    var state = {
        type: 'budget',   // active tab
        lists: [],        // all lists in the current overview (full set)
        list: null,       // currently opened list
        tasks: [],        // tasks of the opened list
        query: '',        // list search text
        page: 1           // current lists page
    };

    var PAGE_SIZE = 20;
    var pagerInited = false;
    var sortableLists = null;
    var sortableTasks = null;
    var listModal, taskModal;

    // --- helpers --------------------------------------------------------------
    function peso(n) {
        return '₱' + Number(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function lineTotal(t) { return Number(t.amount || 0) * Number(t.quantity || 1); }

    var toastTimer;
    function toast(msg, isError) {
        var $t = $('#toast').text(msg).toggleClass('is-error', !!isError).addClass('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { $t.removeClass('show'); }, 2600);
    }

    function api(method, url, data) {
        return $.ajax({
            url: url, method: method,
            data: data ? JSON.stringify(data) : undefined,
            contentType: 'application/json',
            dataType: 'json'
        });
    }

    // =========================================================================
    // THEME
    // =========================================================================
    $('#themeToggle').on('click', function () {
        var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        try { localStorage.setItem('budlist-theme', next); } catch (e) {}
    });

    // =========================================================================
    // TABS
    // =========================================================================
    function moveIndicator(index) {
        $('.tab-indicator').css('transform', 'translateX(' + (index * 100) + '%)');
    }
    $('#tabs').on('click', '.tab', function () {
        var $tab = $(this), type = $tab.data('type');
        if (type === state.type && $('#screen-lists').hasClass('is-active')) return;
        $('.tab').removeClass('is-active');
        $tab.addClass('is-active');
        moveIndicator($tab.index());
        state.type = type;
        state.query = ''; $('#listSearch').val('');   // reset search per tab
        showListsScreen();
        loadLists();
    });

    // =========================================================================
    // SEARCH (filters the current type's lists by title, debounced)
    // =========================================================================
    var searchTimer;
    $('#listSearch').on('input', function () {
        var v = this.value;
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            state.query = v;
            state.page = 1;
            renderLists();
        }, 160);
    });

    // =========================================================================
    // SCREEN SWITCHING
    // =========================================================================
    function showListsScreen() {
        $('#screen-detail').removeClass('is-active');
        $('#screen-lists').addClass('is-active');
        $('#listsHeading').text(capitalize(state.type));
        window.scrollTo({ top: 0 });
    }
    function showDetailScreen() {
        $('#screen-lists').removeClass('is-active');
        $('#screen-detail').addClass('is-active');
        window.scrollTo({ top: 0 });
    }
    function capitalize(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

    $('#backBtn').on('click', function () {
        state.list = null;
        showListsScreen();
        loadLists(); // refresh overview totals after edits
    });

    // =========================================================================
    // LISTS OVERVIEW
    // =========================================================================
    function loadLists() {
        $('#listsPager').empty();
        var $c = $('#listsContainer').html(
            '<div class="skeleton"></div><div class="skeleton"></div><div class="skeleton"></div>'
        );
        $('#listsEmpty').prop('hidden', true);
        state.page = 1;

        api('GET', '/api/lists/' + state.type).done(function (lists) {
            state.lists = lists;
            renderLists();
        }).fail(function () {
            $c.empty();
            toast('Could not load lists', true);
        });
    }

    function filteredLists() {
        var q = (state.query || '').trim().toLowerCase();
        if (!q) return state.lists;
        return state.lists.filter(function (l) {
            return String(l.title).toLowerCase().indexOf(q) !== -1;
        });
    }

    // Repaginate + render. paginationjs slices the data and fires the callback
    // for the active page; we render that page's cards there.
    function renderLists() {
        var data = filteredLists();

        if (!data.length) {
            $('#listsContainer').empty();
            $('#listsPager').empty();
            if (pagerInited) { try { $('#listsPager').pagination('destroy'); } catch (e) {} pagerInited = false; }
            var $e = $('#listsEmpty').prop('hidden', false);
            if (state.query) {
                $e.find('p').text('No matches');
                $e.find('span').text('No lists match your search.');
            } else {
                $e.find('p').text('No lists here yet.');
                $e.find('span').html('Tap <strong>New list</strong> to create one.');
            }
            if (sortableLists) { sortableLists.destroy(); sortableLists = null; }
            return;
        }
        $('#listsEmpty').prop('hidden', true);

        if (pagerInited) { try { $('#listsPager').pagination('destroy'); } catch (e) {} }
        var totalPages = Math.ceil(data.length / PAGE_SIZE);
        var startPage = Math.min(state.page || 1, totalPages) || 1;
        $('#listsPager').pagination({
            dataSource: data,
            pageSize: PAGE_SIZE,
            pageNumber: startPage,
            showPrevious: true,
            showNext: true,
            pageRange: 2,
            hideOnlyOnePage: true,
            callback: function (pageData, pagination) {
                state.page = pagination.pageNumber;
                renderListPage(pageData);
            }
        });
        pagerInited = true;
    }

    function renderListPage(pageData) {
        var searching = !!(state.query && state.query.trim());
        var $c = $('#listsContainer').empty().toggleClass('searching', searching);
        pageData.forEach(function (list) { $c.append(buildListCard(list)); });
        initListSortable(searching);   // reordering is disabled while searching
        window.scrollTo({ top: 0 });
    }

    function buildListCard(list) {
        var total = Number(list.items_total || 0);
        var $card = $('<div class="lcard">').attr('data-id', list.id);

        // drag handle — vertically centered, far left
        $card.append(
            $('<div class="drag-handle" title="Drag to reorder">').html(dragSvg())
        );

        // left block: title + item count, beside the handle
        var $body = $('<div class="lcard-body">');
        $body.append($('<h3 class="lcard-title">').text(list.title));
        var meta = (list.tasks_count || 0) + (list.tasks_count === 1 ? ' item' : ' items');
        $body.append($('<div class="lcard-meta">').append($('<span>').text(meta)));
        $card.append($body);

        // money block: larger, centered, toward the right
        var $right = $('<div class="lcard-right">');
        $right.append($('<div class="lcard-total">').text(peso(total)));
        if (list.budget && Number(list.budget) > 0) {
            var budget = Number(list.budget);
            var pct = Math.min(100, (total / budget) * 100);
            $right.append($('<div class="lcard-budget">').text('of ' + peso(budget)));
            var $bar = $('<div class="budget-bar">').toggleClass('over', total > budget);
            $bar.append($('<span>').css('width', pct + '%'));
            $right.append($bar);
        }
        $card.append($right);

        // duplicate button (far right)
        var $dup = $('<button type="button" class="icon-btn lcard-dup" title="Duplicate list" aria-label="Duplicate list">').html(copySvg());
        $dup.on('click', function (e) { e.stopPropagation(); duplicateList(list.id); });
        $card.append($dup);

        $card.on('click', function (e) {
            if ($(e.target).closest('.drag-handle, .lcard-dup').length) return;
            openList(list);
        });
        return $card;
    }

    function duplicateList(id) {
        api('POST', '/api/lists/' + id + '/duplicate').done(function () {
            loadLists();                  // copy lands at the top (newest-first)
            toast('List duplicated');
        }).fail(function () {
            toast('Could not duplicate list', true);
        });
    }

    function initListSortable(disabled) {
        if (sortableLists) { sortableLists.destroy(); sortableLists = null; }
        var el = document.getElementById('listsContainer');
        if (!el || disabled) return;          // no drag-reorder while searching
        sortableLists = Sortable.create(el, {
            handle: '.drag-handle',
            animation: 160,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            onEnd: function () {
                // Only the current page's cards are in the DOM. Splice their new
                // order back into the full list at this page's offset, then send
                // the complete id order so positions stay globally correct.
                var pageIds = $('#listsContainer .lcard').map(function () { return $(this).data('id'); }).get();
                var offset = (state.page - 1) * PAGE_SIZE;
                var prev = state.lists.slice();
                var byId = {};
                state.lists.forEach(function (l) { byId[l.id] = l; });
                var reordered = pageIds.map(function (id) { return byId[id]; });
                Array.prototype.splice.apply(state.lists, [offset, reordered.length].concat(reordered));

                var allIds = state.lists.map(function (l) { return l.id; });
                api('PATCH', '/api/lists/reorder', { ids: allIds }).fail(function () {
                    state.lists = prev;
                    renderLists();
                    toast('Reorder failed — restored', true);
                });
            }
        });
    }

    // =========================================================================
    // LIST detail
    // =========================================================================
    function openList(list) {
        state.list = list;
        $('#detailTitle').text(list.title);
        renderDetailBudget();
        showDetailScreen();

        $('#tasksContainer').html('<div class="skeleton"></div><div class="skeleton"></div>');
        $('#tasksEmpty').prop('hidden', true);
        // Do not auto-focus the add field here — on mobile that pops the keyboard
        // open the moment a list is opened. (We still refocus after an add below.)
        $('#taskText').val('');

        api('GET', '/api/lists/' + list.id + '/tasks').done(function (tasks) {
            state.tasks = tasks;
            renderTasks();
        }).fail(function () {
            $('#tasksContainer').empty();
            toast('Could not load items', true);
        });
    }

    function renderDetailBudget() {
        var b = state.list && state.list.budget;
        $('#detailBudget').text(b && Number(b) > 0 ? 'Budget ' + peso(b) : '');
    }

    function renderTasks() {
        var $c = $('#tasksContainer').empty();
        if (!state.tasks.length) {
            $('#tasksEmpty').prop('hidden', false);
        } else {
            $('#tasksEmpty').prop('hidden', true);
            state.tasks.forEach(function (t) { $c.append(buildTaskRow(t)); });
        }
        initTaskSortable();
        updateTotals();
    }

    function buildTaskRow(t) {
        var $li = $('<li class="task">').attr('data-id', t.id).toggleClass('is-done', !!t.done);

        $li.append($('<div class="drag-handle" title="Drag to reorder">').html(dragSvg()));

        var $check = $('<input type="checkbox" class="check">').prop('checked', !!t.done);
        $check.on('change', function () { toggleTask(t.id, this.checked); });
        $li.append($check);

        var $main = $('<div class="task-main">');
        $main.append($('<div class="task-text">').text(t.text));
        var $sub = $('<div class="task-sub">');
        if (state.type === 'loan' && t.due_date) {
            $sub.append($('<span class="due-badge">').text(formatDue(t.due_date)));
        }
        if (t.note) $sub.append($('<span class="task-note">').text(t.note));
        if ($sub.children().length) $main.append($sub);
        $main.on('click', function () { openTaskEditor(t.id); });
        $li.append($main);

        var amt = Number(t.amount || 0);
        var $amount = $('<div class="task-amount" title="Click to edit price">');
        if (amt > 0) {
            $amount.text(peso(lineTotal(t)));
            if (Number(t.quantity || 1) > 1) {
                $amount.append($('<span class="task-qty">').text(peso(amt) + ' × ' + t.quantity));
            }
        } else {
            $amount.addClass('is-empty');   // shows a clickable "₱ —" placeholder
        }
        // click the price to edit just the price (unit amount), inline
        $amount.on('click', function (e) { e.stopPropagation(); startPriceEdit(t, $amount); });
        $li.append($amount);
        return $li;
    }

    // Inline edit of a task's unit price only (not quantity/note). Optimistic.
    function startPriceEdit(t, $amount) {
        if ($amount.find('.amount-input').length) return;   // already editing
        var current = (t.amount && Number(t.amount)) ? Number(t.amount) : '';
        var $input = $('<input type="number" step="0.01" inputmode="decimal" class="amount-input">').val(current);
        $amount.removeClass('is-empty').empty().append($input);
        $input.trigger('focus').trigger('select');

        var settled = false;
        function rerender() {
            $('.task[data-id="' + t.id + '"]').replaceWith(buildTaskRow(t));
            initTaskSortable();
            updateTotals();
        }
        function commit(save) {
            if (settled) return;
            settled = true;
            var newVal = save ? (parseFloat($input.val()) || 0) : Number(t.amount || 0);
            if (newVal === Number(t.amount || 0)) { rerender(); return; }   // no change / cancel

            var snapshot = t.amount;
            t.amount = newVal;                                  // optimistic
            rerender();
            if (String(t.id).indexOf('tmp-') === 0) return;     // unsaved task; local only
            api('PATCH', '/api/tasks/' + t.id, { amount: newVal }).fail(function () {
                t.amount = snapshot;                            // rollback
                rerender();
                toast('Could not save price — reverted', true);
            });
        }
        $input.on('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); commit(true); }
            else if (e.key === 'Escape') { e.preventDefault(); commit(false); }
        });
        $input.on('blur', function () { commit(true); });
    }

    function formatDue(d) {
        var parts = String(d).slice(0, 10).split('-');
        if (parts.length !== 3) return d;
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        return months[parseInt(parts[1], 10) - 1] + ' ' + parseInt(parts[2], 10) + ', ' + parts[0];
    }

    function updateTotals() {
        var all = 0, done = 0;
        state.tasks.forEach(function (t) {
            var lt = lineTotal(t);
            all += lt;
            if (t.done) done += lt;
        });
        $('#totalAll').text(peso(all));
        $('#totalDone').text(peso(done));
        $('#totalLeft').text(peso(all - done));
    }

    function findTask(id) {
        for (var i = 0; i < state.tasks.length; i++) if (state.tasks[i].id === id) return state.tasks[i];
        return null;
    }

    function initTaskSortable() {
        if (sortableTasks) { sortableTasks.destroy(); sortableTasks = null; }
        var el = document.getElementById('tasksContainer');
        if (!el || !state.tasks.length) return;
        sortableTasks = Sortable.create(el, {
            handle: '.drag-handle',
            animation: 160,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            onEnd: function () {
                var ids = $('#tasksContainer .task').map(function () { return $(this).data('id'); }).get();
                var prev = state.tasks.slice();
                state.tasks.sort(function (a, b) { return ids.indexOf(a.id) - ids.indexOf(b.id); });
                api('PATCH', '/api/lists/' + state.list.id + '/tasks/reorder', { ids: ids }).fail(function () {
                    state.tasks = prev;
                    renderTasks();
                    toast('Reorder failed — restored', true);
                });
            }
        });
    }

    // =========================================================================
    // TASK actions (optimistic)
    // =========================================================================
    function toggleTask(id, done) {
        var t = findTask(id);
        if (!t) return;
        t.done = done;                              // optimistic
        $('.task[data-id="' + id + '"]').toggleClass('is-done', done);
        updateTotals();
        api('PATCH', '/api/tasks/' + id, { done: done }).fail(function () {
            t.done = !done;                         // rollback
            $('.task[data-id="' + id + '"]').toggleClass('is-done', !done)
                .find('.check').prop('checked', !done);
            updateTotals();
            toast('Could not save — reverted', true);
        });
    }

    // Add item (inline)
    $('#addTaskForm').on('submit', function (e) {
        e.preventDefault();
        var text = $.trim($('#taskText').val());
        if (!text || !state.list) return;
        var amount = parseFloat($('#taskAmount').val()) || 0;

        var temp = { id: 'tmp-' + Date.now(), list_id: state.list.id, text: text, amount: amount,
                     quantity: 1, note: null, due_date: null, done: false };
        state.tasks.push(temp);                     // optimistic
        $('#tasksEmpty').prop('hidden', true);
        var $row = buildTaskRow(temp).appendTo('#tasksContainer');
        initTaskSortable();
        updateTotals();
        $('#taskText').val('').focus();
        $('#taskAmount').val('');

        api('POST', '/api/tasks', { list_id: state.list.id, text: text, amount: amount })
            .done(function (saved) {
                temp.id = saved.id;                 // swap temp id → real id
                $row.attr('data-id', saved.id);
                rebindRow($row, temp);
            })
            .fail(function () {
                state.tasks = state.tasks.filter(function (x) { return x !== temp; });
                $row.remove();
                updateTotals();
                if (!state.tasks.length) $('#tasksEmpty').prop('hidden', false);
                toast('Could not add item', true);
            });
    });

    // rebuild a row's event handlers after its id changes
    function rebindRow($row, t) {
        var $new = buildTaskRow(t);
        $row.replaceWith($new);
        initTaskSortable();
    }

    // Edit item (modal)
    var editingTaskId = null;
    function openTaskEditor(id) {
        var t = findTask(id);
        if (!t) return;
        editingTaskId = id;
        $('#editText').val(t.text);
        $('#editAmount').val(t.amount && Number(t.amount) ? t.amount : '');
        $('#editQuantity').val(t.quantity || 1);
        $('#editNote').val(t.note || '');
        var isLoan = state.type === 'loan';
        $('.field-due').prop('hidden', !isLoan);
        $('#editDue').val(t.due_date ? String(t.due_date).slice(0, 10) : '');
        taskModal.show();
    }

    $('#taskForm').on('submit', function (e) {
        e.preventDefault();
        var t = findTask(editingTaskId);
        if (!t) return;
        var snapshot = $.extend({}, t);
        var patch = {
            text: $.trim($('#editText').val()),
            amount: parseFloat($('#editAmount').val()) || 0,
            quantity: parseInt($('#editQuantity').val(), 10) || 1,
            note: $.trim($('#editNote').val()) || null
        };
        if (state.type === 'loan') patch.due_date = $('#editDue').val() || null;
        if (!patch.text) return;

        $.extend(t, patch);                         // optimistic
        $('.task[data-id="' + t.id + '"]').replaceWith(buildTaskRow(t));
        initTaskSortable();
        updateTotals();
        taskModal.hide();

        api('PATCH', '/api/tasks/' + t.id, patch).fail(function () {
            $.extend(t, snapshot);                  // rollback
            $('.task[data-id="' + t.id + '"]').replaceWith(buildTaskRow(t));
            initTaskSortable();
            updateTotals();
            toast('Could not save item — reverted', true);
        });
    });

    $('#deleteTaskBtn').on('click', function () {
        var t = findTask(editingTaskId);
        if (!t) return;
        var idx = state.tasks.indexOf(t);
        state.tasks.splice(idx, 1);                 // optimistic
        $('.task[data-id="' + t.id + '"]').remove();
        updateTotals();
        if (!state.tasks.length) $('#tasksEmpty').prop('hidden', false);
        taskModal.hide();

        api('DELETE', '/api/tasks/' + t.id).fail(function () {
            state.tasks.splice(idx, 0, t);          // rollback
            renderTasks();
            toast('Could not delete — restored', true);
        });
    });

    // =========================================================================
    // LIST actions (modal)
    // =========================================================================
    var editingListId = null;
    $('#addListBtn').on('click', function () {
        editingListId = null;
        $('#listModalTitle').text('New list');
        $('#listTitle').val('');
        $('#listBudget').val('');
        $('#deleteListBtn').prop('hidden', true);
        listModal.show();
        setTimeout(function () { $('#listTitle').focus(); }, 250);
    });

    $('#editListBtn').on('click', function () {
        if (!state.list) return;
        editingListId = state.list.id;
        $('#listModalTitle').text('Edit list');
        $('#listTitle').val(state.list.title);
        $('#listBudget').val(state.list.budget && Number(state.list.budget) ? state.list.budget : '');
        $('#deleteListBtn').prop('hidden', false);
        listModal.show();
    });

    $('#listForm').on('submit', function (e) {
        e.preventDefault();
        var title = $.trim($('#listTitle').val());
        if (!title) return;
        var budget = $('#listBudget').val() === '' ? null : (parseFloat($('#listBudget').val()) || 0);

        if (editingListId) {
            // edit existing — optimistic on the detail header + cache
            var snapshot = { title: state.list.title, budget: state.list.budget };
            state.list.title = title; state.list.budget = budget;
            $('#detailTitle').text(title);
            renderDetailBudget();
            listModal.hide();
            api('PATCH', '/api/lists/' + editingListId, { title: title, budget: budget }).fail(function () {
                state.list.title = snapshot.title; state.list.budget = snapshot.budget;
                $('#detailTitle').text(snapshot.title);
                renderDetailBudget();
                toast('Could not save list — reverted', true);
            });
        } else {
            // create new
            listModal.hide();
            api('POST', '/api/lists', { list_type: state.type, title: title, budget: budget })
                .done(function (list) {
                    list.tasks_count = 0; list.items_total = 0; list.done_total = 0;
                    state.lists.unshift(list);   // new lists land at the top
                    state.query = ''; $('#listSearch').val('');
                    state.page = 1;
                    renderLists();
                    toast('List created');
                })
                .fail(function () { toast('Could not create list', true); });
        }
    });

    $('#deleteListBtn').on('click', function () {
        if (!editingListId) return;
        var id = editingListId;
        listModal.hide();
        // optimistic: go back to overview, drop the list
        state.lists = state.lists.filter(function (l) { return l.id !== id; });
        state.list = null;
        showListsScreen();
        renderLists();
        api('DELETE', '/api/lists/' + id).done(function () {
            toast('List deleted');
        }).fail(function () {
            loadLists();
            toast('Could not delete list', true);
        });
    });

    // =========================================================================
    // icons
    // =========================================================================
    function dragSvg() {
        return '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">' +
            '<circle cx="9" cy="6" r="1.6"/><circle cx="15" cy="6" r="1.6"/>' +
            '<circle cx="9" cy="12" r="1.6"/><circle cx="15" cy="12" r="1.6"/>' +
            '<circle cx="9" cy="18" r="1.6"/><circle cx="15" cy="18" r="1.6"/></svg>';
    }
    function copySvg() {
        return '<svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
            '<rect x="9" y="9" width="11" height="11" rx="2"/>' +
            '<path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>';
    }

    // =========================================================================
    // boot
    // =========================================================================
    $(function () {
        listModal = new bootstrap.Modal('#listModal');
        taskModal = new bootstrap.Modal('#taskModal');
        moveIndicator(0);
        loadLists();
    });

})(jQuery);
