<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Meus certificados</h2></x-slot>
<div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<div class="grid sm:grid-cols-2 gap-4">
@forelse(($certificates??[]) as $c)
<div class="bg-white shadow rounded-xl p-5 text-center border-t-4 border-blue-500">
<i class="bi bi-award text-4xl text-amber-500"></i>
<h3 class="font-bold mt-2">{{ $c->product->title??'Curso' }}</h3>
<p class="font-mono text-sm text-gray-500 mt-1">{{ $c->code }}</p>
<p class="text-xs text-gray-400">Emitido em {{ $c->issued_at?->format('d/m/Y') }}</p>
<a href="{{ route('certificates.verify',$c->code) }}" class="mt-3 inline-block text-sm px-4 py-2 bg-blue-600 text-white rounded-lg">Ver certificado</a>
</div>
@empty<div class="col-span-full bg-white shadow rounded-xl p-8 text-center text-gray-400">Ainda sem certificados. Conclui um curso para emitir.</div>@endforelse
</div>
@if(isset($certificates)&&method_exists($certificates,'links'))<div class="mt-4">{{ $certificates->links() }}</div>@endif
</div></x-app-layout>
