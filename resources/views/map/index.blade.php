<x-app-layout>
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
        <style>
            #map { height: calc(100vh - 4rem); }
            .sidebar-height { height: calc(100vh - 4rem); }
        </style>
    @endpush

    <div class="flex flex-col md:flex-row min-h-screen bg-gray-100 dark:bg-gray-900 overflow-hidden"
         x-data="mapExplorer()">
        <!-- Sidebar -->
        <div class="w-full md:w-96 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col sidebar-height shadow-lg z-10">
            <div class="p-5 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Explorar mapa</h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $cities->count() }} ciudades &middot;
                            {{ $cities->sum('transit_routes_count') }} rutas
                        </p>
                    </div>
                    <a href="{{ route('cities.index') }}" class="text-xs text-gray-500 dark:text-gray-400 hover:text-blue-500 underline">Lista &rarr;</a>
                </div>
                <div class="space-y-2">
                    <input type="text" x-model="search" placeholder="Buscar ciudad o estado..."
                           class="w-full px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-700 rounded-md dark:bg-gray-800 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    <div class="flex gap-2">
                        <select x-model="stateFilter"
                                class="flex-1 px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-700 rounded-md dark:bg-gray-800 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                            <option value="all">Todos los estados</option>
                            <template x-for="st in states" :key="st">
                                <option x-text="st" :value="st"></option>
                            </template>
                        </select>
                    </div>
                    <label class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400 cursor-pointer select-none">
                        <input type="checkbox" x-model="onlyWithRoutes"
                               class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500">
                        Solo ciudades con rutas
                    </label>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700">
                <template x-for="city in filteredCities" :key="city.slug">
                    <div @click="selectCity(city)"
                         :class="selectedCity?.slug === city.slug ? 'bg-blue-50 dark:bg-blue-950/30 border-l-4 border-l-blue-500' : 'border-l-4 border-l-transparent hover:bg-gray-50 dark:hover:bg-gray-750'"
                         class="px-5 py-3 cursor-pointer transition flex justify-between items-start gap-2">
                        <div class="flex items-start gap-3 min-w-0">
                            <span class="w-3 h-3 rounded-full mt-1 shrink-0"
                                  :style="`background-color: ${city.transit_routes_count > 0 ? '#2563eb' : city.nearby_routes_count > 0 ? '#14b8a6' : '#9ca3af'}`"></span>
                            <div class="min-w-0">
                                <h3 class="font-semibold text-gray-900 dark:text-white text-sm truncate" x-text="city.name"></h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="city.state"></p>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-0.5 shrink-0">
                            <template x-if="city.transit_routes_count > 0">
                                <span class="text-xs font-semibold text-blue-600 dark:text-blue-400" x-text="city.transit_routes_count + (city.transit_routes_count === 1 ? ' ruta' : ' rutas')"></span>
                            </template>
                            <template x-if="city.transit_routes_count === 0 && city.nearby_routes_count > 0">
                                <span class="text-xs text-teal-600 dark:text-teal-400" x-text="city.nearby_routes_count + ' cerca'"></span>
                            </template>
                            <template x-if="city.transit_routes_count === 0 && city.nearby_routes_count === 0">
                                <span class="text-xs text-gray-400 italic">Sin rutas</span>
                            </template>
                        </div>
                    </div>
                </template>
                <template x-if="filteredCities.length === 0">
                    <div class="p-8 text-center text-gray-400 dark:text-gray-500 text-sm italic">
                        Ninguna ciudad coincide con los filtros.
                    </div>
                </template>
            </div>

            <div class="px-5 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-xs text-gray-500 dark:text-gray-500 space-y-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600 inline-block"></span>
                    <span>Con rutas registradas</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-teal-500 inline-block"></span>
                    <span>Rutas pasan cerca</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-gray-400 inline-block"></span>
                    <span>Sin rutas</span>
                </div>
                <div class="pt-1.5 text-[10px] text-gray-400 dark:text-gray-600">
                    <span x-text="filteredCities.length"></span> de {{ $cities->count() }} ciudades mostradas
                </div>
            </div>
        </div>

        <!-- Map -->
        <div class="flex-1 relative">
            <div id="map"></div>
            <template x-if="loadingRoutes">
                <div class="absolute top-4 left-1/2 -translate-x-1/2 z-[1000] bg-white dark:bg-gray-800 px-4 py-2 rounded-md shadow-lg text-xs font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2 border border-gray-200 dark:border-gray-700">
                    <svg class="animate-spin h-3.5 w-3.5 text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    Cargando rutas de <span x-text="selectedCity?.name"></span>...
                </div>
            </template>
            <template x-if="selectedCity && !loadingRoutes && routesCount > 0">
                <div class="absolute bottom-24 right-3 z-[1000] bg-white dark:bg-gray-800 px-3 py-2 rounded-md shadow text-xs border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 flex items-center gap-2">
                    <span class="text-blue-600 dark:text-blue-400 font-semibold" x-text="routesCount"></span>
                    rutas de <span class="font-semibold text-gray-900 dark:text-white" x-text="selectedCity?.name"></span>
                    <button @click="clearRoutes()" class="ml-1 text-gray-400 hover:text-red-500 transition" title="Limpiar rutas">&times;</button>
                </div>
            </template>
        </div>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
        <script>
            function mapExplorer() {
                return {
                    cities: @json($cities),
                    search: '',
                    onlyWithRoutes: false,
                    stateFilter: 'all',
                    map: null,
                    markerCluster: null,
                    markers: [],
                    routesLayer: null,
                    selectedCity: null,
                    loadingRoutes: false,
                    routesCount: 0,

                    get states() {
                        return [...new Set(this.cities.map(c => c.state))].sort();
                    },

                    get filteredCities() {
                        return this.cities.filter(c => {
                            const q = this.search.toLowerCase();
                            const matchSearch = !q || c.name.toLowerCase().includes(q) || c.state.toLowerCase().includes(q);
                            const matchState = this.stateFilter === 'all' || c.state === this.stateFilter;
                            const matchRoutes = !this.onlyWithRoutes || c.transit_routes_count > 0 || c.nearby_routes_count > 0;
                            return matchSearch && matchState && matchRoutes;
                        });
                    },

                    init() {
                        this.$nextTick(() => {
                            this.initMap();
                        });
                    },

                    initMap() {
                        this.map = L.map('map', {
                            zoomControl: true,
                            attributionControl: true
                        }).setView([23.6345, -102.5528], 5);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                        }).addTo(this.map);

                        this.markerCluster = L.markerClusterGroup({
                            chunkedLoading: true,
                            spiderfyOnMaxZoom: true,
                            showCoverageOnHover: false,
                            zoomToBoundsOnClick: true,
                            maxClusterRadius: 60
                        });

                        this.createMarkers();
                        this.map.addLayer(this.markerCluster);

                        if (this.markers.length > 0) {
                            const bounds = this.markerCluster.getBounds();
                            if (bounds.isValid()) {
                                this.map.fitBounds(bounds, { padding: [40, 40] });
                            }
                        }
                    },

                    createMarkers() {
                        this.markers = [];
                        this.cities.forEach(city => {
                            const marker = this.buildMarker(city);
                            this.markers.push(marker);
                            this.markerCluster.addLayer(marker);
                        });
                    },

                    buildMarker(city) {
                        const count = city.transit_routes_count || 0;
                        const nearby = city.nearby_routes_count || 0;
                        const hasRoutes = count > 0;
                        const hasNearby = nearby > 0;
                        const radius = Math.max(8, Math.min(18, 8 + (count + nearby) * 1.2));

                        let fillColor, fillOpacity;
                        if (hasRoutes) { fillColor = '#2563eb'; fillOpacity = 0.85; }
                        else if (hasNearby) { fillColor = '#14b8a6'; fillOpacity = 0.8; }
                        else { fillColor = '#9ca3af'; fillOpacity = 0.5; }

                        const marker = L.circleMarker([city.latitude, city.longitude], {
                            radius, fillColor, fillOpacity,
                            color: '#ffffff', weight: 2, opacity: 1
                        });

                        marker.bindTooltip(city.name, { sticky: true, direction: 'top', offset: [0, -5] });

                        let popup = '<div class="p-1" style="min-width:170px">' +
                            `<h3 class="font-bold text-sm text-gray-900">${city.name}</h3>` +
                            `<p class="text-xs text-gray-500">${city.state}, ${city.country}</p>`;
                        if (hasRoutes) {
                            popup += `<p class="text-xs mt-2"><span class="font-semibold text-blue-600">${count}</span> <span class="text-gray-600">${count === 1 ? 'ruta registrada' : 'rutas registradas'}</span></p>`;
                        }
                        if (hasNearby) {
                            popup += `<p class="text-xs ${hasRoutes ? '' : 'mt-2'}"><span class="font-semibold text-teal-600">${nearby}</span> <span class="text-gray-600">${nearby === 1 ? 'ruta pasa cerca' : 'rutas pasan cerca'}</span></p>`;
                        }
                        if (!hasRoutes && !hasNearby) {
                            popup += '<p class="text-xs mt-2 text-gray-400 italic">Sin rutas registradas</p>';
                        }
                        popup += `<a href="/cities/${city.slug}" class="text-xs font-semibold text-blue-600 hover:underline block mt-2">Explorar ciudad &rarr;</a></div>`;
                        marker.bindPopup(popup);

                        marker._citySlug = city.slug;
                        marker.on('click', () => {
                            this.selectCity(city);
                        });

                        return marker;
                    },

                    selectCity(city) {
                        if (this.selectedCity?.slug === city.slug) return;

                        this.selectedCity = city;

                        const marker = this.markers.find(m => m._citySlug === city.slug);
                        if (marker) {
                            this.map.setView([city.latitude, city.longitude], Math.max(city.zoom_level || 10, 10), { animate: true });
                            marker.openPopup();
                        }

                        if (city.transit_routes_count > 0 || city.nearby_routes_count > 0) {
                            this.loadCityRoutes(city);
                        } else {
                            this.clearRoutes();
                        }
                    },

                    loadCityRoutes(city) {
                        this.loadingRoutes = true;
                        this.routesCount = 0;
                        this.clearRoutesLayer();

                        fetch(`/api/cities/${city.slug}/routes`)
                            .then(res => res.json())
                            .then(data => {
                                if (data.features && data.features.length > 0) {
                                    this.routesLayer = L.geoJSON(data, {
                                        style: feature => ({
                                            color: feature.properties.color || '#3b82f6',
                                            weight: 4,
                                            opacity: 0.9
                                        }),
                                        onEachFeature: (feature, layer) => {
                                            const p = feature.properties;
                                            const popup = `<div class="p-1">
                                                <h4 class="font-bold text-xs text-gray-900">${p.route_number ? '[' + p.route_number + '] ' : ''}${p.name}</h4>
                                                <p class="text-[10px] text-gray-500 capitalize">${p.transport_type}</p>
                                                <a href="${p.url}" class="text-[10px] font-bold text-blue-600 hover:underline block mt-1">Ver ruta &rarr;</a>
                                            </div>`;
                                            layer.bindPopup(popup);
                                        }
                                    }).addTo(this.map);

                                    this.routesCount = data.features.length;

                                    const routesBounds = this.routesLayer.getBounds();
                                    if (routesBounds.isValid()) {
                                        this.map.fitBounds(routesBounds, { padding: [50, 50] });
                                    }
                                }
                                this.loadingRoutes = false;
                            })
                            .catch(() => {
                                this.loadingRoutes = false;
                            });
                    },

                    clearRoutesLayer() {
                        if (this.routesLayer) {
                            this.map.removeLayer(this.routesLayer);
                            this.routesLayer = null;
                        }
                    },

                    clearRoutes() {
                        this.clearRoutesLayer();
                        this.routesCount = 0;
                        this.selectedCity = null;

                        if (this.markers.length > 0) {
                            const bounds = this.markerCluster.getBounds();
                            if (bounds.isValid()) {
                                this.map.fitBounds(bounds, { padding: [40, 40] });
                            }
                        }
                    }
                };
            }
        </script>
    @endpush
</x-app-layout>
