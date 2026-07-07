<x-app-layout>
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <style>
            #diff-map { height: 450px; border-radius: 0.5rem; }
        </style>
    @endpush
    @section('title', 'Diff: ' . $route->name . ' · ' . $city->name)

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
                <a href="{{ route('routes.history', [$city, $route]) }}" class="hover:underline text-blue-600 dark:text-blue-400">Historial</a>
                <span class="mx-1">&gt;</span>
                <span class="text-gray-900 dark:text-white font-semibold">Diff</span>
            </div>

            <h1 class="text-3xl font-normal font-serif border-b border-gray-300 dark:border-gray-700 pb-2 mb-2 tracking-tight">
                Comparación de revisiones: {{ $route->name }}
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">
                Comparando
                @if($comparingWithCurrent)
                    la revisión del <strong>{{ $revision->created_at->format('d M Y, H:i') }}</strong> contra la <strong>versión actual publicada</strong>
                @else
                    la revisión del <strong>{{ $revision->created_at->format('d M Y, H:i') }}</strong>
                    @if($previousRevision)
                        contra la del <strong>{{ $previousRevision->created_at->format('d M Y, H:i') }}</strong>
                    @else
                        contra la <strong>versión original</strong>
                    @endif
                @endif
                &middot; Editor: <strong>{{ $revision->user?->name ?? 'Anónimo' }}</strong>
            </p>

            @if($revision->change_summary)
                <div class="bg-blue-50 dark:bg-blue-950/30 border-l-4 border-blue-500 p-4 mb-6 rounded-r-sm text-sm">
                    <span class="font-bold text-blue-800 dark:text-blue-200">Resumen del cambio:</span>
                    <span class="text-blue-700 dark:text-blue-300">{{ $revision->change_summary }}</span>
                </div>
            @endif

            <!-- Visual Revision Timeline -->
            <div class="bg-gray-50 dark:bg-gray-800/40 border border-gray-300 dark:border-gray-700 p-4 mb-6 rounded-sm">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white mb-3">Línea de tiempo de revisiones</h2>
                <div class="flex gap-4 overflow-x-auto pb-2 snap-x scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-700">
                    
                    <!-- Versión Actual Publicada Card -->
                    <div class="flex-none w-48 snap-start">
                        <a href="{{ route('routes.history.diff', [$city, $route, $route->revisions->first()]) }}?against=current" class="block">
                            <div class="border rounded p-2 transition bg-white dark:bg-gray-800 hover:shadow-sm cursor-pointer h-full flex flex-col justify-between 
                                        {{ $comparingWithCurrent ? 'border-green-500 ring-2 ring-green-100 dark:ring-green-900/30' : 'border-gray-200 dark:border-gray-700' }}">
                                <div id="mini-map-current" class="h-20 w-full bg-gray-100 dark:bg-gray-900 rounded mb-1.5 overflow-hidden pointer-events-none relative">
                                    <span class="absolute top-1 left-1 z-[1000] text-[8px] font-bold px-1.5 py-0.5 rounded bg-green-500 text-white shadow-sm font-sans">Actual</span>
                                </div>
                                <div class="text-[10px] space-y-0.5">
                                    <div class="font-bold text-gray-900 dark:text-white truncate">Versión Actual</div>
                                    <div class="text-gray-500 dark:text-gray-400 truncate">Editor: {{ $route->user?->name ?? 'Anónimo' }}</div>
                                    <div class="italic text-gray-400 truncate">"Versión publicada"</div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Revisions -->
                    @foreach($route->revisions as $rev)
                        <div class="flex-none w-48 snap-start">
                            <a href="{{ route('routes.history.diff', [$city, $route, $rev]) }}" class="block">
                                <div class="border rounded p-2 transition bg-white dark:bg-gray-800 hover:shadow-sm cursor-pointer h-full flex flex-col justify-between 
                                            {{ (!$comparingWithCurrent && $revision->id === $rev->id) ? 'border-blue-500 ring-2 ring-blue-100 dark:ring-blue-900/30' : (($previousRevision && $previousRevision->id === $rev->id) ? 'border-gray-500 ring-2 ring-gray-100 dark:ring-gray-900/30' : 'border-gray-200 dark:border-gray-700') }}">
                                    <div id="mini-map-{{ $rev->id }}" class="h-20 w-full bg-gray-100 dark:bg-gray-900 rounded mb-1.5 overflow-hidden pointer-events-none relative font-mono text-[10px]">
                                        @if(!$comparingWithCurrent && $revision->id === $rev->id)
                                            <span class="absolute top-1 left-1 z-[1000] text-[8px] font-bold px-1.5 py-0.5 rounded bg-blue-500 text-white shadow-sm font-sans">Nueva</span>
                                        @elseif($previousRevision && $previousRevision->id === $rev->id)
                                            <span class="absolute top-1 left-1 z-[1000] text-[8px] font-bold px-1.5 py-0.5 rounded bg-gray-500 text-white shadow-sm font-sans">Anterior</span>
                                        @endif
                                    </div>
                                    <div class="text-[10px] space-y-0.5">
                                        <div class="font-bold text-gray-900 dark:text-white truncate">{{ $rev->created_at->format('d M Y, H:i') }}</div>
                                        <div class="text-gray-500 dark:text-gray-400 truncate">Editor: {{ $rev->user?->name ?? 'Anónimo' }}</div>
                                        <div class="italic text-gray-600 dark:text-gray-300 truncate" title="{{ $rev->change_summary ?: 'Sin resumen de edición' }}">
                                            "{{ $rev->change_summary ?: 'Sin resumen' }}"
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach

                </div>
            </div>

            <!-- Map Overlay -->
            <div class="bg-gray-50 dark:bg-gray-800/40 border border-gray-300 dark:border-gray-700 p-4 mb-6 rounded-sm">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-3">
                    <span>Mapa comparativo</span>
                    <span class="flex items-center gap-2 text-xs font-normal font-mono">
                        <span class="inline-block w-3 h-0.5 bg-gray-400"></span> Anterior
                        <span class="inline-block w-3 h-0.5" style="background-color: {{ $route->color }}"></span> Nueva
                        @if(!empty($oldGeometryReturn) || !empty($newGeometryReturn))
                            <span class="inline-block w-3 h-0.5 border-t-2 border-dotted border-gray-400"></span> Ida ant.
                            <span class="inline-block w-3 h-0.5 border-t-2 border-dashed" style="border-color: {{ $route->color }}"></span> Vuelta nueva
                        @endif
                    </span>
                </h2>
                <div id="diff-map"></div>
            </div>

            <!-- Summary Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
                <div class="bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-800 rounded-sm p-4 text-center">
                    <p class="text-2xl font-bold text-green-700 dark:text-green-300">{{ $addedStops->count() }}</p>
                    <p class="text-xs text-green-600 dark:text-green-400 font-semibold">Paradas agregadas</p>
                </div>
                <div class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 rounded-sm p-4 text-center">
                    <p class="text-2xl font-bold text-red-700 dark:text-red-300">{{ $removedStops->count() }}</p>
                    <p class="text-xs text-red-600 dark:text-red-400 font-semibold">Paradas eliminadas</p>
                </div>
                <div class="bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 rounded-sm p-4 text-center">
                    @php
                        $oldCoordCount = isset($oldGeometry['coordinates']) ? count($oldGeometry['coordinates']) : 0;
                        $newCoordCount = isset($newGeometry['coordinates']) ? count($newGeometry['coordinates']) : 0;
                        $oldReturnCoordCount = isset($oldGeometryReturn['coordinates']) ? count($oldGeometryReturn['coordinates']) : 0;
                        $newReturnCoordCount = isset($newGeometryReturn['coordinates']) ? count($newGeometryReturn['coordinates']) : 0;
                        $coordDiff = ($newCoordCount + $newReturnCoordCount) - ($oldCoordCount + $oldReturnCoordCount);
                    @endphp
                    <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ $coordDiff > 0 ? '+' : '' }}{{ $coordDiff }}</p>
                    <p class="text-xs text-blue-600 dark:text-blue-400 font-semibold">Puntos en trazado{{ $coordDiff >= 0 ? ' (+)' : ' (-)' }}</p>
                </div>
                @if($oldRoundTrip || $newRoundTrip)
                <div class="bg-purple-50 dark:bg-purple-950/30 border border-purple-200 dark:border-purple-800 rounded-sm p-4 text-center">
                    <p class="text-2xl font-bold text-purple-700 dark:text-purple-300">
                        @if(!$oldRoundTrip && $newRoundTrip) +Ida+Vuelta
                        @elseif($oldRoundTrip && !$newRoundTrip) -Round Trip
                        @else Ida+Vuelta
                        @endif
                    </p>
                    <p class="text-xs text-purple-600 dark:text-purple-400 font-semibold">Trayecto de regreso</p>
                </div>
                @endif
                <div class="bg-gray-50 dark:bg-gray-800/40 border border-gray-300 dark:border-gray-700 rounded-sm p-4 text-center">
                    <p class="text-2xl font-bold text-gray-700 dark:text-gray-300">{{ $revision->created_at->diffForHumans() }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold">Tiempo desde edición</p>
                </div>
            </div>

            <!-- Stops Comparison Table -->
            <div class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-sm overflow-hidden shadow-sm mb-6">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                    Comparación de paradas
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-gray-900/70 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                <th class="px-4 py-2 text-left">#</th>
                                <th class="px-4 py-2 text-left">Anterior</th>
                                <th class="px-4 py-2 text-left">Nueva</th>
                                <th class="px-4 py-2 text-left">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($stopDiff as $row)
                                <tr class="{{ $row['status'] === 'added' ? 'bg-green-50 dark:bg-green-950/20' : ($row['status'] === 'removed' ? 'bg-red-50 dark:bg-red-950/20' : ($row['status'] === 'modified' ? 'bg-amber-50 dark:bg-amber-950/20' : '')) }}">
                                    <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400 font-mono text-xs">{{ $row['order'] }}</td>
                                    <td class="px-4 py-2.5">
                                        @if($row['old'])
                                            <span class="font-semibold text-gray-900 dark:text-white">{{ $row['old']['name'] }}</span>
                                            <span class="text-xs text-gray-400 dark:text-gray-500 block">{{ number_format($row['old']['latitude'], 4) }}, {{ number_format($row['old']['longitude'], 4) }}</span>
                                        @else
                                            <span class="text-gray-400 italic">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5">
                                        @if($row['new'])
                                            <span class="font-semibold text-gray-900 dark:text-white">{{ $row['new']['name'] }}</span>
                                            <span class="text-xs text-gray-400 dark:text-gray-500 block">{{ number_format($row['new']['latitude'], 4) }}, {{ number_format($row['new']['longitude'], 4) }}</span>
                                        @else
                                            <span class="text-gray-400 italic">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5">
                                        @if($row['status'] === 'added')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">Agregada</span>
                                        @elseif($row['status'] === 'removed')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">Eliminada</span>
                                        @elseif($row['status'] === 'modified')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">Modificada</span>
                                        @else
                                            <span class="text-gray-400 text-xs">Sin cambio</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500 text-sm italic">
                                        No hay paradas registradas en ninguna de las dos versiones.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Navigation -->
            <div class="flex justify-between items-center mt-6">
                <div class="flex gap-2">
                    <a href="{{ route('routes.history', [$city, $route]) }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 shadow-sm text-sm font-semibold rounded-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        &larr; Historial
                    </a>
                    @if(!$comparingWithCurrent)
                        <a href="{{ route('routes.history.diff', [$city, $route, $revision]) }}?against=current"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 shadow-sm text-sm font-semibold rounded-sm text-blue-700 dark:text-blue-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            vs Actual &rarr;
                        </a>
                    @endif
                </div>

                @if($previousRevision && !$comparingWithCurrent)
                    <a href="{{ route('routes.history.diff', [$city, $route, $previousRevision]) }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 shadow-sm text-sm font-semibold rounded-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Diff anterior &rarr;
                    </a>
                @endif
            </div>

        </div>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const map = L.map('diff-map').setView([{{ $city->latitude }}, {{ $city->longitude }}], {{ $city->zoom_level }});

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);

                const hasOld = @json(!empty($oldGeometry));
                const hasNew = @json(!empty($newGeometry));
                const hasOldReturn = @json(!empty($oldGeometryReturn));
                const hasNewReturn = @json(!empty($newGeometryReturn));

                const features = [];
                const bounds = [];

                // Old geometry (gray, dashed)
                if (hasOld) {
                    const oldGeo = @json($oldGeometry);
                    const oldLayer = L.geoJSON(oldGeo, {
                        style: {
                            color: '#9ca3af',
                            weight: 5,
                            opacity: 0.6,
                            dashArray: '8, 8'
                        }
                    }).addTo(map);
                    features.push(oldLayer);
                    if (oldLayer.getBounds().isValid()) bounds.push(oldLayer.getBounds());
                }

                // Old return geometry (gray, dotted)
                if (hasOldReturn) {
                    const oldReturnGeo = @json($oldGeometryReturn);
                    const oldReturnLayer = L.geoJSON(oldReturnGeo, {
                        style: {
                            color: '#9ca3af',
                            weight: 3,
                            opacity: 0.4,
                            dashArray: '4, 8'
                        }
                    }).addTo(map);
                    features.push(oldReturnLayer);
                    if (oldReturnLayer.getBounds().isValid()) bounds.push(oldReturnLayer.getBounds());
                }

                // New geometry (colored, solid)
                if (hasNew) {
                    const newGeo = @json($newGeometry);
                    const newLayer = L.geoJSON(newGeo, {
                        style: {
                            color: '{{ $route->color }}',
                            weight: 5,
                            opacity: 0.9
                        }
                    }).addTo(map);
                    features.push(newLayer);
                    if (newLayer.getBounds().isValid()) bounds.push(newLayer.getBounds());
                }

                // New return geometry (colored, dashed)
                if (hasNewReturn) {
                    const newReturnGeo = @json($newGeometryReturn);
                    const newReturnLayer = L.geoJSON(newReturnGeo, {
                        style: {
                            color: '{{ $route->color }}',
                            weight: 4,
                            opacity: 0.7,
                            dashArray: '8, 8'
                        }
                    }).addTo(map);
                    features.push(newReturnLayer);
                    if (newReturnLayer.getBounds().isValid()) bounds.push(newReturnLayer.getBounds());
                }

                // Stop markers - old (gray circles)
                const oldStops = @json($oldStops);
                if (oldStops && oldStops.length > 0) {
                    const oldMarkers = L.featureGroup();
                    oldStops.forEach(function(stop) {
                        const marker = L.circleMarker([stop.latitude, stop.longitude], {
                            radius: 7,
                            color: '#9ca3af',
                            fillColor: '#d1d5db',
                            fillOpacity: 0.7,
                            weight: 2,
                            dashArray: '3, 3'
                        }).bindPopup('<b>' + stop.name + '</b><br><span class="text-xs text-gray-500">Anterior</span>');
                        oldMarkers.addLayer(marker);
                    });
                    oldMarkers.addTo(map);
                    if (oldMarkers.getBounds().isValid()) bounds.push(oldMarkers.getBounds());
                }

                // Stop markers - new (colored circles)
                const newStops = @json($newStops);
                if (newStops && newStops.length > 0) {
                    const newMarkers = L.featureGroup();
                    newStops.forEach(function(stop) {
                        const marker = L.circleMarker([stop.latitude, stop.longitude], {
                            radius: 7,
                            color: '{{ $route->color }}',
                            fillColor: '{{ $route->color }}',
                            fillOpacity: 0.8,
                            weight: 2
                        }).bindPopup('<b>' + stop.name + '</b><br><span class="text-xs text-gray-500">Nueva</span>');
                        newMarkers.addLayer(marker);
                    });
                    newMarkers.addTo(map);
                    if (newMarkers.getBounds().isValid()) bounds.push(newMarkers.getBounds());
                }

                // Fit map to show all layers
                if (bounds.length > 0) {
                    const combined = L.featureGroup(
                        features.filter(function(f) { return f.getLayers && f.getLayers().length > 0; })
                    );
                    if (combined.getLayers().length > 0 && combined.getBounds().isValid()) {
                        map.fitBounds(combined.getBounds(), { padding: [40, 40] });
                    }
                }

                // Legend
                const legend = L.control({ position: 'bottomright' });
                legend.onAdd = function() {
                    const div = L.DomUtil.create('div', 'bg-white dark:bg-gray-800 px-3 py-2 rounded shadow text-xs border border-gray-200 dark:border-gray-700');
                    div.innerHTML = `
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span style="display:inline-block;width:20px;height:3px;background:#9ca3af;border-style:dashed;border-width:1px;border-color:#9ca3af;"></span>
                                <span class="text-gray-600 dark:text-gray-400">Anterior</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span style="display:inline-block;width:20px;height:3px;background:{{ $route->color }};"></span>
                                <span class="text-gray-600 dark:text-gray-400">Nueva</span>
                            </div>
                        </div>
                    `;
                    return div;
                };
                legend.addTo(map);

                // --- Visual Revision Timeline Mini-Maps ---
                const miniMapsData = [];

                miniMapsData.push({
                    id: 'current',
                    color: '{{ $route->color }}',
                    geometry: @json($route->geometry),
                    geometryReturn: @json($route->geometry_return)
                });

                @foreach($route->revisions as $rev)
                    miniMapsData.push({
                        id: '{{ $rev->id }}',
                        color: '#6366f1',
                        geometry: @json($rev->geometry),
                        geometryReturn: @json($rev->geometry_return)
                    });
                @endforeach

                function initMiniMap(mapId, item) {
                    const miniMap = L.map(mapId, {
                        zoomControl: false,
                        dragging: false,
                        touchZoom: false,
                        scrollWheelZoom: false,
                        doubleClickZoom: false,
                        boxZoom: false,
                        keyboard: false,
                        attributionControl: false
                    }).setView([{{ $city->latitude }}, {{ $city->longitude }}], {{ $city->zoom_level - 1 }});

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19
                    }).addTo(miniMap);

                    const layers = [];

                    if (item.geometry) {
                        const layer = L.geoJSON(item.geometry, {
                            style: {
                                color: item.color,
                                weight: 3,
                                opacity: 0.8
                            }
                        }).addTo(miniMap);
                        layers.push(layer);
                    }

                    if (item.geometryReturn) {
                        const layerReturn = L.geoJSON(item.geometryReturn, {
                            style: {
                                color: item.color,
                                weight: 2,
                                opacity: 0.6,
                                dashArray: '4, 4'
                            }
                        }).addTo(miniMap);
                        layers.push(layerReturn);
                    }

                    if (layers.length > 0) {
                        const group = L.featureGroup(layers);
                        if (group.getBounds().isValid()) {
                            miniMap.fitBounds(group.getBounds(), { padding: [5, 5] });
                        }
                    }
                }

                // Intersection Observer to lazy load mini maps in horizontal scroll viewport
                const observer = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            const element = entry.target;
                            const itemId = element.dataset.id;
                            const item = miniMapsData.find(d => d.id == itemId);
                            if (item && !element.dataset.loaded) {
                                initMiniMap(element.id, item);
                                element.dataset.loaded = "true";
                                observer.unobserve(element);
                            }
                        }
                    });
                }, { 
                    root: document.querySelector('.overflow-x-auto'), 
                    rootMargin: '0px 150px 0px 150px' 
                });

                // Set up elements for observation
                miniMapsData.forEach(function(item) {
                    const mapId = 'mini-map-' + item.id;
                    const element = document.getElementById(mapId);
                    if (element) {
                        element.dataset.id = item.id;
                        observer.observe(element);
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
