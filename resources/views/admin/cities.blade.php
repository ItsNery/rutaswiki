<x-app-layout>
    @section('title', 'Admin · Ciudades')

    <div class="py-12 bg-gray-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Ciudades</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Gestiona todas las ciudades del sitio.</p>
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
                                <th class="px-4 py-3 text-left">Estado / País</th>
                                <th class="px-4 py-3 text-center">Rutas</th>
                                <th class="px-4 py-3 text-center">Estado</th>
                                <th class="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($cities as $city)
                                <tr class="{{ $city->trashed() ? 'bg-red-50 dark:bg-red-950/20 text-gray-400' : '' }}">
                                    <td class="px-4 py-3 font-semibold">
                                        @if($city->trashed())
                                            <s>{{ $city->name }}</s>
                                        @else
                                            {{ $city->name }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">{{ $city->state }}, {{ $city->country }}</td>
                                    <td class="px-4 py-3 text-center">{{ $city->transitRoutes()->withTrashed()->count() }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if($city->trashed())
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">Eliminada</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">Activa</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right space-x-2">
                                        @if($city->trashed())
                                            <form action="{{ route('admin.cities.restore', $city->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-xs font-bold text-green-600 hover:text-green-800 dark:text-green-400">Restaurar</button>
                                            </form>
                                            <form action="{{ route('admin.cities.force-delete', $city->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar permanentemente esta ciudad y todos sus datos? No se puede deshacer.');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-xs font-bold text-red-600 hover:text-red-800 dark:text-red-400">Borrar永久</button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.cities.delete', $city) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta ciudad? Las rutas se ocultarán.');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-xs font-bold text-red-600 hover:text-red-800 dark:text-red-400">Eliminar</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-400">No hay ciudades registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($cities->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                        {{ $cities->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
