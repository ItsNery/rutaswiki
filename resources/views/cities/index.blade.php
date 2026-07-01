<x-app-layout>
    <div class="py-12 bg-gray-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Header and search -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-8">
                <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Ciudades Disponibles</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Selecciona una ciudad para ver sus rutas de transporte público o registrar una nueva.</p>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-3 items-center flex-1 max-w-2xl justify-end">
                        <form action="{{ route('cities.index') }}" method="GET" class="w-full sm:max-w-md flex gap-2">
                            <input type="text" name="search" value="{{ $search ?? '' }}"
                                class="block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 text-sm"
                                placeholder="Buscar por nombre, estado o país...">
                            <button type="submit" 
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition font-medium text-sm">
                                Buscar
                            </button>
                        </form>
                        @auth
                            <a href="{{ route('cities.create') }}" class="w-full sm:w-auto px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-md transition text-center shadow-sm text-sm shrink-0">
                                + Registrar Ciudad
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="w-full sm:w-auto px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-750 text-gray-700 dark:text-gray-350 font-medium rounded-md transition text-center border border-gray-300 dark:border-gray-750 text-sm shrink-0">
                                Iniciar sesión para agregar ciudad
                            </a>
                        @endauth
                    </div>
                </div>
            </div>

            <!-- Cities list -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($cities as $city)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg hover:shadow-md transition duration-200 flex flex-col justify-between">
                        <div class="p-6">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $city->name }}</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ $city->state }}, {{ $city->country }}</p>
                            
                            <div class="flex justify-between items-center text-sm border-t border-gray-100 dark:border-gray-700 pt-4">
                                <span class="text-gray-500 dark:text-gray-400">Coordenadas:</span>
                                <span class="font-mono text-gray-600 dark:text-gray-300">
                                    {{ number_format($city->latitude, 4) }}, {{ number_format($city->longitude, 4) }}
                                </span>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800 px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                                {{ $city->transit_routes_count }} {{ $city->transit_routes_count == 1 ? 'ruta' : 'rutas' }}
                            </span>
                            <a href="{{ route('cities.show', $city) }}" 
                                class="text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition">
                                Explorar &rarr;
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white dark:bg-gray-800 rounded-lg p-12 text-center shadow">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No encontramos ciudades</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Prueba con otra búsqueda o regresa al inicio.</p>
                        <div class="mt-6">
                            <a href="{{ route('cities.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition">
                                Ver todas las ciudades
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $cities->links() }}
            </div>

        </div>
    </div>
</x-app-layout>
