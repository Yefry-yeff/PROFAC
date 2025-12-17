<x-guest-layout>
    <div class="login-container animate__animated animate__fadeIn">
        <!-- Header con logo -->
        <div class="login-header">
            <div class="logo-container animate__animated animate__zoomIn">
                <img class="w-full h-full object-cover" src="{{ asset('img/LOGO_VALENCIA.jpg') }}" alt="Logo Valencia" />
            </div>
            <h2 class="text-white text-xl font-bold mt-3 mb-1">Bienvenido</h2>
            <p class="text-white text-sm opacity-90">Ingresa tus credenciales para continuar</p>
        </div>

        <!-- Body con formulario -->
        <div class="login-body">
            <x-jet-validation-errors class="mb-4" />

            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded-lg text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div class="mb-4 animate__animated animate__fadeInLeft">
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2"></i>Correo Electrónico
                    </label>
                    <input id="email" 
                           class="form-input w-full" 
                           type="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required 
                           autofocus 
                           placeholder="tu@email.com" />
                </div>

                <!-- Password -->
                <div class="mb-4 animate__animated animate__fadeInRight">
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2"></i>Contraseña
                    </label>
                    <input id="password" 
                           class="form-input w-full" 
                           type="password" 
                           name="password" 
                           required 
                           autocomplete="current-password" 
                           placeholder="••••••••" />
                </div>

                <!-- Remember me -->
                <div class="flex items-center justify-between mb-5">
                    <label for="remember_me" class="flex items-center cursor-pointer">
                        <input id="remember_me" 
                               type="checkbox" 
                               name="remember" 
                               class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-600 font-medium">Recuérdame</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="forgot-password text-sm font-medium" href="{{ route('password.request') }}">
                            ¿Olvidaste tu contraseña?
                        </a>
                    @endif
                </div>

                <!-- Submit button -->
                <div class="animate__animated animate__fadeInUp">
                    <button type="submit" id="loginBtn" class="btn-login w-full text-white font-semibold py-3 px-6">
                        <span class="spinner mr-2"></span>
                        <span class="btn-text">
                            <i class="fas fa-sign-in-alt mr-2"></i>Iniciar Sesión
                        </span>
                    </button>
                </div>
            </form>
            
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const loginForm = document.querySelector('form');
                    const loginBtn = document.getElementById('loginBtn');
                    
                    loginForm.addEventListener('submit', function() {
                        loginBtn.classList.add('loading');
                        loginBtn.disabled = true;
                    });
                });
            </script>

            <!-- Footer -->
            <div class="mt-6 text-center">
                <p class="text-xs text-gray-500">
                    © {{ date('Y') }} D. Valencia. Todos los derechos reservados.
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>
