<div class="grid gap-4">
<div>
<label class="text-sm font-medium">Título</label>
<input name="title" value="{{ old('title',$product->title??'') }}" required class="mt-1 w-full border rounded-lg px-3 py-2">
</div>
<div>
<label class="text-sm font-medium">Descrição</label>
<textarea name="description" rows="4" class="mt-1 w-full border rounded-lg px-3 py-2">{{ old('description',$product->description??'') }}</textarea>
</div>
<div class="grid sm:grid-cols-3 gap-4">
<div><label class="text-sm font-medium">Preço (MT)</label><input type="number" step="0.01" min="1" name="price" value="{{ old('price',$product->price??'') }}" required class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<div><label class="text-sm font-medium">Tipo</label>
<select name="type" class="mt-1 w-full border rounded-lg px-3 py-2">
@foreach(['course'=>'Curso','ebook'=>'Ebook','link'=>'Link','file'=>'Ficheiro'] as $v=>$l)
<option value="{{ $v }}" @selected(old('type',$product->type??'')==$v)>{{ $l }}</option>
@endforeach</select></div>
<div><label class="text-sm font-medium">Comissão afiliado (%)</label><input type="number" step="0.1" min="0" max="90" name="commission_rate" value="{{ old('commission_rate',$product->commission_rate??30) }}" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
</div>
<div class="grid sm:grid-cols-2 gap-4">
<div><label class="text-sm font-medium">URL externa (link/curso)</label><input name="external_url" value="{{ old('external_url',$product->external_url??'') }}" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<div><label class="text-sm font-medium">Ficheiro (ebook/ficheiro)</label><input type="file" name="file" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
</div>
<div><label class="text-sm font-medium">Capa</label><input type="file" name="cover" accept="image/*" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
</div>
