<x-app-layout>
    @push('meta')
        <meta property="og:title" content="{{ $city->name }}, {{ $city->state }}">
        <meta property="og:description" content="{{ $city->transitRoutes->count() }} rutas de transporte público registradas en {{ $city->name }}, {{ $city->state }}. Explora el mapa interactivo en RutasWiki.">
        <meta property="og:url" content="{{ route('cities.show', $city) }}">
        <meta name="description" content="{{ $city->transitRoutes->count() }} rutas de transporte público registradas en {{ $city->name }}, {{ $city->state }}.">
    @endpush
    @section('title', $city->name . ' · Rutas de transporte')
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <style>
            #map {
                height: calc(100vh - 4rem);
            }
            .sidebar-height {
                height: calc(100vh - 4rem);
            }
            @media (max-width: 767px) {
                #map {
                    height: calc(100vh - 4rem);
                }
                .sidebar-mobile {
                    position: fixed;
                    top: 4rem;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    z-index: 50;
                    width: 100% !important;
                    height: calc(100vh - 4rem) !important;
                    transition: transform 0.25s ease-in-out;
                }
                .sidebar-mobile.hidden {
                    transform: translateX(-100%);
                }
                .sidebar-backdrop {
                    position: fixed;
                    inset: 0;
                    top: 4rem;
                    background: rgba(0,0,0,0.4);
                    z-index: 40;
                }
            }
        </style>
    @endpush

    <div class="flex flex-col md:flex-row min-h-screen bg-gray-100 dark:bg-gray-900 overflow-hidden"
         x-data="{ 
            mobileSidebarOpen: false,
            search: '',
            typeFilter: 'all',
            routes: [],
            loading: true,
            init() {
                fetch('{{ route('api.routes.index', $city) }}')
                    .then(res => res.json())
                    .then(data => {
                        this.routes = data.features || [];
                        this.loading = false;
                        initMap(data);
                    });
            },
            get filteredRoutes() {
                return this.routes.filter(r => {
                    const matchesSearch = r.properties.name.toLowerCase().includes(this.search.toLowerCase()) || 
                                          (r.properties.route_number && r.properties.route_number.includes(this.search));
                    const matchesType = this.typeFilter === 'all' || r.properties.transport_type === this.typeFilter;
                    return matchesSearch && matchesType;
                });
            },
            selectRoute(routeId) {
                highlightRouteOnMap(routeId);
                if (window.innerWidth < 768) {
                    this.mobileSidebarOpen = false;
                }
            },
            toggleSidebar() {
                this.mobileSidebarOpen = !this.mobileSidebarOpen;
            }
         }">
        
        <!-- Mobile backdrop -->
        <div x-show="mobileSidebarOpen" class="sidebar-backdrop md:hidden" @click="mobileSidebarOpen = false" x-cloak></div>

        <!-- Sidebar -->
        <div class="w-full md:w-96 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col sidebar-height shadow-lg z-10"
             :class="mobileSidebarOpen ? 'sidebar-mobile' : 'sidebar-mobile hidden'">

            <!-- Sidebar Header -->
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $city->name }}</h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $city->state }}, {{ $city->country }}</p>
                    </div>
                    <a href="{{ route('cities.index') }}" class="text-xs text-gray-500 dark:text-gray-400 hover:text-blue-500 underline">&larr; Volver</a>
                </div>

                @auth
                    <a href="{{ route('routes.create', $city) }}" 
                       class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-semibold rounded-md text-white bg-blue-600 hover:bg-blue-700 transition shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Agregar Nueva Ruta
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                       class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-700 text-sm font-semibold rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Entra para agregar rutas
                    </a>
                @endauth
            </div>

            <!-- Filters -->
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 space-y-2">
                <input type="text" x-model="search" placeholder="Buscar ruta..." 
                       class="w-full px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-700 rounded-md dark:bg-gray-800 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                
                <select x-model="typeFilter" 
                        class="w-full px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-700 rounded-md dark:bg-gray-800 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    <option value="all">Todos los transportes</option>
                    <option value="bus">Camión / Autobús</option>
                    <option value="combi">Combi / Colectivo</option>
                    <option value="metro">Metro</option>
                    <option value="tram">Tren Ligero / Tranvía</option>
                    <option value="trolley">Trolebús</option>
                    <option value="other">Otro</option>
                </select>
            </div>

            <!-- Routes List -->
            <div class="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700">
                <template x-if="loading">
                    <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                        <svg class="animate-spin h-5 w-5 mx-auto mb-2 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Cargando rutas...
                    </div>
                </template>

                <template x-if="!loading && filteredRoutes.length === 0">
                    <div class="p-6 text-center text-gray-500 dark:text-gray-400 text-sm">
                        No se encontraron rutas.
                    </div>
                </template>

                <template x-for="route in filteredRoutes" :key="route.id">
                    <div @click="selectRoute(route.id)" 
                         class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition flex justify-between items-start gap-2">
                        <div class="flex gap-2">
                            <span class="w-3 h-3 rounded-full mt-1.5 shrink-0" :style="'background-color: ' + route.properties.color"></span>
                            <div>
                                <h3 class="font-bold text-gray-900 dark:text-white text-sm">
                                    <span x-text="route.properties.route_number ? '[' + route.properties.route_number + '] ' : ''"></span>
                                    <span x-text="route.properties.name"></span>
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 capitalize" x-text="route.properties.transport_type"></p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 line-clamp-1" x-text="route.properties.description || 'Sin descripción'"></p>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <span class="text-xs font-semibold px-2 py-0.5 rounded" 
                                  :class="route.properties.vote_score >= 0 ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300'">
                                <span x-text="route.properties.vote_score > 0 ? '+' : ''"></span><span x-text="route.properties.vote_score"></span>
                            </span>
                            <a :href="route.properties.url" class="text-xs text-blue-600 dark:text-blue-400 font-semibold hover:underline mt-2">Detalles</a>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Map Container -->
        <div class="flex-1 relative">
            <!-- Mobile toggle button -->
            <button @click="toggleSidebar()" 
                    class="md:hidden absolute top-3 left-3 z-20 w-10 h-10 bg-white dark:bg-gray-800 rounded-full shadow-lg border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition focus:outline-none"
                    title="Mostrar lista de rutas">
                <svg x-show="!mobileSidebarOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                <svg x-show="mobileSidebarOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></svg>
            </button>
            <div id="map"></div>
        </div>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            let map;
            let geojsonLayer;
            let layersMap = {}; // Maps routeId to leaflet layer

            function initMap(geojson) {
                // Initialize map centered at city coordinates
                map = L.map('map').setView([{{ $city->latitude }}, {{ $city->longitude }}], {{ $city->zoom_level }});

                // Add OpenStreetMap Tile Layer
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);

                // Add GeoJSON Layer
                geojsonLayer = L.geoJSON(geojson, {
                    style: function(feature) {
                        return {
                            color: feature.properties.color || '#3b82f6',
                            weight: 4,
                            opacity: 0.8
                        };
                    },
                    onEachFeature: function(feature, layer) {
                        layersMap[feature.id] = layer;
                        
                        // Create popup
                        let popupContent = `
                            <div class="p-2">
                                <h3 class="font-bold text-sm text-gray-900">
                                    ${feature.properties.route_number ? '[' + feature.properties.route_number + '] ' : ''}
                                    ${feature.properties.name}
                                </h3>
                                <p class="text-xs text-gray-500 capitalize my-1">${feature.properties.transport_type}</p>
                                <a href="${feature.properties.url}" class="text-xs font-semibold text-blue-600 hover:underline block mt-2">Ver detalles de la ruta &rarr;</a>
                            </div>
                        `;
                        layer.bindPopup(popupContent);

                        // Highlight on hover
                        layer.on({
                            mouseover: function(e) {
                                let l = e.target;
                                l.setStyle({
                                    weight: 6,
                                    opacity: 1
                                });
                            },
                            mouseout: function(e) {
                                let l = e.target;
                                // Reset only if it is not selected
                                if (window.selectedLayer !== l) {
                                    geojsonLayer.resetStyle(l);
                                }
                            },
                            click: function(e) {
                                selectLayer(e.target);
                            }
                        });
                    }
                }).addTo(map);

                // Fit map to layer bounds if features exist
                if (geojson.features && geojson.features.length > 0) {
                    map.fitBounds(geojsonLayer.getBounds(), { padding: [50, 50] });
                }
            }

            function selectLayer(layer) {
                // Reset previous selection
                if (window.selectedLayer) {
                    geojsonLayer.resetStyle(window.selectedLayer);
                }
                window.selectedLayer = layer;
                
                // Style new selection
                layer.setStyle({
                    weight: 8,
                    opacity: 1
                });
                layer.bringToFront();
            }

            function highlightRouteOnMap(routeId) {
                let layer = layersMap[routeId];
                if (layer) {
                    selectLayer(layer);
                    layer.openPopup();
                    
                    // Pan map to layer center/bounds
                    let bounds = layer.getBounds();
                    map.fitBounds(bounds, { padding: [50, 50], maxZoom: 16 });
                }
            }
        </script>
    @endpush
</x-app-layout>
