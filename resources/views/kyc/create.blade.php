<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Submeter KYC</h2></x-slot>
<div class="py-6 max-w-xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<form method="POST" action="{{ Route::has('kyc.store')?route('kyc.store'):'#' }}" enctype="multipart/form-data" class="bg-white shadow rounded-xl p-6">@csrf
<div class="mb-3"><label class="text-sm font-medium">Tipo de documento</label><select name="document_type" class="mt-1 w-full border rounded-lg px-3 py-2"><option value="bi">Bilhete de Identidade</option><option value="passport">Passaporte</option><option value="dirigir">Carta de Condução</option></select></div>
<div class="mb-3"><label class="text-sm font-medium">Frente do documento</label><input type="file" name="document_front" required accept="image/*,.pdf" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<div class="mb-3"><label class="text-sm font-medium">Verso do documento</label><input type="file" name="document_back" accept="image/*,.pdf" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<div class="mb-3"><label class="text-sm font-medium">Selfie com documento</label><input type="file" name="selfie" accept="image/*" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<button class="w-full py-2 bg-blue-600 text-white rounded-lg font-semibold">Submeter</button></form>
</div></x-app-layout>
