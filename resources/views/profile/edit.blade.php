<x-app-layout>
<div class="max-w-[1200px] mx-auto px-1 sm:px-0" x-data="{ showEmail:false, showPass:false, show2fa:false }">
  <h1 class="font-extrabold text-xl sm:text-2xl">Meu perfil</h1>
  <p class="text-slate-500 text-sm sm:text-[15px] mt-0.5">Os seus dados, a segurança da conta e os anexos.</p>

  <div class="grid lg:grid-cols-[240px_1fr] gap-5 lg:gap-8 mt-5 items-start">
    <aside class="hidden lg:block">
      <h2 class="font-bold">Dados gerais</h2>
      <p class="text-sm text-slate-500 mt-1">Informações sobre a sua conta</p>
    </aside>

    <div class="min-w-0">
      <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data"
            class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-4 sm:p-8">
        @csrf @method('PATCH')
        @include('layouts.partials.flash')

        {{-- Avatar --}}
        <div class="relative w-24 h-24 sm:w-28 sm:h-28">
          @if(!empty($user->avatar))
          <img src="{{ asset('storage/'.$user->avatar) }}" class="w-24 h-24 sm:w-28 sm:h-28 rounded-full object-cover border-4 border-white dark:border-slate-700 shadow" alt="Foto de perfil">
          @else
          <span class="w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-blue-600 text-white flex items-center justify-center text-4xl font-bold border-4 border-white dark:border-slate-700 shadow">{{ mb_substr($user->name ?? 'E', 0, 1) }}</span>
          @endif
          <label title="Alterar foto" class="absolute bottom-0 left-0 w-9 h-9 rounded-full bg-blue-600 hover:bg-blue-700 text-white flex items-center justify-center shadow cursor-pointer border-2 border-white dark:border-slate-800">
            <i class="bi bi-pencil-fill text-sm"></i>
            <input type="file" name="avatar" accept="image/*" class="hidden" onchange="this.form.submit()">
          </label>
        </div>

        <div class="grid gap-4 sm:gap-5 mt-5">
          <div>
            <label class="text-slate-600 dark:text-slate-300 text-sm sm:text-base">Nome</label>
            <input name="name" required value="{{ old('name', $user->name) }}" class="mt-1 w-full border border-slate-300 dark:border-slate-600 rounded-lg px-3.5 py-2.5 font-semibold bg-white dark:bg-slate-800">
            @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
          </div>

          <div>
            <label class="text-slate-600 dark:text-slate-300 text-sm sm:text-base">Email</label>
            <div class="mt-1 flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3">
              <p class="font-semibold break-all">{{ $user->email }}</p>
              <button type="button" @click="showEmail = !showEmail" class="sm:ml-auto text-blue-600 font-medium text-sm whitespace-nowrap">Alterar email</button>
            </div>
            <div x-show="showEmail" class="mt-2">
              <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full border border-slate-300 dark:border-slate-600 rounded-lg px-3.5 py-2.5 bg-white dark:bg-slate-800">
              <p class="text-xs text-slate-500 mt-1">Ao trocar o email terás de verificá-lo de novo.</p>
              @error('email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
          </div>

          <div>
            <label class="text-slate-600 dark:text-slate-300 text-sm sm:text-base">Número de Telefone</label>
            <input name="phone" value="{{ old('phone', $user->phone) }}" placeholder="84… / 85…" class="mt-1 w-full border border-slate-300 dark:border-slate-600 rounded-lg px-3.5 py-2.5 font-semibold bg-white dark:bg-slate-800">
          </div>

          <div>
            <p class="text-slate-600 dark:text-slate-300 text-sm sm:text-base">Senha</p>
            <button type="button" @click="showPass = !showPass" class="mt-1 px-5 py-2 rounded-full bg-slate-100 dark:bg-slate-700 text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-600">Alterar senha</button>
          </div>

          <div>
            <p class="text-slate-600 dark:text-slate-300 text-sm sm:text-base flex items-center gap-1.5"><i class="bi bi-shield"></i> Autenticação de Dois Fatores</p>
            <div class="mt-2 flex flex-col sm:flex-row sm:items-center gap-3 p-3.5 sm:p-4 rounded-xl bg-blue-50/60 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-900">
              <span class="w-11 h-11 shrink-0 rounded-full bg-blue-600 text-white flex items-center justify-center text-xl"><i class="bi bi-shield-check"></i></span>
              <div class="min-w-0"><p class="font-bold text-blue-700 dark:text-blue-300 text-sm sm:text-base">2FA Desativado</p>
              <p class="text-xs sm:text-sm text-blue-600/80 dark:text-blue-300/80">A verificação em duas etapas chega em breve.</p></div>
              <button type="button" @click="show2fa = !show2fa" class="sm:ml-auto shrink-0 px-5 py-2 rounded-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Gerenciar</button>
            </div>
            <p x-show="show2fa" class="mt-2 text-xs sm:text-sm text-slate-500">Por enquanto, mantém uma palavra-passe forte e única e não a partilhes com ninguém.</p>
          </div>

          <div>
            <label class="text-slate-600 dark:text-slate-300 text-sm sm:text-base">Idioma</label>
            <select name="locale" class="mt-1 w-full border border-slate-300 dark:border-slate-600 rounded-lg px-3.5 py-2.5 font-semibold bg-white dark:bg-slate-800">
              <option value="pt" @selected(old('locale', $user->locale ?? 'pt') == 'pt')>Português</option>
              <option value="en" @selected(old('locale', $user->locale) == 'en')>English</option>
            </select>
          </div>

          <div>
            <p class="text-slate-600 dark:text-slate-300 text-sm sm:text-base">Tema da Aplicação</p>
            <div class="grid grid-cols-2 gap-3 mt-2">
              <label class="cursor-pointer rounded-xl border-2 p-2.5 transition {{ old('theme', $user->theme ?? 'light') == 'light' ? 'border-blue-600 bg-blue-50/50 dark:bg-blue-900/10' : 'border-slate-200 dark:border-slate-700' }}">
                <input type="radio" name="theme" value="light" class="hidden" @checked(old('theme', $user->theme ?? 'light') == 'light') onclick="window.ElpayTheme?.set('light')">
                <span class="block rounded-lg bg-slate-100 dark:bg-slate-700 p-2">
                  <span class="block h-1.5 w-2/3 rounded bg-slate-300 dark:bg-slate-600 mb-1.5"></span>
                  <span class="flex gap-1.5"><span class="h-5 flex-1 rounded bg-white dark:bg-slate-600"></span><span class="h-5 w-1/3 rounded bg-blue-600"></span></span>
                </span>
                <span class="mt-2 flex items-center gap-1.5 font-semibold text-sm sm:text-base"><i class="bi bi-sun text-amber-500"></i> Claro
                <i class="bi bi-check-circle-fill text-blue-600 ml-auto theme-check-light"></i></span>
              </label>
              <label class="cursor-pointer rounded-xl border-2 p-2.5 transition {{ old('theme', $user->theme ?? 'light') == 'dark' ? 'border-blue-600 bg-blue-50/50 dark:bg-blue-900/10' : 'border-slate-200 dark:border-slate-700' }}">
                <input type="radio" name="theme" value="dark" class="hidden" @checked(old('theme', $user->theme) == 'dark') onclick="window.ElpayTheme?.set('dark')">
                <span class="block rounded-lg bg-slate-900 p-2">
                  <span class="block h-1.5 w-2/3 rounded bg-slate-700 mb-1.5"></span>
                  <span class="flex gap-1.5"><span class="h-5 flex-1 rounded bg-slate-800"></span><span class="h-5 w-1/3 rounded bg-blue-600"></span></span>
                </span>
                <span class="mt-2 flex items-center gap-1.5 font-semibold text-sm sm:text-base"><i class="bi bi-moon-fill text-indigo-400"></i> Escuro
                <i class="bi bi-circle text-slate-300 ml-auto"></i></span>
              </label>
            </div>
          </div>

          <div>
            <label class="text-slate-600 dark:text-slate-300 text-sm sm:text-base">Especialidade</label>
            <input name="specialty" value="{{ old('specialty', $user->specialty) }}" placeholder="Ex: Nutricionista, Personal Trainer, Psicólogo…" class="mt-1 w-full border border-slate-300 dark:border-slate-600 rounded-lg px-3.5 py-2.5 bg-white dark:bg-slate-800">
            <p class="text-xs sm:text-sm text-slate-500 mt-1">A sua área de atuação ou formação principal.</p>
          </div>
        </div>

        <button class="mt-6 w-full py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm sm:text-base shadow-lg shadow-blue-600/25">Salvar alterações</button>
      </form>

      {{-- Senha (form separado Breeze) --}}
      <div x-show="showPass" class="mt-4 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-4 sm:p-6">
        <form method="POST" action="{{ route('password.update') }}" class="grid gap-3">
          @csrf @method('PUT')
          <div><label class="text-sm font-medium">Senha atual</label><input type="password" name="current_password" class="mt-1 w-full border rounded-lg px-3 py-2.5" autocomplete="current-password"></div>
          <div class="grid sm:grid-cols-2 gap-3">
            <div><label class="text-sm font-medium">Nova senha</label><input type="password" name="password" class="mt-1 w-full border rounded-lg px-3 py-2.5" autocomplete="new-password"></div>
            <div><label class="text-sm font-medium">Confirmar senha</label><input type="password" name="password_confirmation" class="mt-1 w-full border rounded-lg px-3 py-2.5" autocomplete="new-password"></div>
          </div>
          @if($errors->updatePassword->any())
          <ul class="text-xs text-red-600">@foreach($errors->updatePassword->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
          @endif
          <div><button class="px-5 py-2 rounded-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Atualizar senha</button></div>
        </form>
      </div>

      @if(!empty($user->avatar))
      <form method="POST" action="{{ route('profile.avatar.destroy') }}" onsubmit="return confirm('Remover a foto de perfil?')" class="mt-3 text-sm">
        @csrf @method('DELETE')
        <button class="text-red-500 hover:underline"><i class="bi bi-trash"></i> Remover foto de perfil</button>
      </form>
      @endif
    </div>
  </div>
</div>
</x-app-layout>
