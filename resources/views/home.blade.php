<x-app-layout>
    @push('meta')
        <meta property="og:title" content="RutasWiki — Enciclopedia colaborativa de transporte público">
        <meta property="og:description" content="Explora, registra y edita rutas de camiones, combis y metros en México. La enciclopedia libre de transporte público que cualquiera puede editar.">
        <meta property="og:url" content="{{ route('home') }}">
        <meta name="description" content="Explora, registra y edita rutas de camiones, combis y metros en México. La enciclopedia libre de transporte público que cualquiera puede editar.">
    @endpush
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    @endpush
    <div class="py-6 bg-gray-100 dark:bg-gray-900 min-h-screen text-gray-800 dark:text-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Wikipedia Style Welcome Banner -->
            <div
                class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 p-6 mb-6 rounded-sm shadow-sm flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-normal tracking-tight text-gray-900 dark:text-white font-serif">
                        Bienvenidos a <span class="font-bold">RutasWiki</span>,
                    </h1>
                    <p class="text-sm md:text-base text-gray-600 dark:text-gray-400 mt-1">
                        la enciclopedia libre y colaborativa de transporte público que <span
                            class="font-semibold text-blue-600 dark:text-blue-400">cualquiera puede editar</span>.
                    </p>
                </div>

                <!-- Quick Search in Welcome box -->
                <div class="w-full md:w-80">
                    <form action="{{ route('search') }}" method="GET" class="flex">
                        <div class="relative w-full">
                            <input type="text" name="q" required
                                class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400"
                                placeholder="Buscar ruta o ciudad...">
                        </div>
                        <button type="submit"
                            class="ml-1 px-3 py-1.5 text-sm font-medium bg-gray-100 border border-gray-300 hover:bg-gray-200 dark:bg-gray-700 dark:border-gray-600 dark:hover:bg-gray-650 rounded-sm transition">
                            Buscar
                        </button>
                    </form>
                </div>
            </div>

            <!-- Two Column Wikipedia Main Page Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Left Column (Main Content - Featured & Portada Details) -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Rutas Cercanas a Mí (Dynamic Widget) -->
                    <div class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-sm shadow-sm"
                         x-data="{
                            loading: false,
                            coords: null,
                            routes: [],
                            errorMsg: '',
                            map: null,
                            geojsonLayer: null,
                            radius: 5,

                            locateUser() {
                                this.loading = true;
                                this.errorMsg = '';
                                this.routes = [];
                                
                                if (!navigator.geolocation) {
                                    this.errorMsg = 'Tu navegador no soporta geolocalización.';
                                    this.loading = false;
                                    return;
                                }

                                navigator.geolocation.getCurrentPosition(
                                    (position) => {
                                        this.coords = {
                                            lat: position.coords.latitude,
                                            lng: position.coords.longitude
                                        };
                                        this.fetchNearbyRoutes();
                                    },
                                    (error) => {
                                        console.error(error);
                                        this.errorMsg = 'No pudimos acceder a tu ubicación. Por favor, concede permisos en tu navegador.';
                                        this.loading = false;
                                    }
                                );
                            },

                            fetchNearbyRoutes() {
                                const url = `/api/nearby-routes?latitude=${this.coords.lat}&longitude=${this.coords.lng}&radius=${this.radius}`;
                                fetch(url)
                                    .then(res => res.json())
                                    .then(data => {
                                        this.routes = data.features || [];
                                        this.loading = false;
                                        this.$nextTick(() => {
                                            this.renderMap(data);
                                        });
                                    })
                                    .catch(err => {
                                        console.error(err);
                                        this.errorMsg = 'Error al cargar las rutas cercanas.';
                                        this.loading = false;
                                    });
                            },

                            renderMap(geojson) {
                                // If map doesn't exist, create it
                                if (!this.map) {
                                    this.map = L.map('nearby-map').setView([this.coords.lat, this.coords.lng], 13);
                                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                        maxZoom: 19,
                                        attribution: '© OpenStreetMap contributors'
                                    }).addTo(this.map);
                                } else {
                                    this.map.setView([this.coords.lat, this.coords.lng], 13);
                                    if (this.geojsonLayer) {
                                        this.map.removeLayer(this.geojsonLayer);
                                    }
                                }

                                // Add user location marker
                                L.marker([this.coords.lat, this.coords.lng])
                                    .addTo(this.map)
                                    .bindPopup('<b>Tu ubicación actual</b>')
                                    .openPopup();

                                if (geojson.features && geojson.features.length > 0) {
                                    this.geojsonLayer = L.geoJSON(geojson, {
                                        style: (feature) => ({
                                            color: feature.properties.color || '#3b82f6',
                                            weight: 4,
                                            opacity: 0.8
                                        }),
                                        onEachFeature: (feature, layer) => {
                                            let popup = `
                                                <div class='p-1'>
                                                    <h4 class='font-bold text-xs text-gray-900'>${feature.properties.route_number ? '['+feature.properties.route_number+'] ' : ''}${feature.properties.name}</h4>
                                                    <p class='text-[10px] text-gray-500 capitalize'>${feature.properties.transport_type}</p>
                                                    <a href='${feature.properties.url}' class='text-[10px] font-bold text-blue-600 hover:underline block mt-1'>Ver ruta &rarr;</a>
                                                </div>
                                            `;
                                            layer.bindPopup(popup);
                                        }
                                    }).addTo(this.map);

                                    // Fit bounds to include both user and routes
                                    const bounds = this.geojsonLayer.getBounds();
                                    bounds.extend([this.coords.lat, this.coords.lng]);
                                    this.map.fitBounds(bounds, { padding: [30, 30] });
                                }
                            }
                         }">
                        <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-2 border-b border-gray-300 dark:border-gray-700 flex justify-between items-center">
                            <h2 class="text-base font-bold font-serif text-gray-900 dark:text-white flex items-center gap-2">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                ¿Qué rutas pasan cerca de mí?
                            </h2>
                        </div>
                        <div class="p-5">
                            <!-- State 1: Ask for geolocation -->
                            <div x-show="!coords && !loading" class="text-center py-4 space-y-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Encuentra camiones, combis y líneas de metro que pasen cerca de tu ubicación actual en tiempo real.
                                </p>
                                <button type="button" @click="locateUser()"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-xs font-bold rounded-sm text-white bg-blue-600 hover:bg-blue-700 shadow transition">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    Buscar rutas cerca de mí
                                </button>
                            </div>

                            <!-- State 2: Loading -->
                            <div x-show="loading" class="text-center py-6 text-gray-500 dark:text-gray-400 text-sm">
                                <svg class="animate-spin h-6 w-6 mx-auto mb-3 text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Obteniendo ubicación y buscando rutas cercanas...
                            </div>

                            <!-- State 3: Error -->
                            <div x-show="errorMsg" class="bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-800 p-3 rounded text-xs text-red-700 dark:text-red-300 text-center" x-text="errorMsg"></div>

                            <!-- State 4: Results (Map & List) -->
                            <div x-show="coords && !loading" class="space-y-4" x-cloak>
                                <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
                                    <span class="text-gray-500">Ubicación fijada. Mostrando rutas en un radio de:</span>
                                    <div class="flex items-center gap-1.5">
                                        <select x-model="radius" @change="locateUser()" class="px-2 py-1 text-xs border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 dark:text-white rounded">
                                            <option value="2">2 km</option>
                                            <option value="5">5 km</option>
                                            <option value="10">10 km</option>
                                            <option value="15">15 km</option>
                                        </select>
                                        <button type="button" @click="locateUser()" class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded text-blue-600 dark:text-blue-400" title="Recargar ubicación">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.2"></path></svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Leaflet Map Box -->
                                <div id="nearby-map" class="h-64 w-full rounded border border-gray-300 dark:border-gray-700 shadow-inner z-0"></div>

                                <!-- List of nearby routes -->
                                <div class="border border-gray-200 dark:border-gray-700 rounded overflow-hidden">
                                    <div class="bg-gray-50 dark:bg-gray-900/30 px-3 py-1.5 border-b border-gray-200 dark:border-gray-700 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                        Líneas encontradas (<span x-text="routes.length"></span>)
                                    </div>
                                    <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-48 overflow-y-auto">
                                        <template x-for="route in routes" :key="route.id">
                                            <div class="p-3 text-xs hover:bg-gray-50 dark:hover:bg-gray-750 flex justify-between items-center transition">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-2.5 h-2.5 rounded-full" :style="'background-color: ' + route.properties.color"></span>
                                                    <div>
                                                        <span class="font-bold text-gray-900 dark:text-white" x-text="(route.properties.route_number ? '[' + route.properties.route_number + '] ' : '') + route.properties.name"></span>
                                                        <span class="text-[10px] text-gray-400 dark:text-gray-500 capitalize ml-1.5" x-text="route.properties.transport_type"></span>
                                                    </div>
                                                </div>
                                                <a :href="route.properties.url" class="text-blue-600 dark:text-blue-400 font-bold hover:underline">Ver &rarr;</a>
                                            </div>
                                        </template>
                                        <template x-if="routes.length === 0">
                                            <div class="p-4 text-center text-gray-400 dark:text-gray-500 italic">
                                                No se encontraron rutas dentro del radio seleccionado. Intenta aumentar el rango de búsqueda.
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Article of the Day (Ruta Destacada) -->
                    <div
                        class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-sm shadow-sm">
                        <div
                            class="bg-blue-50 dark:bg-blue-950/40 px-4 py-2 border-b border-gray-300 dark:border-gray-700">
                            <h2
                                class="text-base font-bold font-serif text-gray-900 dark:text-white flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                    </path>
                                </svg>
                                Ruta destacada
                            </h2>
                        </div>
                        <div class="p-5">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                <a href="/cities/1/routes/1" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    Centro - Buena Vista (La Barca)
                                </a>
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Tlaxco, Tlaxcala &middot;
                                Transporte: Combi</p>

                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-3 leading-relaxed">
                                Esta emblemática ruta de combi conecta el centro histórico de Tlaxco con la comunidad de
                                San Andrés Buenavista, donde se encuentra la famosa <strong>La Barca de la Fe</strong>,
                                un templo católico único construido en forma de carabela con motivos prehispánicos
                                otomíes. El trayecto cruza por carreteras rurales secundarias brindando conectividad
                                clave a pobladores locales.
                            </p>

                            <div
                                class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700/50 flex justify-between items-center">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Puntuación comunitaria:
                                    <strong>+5</strong></span>
                                <a href="/cities/1/routes/1"
                                    class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                    Leer artículo completo &rarr;
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- How to contribute (Como colaborar) -->
                    <div
                        class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-sm shadow-sm">
                        <div
                            class="bg-gray-50 dark:bg-gray-900/50 px-4 py-2 border-b border-gray-300 dark:border-gray-700">
                            <h2 class="text-base font-bold font-serif text-gray-900 dark:text-white">Cómo colaborar</h2>
                        </div>
                        <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                            <div class="space-y-2 flex flex-col justify-between">
                                <div>
                                    <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                        <span
                                            class="w-5 h-5 bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 rounded-full flex items-center justify-center text-xs font-mono">1</span>
                                        Crear Ciudades
                                    </h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-450 mt-1 leading-relaxed">
                                        Busca tu ciudad. Si no existe en la enciclopedia, regístrala para comenzar a
                                        agregar sus rutas.
                                    </p>
                                </div>
                                <div class="pt-1.5">
                                    <a href="{{ route('cities.create') }}"
                                        class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                        Registrar ciudad &rarr;
                                    </a>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                    <span
                                        class="w-5 h-5 bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 rounded-full flex items-center justify-center text-xs font-mono">2</span>
                                    Trazar el mapa
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Usa la herramienta del mapa para dibujar el recorrido exacto por las calles de tu
                                    colonia.
                                </p>
                            </div>
                            <div class="space-y-2">
                                <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                    <span
                                        class="w-5 h-5 bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 rounded-full flex items-center justify-center text-xs font-mono">3</span>
                                    Hacer correcciones
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Si una combi cambia de calle o hay una nueva parada, edita la ruta y deja un resumen
                                    detallado.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column (Sidebar Stats, Active Cities) -->
                <div class="space-y-6">

                    <!-- Cities Directory -->
                    <div
                        class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-sm shadow-sm">
                        <div
                            class="bg-gray-50 dark:bg-gray-900/50 px-4 py-2 border-b border-gray-300 dark:border-gray-700">
                            <h2 class="text-base font-bold font-serif text-gray-900 dark:text-white">Ciudades activas
                            </h2>
                        </div>
                        <div class="p-4 divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($featuredCities as $city)
                                <div class="py-2.5 flex justify-between items-center first:pt-0 last:pb-0 text-sm">
                                    <div>
                                        <a href="{{ route('cities.show', $city) }}"
                                            class="font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                            {{ $city->name }}
                                        </a>
                                        <p class="text-[11px] text-gray-400">{{ $city->state }}</p>
                                    </div>
                                    <span
                                        class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded text-gray-600 dark:text-gray-300">
                                        {{ $city->transit_routes_count }}
                                        {{ $city->transit_routes_count == 1 ? 'ruta' : 'rutas' }}
                                    </span>
                                </div>
                            @empty
                                <p class="text-xs text-gray-400 py-4 text-center">No hay ciudades registradas.</p>
                            @endforelse
                        </div>
                        <div
                            class="bg-gray-50 dark:bg-gray-900/30 px-4 py-2 border-t border-gray-200 dark:border-gray-700 text-center">
                            <a href="{{ route('cities.index') }}"
                                class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-semibold">
                                Directorio completo de ciudades &rarr;
                            </a>
                        </div>
                    </div>

                    <!-- Project Statistics -->
                    <div
                        class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-sm shadow-sm">
                        <div
                            class="bg-gray-50 dark:bg-gray-900/50 px-4 py-2 border-b border-gray-300 dark:border-gray-700">
                            <h2 class="text-base font-bold font-serif text-gray-900 dark:text-white">Estadísticas</h2>
                        </div>
                        <div class="p-4 text-xs space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Ciudades mapeadas:</span>
                                <span class="font-bold">{{ $featuredCities->count() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Rutas publicadas:</span>
                                <span class="font-bold">{{ \App\Models\TransitRoute::count() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Editores registrados:</span>
                                <span class="font-bold">{{ \App\Models\User::count() }}</span>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>
    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    @endpush
</x-app-layout>
