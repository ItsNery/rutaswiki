<x-app-layout>
    @section('title', 'Admin · Rutas')

    <div class="py-12 bg-gray-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Rutas</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Gestiona todas las rutas del sitio.</p>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">&larr; Volver al panel</a>
            </div>

            @if (session('success'))
                <div class="mb-6 bg-green-50 dark:bg-green-950/30 border border-green-300 dark:border-green-700 p-4 rounded-sm text-green-800 dark:text-green-200 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-gray-900/70 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                <th class="px-4 py-3 text-left">Nombre</th>
                                <th class="px-4 py-3 text-left">Ciudad</th>
                                <th class="px-4 py-3 text-center">Tipo</th>
                                <th class="px-4 py-3 text-center">Paradas</th>
                                <th class="px-4 py-3 text-center">Puntaje</th>
                                <th class="px-4 py-3 text-center">Estado</th>
                                <th class="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($routes as $route)
                                <tr class="{{ $route->trashed() ? 'bg-red-50 dark:bg-red-950/20 text-gray-400' : '' }}">
                                    <td class="px-4 py-3 font-semibold">
                                        @if($route->trashed())
                                            <s>{{ $route->route_number ? '[' . $route->route_number . '] ' : '' }}{{ $route->name }}</s>
                                        @else
                                            {{ $route->route_number ? '[' . $route->route_number . '] ' : '' }}{{ $route->name }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">{{ $route->city?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-center capitalize">{{ $route->transport_type }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if($route->has_designated_stops)
                                            <span class="text-xs font-bold text-blue-600 dark:text-blue-400">Designadas</span>
                                        @else
                                            <span class="text-xs font-bold text-yellow-600 dark:text-yellow-400">A solicitud</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono">{{ $route->vote_score }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if($route->trashed())
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">Eliminada</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">Activa</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right space-x-2">
                                        @if($route->trashed())
                                            <form action="{{ route('admin.routes.restore', $route->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-xs font-bold text-green-600 hover:text-green-800 dark:text-green-400">Restaurar</button>
                                            </form>
                                            <form action="{{ route('admin.routes.force-delete', $route->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar permanentemente esta ruta y todos sus datos? No se puede deshacer.');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-xs font-bold text-red-600 hover:text-red-800 dark:text-red-400">Borrar永久</button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.routes.delete', $route) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta ruta? Se ocultará del mapa.');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-xs font-bold text-red-600 hover:text-red-800 dark:text-red-400">Eliminar</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-gray-400">No hay rutas registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($routes->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                        {{ $routes->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
