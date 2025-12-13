<x-guest-layout>
    <x-jet-authentication-card  class="animate__animated" style="border-block-color: 00000000;">
        <x-slot name="logo" >
            {{-- <x-jet-authentication-card-logo /> --}}
            <img class="rounded-full object-cover animate__animated animate__fadeInDown animate__fadeInLeft" height="300px" width="300px"
                                        src="{{  asset('img/LOGO_VALENCIA.jpg') }}"/ >
        </x-slot>

        <x-jet-validation-errors class="mb-4 " />

        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="animate__animated animate__fadeInLeft">
                <x-jet-label for="email" value="{{ __('Correo Electrónico') }}" />
                <x-jet-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            </div>

            <div class="mt-4 animate__animated animate__fadeInRight">
                <x-jet-label for="password" value="{{ __('Contraseña') }}" />
                <x-jet-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            </div>

            <div class="block mt-4">
                <label for="remember_me" class="flex items-center">
                    <x-jet-checkbox id="remember_me" name="remember" />
                    <span class="ml-2 text-sm text-gray-600">{{ __('Recuerdame') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.request') }}">
                        {{ __('He olvidado mi contraseña') }}
                    </a>
                @endif

                <x-jet-button class="ml-4" style="background-color:#F15533 ;">
                    {{ __('Entrar') }}
                </x-jet-button>
            </div>
        </form>
    </x-jet-authentication-card>
</x-guest-layout>
