<x-guest-layout maxWidth="4xl">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-stretch divide-y md:divide-y-0 md:divide-x divide-gray-200 dark:divide-gray-800">
        
        <!-- Left Side: Informational -->
        <div class="md:col-span-6 space-y-6 pb-6 md:pb-0 md:pr-8 flex flex-col justify-between">
            <div class="space-y-4">
                <h2 class="text-2xl font-serif font-normal text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Bienvenido a RutasWiki
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                    RutasWiki es la enciclopedia libre y colaborativa de transporte público. Al iniciar sesión con tu cuenta de editor, podrás participar activamente en el mapeo de rutas.
                </p>
                <div class="space-y-4 pt-2">
                    <div class="flex items-start gap-3">
                        <span class="text-blue-600 dark:text-blue-400 mt-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </span>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Edita y actualiza rutas</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Corrige trazos, añade paradas, actualiza tarifas y horarios en tu comunidad.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="text-blue-600 dark:text-blue-400 mt-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </span>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Registra nuevas ciudades</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Si tu ciudad no existe en el sistema, agrégala y sé el pionero en trazar su red de transporte.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-blue-50 dark:bg-blue-950/40 p-4 border border-blue-200 dark:border-blue-800 rounded-sm mt-4">
                <p class="text-xs text-blue-800 dark:text-blue-300 leading-relaxed">
                    <strong>¿No tienes cuenta de editor?</strong> El registro es totalmente gratuito. Únete a la comunidad para comenzar a editar de inmediato. ¡Tus aportes guían a miles de pasajeros!
                </p>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="md:col-span-6 pt-6 md:pt-0 md:pl-8 space-y-5">
            <h2 class="text-2xl font-serif font-normal text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2">
                Iniciar sesión
            </h2>
            
            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Correo electrónico</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                           class="mt-1 block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors shadow-inner" />
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <!-- Password -->
                <div>
                    <div class="flex justify-between items-center">
                        <label for="password" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Contraseña</label>
                        @if (Route::has('password.request'))
                            <a class="text-xs text-blue-600 dark:text-blue-400 hover:underline" href="{{ route('password.request') }}">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                           class="mt-1 block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors shadow-inner" />
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <input id="remember_me" type="checkbox" name="remember" 
                           class="rounded-sm border-gray-300 dark:border-gray-700 text-blue-600 focus:ring-blue-500 dark:bg-gray-850" />
                    <label for="remember_me" class="ml-2 text-xs text-gray-600 dark:text-gray-400">Recordar mi sesión en este dispositivo</label>
                </div>

                <div class="pt-2">
                    <button type="submit" 
                            class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold py-2.5 px-4 rounded-sm transition-colors text-sm shadow-sm">
                        Acceder
                    </button>
                </div>
                
                <div class="text-center pt-4 border-t border-gray-200 dark:border-gray-800 flex items-center justify-center gap-1.5">
                    <span class="text-xs text-gray-500 dark:text-gray-400">¿Eres nuevo en RutasWiki?</span>
                    <a href="{{ route('register') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                        Crear una cuenta
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>

