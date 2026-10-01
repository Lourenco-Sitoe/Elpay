<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Novo produto</h2></x-slot>
<div class="py-6 max-w-3xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<form method="POST" action="{{ Route::has('products.store')?route('products.store'):'#' }}" enctype="multipart/form-data" class="bg-white shadow rounded-xl p-6">@csrf @include('products._form')
<div class="mt-4"><button class="px-6 py-2 bg-blue-600 text-white rounded-lg">Guardar</button></div></form>
</div></x-app-layout>
