<x-guest-layout maxWidth="md">
    <div class="space-y-4">
        <h2 class="text-xl font-serif font-normal text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2">
            Restablecer contraseña
        </h2>

        <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
            @csrf

            <!-- Password Reset Token -->
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Correo electrónico</label>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                       class="mt-1 block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors shadow-inner" />
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Nueva contraseña</label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                       class="mt-1 block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors shadow-inner" />
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Confirmar nueva contraseña</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                       class="mt-1 block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors shadow-inner" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold py-2 px-4 rounded-sm transition-colors text-xs shadow-sm">
                    Restablecer contraseña
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>

