<x-guest-layout>
    <div class="text-center mb-6">
        <h1 class="text-2xl font-extrabold">Bem-vindo de volta</h1>
        <p class="text-sm text-gray-500 mt-1">Entra para gerires os teus produtos e ganhos.</p>
    </div>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block mt-1 w-full !rounded-xl !py-2.5" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="tu@email.co.mz" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" class="block mt-1 w-full !rounded-xl !py-2.5" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
        <div class="flex items-center justify-between text-sm">
            <label for="remember_me" class="inline-flex items-center gap-2 text-gray-600">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" name="remember">
                Lembrar-me
            </label>
            @if (Route::has('password.request'))
                <a class="font-semibold text-blue-600 hover:underline" href="{{ route('password.request') }}">Esqueceste a password?</a>
            @endif
        </div>
        <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition shadow-lg shadow-blue-600/20">
            Entrar <i class="bi bi-arrow-right ms-1"></i>
        </button>
        <p class="text-center text-sm text-gray-500">Sem conta? <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:underline">Criar grátis</a></p>
    </form>
</x-guest-layout>
