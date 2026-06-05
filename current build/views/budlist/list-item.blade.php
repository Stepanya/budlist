@extends('budlist.layouts.master')

@section('title', $title)

@section('returnButton')
  <div>
    <a href="/budlist/{{ $type }}" class="text-gray-500 dark:text-gray-500 hover:text-gray-900 dark:hover:text-white text-lg">
      <i class="fas fa-arrow-left"></i> Back
    </a>
  </div>
@endsection

@section('budgetText')
  @if(!empty($list->budget) && $list->budget > 0)
    <span class="mr-2">BUDGET</span> ₱ {{ number_format($list->budget, 2) }}
  @endif
@endsection

@section('editButton')
  <button 
    id="editListButton"
    data-twe-toggle="modal"
    data-twe-target="#editListModal" 
    data-twe-ripple-init 
    data-twe-ripple-color="light" 
    class="mr-3 text-xl rounded-full">
    <i class="fas fa-pen"></i>
  </button>
@endsection

@section('content')
<!-- Main Content -->
<main class="p-4 space-y-4 mb-20">
  <!-- No Lists Message -->
  
  @if($list->items->isEmpty())
  <div id="noListsMessage" class="text-center">
    Your list is empty
  </div>
  @endif
  
  <ul class="w-full text-surface dark:text-white">
    @foreach ($list->items as $item)
      <li 
        class="w-full listItem border-b-2 pt-2 pb-3 px-2 border-neutral-100 dark:border-white/10 flex items-center justify-between h-[70px] hover:cursor-pointer "
        data-twe-ripple-init
        data-twe-ripple-color="light"
        data-list-item-id="{{ $item->id }}">

        <div class="block min-h-[1.5rem] ps-[1.5rem]">
          <input
            id="item_{{ $item->id }}"
            class="item-checkbox scale-125 relative top-1 -ms-[1.5rem] me-[6px] mt-[0.15rem] h-[1.125rem] w-[1.125rem] appearance-none rounded-[0.25rem] border-[0.125rem] border-solid border-secondary-500 outline-none before:pointer-events-none before:absolute before:h-[0.875rem] before:w-[0.875rem] before:scale-0 before:rounded-full before:bg-transparent before:opacity-0 before:shadow-checkbox before:shadow-transparent before:content-[''] checked:border-primary checked:bg-primary checked:before:opacity-[0.16] checked:after:absolute checked:after:-mt-px checked:after:ms-[0.25rem] checked:after:block checked:after:h-[0.8125rem] checked:after:w-[0.375rem] checked:after:rotate-45 checked:after:border-[0.125rem] checked:after:border-l-0 checked:after:border-t-0 checked:after:border-solid checked:after:border-white checked:after:bg-transparent checked:after:content-[''] hover:cursor-pointer hover:before:opacity-[0.04] hover:before:shadow-black/60 focus:shadow-none focus:transition-[border-color_0.2s] focus:before:scale-100 focus:before:opacity-[0.12] focus:before:shadow-black/60 focus:before:transition-[box-shadow_0.2s,transform_0.2s] focus:after:absolute focus:after:z-[1] focus:after:block focus:after:h-[0.875rem] focus:after:w-[0.875rem] focus:after:rounded-[0.125rem] focus:after:content-[''] checked:focus:before:scale-100 checked:focus:before:shadow-checkbox checked:focus:before:transition-[box-shadow_0.2s,transform_0.2s] checked:focus:after:-mt-px checked:focus:after:ms-[0.25rem] checked:focus:after:h-[0.8125rem] checked:focus:after:w-[0.375rem] checked:focus:after:rotate-45 checked:focus:after:rounded-none checked:focus:after:border-[0.125rem] checked:focus:after:border-l-0 checked:focus:after:border-t-0 checked:focus:after:border-solid checked:focus:after:border-white checked:focus:after:bg-transparent rtl:float-right dark:border-neutral-400 dark:checked:border-primary dark:checked:bg-primary"
            type="checkbox"
            value="{{ $item->id }}"
            data-checked="{{ $item->checked }}"
            {{ $item->checked ? 'checked' : '' }}/>
          <label
            class="inline-block ps-[0.15rem] item-text"
            for="item_{{ $item->id }}">
          {{ $item->item_name }}
            <span class="text-info">
              @if($type === 'loan' && isset($item->date))
                | {{ \Carbon\Carbon::parse($item->date)->format('M d, Y') }}
              @endif
            </span>
          </label>

          <div class="text-sm text-gray-400 dark:text-red-500 ml-2 item-text">
            <!-- Note (muted text) -->
            <p>{{ $item->note }}</p>
          </div>
        </div>
        
        <div 
          class="text-lg font-semibold dark:text-white text-right item-text"
          id="listPrice"
          data-twe-toggle="modal"
          data-twe-target="#priceModalBackdrop"
          data-twe-ripple-init
          data-twe-ripple-color="light"
          data-list-item-id="{{ $item->id }}"
          data-price="{{ $item->price }}"
          data-quantity="{{ $item->quantity }}" 
        >
          <!-- Price on the right -->
          @if(!empty($item->price) && $item->price > 0)
            ₱ {{ number_format($item->price * $item->quantity, 2) }}
          @endif

          @if($item->quantity > 1)
            <!-- Muted text with dynamic calculation -->
            <div id="calculatedPrice" class="text-sm text-gray-500 mt-1">
              ₱ <span>{{ number_format($item->price, 2) }} x {{ $item->quantity }}</span> <!-- Price x Quantity -->
            </div>
          @endif
        </div>
      </li>
    @endforeach
  </ul>

</main>

<footer class="bg-zinc-50 text-center dark:bg-neutral-700 lg:text-left w-full fixed bottom-0">
  <!-- Floating Action Button -->
  <div class="absolute top-[-20px] left-1/2 transform -translate-x-1/2">
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
    
  <div class="flex justify-between items-center p-4">
    <!-- Left side text (stacked) -->
    <div class="flex flex-col text-left">
      <div class="inline-flex space-x-2">
        <small class="text-xs text-surface text-gray-400">ALL</small>
        <span class="text-surface dark:text-white" id="totalPriceText">₱ {{ number_format( $totalPrice, 2) }}</span>
      </div>
      <div class="inline-flex space-x-2">
        <small class="text-xs text-surface text-gray-400">TICK</small>
        <span class="text-surface dark:text-white" id="checkedPriceText">₱ {{ number_format( $checkedPrice, 2) }}</span>
      </div>
    </div>

    <!-- Right side text -->
    <span class="text-surface dark:text-white" id="uncheckedPriceText">₱ {{ number_format( $uncheckedPrice, 2) }}</span>
  </div>
</footer>

<!-- Edit List Modal -->
<div
data-twe-modal-init
class="fixed left-0 top-0 z-[1055] hidden h-full w-full overflow-y-auto overflow-x-hidden outline-none"
id="editListModal"
tabindex="-1"
aria-labelledby="editListModal"
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
        Edit List
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
      <form id="edit-list-form" action="/budlist/budget/{{ $list->id }}" method="POST">
        {!! csrf_field() !!}
        @method('PUT')

        <input type="hidden" name="state" value="1">

        <div class="relative mb-3" data-twe-input-wrapper-init>
          <input
            type="text"
            id="listName"
            class="peer block min-h-[auto] w-full rounded border-0 bg-transparent px-3 py-[0.32rem] leading-[2.15] outline-none transition-all duration-200 ease-linear focus:placeholder:opacity-100 peer-focus:text-primary data-[twe-input-state-active]:placeholder:opacity-100 motion-reduce:transition-none dark:text-white dark:placeholder:text-neutral-300 dark:autofill:shadow-autofill dark:peer-focus:text-primary [&:not([data-twe-input-placeholder-active])]:placeholder:opacity-0"
            placeholder="Example label" 
            name="title"
            value="{{ $list->title }}"
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
            value="{{ $list->budget }}"
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
        id="edit-list-submit-button"
        class="ms-1 inline-block rounded bg-primary px-6 pb-2 pt-2.5 text-xs font-medium uppercase leading-normal text-white shadow-primary-3 transition duration-150 ease-in-out hover:bg-primary-accent-300 hover:shadow-primary-2 focus:bg-primary-accent-300 focus:shadow-primary-2 focus:outline-none focus:ring-0 active:bg-primary-600 active:shadow-primary-2 dark:shadow-black/30 dark:hover:shadow-dark-strong dark:focus:shadow-dark-strong dark:active:shadow-dark-strong"
        data-twe-ripple-init
        data-twe-ripple-color="light">
        Done
      </button>
    </div>
  </div>
</div>
</div>

<!--Add List Item Modal -->
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
    <div class="pointer-events-auto relative flex w-full flex-col rounded-md border-none bg-white bg-clip-padding text-current shadow-4 outline-none dark:bg-surface-dark m-4">
      <div class="flex flex-shrink-0 items-center justify-between rounded-t-md border-b-2 border-neutral-100 p-4 dark:border-white/10">
        <!-- Modal title -->
        <h5 class="text-xl font-medium leading-normal text-surface dark:text-white" id="exampleModalCenterTitle">
        Add New Item
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
        <form id="addListItemForm" action="/budlist/budget/list/{{ $id }}" method="POST">
          {!! csrf_field() !!}
          <input type="hidden" name="list_id" value="{{ $id }}">

          <div class="grid {{ $type === "loan" ? "grid-cols-2" : "" }} gap-3">
            <!-- Item Name Field -->
            <div class="relative mb-3 " data-twe-input-wrapper-init>
              <input
                type="text"
                id="itemName"
                class="peer block min-h-[auto] w-full rounded border-0 bg-transparent px-3 py-[0.32rem] leading-[2.15] outline-none transition-all duration-200 ease-linear focus:placeholder:opacity-100 peer-focus:text-primary data-[twe-input-state-active]:placeholder:opacity-100 motion-reduce:transition-none dark:text-white dark:placeholder:text-neutral-300 dark:autofill:shadow-autofill dark:peer-focus:text-primary [&:not([data-twe-input-placeholder-active])]:placeholder:opacity-0"
                placeholder="Example label" 
                name="item_name"
                required/>
                <label
                class="pointer-events-none absolute left-3 top-0 mb-0 max-w-[90%] origin-[0_0] truncate pt-[0.37rem] leading-[2.15] text-neutral-500 transition-all duration-200 ease-out peer-focus:-translate-y-[0.9rem] peer-focus:scale-[0.8] peer-focus:text-primary peer-data-[twe-input-state-active]:-translate-y-[0.9rem] peer-data-[twe-input-state-active]:scale-[0.8] motion-reduce:transition-none dark:text-neutral-400 dark:peer-focus:text-primary"
                >Item Name
              </label>
              <span class="hidden text-sm text-red-500 error-message" id="itemNameError"></span>
            </div>
           <!-- Item Date Field -->
            @if($type === 'loan')
              <div class="relative mb-3 " data-twe-input-wrapper-init>
                <input
                    type="date"
                    id="itemDate"
                    class="peer block min-h-[auto] w-full rounded border-0 bg-transparent px-3 py-[0.32rem] leading-[2.15] outline-none transition-all duration-200 ease-linear focus:placeholder:opacity-100 peer-focus:text-primary data-[twe-input-state-active]:placeholder:opacity-100 motion-reduce:transition-none dark:text-white dark:placeholder:text-neutral-300 dark:autofill:shadow-autofill dark:peer-focus:text-primary [&:not([data-twe-input-placeholder-active])]:placeholder:opacity-0"
                    placeholder="Example label" 
                    name="date"
                    required/>
                <label
                class="pointer-events-none absolute left-3 top-0 mb-0 max-w-[90%] origin-[0_0] truncate pt-[0.37rem] leading-[2.15] text-neutral-500 transition-all duration-200 ease-out peer-focus:-translate-y-[0.9rem] peer-focus:scale-[0.8] peer-focus:text-primary peer-data-[twe-input-state-active]:-translate-y-[0.9rem] peer-data-[twe-input-state-active]:scale-[0.8] motion-reduce:transition-none dark:text-neutral-400 dark:peer-focus:text-primary"
                >Date To Be Paid
                </label>
                <span class="hidden text-sm text-red-500 error-message" id="itemDateError"></span>
              </div>
            @endif
          </div>
          
          
          <div class="grid grid-cols-3 gap-3">
            <!-- Price Field -->
           
            <div class="relative mb-3 col-span-2" data-twe-input-wrapper-init>
              <input
              type="number"
              id="price"
              class="peer block min-h-[auto] w-full rounded border-0 bg-transparent px-3 py-[0.32rem] leading-[2.15] outline-none transition-all duration-200 ease-linear focus:placeholder:opacity-100 peer-focus:text-primary data-[twe-input-state-active]:placeholder:opacity-100 motion-reduce:transition-none dark:text-white dark:placeholder:text-neutral-300 dark:autofill:shadow-autofill dark:peer-focus:text-primary [&:not([data-twe-input-placeholder-active])]:placeholder:opacity-0"
              placeholder="Example label"
              name="price"
              value="0"
              required/>
              <label
              class="pointer-events-none absolute left-3 top-0 mb-0 max-w-[90%] origin-[0_0] truncate pt-[0.37rem] leading-[2.15] text-neutral-500 transition-all duration-200 ease-out peer-focus:-translate-y-[0.9rem] peer-focus:scale-[0.8] peer-focus:text-primary peer-data-[twe-input-state-active]:-translate-y-[0.9rem] peer-data-[twe-input-state-active]:scale-[0.8] motion-reduce:transition-none dark:text-neutral-400 dark:peer-focus:text-primary"
              >Price
            </label>
            </div>
            <span class="hidden text-sm text-red-500 error-message" id="priceError"></span>
           
            <!-- Quantity Field -->
            
            <div class="relative mb-3" data-twe-input-wrapper-init>
              <input
                type="number"
                id="quantity"
                class="peer block min-h-[auto] w-full rounded border-0 bg-transparent px-3 py-[0.32rem] leading-[2.15] outline-none transition-all duration-200 ease-linear focus:placeholder:opacity-100 peer-focus:text-primary data-[twe-input-state-active]:placeholder:opacity-100 motion-reduce:transition-none dark:text-white dark:placeholder:text-neutral-300 dark:autofill:shadow-autofill dark:peer-focus:text-primary [&:not([data-twe-input-placeholder-active])]:placeholder:opacity-0"
                placeholder="Example label"
                name="quantity"
                min="1"
                required/>
                <label
                class="pointer-events-none absolute left-3 top-0 mb-0 max-w-[90%] origin-[0_0] truncate pt-[0.37rem] leading-[2.15] text-neutral-500 transition-all duration-200 ease-out peer-focus:-translate-y-[0.9rem] peer-focus:scale-[0.8] peer-focus:text-primary peer-data-[twe-input-state-active]:-translate-y-[0.9rem] peer-data-[twe-input-state-active]:scale-[0.8] motion-reduce:transition-none dark:text-neutral-400 dark:peer-focus:text-primary"
                >Quantity
              </label>
            </div>
            <span class="hidden text-sm text-red-500 error-message" id="quantityError"></span>
            
          </div>
          <!-- Note Field -->
          <div class="relative mb-3" data-twe-input-wrapper-init>
              <input
                type="text"
                id="note"
                class="peer block min-h-[auto] w-full rounded border-0 bg-transparent px-3 py-[0.32rem] leading-[2.15] outline-none transition-all duration-200 ease-linear focus:placeholder:opacity-100 peer-focus:text-primary data-[twe-input-state-active]:placeholder:opacity-100 motion-reduce:transition-none dark:text-white dark:placeholder:text-neutral-300 dark:autofill:shadow-autofill dark:peer-focus:text-primary [&:not([data-twe-input-placeholder-active])]:placeholder:opacity-0"
                placeholder="Example label" 
                name="note"
                required/>
              <label
                class="pointer-events-none absolute left-3 top-0 mb-0 max-w-[90%] origin-[0_0] truncate pt-[0.37rem] leading-[2.15] text-neutral-500 transition-all duration-200 ease-out peer-focus:-translate-y-[0.9rem] peer-focus:scale-[0.8] peer-focus:text-primary peer-data-[twe-input-state-active]:-translate-y-[0.9rem] peer-data-[twe-input-state-active]:scale-[0.8] motion-reduce:transition-none dark:text-neutral-400 dark:peer-focus:text-primary"
                >Note
              </label>
          </div>
          <span class="hidden text-sm text-red-500 error-message" id="noteError"></span>
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

<!--Edit List Item Modal -->
<div
  data-twe-modal-init
  class="fixed left-0 top-0 z-[1055] hidden h-full w-full overflow-y-auto overflow-x-hidden outline-none"
  id="itemEditModal"
  tabindex="-1"
  aria-labelledby="itemEditModal"
  aria-modal="true"
  role="dialog">
  <div
    data-twe-modal-dialog-ref
    class="pointer-events-none relative flex min-h-[calc(100%-1rem)] w-auto translate-y-[-50px] items-center opacity-0 transition-all duration-300 ease-in-out min-[576px]:mx-auto min-[576px]:mt-7 min-[576px]:min-h-[calc(100%-3.5rem)] min-[576px]:max-w-[500px]">
    <div class="pointer-events-auto relative flex w-full flex-col rounded-md border-none bg-white bg-clip-padding text-current shadow-4 outline-none dark:bg-surface-dark m-4">
      <div class="flex flex-shrink-0 items-center justify-between rounded-t-md border-b-2 border-neutral-100 p-4 dark:border-white/10">
        <!-- Modal title -->
        <h5 class="text-xl font-medium leading-normal text-surface dark:text-white" id="exampleModalCenterTitle">
        Edit Item
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
        <form id="editListItemForm" action="" method="POST">
          {!! csrf_field() !!}
          @method('PUT')

          <input type="hidden" name="list_id" value="{{ $id }}">
          
          <div class="grid {{ $type === "loan" ? "grid-cols-2" : "" }} gap-3">
            <!-- Item Name Field -->
            <div class="relative mb-3" data-twe-input-wrapper-init>
              <input
                type="text"
                id="itemNameEdit"
                class="peer block min-h-[auto] w-full rounded border-0 bg-transparent px-3 py-[0.32rem] leading-[2.15] outline-none transition-all duration-200 ease-linear focus:placeholder:opacity-100 peer-focus:text-primary data-[twe-input-state-active]:placeholder:opacity-100 motion-reduce:transition-none dark:text-white dark:placeholder:text-neutral-300 dark:autofill:shadow-autofill dark:peer-focus:text-primary [&:not([data-twe-input-placeholder-active])]:placeholder:opacity-0"
                placeholder="Example label" 
                name="item_name"
                required/>
                <label
                class="pointer-events-none absolute left-3 top-0 mb-0 max-w-[90%] origin-[0_0] truncate pt-[0.37rem] leading-[2.15] text-neutral-500 transition-all duration-200 ease-out peer-focus:-translate-y-[0.9rem] peer-focus:scale-[0.8] peer-focus:text-primary peer-data-[twe-input-state-active]:-translate-y-[0.9rem] peer-data-[twe-input-state-active]:scale-[0.8] motion-reduce:transition-none dark:text-neutral-400 dark:peer-focus:text-primary"
                >Item Name
              </label>
              <span class="hidden text-sm text-red-500 error-message" id=""></span>
            </div>
            @if($type === 'loan')
              <!-- Item Date Field -->
              <div class="relative mb-3 " data-twe-input-wrapper-init>
                <input
                    type="date"
                    id="itemDate"
                    class="peer block min-h-[auto] w-full rounded border-0 bg-transparent px-3 py-[0.32rem] leading-[2.15] outline-none transition-all duration-200 ease-linear focus:placeholder:opacity-100 peer-focus:text-primary data-[twe-input-state-active]:placeholder:opacity-100 motion-reduce:transition-none dark:text-white dark:placeholder:text-neutral-300 dark:autofill:shadow-autofill dark:peer-focus:text-primary [&:not([data-twe-input-placeholder-active])]:placeholder:opacity-0"
                    placeholder="Example label" 
                    name="date"
                    required/>
                <label
                class="pointer-events-none absolute left-3 top-0 mb-0 max-w-[90%] origin-[0_0] truncate pt-[0.37rem] leading-[2.15] text-neutral-500 transition-all duration-200 ease-out peer-focus:-translate-y-[0.9rem] peer-focus:scale-[0.8] peer-focus:text-primary peer-data-[twe-input-state-active]:-translate-y-[0.9rem] peer-data-[twe-input-state-active]:scale-[0.8] motion-reduce:transition-none dark:text-neutral-400 dark:peer-focus:text-primary"
                >Date To Be Paid
                </label>
                <span class="hidden text-sm text-red-500 error-message" id="itemDateError"></span>
              </div>
            @endif
          </div>
          <div class="grid grid-cols-3 gap-3">
            <!-- Price Field -->
            <div class="relative col-span-2">
              <div class="relative mb-3" data-twe-input-wrapper-init>
                <input
                type="number"
                id="priceEdit"
                class="peer block min-h-[auto] w-full rounded border-0 bg-transparent px-3 py-[0.32rem] leading-[2.15] outline-none transition-all duration-200 ease-linear focus:placeholder:opacity-100 peer-focus:text-primary data-[twe-input-state-active]:placeholder:opacity-100 motion-reduce:transition-none dark:text-white dark:placeholder:text-neutral-300 dark:autofill:shadow-autofill dark:peer-focus:text-primary [&:not([data-twe-input-placeholder-active])]:placeholder:opacity-0"
                placeholder="Example label"
                name="price"
                value="0"
                required/>
                <label
                class="pointer-events-none absolute left-3 top-0 mb-0 max-w-[90%] origin-[0_0] truncate pt-[0.37rem] leading-[2.15] text-neutral-500 transition-all duration-200 ease-out peer-focus:-translate-y-[0.9rem] peer-focus:scale-[0.8] peer-focus:text-primary peer-data-[twe-input-state-active]:-translate-y-[0.9rem] peer-data-[twe-input-state-active]:scale-[0.8] motion-reduce:transition-none dark:text-neutral-400 dark:peer-focus:text-primary"
                >Price
              </label>
              </div>
              <span class="hidden text-sm text-red-500 error-message" id=""></span>
            </div>
            <!-- Quantity Field -->
            <div class="relative">
              <div class="relative mb-3" data-twe-input-wrapper-init>
                <input
                  type="number"
                  id="quantityEdit"
                  class="peer block min-h-[auto] w-full rounded border-0 bg-transparent px-3 py-[0.32rem] leading-[2.15] outline-none transition-all duration-200 ease-linear focus:placeholder:opacity-100 peer-focus:text-primary data-[twe-input-state-active]:placeholder:opacity-100 motion-reduce:transition-none dark:text-white dark:placeholder:text-neutral-300 dark:autofill:shadow-autofill dark:peer-focus:text-primary [&:not([data-twe-input-placeholder-active])]:placeholder:opacity-0"
                  placeholder="Example label"
                  name="quantity"
                  required/>
                  <label
                  class="pointer-events-none absolute left-3 top-0 mb-0 max-w-[90%] origin-[0_0] truncate pt-[0.37rem] leading-[2.15] text-neutral-500 transition-all duration-200 ease-out peer-focus:-translate-y-[0.9rem] peer-focus:scale-[0.8] peer-focus:text-primary peer-data-[twe-input-state-active]:-translate-y-[0.9rem] peer-data-[twe-input-state-active]:scale-[0.8] motion-reduce:transition-none dark:text-neutral-400 dark:peer-focus:text-primary"
                  >Quantity
                </label>
              </div>
              <span class="hidden text-sm text-red-500 error-message" id=""></span>
            </div>
          </div>
          <!-- Note Field -->
          <div class="relative mb-3" data-twe-input-wrapper-init>
              <input
                type="text"
                id="noteEdit"
                class="peer block min-h-[auto] w-full rounded border-0 bg-transparent px-3 py-[0.32rem] leading-[2.15] outline-none transition-all duration-200 ease-linear focus:placeholder:opacity-100 peer-focus:text-primary data-[twe-input-state-active]:placeholder:opacity-100 motion-reduce:transition-none dark:text-white dark:placeholder:text-neutral-300 dark:autofill:shadow-autofill dark:peer-focus:text-primary [&:not([data-twe-input-placeholder-active])]:placeholder:opacity-0"
                placeholder="Example label" 
                name="note"
                required/>
              <label
                class="pointer-events-none absolute left-3 top-0 mb-0 max-w-[90%] origin-[0_0] truncate pt-[0.37rem] leading-[2.15] text-neutral-500 transition-all duration-200 ease-out peer-focus:-translate-y-[0.9rem] peer-focus:scale-[0.8] peer-focus:text-primary peer-data-[twe-input-state-active]:-translate-y-[0.9rem] peer-data-[twe-input-state-active]:scale-[0.8] motion-reduce:transition-none dark:text-neutral-400 dark:peer-focus:text-primary"
                >Note
              </label>
          </div>
          <span class="hidden text-sm text-red-500 error-message" id=""></span>
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
        id="edit-submit-button"
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
  id="itemActionModal"
  tabindex="-1"
  aria-labelledby="itemActionModal"
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
        <!-- Edit Button -->
        <button
          type="button"
          class="inline-block w-full rounded bg-primary px-6 pb-5 pt-5 text-sm font-medium uppercase leading-normal text-white shadow-primary-3 transition duration-150 ease-in-out hover:bg-primary-accent-300 focus:bg-primary-accent-300 focus:outline-none focus:ring-0 active:bg-primary-600 dark:shadow-black/30 dark:hover:shadow-dark-strong dark:focus:shadow-dark-strong dark:active:shadow-dark-strong"
          id="editButton"
          data-list-item-id=""
        >
          Edit
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

<!-- Price Modal -->
<div
  data-twe-modal-init
  class="fixed left-0 top-0 z-[1055] hidden h-full w-full overflow-y-auto overflow-x-hidden outline-none"
  id="priceModalBackdrop"
  tabindex="-1"
  aria-labelledby="priceModalBackdrop"
  aria-modal="true"
  role="dialog">
  <div
    data-twe-modal-dialog-ref
    class="pointer-events-none relative flex min-h-[calc(100%-1rem)] w-auto translate-y-[-50px] items-center opacity-0 transition-all duration-300 ease-in-out min-[576px]:mx-auto min-[576px]:mt-7 min-[576px]:min-h-[calc(100%-3.5rem)] min-[576px]:max-w-[500px]">
    <div class="pointer-events-auto relative flex w-full flex-col rounded-md border-none bg-white bg-clip-padding text-current shadow-4 outline-none dark:bg-surface-dark m-4">
      <div class="flex flex-shrink-0 items-center justify-between rounded-t-md border-b-2 border-neutral-100 p-4 dark:border-white/10">
        <!-- Modal title -->
        <h5 class="text-xl font-medium leading-normal text-surface dark:text-white" id="exampleModalCenterTitle">
          Edit Price
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
        <form id="priceForm" method="POST">
          {!! csrf_field() !!}
          @method('PUT')
          <input type="hidden" name="list_item_id" />
          
            <!-- Price Field -->
            <div class="relative mb-3" data-twe-input-wrapper-init>
              <input
              type="number"
              id="priceModal"
              class="peer block min-h-[auto] w-full rounded border-0 bg-transparent px-3 py-[0.32rem] leading-[2.15] outline-none transition-all duration-200 ease-linear focus:placeholder:opacity-100 peer-focus:text-primary data-[twe-input-state-active]:placeholder:opacity-100 motion-reduce:transition-none dark:text-white dark:placeholder:text-neutral-300 dark:autofill:shadow-autofill dark:peer-focus:text-primary [&:not([data-twe-input-placeholder-active])]:placeholder:opacity-0"
              placeholder="Example label"
              name="price"
              value="0"
              required/>
              <label
              class="pointer-events-none absolute left-3 top-0 mb-0 max-w-[90%] origin-[0_0] truncate pt-[0.37rem] leading-[2.15] text-neutral-500 transition-all duration-200 ease-out peer-focus:-translate-y-[0.9rem] peer-focus:scale-[0.8] peer-focus:text-primary peer-data-[twe-input-state-active]:-translate-y-[0.9rem] peer-data-[twe-input-state-active]:scale-[0.8] motion-reduce:transition-none dark:text-neutral-400 dark:peer-focus:text-primary"
              >Price
            </label>
            
            <span class="hidden text-sm text-red-500 error-message" id="priceError"></span>
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
        id="price-submit-button"
        class="ms-1 inline-block rounded bg-primary px-6 pb-2 pt-2.5 text-xs font-medium uppercase leading-normal text-white shadow-primary-3 transition duration-150 ease-in-out hover:bg-primary-accent-300 hover:shadow-primary-2 focus:bg-primary-accent-300 focus:shadow-primary-2 focus:outline-none focus:ring-0 active:bg-primary-600 active:shadow-primary-2 dark:shadow-black/30 dark:hover:shadow-dark-strong dark:focus:shadow-dark-strong dark:active:shadow-dark-strong"
        data-twe-ripple-init
        data-twe-ripple-color="light">
        Done
        </button>
      </div>
    </div>
  </div>
</div>

@endsection

@section('js')
<script>
  $(document).ready(function () {
    
    const itemActionModal = new twe.Modal(document.getElementById("itemActionModal"));
    const itemEditModal = new twe.Modal(document.getElementById("itemEditModal"));
    const listItems = @json($list->items);
    
    $('#addButton').click(function (){
      $("#itemName").focus();
    })

    $('.item-checkbox').each(function() {
        toggleListItemStyle(this); // Call the function for each checkbox
    });

    $('.item-checkbox').on('change', function() {
      const $checkbox = $(this);
      const itemId = $(this).val(); 
      const isChecked = $(this).prop('checked') ? 1 : 0; 

      $.ajax({
        url: `/budlist/budget/list/${itemId}`, 
        type: 'PUT',
        data: {
          _token: '{{ csrf_token() }}',
          checked: isChecked ? 1 : 0
        },
        success: function(response) {
            toggleListItemStyle($checkbox);
            const list = response.list.items;
          
            // Reset totals
            let totalPrice = 0;
            let checkedPrice = 0;
            let uncheckedPrice = 0;

            // Recalculate totals from the updated list
            list.forEach(item => {
                const price = parseFloat(item.price);
                const quantity = parseInt(item.quantity) || 1;  // Default to 1 if quantity is not set

                // Add the price * quantity to the total price
                totalPrice += price * quantity;

                // Check if the item is checked, then add to checked or unchecked price
                if (item.checked === 1) {
                    checkedPrice += price * quantity;
                } else {
                    uncheckedPrice += price * quantity;
                }
            });
        
            // Update the UI with new totals
            $('#totalPriceText').text(`₱ ${totalPrice.toFixed(2)}`);
            $('#checkedPriceText').text(`₱ ${checkedPrice.toFixed(2)}`);
            $('#uncheckedPriceText').text(`₱ ${uncheckedPrice.toFixed(2)}`);
        },

        error: function(xhr, status, error) {
          console.error('Error updating status', error);
          $checkbox.prop('checked', !isChecked);
        }
      });
    });

    $('#submit-button').click(function () {
      const formData = $('#addListItemForm').serializeArray();

      formData.forEach(function(field) {
        if (field.name === 'quantity' && !field.value) {
            field.value = 1;  // Set to 1 if falsy (null, undefined, 0, etc.)
            $('#quantity').val(1);
        }
      });

      if (validateBudgetList(formData)) {
        $('#addListItemForm').submit();
      }
    });

    $(document).on('click', '#listPrice', function () {
      event.stopPropagation();

      const listItemId = $(this).data('list-item-id')
      const price = $(this).data('price')

      $('#priceModal').val(price)
      $('#priceForm').attr('action', `/budlist/budget/list/${listItemId}?fromForm=1`)
    })

    $('.listItem').click(function() {
      if ($(event.target).is('#listPrice') || $(event.target).is('#calculatedPrice') || $(event.target).hasClass('item-checkbox')) {
        // If clicked on listPrice or inside listPrice, don't open the modal
        return;
      }

      const listItemId = $(this).data('list-item-id')  
      const item = listItems.find(item => item.id === listItemId);
      
      $("#modalItemTitle").text(item.item_name)
      $("#editButton").data('list-item-id', item.id)
      $("#delete-form").attr('action', `/budlist/{{ $type }}/list/${listItemId}`)
      itemActionModal.show()
      // $('#itemActionModal').
    })

    $('#editButton').click(function() {
      const listItemId = $(this).data('list-item-id')
      
      const item = listItems.find(item => item.id === listItemId);
   
      $('#itemNameEdit').val(item.item_name)
      $('#priceEdit').val(item.price)
      $('#quantityEdit').val(item.quantity || 1);
      $('#noteEdit').val(item.note)

      $('#editListItemForm').attr('action', `/budlist/budget/list/${listItemId}?fromForm=1`)

      itemEditModal.show()
      itemActionModal.hide()
    })

    $('#edit-submit-button').click(function () {
      const formData = $('#editListItemForm').serializeArray();

      formData.forEach(function(field) {
        if (field.name === 'quantity' && !field.value) {
            field.value = 1;  // Set to 1 if falsy (null, undefined, 0, etc.)
            $('#quantityEdit').val(1);
        }
      });

      if (validateBudgetList(formData)) {
        $('#editListItemForm').submit();
      }
    });

    $('#edit-list-submit-button').click(function () { 
        $('#edit-list-form').submit();
    });

    $('#price-submit-button').click(function () {
      const formData = $('#priceForm').serializeArray();
      
      if (formData.price  !== '' && isNaN(formData.price)) {
        $('#priceForm').submit();
      }
    });

  });
  
  function toggleListItemStyle(checkbox) {
    const listItem = $(checkbox).closest('li');
      const itemText = listItem.find('.item-text');

      // Check if the checkbox is checked
      if ($(checkbox).is(':checked')) {
          // Add strike-through and muted styles to text when checked
          itemText.addClass('line-through text-gray-500');
          listItem.addClass('opacity-75'); // Make entire item darker
          listItem.appendTo(listItem.parent());
      } else {
          // Remove strike-through and muted styles when unchecked
          itemText.removeClass('line-through text-gray-500');
          listItem.removeClass('opacity-75'); // Reset brightness to default
          listItem.prependTo(listItem.parent());
      }
  }

  function validateBudgetList(formData) {
    let isValid = true;
    
    // Clear all previous errors
    $('.error-message').hide();
    
    // Convert formData into an object for easier access
    const data = {};
    formData.forEach((field) => {
      if (typeof field.value === 'string') {
        data[field.name] = field.value.trim();
      } else {
          data[field.name] = field.value;
      }
    });
    
    // Validate list name (required)
    if (!data.item_name) {
      $('#itemNameError').text('Please enter a valid name').fadeIn('fast');
      isValid = false;
    }
    
    // Validate price (optional, must be a number if entered)
    if (data.price && isNaN(data.price)) {
      $('#priceError').text('Please enter a valid number').fadeIn('fast');
      isValid = false;
    }
    
    // Validate price (optional, must be a number if entered)
    if (data.quantity && isNaN(data.quantity)) {
      $('#quantityError').text('Please enter a valid number').fadeIn('fast');
      isValid = false;
    }
    
    return isValid;
  }
</script>
@endsection