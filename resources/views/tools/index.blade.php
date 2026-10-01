<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Ferramentas</h2></x-slot>
<div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
@php $tools = [
  ['Order Bump','Adiciona oferta extra no checkout e aumenta o ticket médio.','bi-cart3','bg-red-100 text-red-600','bumps.index',($stats['bumps']??0).' ativos'],
  ['Upsell','Oferta pós-compra e downsell alternativo.','bi-cart-plus','bg-blue-100 text-blue-600','upsells.index',($stats['upsells']??0).' ativos'],
  ['Meta Pixel','Instala pixels e acompanha conversões dos anúncios.','bi-facebook','bg-blue-100 text-blue-600','pixels.index',($stats['pixels']??0).' pixels'],
  ['Webhooks','Notifica os teus sistemas em tempo real a cada venda.','bi-plug','bg-purple-100 text-purple-600','webhooks.index',($stats['webhooks']??0).' ativos'],
]; @endphp
@foreach($tools as $t)
<a href="{{ Route::has($t[4])?route($t[4]):'#' }}" class="bg-white rounded-xl shadow p-5 hover:shadow-lg transition">
<div class="w-10 h-10 rounded-lg {{ $t[3] }} flex items-center justify-center text-xl"><i class="bi {{ $t[2] }}"></i></div>
<h3 class="font-bold mt-3">{{ $t[0] }}</h3><p class="text-sm text-gray-500 mt-1">{{ $t[1] }}</p>
<p class="text-xs text-blue-600 font-semibold mt-2">{{ $t[5] }}</p></a>
@endforeach
</div></div></x-app-layout>
