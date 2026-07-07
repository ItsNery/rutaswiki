<x-app-layout>
    <div class="py-6 bg-white dark:bg-gray-900 min-h-screen text-gray-800 dark:text-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Breadcrumbs -->
            <div class="mb-4 text-xs text-gray-500 dark:text-gray-400">
                <a href="{{ route('home') }}" class="hover:underline text-blue-600 dark:text-blue-400">Inicio</a>
                <span class="mx-1">&gt;</span>
                <a href="{{ route('cities.show', $city) }}" class="hover:underline text-blue-600 dark:text-blue-400">{{ $city->name }}</a>
                <span class="mx-1">&gt;</span>
                <a href="{{ route('routes.show', [$city, $route]) }}" class="hover:underline text-blue-600 dark:text-blue-400">{{ $route->name }}</a>
                <span class="mx-1">&gt;</span>
                <span class="text-gray-900 dark:text-white font-semibold">Historial de Revisiones</span>
            </div>

            <!-- Page Title with Wikipedia Style Line -->
            <h1 class="text-3xl font-normal font-serif border-b border-gray-300 dark:border-gray-700 pb-2 mb-2 tracking-tight">
                Historial de revisiones de «{{ $route->name }}»
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">
                Ver o restaurar versiones anteriores de este trayecto y paradas.
            </p>

            <!-- Wikipedia Style Revisions Box -->
            <div class="bg-gray-50 dark:bg-gray-800/40 border border-gray-300 dark:border-gray-700 p-4 mb-6 rounded-sm text-xs">
                <p class="font-bold text-gray-950 dark:text-white mb-2">Ayuda para el historial:</p>
                <p class="text-gray-600 dark:text-gray-400">
                    Las revisiones se ordenan cronológicamente de la más reciente a la más antigua. Cada versión registra el trazado de la calle, el listado de paradas en el momento y la justificación dada por el editor.
                </p>
            </div>

            <!-- Wikipedia Revision List Structure -->
            <div class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-sm p-6 shadow-sm">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white mb-4 font-mono">Registro de ediciones:</h2>
                
                <ul class="space-y-3 text-sm list-disc pl-5">
                    @forelse($revisions as $index => $revision)
                        <li class="text-gray-600 dark:text-gray-400">
                            <!-- Revision details in Wikipedia style line -->
                            <span class="text-xs text-gray-400 dark:text-gray-500 font-mono">
                                <a href="{{ route('routes.history.diff', [$city, $route, $revision]) }}?against=current" class="text-blue-600 dark:text-blue-400 hover:underline" title="Comparar con la versión actual">act</a>
                                @if(!$loop->last)
                                    <a href="{{ route('routes.history.diff', [$city, $route, $revision]) }}?against={{ $revisions[$loop->index + 1]->id }}" class="text-blue-600 dark:text-blue-400 hover:underline" title="Comparar con la versión anterior">prev</a>
                                @else
                                    <span class="text-gray-400">prev</span>
                                @endif
                                . .
                            </span>
                            <a href="{{ route('routes.history.diff', [$city, $route, $revision]) }}" class="font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                {{ $revision->created_at->format('d M Y, H:i') }}
                            </a>
                            <span class="text-gray-400 font-normal">. .</span>
                            <strong class="text-gray-900 dark:text-white font-semibold">
                                {{ $revision->user?->name ?? 'Anónimo' }}
                            </strong>
                            <span class="text-xs text-gray-400">
                                (Discusión | contribuciones)
                            </span>
                            <span class="text-gray-400 font-normal">. .</span>
                            <span class="text-xs font-mono text-gray-500">
                                ({{ strlen(json_encode($revision->geometry)) }} bytes)
                            </span>
                            <span class="text-gray-400 font-normal">. .</span>
                            <span class="text-gray-700 dark:text-gray-300 italic">
                                ({{ $revision->change_summary ?: 'Sin resumen de edición' }})
                            </span>
                        </li>
                    @empty
                        <div class="text-center py-8 text-gray-400 dark:text-gray-500 text-sm list-none">
                            Esta ruta no cuenta con revisiones anteriores. La versión actual es la original.
                        </div>
                    @endforelse
                </ul>

                <!-- Pagination -->
                @if($revisions->hasPages())
                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $revisions->links() }}
                    </div>
                @endif
            </div>

            <div class="mt-6">
                <a href="{{ route('routes.show', [$city, $route]) }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 shadow-sm text-sm font-semibold rounded-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    &larr; Volver al artículo de la ruta
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
