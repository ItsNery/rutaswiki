<x-guest-layout maxWidth="4xl">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-stretch divide-y md:divide-y-0 md:divide-x divide-gray-200 dark:divide-gray-800">
        
        <!-- Left Side: Informational -->
        <div class="md:col-span-6 space-y-6 pb-6 md:pb-0 md:pr-8 flex flex-col justify-between">
            <div class="space-y-4">
                <h2 class="text-2xl font-serif font-normal text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Únete como editor
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                    Al registrarte en RutasWiki, te conviertes en parte de una comunidad libre dedicada a recopilar y estructurar información sobre transporte público para tu localidad.
                </p>
                <div class="space-y-4 pt-2">
                    <div class="flex items-start gap-3">
                        <span class="text-blue-600 dark:text-blue-400 mt-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </span>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Crea nuevas páginas de rutas</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Añade fichas detalladas de rutas de autobús, combis, metro o colectivos.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="text-blue-600 dark:text-blue-400 mt-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </span>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Colabora y corrige</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Edita trazos obsoletos, actualiza nombres de avenidas o cambia horarios desfasados.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="text-blue-600 dark:text-blue-400 mt-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        </span>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Historial y transparencia</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Toda edición queda grabada en un historial público asociado a tu nombre de editor.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 dark:bg-gray-900/30 p-4 border border-gray-250 dark:border-gray-800 rounded-sm mt-4">
                <p class="text-xs text-gray-500 dark:text-gray-450 leading-relaxed">
                    Crear y editar páginas es libre y abierto, pero te pedimos que toda la información aportada sea verídica y de utilidad pública. ¡Hagamos que viajar sea más fácil!
                </p>
            </div>
        </div>

        <!-- Right Side: Register Form -->
        <div class="md:col-span-6 pt-6 md:pt-0 md:pl-8 space-y-5">
            <h2 class="text-2xl font-serif font-normal text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2">
                Crear una cuenta
            </h2>

            <form method="POST" action="{{ route('register') }}" class="space-y-4"
                  x-data="{
                      name: '{{ old('name') }}',
                      nameAvailable: null,
                      nameChecking: false,
                      timeoutId: null,

                      checkName() {
                          const trimmed = this.name.trim();
                          if (trimmed === '') {
                              this.nameAvailable = null;
                              return;
                          }
                          this.nameChecking = true;
                          this.nameAvailable = null;

                          if (this.timeoutId) clearTimeout(this.timeoutId);

                          this.timeoutId = setTimeout(() => {
                              fetch(`/check-username?name=${encodeURIComponent(trimmed)}`)
                                  .then(res => res.json())
                                  .then(data => {
                                      this.nameAvailable = data.available;
                                      this.nameChecking = false;
                                  })
                                  .catch(err => {
                                      console.error(err);
                                      this.nameChecking = false;
                                  });
                          }, 400);
                      }
                  }">
                @csrf

                <!-- Name -->
                <div>
                    <label for="name" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Nombre de usuario / editor</label>
                    <input id="name" type="text" name="name" required autofocus autocomplete="name"
                           x-model="name" @input="checkName()"
                           class="mt-1 block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors shadow-inner" />
                    
                    <p x-show="nameChecking" class="text-[10px] text-blue-500 mt-1" x-cloak>Verificando disponibilidad...</p>
                    <p x-show="nameAvailable === false" class="text-[10px] text-red-500 font-semibold mt-1" x-cloak>Este nombre de usuario ya está registrado.</p>
                    <p x-show="nameAvailable === true" class="text-[10px] text-green-600 font-semibold mt-1" x-cloak>Nombre de usuario disponible.</p>

                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    <p class="text-[10px] text-gray-450 dark:text-gray-500 mt-1">Este nombre identificará tus aportaciones públicas en el historial de rutas.</p>
                </div>

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Correo electrónico</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                           class="mt-1 block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors shadow-inner" />
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Contraseña</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                           class="mt-1 block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors shadow-inner" />
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Confirmar contraseña</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                           class="mt-1 block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors shadow-inner" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
                </div>

                <div class="pt-2">
                    <button type="submit" :disabled="nameAvailable === false || nameChecking"
                            :class="nameAvailable === false || nameChecking ? 'opacity-50 cursor-not-allowed' : ''"
                            class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold py-2.5 px-4 rounded-sm transition-all text-sm shadow-sm">
                        Crear mi cuenta
                    </button>
                </div>

                <div class="text-center pt-4 border-t border-gray-200 dark:border-gray-800 flex items-center justify-center gap-1.5">
                    <span class="text-xs text-gray-500 dark:text-gray-400">¿Ya tienes una cuenta registrada?</span>
                    <a href="{{ route('login') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                        Iniciar sesión
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>

