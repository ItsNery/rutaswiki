<x-app-layout>
    <div class="py-8 bg-gray-100 dark:bg-gray-900 min-h-screen text-gray-800 dark:text-gray-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-sm shadow-sm p-6 mb-6">
                <form action="{{ route('search') }}" method="GET" class="flex gap-2">
                    <div class="relative flex-1">
                        <input type="text" name="q" value="{{ $q }}" required autofocus
                               class="w-full px-4 py-2 text-sm border border-gray-300 rounded-md dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Buscar rutas, ciudades...">
                    </div>
                    <button type="submit"
                            class="px-5 py-2 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-md transition shadow-sm">
                        Buscar
                    </button>
                </form>
            </div>

            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                Resultados para <strong class="text-gray-900 dark:text-white">&ldquo;{{ $q }}&rdquo;</strong>
                &middot; {{ $totalResults }} {{ $totalResults === 1 ? 'coincidencia' : 'coincidencias' }}
            </p>

            @if($routes->count() > 0)
                <div class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-sm shadow-sm mb-6 overflow-hidden">
                    <div class="bg-gray-50 dark:bg-gray-900/50 px-5 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-base font-bold font-serif text-gray-900 dark:text-white">
                            Rutas encontradas ({{ $routes->total() }})
                        </h2>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($routes as $route)
                            <div class="px-5 py-4 flex items-center justify-between gap-4 hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $route->color }}"></span>
                                    <div class="min-w-0">
                                        <a href="{{ route('routes.show', [$route->city, $route]) }}"
                                           class="font-semibold text-blue-600 dark:text-blue-400 hover:underline text-sm">
                                            {{ $route->route_number ? '[' . $route->route_number . '] ' : '' }}{{ $route->name }}
                                        </a>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $route->city->name }}, {{ $route->city->state }}
                                            &middot; <span class="capitalize">{{ $route->transport_type }}</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 shrink-0">
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $route->vote_score >= 0 ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' }}">
                                        {{ $route->vote_score > 0 ? '+' : '' }}{{ $route->vote_score }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4">
                    {{ $routes->appends(['q' => $q])->links() }}
                </div>
            @endif

            @if($cities->count() > 0)
                <div class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-sm shadow-sm overflow-hidden">
                    <div class="bg-gray-50 dark:bg-gray-900/50 px-5 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-base font-bold font-serif text-gray-900 dark:text-white">
                            Ciudades encontradas ({{ $cities->count() }})
                        </h2>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($cities as $city)
                            <div class="px-5 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                                <div>
                                    <a href="{{ route('cities.show', $city) }}"
                                       class="font-semibold text-blue-600 dark:text-blue-400 hover:underline text-sm">
                                        {{ $city->name }}
                                    </a>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $city->state }}</p>
                                </div>
                                <span class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded text-gray-600 dark:text-gray-300">
                                    {{ $city->transit_routes_count }} {{ $city->transit_routes_count === 1 ? 'ruta' : 'rutas' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($routes->count() === 0 && $cities->count() === 0)
                <div class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-sm shadow-sm p-10 text-center">
                    <p class="text-gray-400 dark:text-gray-500 text-sm italic">
                        No se encontraron resultados para &ldquo;{{ $q }}&rdquo;.
                    </p>
                    <p class="text-xs text-gray-400 mt-2">
                        Prueba con otro término o
                        <a href="{{ route('cities.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline font-semibold">explora el directorio de ciudades</a>.
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
