<x-guest-layout maxWidth="md">
    <div class="space-y-4">
        <h2 class="text-xl font-serif font-normal text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2">
            Verifica tu correo electrónico
        </h2>

        <div class="text-xs text-gray-655 dark:text-gray-400 leading-relaxed">
            ¡Gracias por registrarte en RutasWiki! Antes de comenzar a colaborar, ¿podrías verificar tu dirección de correo electrónico haciendo clic en el enlace que te acabamos de enviar por correo? Si no lo recibiste, con gusto te enviaremos otro.
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="p-3 text-xs bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-800 rounded-sm text-green-800 dark:text-green-300">
                Se ha enviado un nuevo enlace de verificación a la dirección de correo electrónico que proporcionaste durante el registro.
            </div>
        @endif

        <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-4">
            <form method="POST" action="{{ route('verification.send') }}" class="w-full sm:w-auto">
                @csrf
                <button type="submit" 
                        class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold py-2 px-4 rounded-sm transition-colors text-xs shadow-sm">
                    Reenviar correo de verificación
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto text-center sm:text-right">
                @csrf
                <button type="submit" class="underline text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                    Cerrar sesión
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>

