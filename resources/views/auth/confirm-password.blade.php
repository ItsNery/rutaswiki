<x-guest-layout maxWidth="md">
    <div class="space-y-4">
        <h2 class="text-xl font-serif font-normal text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2">
            Confirmar contraseña
        </h2>

        <div class="text-xs text-gray-655 dark:text-gray-400 leading-relaxed">
            Esta es un área de seguridad. Por favor, confirma tu contraseña antes de continuar con la acción solicitada.
        </div>

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
            @csrf

            <!-- Password -->
            <div>
                <label for="password" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Contraseña</label>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                       class="mt-1 block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors shadow-inner" />
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold py-2 px-4 rounded-sm transition-colors text-xs shadow-sm">
                    Confirmar
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>

