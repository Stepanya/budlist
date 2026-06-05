@extends('budlist.layouts.master')

@section('title', "BUDLIST - $title")

@section('editButton')
  <a href="{{ !empty($archived) ? "/budlist/$type" : "/budlist/$type/archived" }}" class="text-xl text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white" title="{{ !empty($archived) ? 'Show active lists' : 'Show archived lists' }}">
    <i class="fa-solid fa-box-archive"></i>
  </a>
@endsection

@section('returnButton')
  <div>
    <a href="/budlist" class="text-gray-500 dark:text-gray-500 hover:text-gray-900 dark:hover:text-white text-lg">
      <i class="fas fa-arrow-left"></i> Back
    </a>
  </div>
@endsection

@section('content')
  <!-- Main Content -->
  <main class="p-4 space-y-4">
    <!-- No Lists Message -->

    @if($lists->isEmpty())
      <div id="noListsMessage" class="text-center">
        No lists yet. Start by creating one!
      </div>
    @endif

    <!-- Lists Container -->
    @foreach ($lists as $list)
      <div id="listsContainer" class="space-y-4">
        <div
          class="block relative rounded-lg bg-white text-surface shadow-secondary-1 dark:bg-surface-dark dark:text-white list"
          data-twe-ripple-init
          data-twe-ripple-color="light"
          data-id="{{ $list->id }}">

          <div class="absolute top-0 right-0">
            @if (empty($archived))
              <a href="/budlist/{{ $type }}/duplicate/{{ $list->id }}" class="duplicate-btn text-amber-500 hover:text-amber-700 pr-0 p-3 focus:outline-none" title="Duplicate">
                <i class="fa-regular fa-copy text-xl"></i>
              </a>
            @endif
            <form class="inline" method="POST" action="{{ !empty($archived) ? "/budlist/$type/unarchive/$list->id" : "/budlist/$type/archive/$list->id" }}">
              @csrf
              @method('PUT')
              <button class="archive-btn text-blue-500 hover:text-blue-700 pr-0 p-3 focus:outline-none" data-id="{{ $list->id }}" data-title="{{ $list->title }}" title="{{ !empty($archived) ? 'Unarchive' : 'Archive' }}">
                <i class="fa-solid fa-box-archive text-xl"></i>
              </button>
            </form>

            <button class="delete-btn text-red-500 hover:text-red-700 p-3 focus:outline-none " data-id="{{ $list->id }}" data-title="{{ $list->title }}" title="Delete">
              <i class="fas fa-trash-alt text-xl"></i>
            </button>
          </div>

          <i class=""></i>
          <div class="py-5 px-3">
            <h5 class="mb-2 text-xl font-medium leading-tight">{{ $list->title }}</h5>

            @foreach ($list->items as $item)
              @if(!$item->checked)
                <div class="mb-[0.125rem] block min-h-[1.5rem] ps-[1.5rem]">
                  <input
                    class="relative float-left -ms-[1.5rem] me-[6px] mt-[0.15rem] h-[1.125rem] w-[1.125rem] appearance-none rounded-[0.25rem] border-[0.125rem] border-solid border-secondary-500 outline-none before:pointer-events-none before:absolute before:h-[0.875rem] before:w-[0.875rem] before:scale-0 before:rounded-full before:bg-transparent before:opacity-0 before:shadow-checkbox before:shadow-transparent before:content-[''] checked:border-primary checked:bg-primary checked:before:opacity-[0.16] checked:after:absolute checked:after:-mt-px checked:after:ms-[0.25rem] checked:after:block checked:after:h-[0.8125rem] checked:after:w-[0.375rem] checked:after:rotate-45 checked:after:border-[0.125rem] checked:after:border-l-0 checked:after:border-t-0 checked:after:border-solid checked:after:border-white checked:after:bg-transparent checked:after:content-[''] hover:cursor-pointer hover:before:opacity-[0.04] hover:before:shadow-black/60 focus:shadow-none focus:transition-[border-color_0.2s] focus:before:scale-100 focus:before:opacity-[0.12] focus:before:shadow-black/60 focus:before:transition-[box-shadow_0.2s,transform_0.2s] focus:after:absolute focus:after:z-[1] focus:after:block focus:after:h-[0.875rem] focus:after:w-[0.875rem] focus:after:rounded-[0.125rem] focus:after:content-[''] checked:focus:before:scale-100 checked:focus:before:shadow-checkbox checked:focus:before:transition-[box-shadow_0.2s,transform_0.2s] checked:focus:after:-mt-px checked:focus:after:ms-[0.25rem] checked:focus:after:h-[0.8125rem] checked:focus:after:w-[0.375rem] checked:focus:after:rotate-45 checked:focus:after:rounded-none checked:focus:after:border-[0.125rem] checked:focus:after:border-l-0 checked:focus:after:border-t-0 checked:focus:after:border-solid checked:focus:after:border-white checked:focus:after:bg-transparent rtl:float-right dark:border-neutral-400 dark:checked:border-primary dark:checked:bg-primary"
                    type="checkbox"
                    value=""
                    read/>
                  <label
                    class="inline-block ps-[0.15rem] hover:cursor-pointer"
                    for="checkboxDefault">
                    {{ $item->item_name }}
                  </label>
                </div>
              @endif
            @endforeach

          </div>
          @if (isset($list->budget))
            <div class="text-right border-t-2 border-neutral-100 py-2 px-5 text-surface/75 dark:border-white/10 dark:text-neutral-300">
              <span>₱ {{$list->budget}}</span>
            </div>
          @endif
        </div>
      </div>
    @endforeach
  </main>

  <!-- Floating Action Button -->
  <div class="fixed bottom-4 right-4">
    <button 
      id="addButton" 
      type="button"
      data-twe-toggle="modal"
      data-twe-target="#createListModalBackdrop"
      data-twe-ripple-init
      data-twe-ripple-color="light"
      class="flex items-center justify-center w-16 h-16 text-white bg-blue-500 rounded-full shadow-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
      <i class="fas fa-plus text-2xl"></i>
    </button>
  </div>

  <!-- Modal -->
  <div
    data-twe-modal-init
    class="fixed left-0 top-0 z-[1055] hidden h-full w-full overflow-y-auto overflow-x-hidden outline-none"
    id="createListModalBackdrop"
    tabindex="-1"
    aria-labelledby="createListModalBackdrop"
    aria-modal="true"
    role="dialog">
    <div
      data-twe-modal-dialog-ref
      class="pointer-events-none relative flex min-h-[calc(100%-1rem)] w-auto translate-y-[-50px] items-center opacity-0 transition-all duration-300 ease-in-out min-[576px]:mx-auto min-[576px]:mt-7 min-[576px]:min-h-[calc(100%-3.5rem)] min-[576px]:max-w-[500px]">
      <div
        class="pointer-events-auto relative flex w-full flex-col rounded-md border-none bg-white bg-clip-padding text-current shadow-4 outline-none dark:bg-surface-dark m-4">
        <div
          class="flex flex-shrink-0 items-center justify-between rounded-t-md border-b-2 border-neutral-100 p-4 dark:border-white/10">
          <!-- Modal title -->
          <h5
            class="text-xl font-medium leading-normal text-surface dark:text-white"
            id="exampleModalCenterTitle">
            Create New List
          </h5>
          <!-- Close button -->
          <button
            type="button"
            class="box-content rounded-none border-none text-neutral-500 hover:text-neutral-800 hover:no-underline focus:text-neutral-800 focus:opacity-100 focus:shadow-none focus:outline-none dark:text-neutral-400 dark:hover:text-neutral-300 dark:focus:text-neutral-300"
            data-twe-modal-dismiss
            aria-label="Close">
            <span class="[&>svg]:h-6 [&>svg]:w-6">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="currentColor"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M6 18L18 6M6 6l12 12" />
              </svg>
            </span>
          </button>
        </div>

        <!-- Modal body -->
        <div class="relative p-4">
          <form id="add-list-form" action="" method="POST">
            {!! csrf_field() !!}
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="hidden" name="state" value="1">

            <div class="relative mb-3" data-twe-input-wrapper-init>
              <input
                type="text"
                id="listName"
                class="peer block min-h-[auto] w-full rounded border-0 bg-transparent px-3 py-[0.32rem] leading-[2.15] outline-none transition-all duration-200 ease-linear focus:placeholder:opacity-100 peer-focus:text-primary data-[twe-input-state-active]:placeholder:opacity-100 motion-reduce:transition-none dark:text-white dark:placeholder:text-neutral-300 dark:autofill:shadow-autofill dark:peer-focus:text-primary [&:not([data-twe-input-placeholder-active])]:placeholder:opacity-0"
                placeholder="Example label" 
                name="title"
                required/>
              <label
                class="pointer-events-none absolute left-3 top-0 mb-0 max-w-[90%] origin-[0_0] truncate pt-[0.37rem] leading-[2.15] text-neutral-500 transition-all duration-200 ease-out peer-focus:-translate-y-[0.9rem] peer-focus:scale-[0.8] peer-focus:text-primary peer-data-[twe-input-state-active]:-translate-y-[0.9rem] peer-data-[twe-input-state-active]:scale-[0.8] motion-reduce:transition-none dark:text-neutral-400 dark:peer-focus:text-primary"
                >List Name
              </label>
            </div>
            <span class="hidden text-sm text-red-500" id="listNameError"></span>
            <div class="relative mb-3" data-twe-input-wrapper-init>
              <input
                type="number"
                id="listBudget"
                class="peer block min-h-[auto] w-full rounded border-0 bg-transparent px-3 py-[0.32rem] leading-[2.15] outline-none transition-all duration-200 ease-linear focus:placeholder:opacity-100 peer-focus:text-primary data-[twe-input-state-active]:placeholder:opacity-100 motion-reduce:transition-none dark:text-white dark:placeholder:text-neutral-300 dark:autofill:shadow-autofill dark:peer-focus:text-primary [&:not([data-twe-input-placeholder-active])]:placeholder:opacity-0"
                placeholder="Example label"
                name="budget"
                required/>
              <label
                class="pointer-events-none absolute left-3 top-0 mb-0 max-w-[90%] origin-[0_0] truncate pt-[0.37rem] leading-[2.15] text-neutral-500 transition-all duration-200 ease-out peer-focus:-translate-y-[0.9rem] peer-focus:scale-[0.8] peer-focus:text-primary peer-data-[twe-input-state-active]:-translate-y-[0.9rem] peer-data-[twe-input-state-active]:scale-[0.8] motion-reduce:transition-none dark:text-neutral-400 dark:peer-focus:text-primary"
                >List Budget (Optional)
              </label>
            </div>
            <span class="hidden text-sm text-red-500" id="listBudgetError"></span>
          </form>
        </div>

        <!-- Modal footer -->
        <div
          class="flex flex-shrink-0 flex-wrap items-center justify-end rounded-b-md border-t-2 border-neutral-100 p-4 dark:border-white/10">
          <button
            type="button"
            class="inline-block rounded bg-primary-100 px-6 pb-2 pt-2.5 text-xs font-medium uppercase leading-normal text-primary-700 transition duration-150 ease-in-out hover:bg-primary-accent-200 focus:bg-primary-accent-200 focus:outline-none focus:ring-0 active:bg-primary-accent-200 dark:bg-primary-300 dark:hover:bg-primary-400 dark:focus:bg-primary-400 dark:active:bg-primary-400"
            data-twe-modal-dismiss
            data-twe-ripple-init
            data-twe-ripple-color="light">
            Close
          </button>
          <button
            type="button"
            id="submit-button"
            class="ms-1 inline-block rounded bg-primary px-6 pb-2 pt-2.5 text-xs font-medium uppercase leading-normal text-white shadow-primary-3 transition duration-150 ease-in-out hover:bg-primary-accent-300 hover:shadow-primary-2 focus:bg-primary-accent-300 focus:shadow-primary-2 focus:outline-none focus:ring-0 active:bg-primary-600 active:shadow-primary-2 dark:shadow-black/30 dark:hover:shadow-dark-strong dark:focus:shadow-dark-strong dark:active:shadow-dark-strong"
            data-twe-ripple-init
            data-twe-ripple-color="light">
            Done
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Action Modal -->
  <div
  data-twe-modal-init
  class="fixed left-0 top-0 z-[1055] hidden h-full w-full overflow-y-auto overflow-x-hidden outline-none"
  id="listActionModal"
  tabindex="-1"
  aria-labelledby="listActionModal"
  aria-modal="true"
  role="dialog">
    <div
      data-twe-modal-dialog-ref
      class="pointer-events-none relative flex min-h-[calc(100%-1rem)] w-auto translate-y-[-50px] items-center opacity-0 transition-all duration-300 ease-in-out min-[576px]:mx-auto min-[576px]:mt-7 min-[576px]:min-h-[calc(100%-3.5rem)] min-[576px]:max-w-[500px]">
      <div class="pointer-events-auto relative flex w-full flex-col rounded-md border-none bg-white bg-clip-padding text-current shadow-4 outline-none dark:bg-surface-dark m-4">
        <!-- Modal header -->
        <div class="flex flex-shrink-0 items-center justify-center rounded-t-md border-b-2 border-neutral-100 p-4 dark:border-white/10">
          <h5 class="text-xl font-medium leading-normal text-center text-surface dark:text-white" id="modalItemTitle">
            <!-- Dynamic item title will be inserted here -->
            Item Title
          </h5>
        </div>

        <!-- Modal body -->
        <div class="relative flex flex-col items-center justify-center gap-4 p-4">

          <button
            type="button"
            class="inline-block w-full rounded bg-secondary px-6 pb-5 pt-5 text-sm font-medium uppercase leading-normal text-white shadow-primary-3 transition duration-150 ease-in-out hover:bg-secondary-accent-300 focus:bg-primary-accent-300 focus:outline-none focus:ring-0 active:bg-primary-600 dark:shadow-black/30 dark:hover:shadow-dark-strong dark:focus:shadow-dark-strong dark:active:shadow-dark-strong"
            id="cancelButton"
            data-list-item-id=""
          >
            Cancel
          </button>

          <!-- Delete Button -->
          <form id="delete-form" class="w-full" action="" method="POST">
              @csrf
              @method('DELETE')
              <button
                type="submit"
                class="inline-block w-full rounded bg-danger px-6 pb-5 pt-5 text-sm text-center font-medium uppercase leading-normal text-white shadow-danger-3 transition duration-150 ease-in-out hover:bg-danger-accent-300 focus:bg-danger-accent-300 focus:outline-none focus:ring-0 active:bg-danger-600 dark:shadow-black/30 dark:hover:shadow-dark-strong dark:focus:shadow-dark-strong dark:active:shadow-dark-strong"
                id="confirmDeleteButton">
                Delete
              </button>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('js')
<script>
  $(document).ready(function () {
    
    const listActionModal = new twe.Modal(document.getElementById("listActionModal"));

    $('.cancelButton').click(function(e) {
      listActionModal.close()
    })

    $('.duplicate-btn').click(function(e) {
      e.stopPropagation()
    })

    $('.archive-btn').click(function(e) {
      e.stopPropagation()
    })

    $('.delete-btn').click(function(e) {
      e.stopPropagation()

      const listId = $(this).data('id')
      const title = $(this).data('title')
      
      $('#modalItemTitle').text(`Are you sure you want to delete "${title}"?`)
      $('#delete-form').attr('action', `/budlist/{{ $type }}/${listId}`)
      
      listActionModal.show()
      
    })

    $('.list').click(function() {
      const id = $(this).data('id');
      if (id) {
          window.location.href = `{{ $type }}/${id}`;
      } else {
          console.error('Data-id is missing.');
      }
    })

    $('#submit-button').click(function() {
      // Clear previous errors
      $('#listNameError, #listBudgetError').hide();

      let isValid = true;
      const listName = $('#listName').val().trim();
      const listBudget = $('#listBudget').val().trim();

      // Validate list name (required)
      if (listName === '') {
        $('#listNameError').text('Please enter a valid name').fadeIn('fast');
        isValid = false;
      }

      // Validate list budget (optional but must be a valid number if entered)
     
      if (listBudget !== '' && isNaN(listBudget)) {
        $('#listBudgetError').text('Please enter a valid number').fadeIn('fast');
        isValid = false;
      }

      // If valid, submit the form
      if (isValid) {
        $('#add-list-form').submit();
      }
    })
  });
</script>
@endsection
