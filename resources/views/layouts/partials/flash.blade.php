@if(session('success'))
<div class="mb-4 p-3 rounded bg-blue-50 border border-blue-200 text-blue-800 text-sm"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 p-3 rounded bg-red-50 border border-red-200 text-red-800 text-sm"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</div>
@endif
@if($errors->any())
<div class="mb-4 p-3 rounded bg-red-50 border border-red-200 text-red-800 text-sm">
<ul class="list-disc ml-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
