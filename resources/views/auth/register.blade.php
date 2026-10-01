<x-guest-layout>
    <div class="text-center mb-6">
        <h1 class="text-2xl font-extrabold">Começa a vender hoje</h1>
        <p class="text-sm text-gray-500 mt-1">Conta de produtor grátis · sem cartão.</p>
    </div>
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        <div>
            <x-input-label for="name" value="Nome" />
            <x-text-input id="name" class="block mt-1 w-full !rounded-xl !py-2.5" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="O teu nome" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block mt-1 w-full !rounded-xl !py-2.5" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="tu@email.co.mz" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
        <div class="grid grid-cols-2 gap-3">
        <div>
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" class="block mt-1 w-full !rounded-xl !py-2.5" type="password" name="password" required autocomplete="new-password" placeholder="Mín. 8" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="password_confirmation" value="Confirmar" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full !rounded-xl !py-2.5" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Repete" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>
        </div>
        <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition shadow-lg shadow-blue-600/20">
            Criar conta grátis <i class="bi bi-arrow-right ms-1"></i>
        </button>
        <p class="text-center text-sm text-gray-500">Já tens conta? <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:underline">Entrar</a></p>
    </form>
</x-guest-layout>
