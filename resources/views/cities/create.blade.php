<x-app-layout>
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <style>
            #city-map {
                height: 400px;
                border-radius: 0.375rem;
                z-index: 10;
            }
        </style>
    @endpush

    <div class="py-12 bg-gray-50 dark:bg-gray-900 min-h-screen" x-data="cityCreator()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-6 flex justify-between items-center px-4 sm:px-0">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Registrar Nueva Ciudad</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Añade una ciudad, municipio o comunidad para empezar a trazar sus rutas de transporte.</p>
                </div>
                <a href="{{ route('cities.index') }}" class="text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                    &larr; Volver al directorio
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Registration Form (Left) -->
                <div class="lg:col-span-1 bg-white dark:bg-gray-800 shadow rounded-lg p-6 flex flex-col justify-between">
                    <form action="{{ route('cities.store') }}" method="POST" class="space-y-6">
                        @csrf
                        
                        <!-- Hidden coordinates -->
                        <input type="hidden" name="latitude" x-model="latitude">
                        <input type="hidden" name="longitude" x-model="longitude">
                        <input type="hidden" name="country" value="México">

                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre de la Ciudad / Municipio / Comunidad</label>
                            <input type="text" id="name" name="name" x-model="name" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                   placeholder="Ej. Apizaco, San Andrés Buenavista">
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- State -->
                        <div>
                            <label for="state" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado</label>
                            <select id="state" name="state" x-model="state" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <option value="" disabled>Selecciona un estado...</option>
                                <option value="Aguascalientes">Aguascalientes</option>
                                <option value="Baja California">Baja California</option>
                                <option value="Baja California Sur">Baja California Sur</option>
                                <option value="Campeche">Campeche</option>
                                <option value="Chiapas">Chiapas</option>
                                <option value="Chihuahua">Chihuahua</option>
                                <option value="Ciudad de México">Ciudad de México</option>
                                <option value="Coahuila">Coahuila</option>
                                <option value="Colima">Colima</option>
                                <option value="Durango">Durango</option>
                                <option value="Estado de México">Estado de México</option>
                                <option value="Guanajuato">Guanajuato</option>
                                <option value="Guerrero">Guerrero</option>
                                <option value="Hidalgo">Hidalgo</option>
                                <option value="Jalisco">Jalisco</option>
                                <option value="Michoacán">Michoacán</option>
                                <option value="Morelos">Morelos</option>
                                <option value="Nayarit">Nayarit</option>
                                <option value="Nuevo León">Nuevo León</option>
                                <option value="Oaxaca">Oaxaca</option>
                                <option value="Puebla">Puebla</option>
                                <option value="Querétaro">Querétaro</option>
                                <option value="Quintana Roo">Quintana Roo</option>
                                <option value="San Luis Potosí">San Luis Potosí</option>
                                <option value="Sinaloa">Sinaloa</option>
                                <option value="Sonora">Sonora</option>
                                <option value="Tabasco">Tabasco</option>
                                <option value="Tamaulipas">Tamaulipas</option>
                                <option value="Tlaxcala">Tlaxcala</option>
                                <option value="Veracruz">Veracruz</option>
                                <option value="Yucatán">Yucatán</option>
                                <option value="Zacatecas">Zacatecas</option>
                            </select>
                            <x-input-error :messages="$errors->get('state')" class="mt-2" />
                        </div>

                        <!-- Zoom level -->
                        <div>
                            <label for="zoom_level" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nivel de Zoom por defecto</label>
                            <select id="zoom_level" name="zoom_level" x-model="zoom_level" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <option value="11">11 - Área metropolitana grande</option>
                                <option value="12">12 - Ciudad grande / mediana (Defecto)</option>
                                <option value="13">13 - Ciudad mediana / pequeña</option>
                                <option value="14">14 - Localidad / Pueblo</option>
                                <option value="15">15 - Comunidad pequeña / Detalle alto</option>
                            </select>
                            <p class="text-[10px] text-gray-400 mt-1">Determina qué tan cerca se verá el mapa de inicio en esta ciudad.</p>
                        </div>

                        <!-- Coordinates feedback indicators -->
                        <div class="p-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded text-xs space-y-1.5">
                            <span class="font-bold text-gray-700 dark:text-gray-300 block mb-1">Coordenadas del Centro:</span>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Latitud:</span>
                                <span class="font-mono" x-text="latitude ? latitude.toFixed(6) : 'No seleccionada'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Longitud:</span>
                                <span class="font-mono" x-text="longitude ? longitude.toFixed(6) : 'No seleccionada'"></span>
                            </div>
                            <span x-show="!latitude || !longitude" class="text-[10px] text-amber-600 dark:text-amber-400 block pt-1">
                                * Por favor haz clic en el mapa de la derecha para fijar el centro de la ciudad.
                            </span>
                        </div>

                        <button type="submit" :disabled="!latitude || !longitude"
                                class="w-full inline-flex items-center justify-center px-4 py-2.5 border border-transparent text-sm font-bold rounded-md text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 disabled:bg-gray-350 disabled:dark:bg-gray-750 disabled:cursor-not-allowed transition shadow shadow-sm">
                            Registrar Ciudad
                        </button>
                    </form>
                </div>

                <!-- Interactive Map Search and Selector (Right) -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        
                        <!-- Search Box -->
                        <div class="mb-4 space-y-2">
                            <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Ubicación Geográfica</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Busca la ciudad por su nombre para centrar el mapa y luego haz clic preciso en el centro para colocar el marcador.</p>
                            
                            <div class="flex gap-2 pt-1">
                                <input type="text" x-model="searchQuery" @keydown.enter.prevent="searchCity"
                                       class="flex-1 px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-700 rounded-md dark:bg-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Buscar ej. 'Apizaco, Tlaxcala' o 'Puebla, Mexico'...">
                                <button type="button" @click="searchCity" :disabled="searching"
                                        class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium transition disabled:bg-blue-450">
                                    <span x-show="!searching">Buscar en mapa</span>
                                    <span x-show="searching">Buscando...</span>
                                </button>
                            </div>
                            <p x-show="searchError" class="text-xs text-red-500" x-text="searchError"></p>
                        </div>

                        <!-- Map -->
                        <div id="city-map"></div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            function cityCreator() {
                return {
                    name: '',
                    state: '',
                    latitude: null,
                    longitude: null,
                    zoom_level: 12,
                    searchQuery: '',
                    searching: false,
                    searchError: '',
                    
                    map: null,
                    marker: null,

                    init() {
                        this.$nextTick(() => {
                            this.initMap();
                        });
                    },

                    initMap() {
                        // Start centered on Mexico roughly
                        this.map = L.map('city-map').setView([23.6345, -102.5528], 5);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '© OpenStreetMap contributors'
                        }).addTo(this.map);

                        // Capture clicks to set coordinate marker
                        this.map.on('click', (e) => {
                            this.setMarker(e.latlng.lat, e.latlng.lng);
                        });
                    },

                    setMarker(lat, lng) {
                        this.latitude = lat;
                        this.longitude = lng;

                        if (this.marker) {
                            this.marker.setLatLng([lat, lng]);
                        } else {
                            this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map);
                            this.marker.on('dragend', (e) => {
                                const position = this.marker.getLatLng();
                                this.latitude = position.lat;
                                this.longitude = position.lng;
                            });
                        }
                    },

                    searchCity() {
                        if (!this.searchQuery.trim()) return;

                        this.searching = true;
                        this.searchError = '';

                        const url = 'https://nominatim.openstreetmap.org/search?format=json&countrycodes=mx&q=' + encodeURIComponent(this.searchQuery);

                        fetch(url)
                            .then(response => response.json())
                            .then(data => {
                                this.searching = false;
                                if (data && data.length > 0) {
                                    const result = data[0];
                                    const lat = parseFloat(result.lat);
                                    const lon = parseFloat(result.lon);

                                    // Zoom into the result
                                    this.map.setView([lat, lon], this.zoom_level);
                                    this.setMarker(lat, lon);

                                    // Try to auto-populate the state dropdown if found in geocoder address
                                    const displayName = result.display_name.toLowerCase();
                                    const statesList = [
                                        "aguascalientes", "baja california", "campeche", "chiapas", "chihuahua", 
                                        "coahuila", "colima", "durango", "guanajuato", "guerrero", "hidalgo", 
                                        "jalisco", "michoacán", "morelos", "nayarit", "nuevo león", "oaxaca", 
                                        "puebla", "querétaro", "quintana roo", "san luis potosí", "sinaloa", 
                                        "sonora", "tabasco", "tamaulipas", "tlaxcala", "veracruz", "yucatán", 
                                        "zacatecas", "estado de méxico", "ciudad de méxico"
                                    ];

                                    for (let state of statesList) {
                                        if (displayName.includes(state)) {
                                            // Map display name to option values
                                            let optionVal = this.mapStateValue(state);
                                            if (optionVal) {
                                                this.state = optionVal;
                                            }
                                            break;
                                        }
                                    }

                                    // Try to auto-populate city name (the first component of query or result name)
                                    if (result.name) {
                                        this.name = result.name;
                                    }
                                } else {
                                    this.searchError = 'No se encontró la ubicación. Intenta con un término más general.';
                                }
                            })
                            .catch(err => {
                                this.searching = false;
                                this.searchError = 'Ocurrió un error al buscar la ubicación. Intenta de nuevo.';
                                console.error(err);
                            });
                    },

                    mapStateValue(stateName) {
                        const mapping = {
                            "aguascalientes": "Aguascalientes",
                            "baja california": "Baja California",
                            "baja california sur": "Baja California Sur",
                            "campeche": "Campeche",
                            "chiapas": "Chiapas",
                            "chihuahua": "Chihuahua",
                            "coahuila": "Coahuila",
                            "colima": "Colima",
                            "durango": "Durango",
                            "guanajuato": "Guanajuato",
                            "guerrero": "Guerrero",
                            "hidalgo": "Hidalgo",
                            "jalisco": "Jalisco",
                            "michoacán": "Michoacán",
                            "morelos": "Morelos",
                            "nayarit": "Nayarit",
                            "nuevo león": "Nuevo León",
                            "oaxaca": "Oaxaca",
                            "puebla": "Puebla",
                            "querétaro": "Querétaro",
                            "quintana roo": "Quintana Roo",
                            "san luis potosí": "San Luis Potosí",
                            "sinaloa": "Sinaloa",
                            "sonora": "Sonora",
                            "tabasco": "Tabasco",
                            "tamaulipas": "Tamaulipas",
                            "tlaxcala": "Tlaxcala",
                            "veracruz": "Veracruz",
                            "yucatán": "Yucatán",
                            "zacatecas": "Zacatecas",
                            "estado de méxico": "Estado de México",
                            "ciudad de méxico": "Ciudad de México"
                        };
                        return mapping[stateName] || null;
                    }
                };
            }
        </script>
    @endpush
</x-app-layout>
