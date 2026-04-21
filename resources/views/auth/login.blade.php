<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        @session('status')
            <div class="mb-4 font-medium text-sm text-emerald-400">
                {{ $value }}
            </div>
        @endsession

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div>
                <x-label for="email" value="{{ __('Correo Electrónico') }}" style="color: #e4eaf2;" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username"
                    style="background: rgba(255,255,255,0.06); border-color: rgba(50,184,212,0.2); color: #e4eaf2;" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Contraseña') }}" style="color: #e4eaf2;" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password"
                    style="background: rgba(255,255,255,0.06); border-color: rgba(50,184,212,0.2); color: #e4eaf2;" />
            </div>

            <div class="block mt-4">
                <label for="remember_me" class="flex items-center">
                    <x-checkbox id="remember_me" name="remember" />
                    <span class="ms-2 text-sm" style="color: #8fa3bc;">{{ __('Recordarme') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <a class="underline text-sm rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500" href="{{ route('password.request') }}" style="color: #8fa3bc;">
                        {{ __('¿Olvidaste tu contraseña?') }}
                    </a>
                @endif

                <button type="submit" class="ms-4 inline-flex items-center px-5 py-2.5 border border-transparent rounded-xl text-sm font-semibold text-white tracking-wide transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500"
                    style="background: linear-gradient(135deg, #1a6d9a, #32b8d4); box-shadow: 0 4px 15px rgba(50, 184, 212, 0.25);">
                    {{ __('Iniciar Sesión') }}
                </button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
