<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Notificações</h2></x-slot>
<div class="py-6 max-w-3xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<div class="bg-white shadow rounded-xl divide-y">
@forelse(($notifications??[]) as $n)
<div class="p-4 flex gap-3 {{ empty($n->read_at)?'bg-blue-50':'' }}">
<i class="bi bi-bell text-blue-600 mt-1"></i>
<div class="flex-1"><p class="text-sm font-medium">{{ $n->title??'Notificação' }}</p><p class="text-sm text-gray-600">{{ $n->message??'' }}</p><p class="text-xs text-gray-400">{{ $n->created_at }}</p></div>
@if(empty($n->read_at))<form method="POST" action="{{ Route::has('notifications.read')?route('notifications.read',$n->id):'#' }}">@csrf @method('PATCH')<button class="text-xs text-blue-600">Marcar lida</button></form>@endif
</div>
@empty<div class="p-8 text-center text-gray-400">Sem notificações.</div>@endforelse
</div>
@if(isset($notifications)&&method_exists($notifications,'links'))<div class="mt-4">{{ $notifications->links() }}</div>@endif
</div></x-app-layout>
