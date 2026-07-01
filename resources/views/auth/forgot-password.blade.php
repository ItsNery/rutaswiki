<x-guest-layout maxWidth="md">
    <div class="space-y-4">
        <h2 class="text-xl font-serif font-normal text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2">
            Recuperar contraseña
        </h2>
        
        <div class="text-xs text-gray-655 dark:text-gray-400 leading-relaxed">
            ¿Olvidaste tu contraseña? Escribe tu dirección de correo electrónico registrada y te enviaremos un enlace para restablecerla y elegir una nueva.
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Correo electrónico</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="mt-1 block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors shadow-inner" />
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>

            <div class="pt-2 flex items-center justify-between gap-4">
                <a href="{{ route('login') }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                    &larr; Volver
                </a>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold py-2 px-4 rounded-sm transition-colors text-xs shadow-sm">
                    Enviar enlace
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>

