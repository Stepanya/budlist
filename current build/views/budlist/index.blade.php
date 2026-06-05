@extends('budlist.layouts.master')

@section('title', 'BUDLIST')

@section('content')
<div class="grid grid-cols-1 gap-4 p-4">
  <a href="/budlist/budget" class="relative w-full h-40 flex justify-center items-center rounded-lg bg-blue-500 text-white shadow-lg hover:bg-blue-600 transition-opacity duration-200 opacity-90">
    <i class="fas fa-wallet text-8xl absolute text-white opacity-20"></i>
    <span class="text-xl font-semibold relative">Budget</span>
  </a>
  <a href="/budlist/loan" class="relative w-full h-40 flex justify-center items-center rounded-lg bg-green-500 text-white shadow-lg hover:bg-green-600 transition-opacity duration-200 opacity-90">
    <i class="fas fa-hand-holding-usd text-8xl absolute text-white opacity-20"></i>
    <span class="text-xl font-semibold relative">Loan</span>
  </a>
  <a href="/budlist/shopping" class="relative w-full h-40 flex justify-center items-center rounded-lg bg-red-500 text-white shadow-lg hover:bg-red-600 transition-opacity duration-200 opacity-90">
    <i class="fas fa-shopping-cart text-8xl absolute text-white opacity-20"></i>
    <span class="text-xl font-semibold relative">Shopping</span>
  </a>
</div>

@endsection