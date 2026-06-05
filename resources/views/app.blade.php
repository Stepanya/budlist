<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Budlist</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">

    {{-- Apply the saved theme before first paint to avoid a flash of the wrong theme. --}}
    <script>
        (function () {
            try {
                var t = localStorage.getItem('budlist-theme');
                if (t !== 'light' && t !== 'dark') {
                    t = window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
                }
                document.documentElement.setAttribute('data-theme', t);
            } catch (e) { /* keep default dark */ }
        })();
    </script>

    {{-- Self-hosted (no CDN): fonts, Bootstrap 5.3.3, paginationjs, app styles --}}
    <link href="{{ asset('vendor/css/fonts.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/css/pagination.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}?v=4" rel="stylesheet">
</head>
<body>
    {{-- Ambient background glows --}}
    <div class="bg-glows" aria-hidden="true">
        <span class="glow glow-1"></span>
        <span class="glow glow-2"></span>
        <span class="glow glow-3"></span>
    </div>

    <div class="app-shell">
        <header class="app-header glass">
            <div class="brand">
                <span class="brand-mark">₱</span>
                <h1 class="brand-name">Budlist</h1>
            </div>
            <div class="header-actions">
                <button id="themeToggle" class="icon-btn theme-toggle" type="button" aria-label="Toggle theme" title="Toggle theme">
                    <svg class="i-sun" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                    <svg class="i-moon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
                </button>
                <form method="POST" action="{{ route('logout') }}" class="logout-form">
                    @csrf
                    <button class="icon-btn" type="submit" aria-label="Sign out" title="Sign out">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                    </button>
                </form>
            </div>
        </header>

        {{-- Type tabs --}}
        <nav class="tabs glass" id="tabs">
            <button class="tab is-active" data-type="budget" type="button">Budget</button>
            <button class="tab" data-type="loan" type="button">Loan</button>
            <button class="tab" data-type="shopping" type="button">Shopping</button>
            <span class="tab-indicator" aria-hidden="true"></span>
        </nav>

        <main class="app-main">
            {{-- SCREEN 1: lists overview --}}
            <section id="screen-lists" class="screen is-active">
                <div class="screen-head">
                    <h2 id="listsHeading" class="screen-title">Budget</h2>
                    <div class="search-box">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                        <input type="search" id="listSearch" class="search-input" placeholder="Search lists…" autocomplete="off" aria-label="Search lists">
                    </div>
                    <button id="addListBtn" class="btn-pill" type="button">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                        <span class="btn-pill-label">New list</span>
                    </button>
                </div>
                <div id="listsContainer" class="cards"></div>
                <div id="listsPager" class="pager"></div>
                <div id="listsEmpty" class="empty-state" hidden>
                    <p>No lists here yet.</p>
                    <span>Tap <strong>New list</strong> to create one.</span>
                </div>
            </section>

            {{-- SCREEN 2: a single list's items --}}
            <section id="screen-detail" class="screen">
                <div class="detail-head glass">
                    <button id="backBtn" class="icon-btn" type="button" aria-label="Back">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                    </button>
                    <div class="detail-title-wrap">
                        <h2 id="detailTitle" class="detail-title">List</h2>
                        <p id="detailBudget" class="detail-budget"></p>
                    </div>
                    <button id="editListBtn" class="icon-btn" type="button" aria-label="Edit list">
                        <svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                    </button>
                </div>

                <form id="addTaskForm" class="add-task glass" autocomplete="off">
                    <input type="text" id="taskText" class="add-task-text" placeholder="Add an item…" required>
                    <input type="number" step="0.01" id="taskAmount" class="add-task-amount" placeholder="₱0">
                    <button class="add-task-btn" type="submit" aria-label="Add item">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                    </button>
                </form>

                <ul id="tasksContainer" class="tasks"></ul>
                <div id="tasksEmpty" class="empty-state" hidden>
                    <p>This list is empty.</p>
                    <span>Add your first item above.</span>
                </div>

                <div class="totals glass" id="totalsBar">
                    <div class="total">
                        <small>ALL</small>
                        <span id="totalAll">₱0.00</span>
                    </div>
                    <div class="total">
                        <small>TICKED</small>
                        <span id="totalDone">₱0.00</span>
                    </div>
                    <div class="total">
                        <small>LEFT</small>
                        <span id="totalLeft">₱0.00</span>
                    </div>
                </div>
            </section>
        </main>
    </div>

    {{-- List create/edit modal --}}
    <div class="modal fade" id="listModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-modal">
                <form id="listForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="listModalTitle">New list</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label class="field-label" for="listTitle">List name</label>
                        <input type="text" class="field-input" id="listTitle" required maxlength="255" placeholder="e.g. Nov 30">
                        <label class="field-label" for="listBudget">Budget <span class="muted">(optional)</span></label>
                        <input type="number" step="0.01" min="0" class="field-input" id="listBudget" placeholder="₱0">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-ghost btn-danger-ghost" id="deleteListBtn" hidden>Delete</button>
                        <button type="submit" class="btn-solid">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Task edit modal --}}
    <div class="modal fade" id="taskModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-modal">
                <form id="taskForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label class="field-label" for="editText">Item</label>
                        <input type="text" class="field-input" id="editText" required maxlength="255">
                        <div class="field-row">
                            <div>
                                <label class="field-label" for="editAmount">Price</label>
                                <input type="number" step="0.01" class="field-input" id="editAmount" placeholder="₱0">
                            </div>
                            <div>
                                <label class="field-label" for="editQuantity">Qty</label>
                                <input type="number" step="1" min="1" class="field-input" id="editQuantity" placeholder="1">
                            </div>
                        </div>
                        <label class="field-label field-due" for="editDue" hidden>Due date</label>
                        <input type="date" class="field-input field-due" id="editDue" hidden>
                        <label class="field-label" for="editNote">Note <span class="muted">(optional)</span></label>
                        <input type="text" class="field-input" id="editNote" placeholder="Add a note…">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-ghost btn-danger-ghost" id="deleteTaskBtn">Delete</button>
                        <button type="submit" class="btn-solid">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div id="toast" class="toast-msg" role="status" aria-live="polite"></div>

    {{-- Self-hosted (no CDN): jQuery 3.7.1, Bootstrap 5.3.3 bundle, SortableJS, paginationjs --}}
    <script src="{{ asset('vendor/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('vendor/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/js/Sortable.min.js') }}"></script>
    <script src="{{ asset('vendor/js/pagination.min.js') }}"></script>
    <script src="{{ asset('js/app.js') }}?v=4"></script>
</body>
</html>
