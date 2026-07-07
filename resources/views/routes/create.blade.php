<x-app-layout>
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
        <style>
            #map {
                height: 500px;
                border-radius: 0.5rem;
            }
            [x-cloak] {
                display: none !important;
            }
        </style>
    @endpush

    <div class="py-12 bg-gray-50 dark:bg-gray-900 min-h-screen" 
         x-data="routeEditor()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-6 flex justify-between items-center px-4 sm:px-0">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dibujar Nueva Ruta</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Ciudad: {{ $city->name }}, {{ $city->state }}</p>
                </div>
                <a href="{{ route('cities.show', $city) }}" class="text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                    &larr; Volver al mapa
                </a>
            </div>

            <!-- Draft restore banner -->
            <div x-show="draftAvailable && !draftRestored" 
                 class="mb-6 bg-amber-50 dark:bg-amber-950/30 border border-amber-300 dark:border-amber-700 p-4 rounded-sm text-amber-800 dark:text-amber-200 text-sm flex items-center justify-between gap-4"
                 x-cloak x-transition>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Tienes un borrador guardado de tu sesión anterior. <strong x-text="draftAge"></strong></span>
                </div>
                <div class="flex gap-2 shrink-0">
                    <button @click="restoreDraft()" class="px-3 py-1 text-xs font-bold bg-amber-500 hover:bg-amber-600 text-white rounded-sm transition">Restaurar</button>
                    <button @click="discardDraft()" class="px-3 py-1 text-xs font-bold border border-amber-300 dark:border-amber-600 hover:bg-amber-100 dark:hover:bg-amber-900/30 rounded-sm transition">Descartar</button>
                </div>
            </div>

            <!-- Autosave indicator -->
            <div x-show="draftRestored || draftSaved"
                 class="mb-4 text-xs text-gray-400 dark:text-gray-500 flex items-center gap-1.5 px-4 sm:px-0"
                 x-cloak>
                <svg x-show="draftSaved" class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span x-text="draftSaved ? 'Borrador guardado automáticamente' : ''"></span>
            </div>

            @if ($errors->any())
                <div class="mb-6 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 p-4 rounded-sm text-red-800 dark:text-red-300 text-sm">
                    <div class="font-bold mb-1">Por favor corrige los siguientes errores:</div>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Editor Form (Left) -->
                <div class="lg:col-span-1 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <form id="route-form" action="{{ route('routes.store', $city) }}" method="POST" @submit.prevent="submitForm">
                        @csrf
                        
                        <!-- Hidden fields for Leaflet data -->
                        <input type="hidden" name="geometry" :value="JSON.stringify(geometry)">
                        <input type="hidden" name="geometry_return" :value="JSON.stringify(geometryReturn)">
                        <input type="hidden" name="round_trip" :value="roundTrip ? '1' : ''">
                        <template x-for="(stop, index) in stops" :key="index">
                            <div>
                                <input type="hidden" :name="'stops[' + index + '][name]'" :value="stop.name">
                                <input type="hidden" :name="'stops[' + index + '][latitude]'" :value="stop.latitude">
                                <input type="hidden" :name="'stops[' + index + '][longitude]'" :value="stop.longitude">
                                <input type="hidden" :name="'stops[' + index + '][description]'" :value="stop.description || ''">
                            </div>
                        </template>

                        <!-- Hidden fields for Schedules data -->
                        <template x-for="(sched, index) in schedules.filter(s => s.is_active)" :key="sched.day_type">
                            <div>
                                <input type="hidden" :name="'schedules[' + index + '][day_type]'" :value="sched.day_type">
                                <input type="hidden" :name="'schedules[' + index + '][start_time]'" :value="sched.start_time">
                                <input type="hidden" :name="'schedules[' + index + '][end_time]'" :value="sched.end_time">
                                <input type="hidden" :name="'schedules[' + index + '][frequency_minutes]'" :value="sched.frequency_minutes">
                            </div>
                        </template>

                        <div class="space-y-6">
                            <!-- Route Number -->
                            <div>
                                <label for="route_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Número de Ruta / Código (opcional)</label>
                                <input type="text" id="route_number" name="route_number" x-model="form.route_number"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md dark:bg-gray-700 dark:text-white dark:border-gray-600"
                                       placeholder="Ej. 102, R-15">
                            </div>

                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre de la Ruta</label>
                                <input type="text" id="name" name="name" x-model="form.name" required
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md dark:bg-gray-700 dark:text-white dark:border-gray-600"
                                       placeholder="Ej. Centro &rarr; Lomas del Sol">
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción / Detalles (horarios, costo, etc.)</label>
                                <textarea id="description" name="description" x-model="form.description" rows="3"
                                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md dark:bg-gray-700 dark:text-white dark:border-gray-600"
                                          placeholder="Ej. Pasa cada 15 min. Costo: $10 pesos. Pasa por el mercado central."></textarea>
                            </div>

                            <!-- Type of transport -->
                            <div>
                                <label for="transport_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Transporte</label>
                                <select id="transport_type" name="transport_type" x-model="form.transport_type" required
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md dark:bg-gray-700 dark:text-white dark:border-gray-600">
                                    <option value="bus">Camión / Autobús</option>
                                    <option value="combi">Combi / Colectivo</option>
                                    <option value="metro">Metro</option>
                                    <option value="tram">Tren Ligero / Tranvía</option>
                                    <option value="trolley">Trolebús</option>
                                    <option value="other">Otro</option>
                                </select>
                            </div>

                            <!-- Color picker -->
                            <div>
                                <label for="color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Color de Ruta en Mapa</label>
                                <div class="mt-1 flex items-center gap-3">
                                    <input type="color" id="color" name="color" x-model="form.color" @input="updateLineColor"
                                           class="w-12 h-10 border border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600">
                                    <span class="text-sm font-mono text-gray-500" x-text="form.color"></span>
                                </div>
                            </div>

                            <!-- Round Trip Toggle -->
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" x-model="roundTrip" @change="onRoundTripChange()"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-gray-750">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">El regreso <strong>no</strong> es por el mismo camino</span>
                                </label>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 ml-6">
                                    Activa esta opción si la vuelta pasa por calles diferentes.
                                </p>

                                <div x-show="roundTrip" x-transition class="mt-3 flex gap-2">
                                    <button type="button" @click="editingMode = 'outbound'"
                                            :class="editingMode === 'outbound' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                                            class="px-3 py-1.5 text-xs font-bold rounded-sm transition flex-1 text-center">
                                        Ida
                                    </button>
                                    <button type="button" @click="editingMode = 'return'"
                                            :class="editingMode === 'return' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                                            class="px-3 py-1.5 text-xs font-bold rounded-sm transition flex-1 text-center">
                                        Vuelta
                                    </button>
                                </div>
                                <div x-show="roundTrip" class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    <span x-show="editingMode === 'outbound'">Dibujando: <strong>Ida</strong> (línea sólida)</span>
                                    <span x-show="editingMode === 'return'">Dibujando: <strong>Vuelta</strong> (línea punteada)</span>
                                </div>
                            </div>

                            <!-- Has Designated Stops -->
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" x-model="hasDesignatedStops"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-gray-750">
                                    <input type="hidden" name="has_designated_stops" :value="hasDesignatedStops ? '1' : '0'">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Tiene paradas designadas</span>
                                </label>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 ml-6">
                                    Si está activada, la ruta solo se detiene en las paradas marcadas. Si no, para en cualquier punto del recorrido.
                                </p>
                            </div>

                            <!-- Additional cities -->
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ciudades adicionales</label>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 mb-2">
                                    Si esta ruta también pasa por otras ciudades, selecciónalas aquí.
                                </p>
                                <select name="additional_cities[]" multiple x-ref="citySelect"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md dark:bg-gray-700 dark:text-white dark:border-gray-600 text-sm"
                                        size="5">
                                    @foreach($cities as $c)
                                        <option value="{{ $c->id }}" {{ $c->id === $city->id ? 'disabled' : '' }}>
                                            {{ $c->name }}{{ $c->id === $city->id ? ' (principal)' : '' }}
                                        </option>
                                    @endforeach
                                </select>

                                <div class="mt-2">
                                    <template x-if="!geometry">
                                        <p class="text-xs text-gray-400">Dibuja la ruta en el mapa para sugerir ciudades cercanas.</p>
                                    </template>
                                    <template x-if="geometry">
                                        <div>
                                            <button type="button" @click="fetchSuggestions()"
                                                    class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                                <i class="fa-solid fa-magnifying-glass"></i>
                                                <span x-text="loadingSuggestions ? 'Buscando...' : ' Buscar lugares cercanos'"></span>
                                            </button>
                                            <div x-show="loadingSuggestions" class="text-xs text-gray-400 mt-1">
                                                <span class="animate-pulse">Consultando OpenStreetMap...</span>
                                            </div>
                                            <div x-show="suggestedPlaces !== null && !loadingSuggestions" class="mt-1 space-y-1">
                                                <template x-if="suggestedPlaces !== null && suggestedPlaces.length === 0">
                                                    <p class="text-xs text-gray-400">No se encontraron lugares nuevos cerca.</p>
                                                </template>
                                                <template x-for="place in suggestedPlaces" :key="place.name">
                                                    <div class="flex items-center justify-between py-1 px-2 bg-gray-50 dark:bg-gray-800 rounded text-xs">
                                                        <span x-text="place.name"></span>
                                                        <span class="text-gray-400 ml-1" x-text="'(' + place.distance_km + ' km)'"></span>
                                                        <button type="button" @click="addSuggestedPlace(place)"
                                                                class="text-green-600 hover:text-green-800 font-medium ml-2 whitespace-nowrap">
                                                            <i class="fa-solid fa-plus"></i> Asociar
                                                        </button>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Horarios y Frecuencias Normalizados -->
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-4">
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-1 font-serif">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Configuración de Horarios
                                </h3>

                                <div class="space-y-4">
                                    <template x-for="(sched, idx) in schedules" :key="sched.day_type">
                                        <div class="border border-gray-200 dark:border-gray-700 rounded-sm p-3 space-y-3 bg-gray-50 dark:bg-gray-850">
                                            <label class="flex items-center cursor-pointer">
                                                <input type="checkbox" x-model="sched.is_active" @change="if (sched.is_active) fetchTimetable(sched);"
                                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-gray-750">
                                                <span class="ml-2 text-xs font-bold text-gray-700 dark:text-gray-350 capitalize" x-text="getDayLabel(sched.day_type)"></span>
                                            </label>

                                            <div x-show="sched.is_active" x-transition class="space-y-3 pt-1">
                                                <div class="grid grid-cols-2 gap-2">
                                                    <div>
                                                        <label class="block text-[10px] font-semibold text-gray-550 dark:text-gray-400">Hora Inicio</label>
                                                        <input type="time" x-model="sched.start_time" @change="fetchTimetable(sched)"
                                                               class="mt-1 block w-full px-2 py-1 text-xs border border-gray-300 rounded dark:bg-gray-700 dark:text-white dark:border-gray-600">
                                                    </div>
                                                    <div>
                                                        <label class="block text-[10px] font-semibold text-gray-550 dark:text-gray-400">Hora Fin</label>
                                                        <input type="time" x-model="sched.end_time" @change="fetchTimetable(sched)"
                                                               class="mt-1 block w-full px-2 py-1 text-xs border border-gray-300 rounded dark:bg-gray-700 dark:text-white dark:border-gray-600">
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-2 gap-2 items-center">
                                                    <div>
                                                        <label class="block text-[10px] font-semibold text-gray-550 dark:text-gray-400">Frecuencia (min)</label>
                                                        <input type="number" x-model.number="sched.frequency_minutes" @input="fetchTimetable(sched)" min="1"
                                                               class="mt-1 block w-full px-2 py-1 text-xs border border-gray-300 rounded dark:bg-gray-700 dark:text-white dark:border-gray-600"
                                                               placeholder="Ej. 15">
                                                    </div>
                                                    <div class="text-[10px] text-gray-500 dark:text-gray-400 pt-3">
                                                        Estimado: <span class="font-bold font-mono text-gray-800 dark:text-gray-250" x-text="sched.timetable ? sched.timetable.length : 0"></span> salidas.
                                                    </div>
                                                </div>

                                                <!-- AJAX generated schedule timeline preview -->
                                                <div x-show="sched.timetable && sched.timetable.length > 0" class="mt-2 text-[10px] font-mono text-gray-600 dark:text-gray-400 max-h-20 overflow-y-auto bg-white dark:bg-gray-900 border border-gray-250 dark:border-gray-750 p-2 rounded flex flex-wrap gap-1 leading-relaxed shadow-inner">
                                                    <template x-for="time in sched.timetable" :key="time">
                                                        <span class="px-1.5 py-0.5 bg-gray-50 dark:bg-gray-850 border border-gray-200 dark:border-gray-700 rounded text-center" x-text="time"></span>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Stops list -->
                            <div x-show="hasDesignatedStops || stops.length > 0">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Paradas agregadas (<span x-text="stops.length"></span>)</span>
                                </div>
                                <template x-if="!hasDesignatedStops && stops.length > 0">
                                    <div class="mb-2 p-2 bg-yellow-50 dark:bg-yellow-950/30 border border-yellow-300 dark:border-yellow-700 rounded text-xs text-yellow-800 dark:text-yellow-200">
                                        Esta ruta no tiene paradas designadas. Los pasajeros pueden solicitar paradas adicionales directamente al conductor con anticipación.
                                    </div>
                                </template>
                                <div class="max-h-48 overflow-y-auto space-y-2 border border-gray-200 dark:border-gray-700 rounded-md p-2">
                                    <template x-if="stops.length === 0">
                                        <p class="text-xs text-gray-400 text-center py-4">Haz click en el botón "Parada" en el mapa y colócala.</p>
                                    </template>
                                    <template x-for="(stop, index) in stops" :key="index">
                                        <div draggable="true"
                                             @dragstart="dragStart(index)"
                                             @dragover.prevent
                                             @dragenter="dragEnter(index)"
                                             @drop="dropStop(index)"
                                             @dragend="dragEnd()"
                                             :class="{'ring-2 ring-blue-400': dragOverIndex === index}"
                                             class="flex items-center justify-between text-xs bg-gray-50 dark:bg-gray-900 p-2 rounded cursor-grab active:cursor-grabbing">
                                            <div class="flex-1 min-w-0 pr-2">
                                                <input type="text" x-model="stop.name" class="font-semibold text-gray-800 dark:text-white bg-transparent border-0 p-0 focus:ring-0 focus:border-blue-500 w-full" placeholder="Nombre de parada">
                                                <input type="text" x-model="stop.description" class="text-gray-500 dark:text-gray-400 bg-transparent border-0 p-0 focus:ring-0 focus:border-blue-500 w-full mt-0.5 text-[10px]" placeholder="Comentario (opcional)">
                                                <p class="text-[10px] text-gray-400 dark:text-gray-500 truncate mt-0.5" x-text="'Lat: ' + Number(stop.latitude).toFixed(4) + ' Lng: ' + Number(stop.longitude).toFixed(4)"></p>
                                            </div>
                                            <button type="button" @click="removeStop(index)" class="text-red-500 hover:text-red-700">
                                                Eliminar
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Submit -->
                            <button type="submit" 
                                    class="w-full inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-bold rounded-md text-white bg-blue-600 hover:bg-blue-700 transition shadow">
                                Publicar Ruta
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Map Canvas (Right) -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <div class="mb-4">
                            <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Instrucciones de Dibujo:</h2>
                            <ul class="list-disc list-inside text-xs text-gray-600 dark:text-gray-400 space-y-1 mt-2">
                                <li>Haz click en el icono de <strong>Polyline (Línea)</strong> en la barra de herramientas del mapa para empezar a trazar la ruta.</li>
                                <li>Haz clics sucesivos en el mapa para trazar el trayecto. Haz doble-clic para finalizar la línea.</li>
                                <li>Haz click en el icono de <strong>Marker (Marcador)</strong> para colocar paradas oficiales sobre la ruta.</li>
                                <li><strong>Editar vértices:</strong> tras dibujar, haz clic en <kbd class="px-1 bg-gray-200 dark:bg-gray-700 rounded font-mono">✎</kbd> y arrastra los puntos blancos para ajustar el trazo.</li>
                            </ul>
                        </div>
                        <div id="map"></div>
                    </div>
                </div>

            </div>

        </div>

        <!-- Custom Modal for naming stops -->
        <div x-show="showStopModal" 
             class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-[9999]"
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            <div class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 shadow-2xl rounded-sm w-full max-w-sm border-t-4 border-t-blue-600 dark:border-t-blue-500 overflow-hidden"
                 @click.away="cancelStop()">
                <div class="p-5 space-y-4">
                    <h3 class="text-lg font-serif font-bold text-gray-900 dark:text-white border-b border-gray-250 dark:border-gray-750 pb-2">
                        Registrar Parada
                    </h3>
                    
                    <div>
                        <label for="modal_stop_name" class="block text-xs font-semibold text-gray-750 dark:text-gray-300 uppercase tracking-wider">Nombre de la Parada</label>
                        <input type="text" id="modal_stop_name" x-model="newStopName" @keydown.enter.prevent="confirmStop()"
                               class="mt-1.5 block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors shadow-inner"
                               placeholder="Ej. Entrada Principal, Cruce del Río">
                    </div>

                    <div>
                        <label for="modal_stop_description" class="block text-xs font-semibold text-gray-750 dark:text-gray-300 uppercase tracking-wider">Comentario / Descripción</label>
                        <textarea id="modal_stop_description" x-model="newStopDescription"
                                  class="mt-1.5 block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors shadow-inner"
                                  placeholder="Ej. La parada está frente al mercado" rows="2"></textarea>
                    </div>

                    <div class="pt-2 flex justify-end gap-3">
                        <button type="button" @click="cancelStop()"
                                class="px-4 py-2 border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750 text-gray-700 dark:text-gray-300 rounded-sm text-xs font-bold transition">
                            Cancelar
                        </button>
                        <button type="button" @click="confirmStop()"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white rounded-sm text-xs font-bold transition shadow-sm">
                            Confirmar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/leaflet-geometryutil@0.10.2/src/leaflet.geometryutil.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/leaflet-snap@0.0.4/leaflet.snap.js"></script>
        <script>
            function routeEditor() {
                return {
                    form: {
                        route_number: '',
                        name: '',
                        description: '',
                        transport_type: 'bus',
                        color: '#3b82f6'
                    },
                    geometry: null,
                    geometryReturn: null,
                    roundTrip: false,
                    hasDesignatedStops: false,
                    editingMode: 'outbound',
                    stops: [],
                    showStopModal: false,
                    newStopLatLng: null,
                    newStopLayer: null,
                    newStopName: '',
                    newStopDescription: '',
                    draggedIndex: null,
                    dragOverIndex: null,
                    tselect: null,
                    allCitiesData: @json($cities),
                    suggestedPlaces: null,
                    loadingSuggestions: false,

                    draftSaved: false,
                    draftAvailable: false,
                    draftRestored: false,
                    draftAge: '',

                    schedules: [
                        { day_type: 'weekday', is_active: true, start_time: '06:00', end_time: '22:00', frequency_minutes: 15, timetable: [] },
                        { day_type: 'saturday', is_active: false, start_time: '07:00', end_time: '20:00', frequency_minutes: 20, timetable: [] },
                        { day_type: 'sunday', is_active: false, start_time: '08:00', end_time: '18:00', frequency_minutes: 25, timetable: [] },
                        { day_type: 'holiday', is_active: false, start_time: '08:00', end_time: '18:00', frequency_minutes: 25, timetable: [] },
                    ],
                    
                    map: null,
                    drawControl: null,
                    drawnItems: null,

                    get storageKey() {
                        return 'route_draft_city_{{ $city->id }}';
                    },

                    init() {
                        this.$nextTick(() => {
                            this.initMap();
                            this.initTomSelect();
                            this.checkDraft();
                            this.fetchAllTimetables();
                            setInterval(() => this.saveDraft(), 5000);
                        });
                    },

                    initMap() {
                        this.map = L.map('map').setView([{{ $city->latitude }}, {{ $city->longitude }}], {{ $city->zoom_level }});

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '© OpenStreetMap contributors'
                        }).addTo(this.map);

                        this.drawnItems = new L.FeatureGroup();
                        this.map.addLayer(this.drawnItems);

                        // Configure leaflet draw options
                        this.drawControl = new L.Control.Draw({
                            edit: {
                                featureGroup: this.drawnItems,
                                remove: true
                            },
                            draw: {
                                polyline: {
                                    shapeOptions: {
                                        color: this.form.color,
                                        weight: 5
                                    }
                                },
                                marker: true, // Used for drawing stops
                                polygon: false,
                                rectangle: false,
                                circle: false,
                                circlemarker: false
                            }
                        });
                        this.map.addControl(this.drawControl);
                        this.setupSnapGuides();

                        // Event handler for draw creations
                        this.map.on(L.Draw.Event.CREATED, (e) => {
                            let type = e.layerType;
                            let layer = e.layer;

                            if (type === 'polyline') {
                                if (this.editingMode === 'return' && this.roundTrip) {
                                    layer._isReturn = true;
                                    layer.setStyle({ color: this.form.color, dashArray: '8, 8', opacity: 0.7 });
                                } else {
                                    // Clear existing outbound line
                                    this.drawnItems.eachLayer((existingLayer) => {
                                        if (existingLayer instanceof L.Polyline && !(existingLayer instanceof L.Marker) && !existingLayer._isReturn) {
                                            this.drawnItems.removeLayer(existingLayer);
                                        }
                                    });
                                }

                                this.drawnItems.addLayer(layer);
                                if (layer._isReturn) {
                                    this.geometryReturn = this.buildReturnGeometry();
                                } else {
                                    this.updateGeometry(layer);
                                    this.suggestCities();
                                    this.setupSnapGuides();
                                }
                                this.saveDraft();
                            } else if (type === 'marker') {
                                this.newStopLatLng = layer.getLatLng();
                                this.newStopLayer = layer;
                                this.newStopName = "Parada " + (this.stops.length + 1);
                                this.showStopModal = true;
                            }
                        });

                        // Event handler for edits
                        this.map.on(L.Draw.Event.EDITED, (e) => {
                            e.layers.eachLayer((layer) => {
                                if (layer instanceof L.Polyline && !(layer instanceof L.Marker)) {
                                    if (layer._isReturn) {
                                        this.geometryReturn = this.buildReturnGeometry();
                                    } else {
                                        this.updateGeometry(layer);
                                        this.suggestCities();
                                        this.setupSnapGuides();
                                    }
                                } else if (layer instanceof L.Marker) {
                                    let stampId = L.stamp(layer);
                                    let stop = this.stops.find(s => s._layerId === stampId);
                                    if (stop) {
                                        let latlng = layer.getLatLng();
                                        stop.latitude = latlng.lat;
                                        stop.longitude = latlng.lng;
                                    }
                                }
                            });
                            this.saveDraft();
                        });

                        // Event handler for deletion
                        this.map.on(L.Draw.Event.DELETED, (e) => {
                            e.layers.eachLayer((layer) => {
                                if (layer instanceof L.Polyline && !(layer instanceof L.Marker)) {
                                    if (layer._isReturn) {
                                        this.geometryReturn = this.buildReturnGeometry();
                                    } else {
                                        this.geometry = null;
                                        this.suggestCities();
                                        this.setupSnapGuides();
                                    }
                                } else if (layer instanceof L.Marker) {
                                    let stampId = L.stamp(layer);
                                    this.stops = this.stops.filter(s => s._layerId !== stampId);
                                }
                            });
                            this.saveDraft();
                        });
                    },

                    initTomSelect() {
                        const el = this.$refs.citySelect;
                        if (!el) return;
                        this.tselect = new TomSelect(el, {
                            create: true,
                            createOnBlur: false,
                            placeholder: 'Selecciona o escribe ciudades...',
                            maxItems: null,
                            persist: false,
                                render: {
                                    option_create: (data, escape) =>
                                        `<div class="create text-green-600 font-medium"><i class="fa-solid fa-plus"></i> Crear: <strong>${escape(data.input)}</strong></div>`
                                }
                        });
                    },

                    updateGeometry(layer) {
                        let latlngs = layer.getLatLngs();
                        let coordinates = latlngs.map(latlng => [latlng.lng, latlng.lat]);

                        if (layer._isReturn) {
                            let existing = this.geometryReturn?.coordinates || [];
                            this.geometryReturn = {
                                type: 'MultiLineString',
                                coordinates: [...existing, coordinates]
                            };
                        } else {
                            this.geometry = {
                                type: 'LineString',
                                coordinates: coordinates
                            };
                        }
                    },

                    onRoundTripChange() {
                        if (!this.roundTrip) {
                            this.geometryReturn = null;
                            this.editingMode = 'outbound';
                            this.removeReturnLine();
                        }
                        this.updateLineColor();
                        this.saveDraft();
                    },

                    removeReturnLine() {
                        if (!this.drawnItems) return;
                        this.drawnItems.eachLayer((layer) => {
                            if (layer._isReturn) {
                                this.drawnItems.removeLayer(layer);
                            }
                        });
                    },

                    setupSnapGuides() {
                        let guides = [];
                        this.drawnItems.eachLayer((layer) => {
                            if (layer instanceof L.Polyline && !(layer instanceof L.Marker) && !layer._isReturn) {
                                guides.push(layer);
                            }
                        });
                        this.drawControl.setDrawingOptions({
                            polyline: {
                                guideLayers: guides.length > 0 ? guides : undefined,
                                snapDistance: 15
                            }
                        });
                    },

                    buildReturnGeometry() {
                        let coords = [];
                        this.drawnItems.eachLayer((layer) => {
                            if (layer instanceof L.Polyline && !(layer instanceof L.Marker) && layer._isReturn) {
                                let latlngs = layer.getLatLngs();
                                coords.push(latlngs.map(ll => [ll.lng, ll.lat]));
                            }
                        });
                        if (coords.length === 0) return null;
                        if (coords.length === 1) return { type: 'LineString', coordinates: coords[0] };
                        return { type: 'MultiLineString', coordinates: coords };
                    },

                    haversineKm(lat1, lng1, lat2, lng2) {
                        const R = 6371;
                        const dLat = (lat2 - lat1) * Math.PI / 180;
                        const dLng = (lng2 - lng1) * Math.PI / 180;
                        const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180) * Math.cos(lat2*Math.PI/180) * Math.sin(dLng/2)**2;
                        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                    },

                    suggestCities() {
                        this.suggestedPlaces = null;
                        const coords = this.geometry?.coordinates;
                        let allCoords = [];
                        if (coords) allCoords.push(...coords);
                        const returnGeo = this.geometryReturn;
                        if (returnGeo && this.roundTrip) {
                            if (returnGeo.type === 'MultiLineString') {
                                returnGeo.coordinates.forEach(seg => allCoords.push(...seg));
                            } else if (returnGeo.type === 'LineString') {
                                allCoords.push(...returnGeo.coordinates);
                            }
                        }
                        if (allCoords.length === 0) return;
                        const threshold = 8;
                        const suggested = new Set();
                        for (const city of this.allCitiesData) {
                            if (city.id === @json($city->id)) continue;
                            for (const [lng, lat] of allCoords) {
                                if (this.haversineKm(lat, lng, city.latitude, city.longitude) < threshold) {
                                    suggested.add(String(city.id));
                                    break;
                                }
                            }
                        }
                        if (this.tselect) {
                            const existing = new Set(this.tselect.getValue());
                            for (const id of suggested) existing.add(id);
                            this.tselect.setValue(Array.from(existing));
                        }
                    },

                    updateLineColor() {
                        this.drawnItems.eachLayer((layer) => {
                            if (layer instanceof L.Polyline && !(layer instanceof L.Marker)) {
                                if (layer._isReturn) {
                                    layer.setStyle({ color: this.form.color, dashArray: '8, 8', opacity: 0.7 });
                                } else {
                                    layer.setStyle({ color: this.form.color, weight: 5 });
                                }
                            }
                        });
                    },

                    fetchSuggestions() {
                        let allCoords = [];

                        const outbound = this.geometry?.coordinates;
                        if (outbound) allCoords.push(...outbound);

                        const returnGeo = this.geometryReturn;
                        if (returnGeo && this.roundTrip) {
                            if (returnGeo.type === 'MultiLineString') {
                                returnGeo.coordinates.forEach(seg => allCoords.push(...seg));
                            } else if (returnGeo.type === 'LineString') {
                                allCoords.push(...returnGeo.coordinates);
                            }
                        }

                        if (allCoords.length < 2) return;
                        this.loadingSuggestions = true;
                        this.suggestedPlaces = null;
                        fetch('/api/suggest-places', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ coordinates: allCoords })
                        })
                        .then(r => r.json())
                        .then(data => {
                            this.suggestedPlaces = Array.isArray(data) ? data : [];
                        })
                        .catch(() => { this.suggestedPlaces = []; })
                        .finally(() => { this.loadingSuggestions = false; });
                    },

                    addSuggestedPlace(place) {
                        if (!this.tselect) return;
                        if (!this.tselect.options[place.name]) {
                            this.tselect.addOption({ value: place.name, text: place.name });
                        }
                        this.tselect.addItem(place.name);
                    },

                    removeStop(index) {
                        let stop = this.stops[index];
                        let layerId = stop._layerId;
                        
                        // Remove layer from map
                        this.drawnItems.eachLayer((layer) => {
                            if (layer instanceof L.Marker && L.stamp(layer) === layerId) {
                                this.drawnItems.removeLayer(layer);
                            }
                        });

                        // Remove from state
                        this.stops.splice(index, 1);
                        this.saveDraft();
                    },

                    confirmStop() {
                        let stopName = this.newStopName.trim();
                        if (!stopName) {
                            stopName = "Parada " + (this.stops.length + 1);
                        }
                        
                        let latlng = this.newStopLatLng;
                        let layer = this.newStopLayer;
                        
                        let stopObj = {
                            name: stopName,
                            latitude: latlng.lat,
                            longitude: latlng.lng,
                            description: this.newStopDescription,
                            _layerId: L.stamp(layer)
                        };
                        this.stops.push(stopObj);
                        
                        layer.bindPopup(`<b>${stopName}</b>`).addTo(this.drawnItems);
                        layer.openPopup();
                        
                        this.showStopModal = false;
                        this.newStopLatLng = null;
                        this.newStopLayer = null;
                        this.newStopName = '';
                        this.saveDraft();
                    },

                    cancelStop() {
                        if (this.newStopLayer) {
                            this.map.removeLayer(this.newStopLayer);
                        }
                        this.showStopModal = false;
                        this.newStopLatLng = null;
                        this.newStopLayer = null;
                        this.newStopName = '';
                        this.newStopDescription = '';
                    },

                    dragStart(index) { this.draggedIndex = index; },
                    dragEnter(index) { if (this.draggedIndex !== null && this.draggedIndex !== index) this.dragOverIndex = index; },
                    dropStop(index) {
                        if (this.draggedIndex === null) return;
                        const item = this.stops.splice(this.draggedIndex, 1)[0];
                        this.stops.splice(index, 0, item);
                        this.dragEnd();
                        this.saveDraft();
                    },
                    dragEnd() { this.draggedIndex = null; this.dragOverIndex = null; },

                    saveDraft() {
                        if (!this.draftRestored && !this.geometry && !this.geometryReturn && this.stops.length === 0 && !this.form.name) {
                            return;
                        }
                        const data = {
                            form: this.form,
                            geometry: this.geometry,
                            geometryReturn: this.geometryReturn,
                            roundTrip: this.roundTrip,
                            editingMode: this.editingMode,
                            stops: this.stops.map(s => ({ name: s.name, latitude: s.latitude, longitude: s.longitude, description: s.description })),
                            schedules: this.schedules.map(s => ({ day_type: s.day_type, is_active: s.is_active, start_time: s.start_time, end_time: s.end_time, frequency_minutes: s.frequency_minutes })),
                            savedAt: new Date().toISOString()
                        };
                        localStorage.setItem(this.storageKey, JSON.stringify(data));
                        this.draftSaved = true;
                    },

                    checkDraft() {
                        const saved = localStorage.getItem(this.storageKey);
                        if (!saved) return;
                        try {
                            const data = JSON.parse(saved);
                            const age = Date.now() - new Date(data.savedAt).getTime();
                            if (age > 86400000) { this.clearDraft(); return; }
                            const mins = Math.round(age / 60000);
                            this.draftAge = mins < 1 ? 'hace segundos' : mins < 60 ? `hace ${mins} min` : `hace ${Math.round(mins/60)}h`;
                            if (data.geometry || data.geometryReturn || data.stops.length > 0 || data.form.name) {
                                this.draftAvailable = true;
                                this._draftData = data;
                            }
                        } catch(e) {}
                    },

                    restoreDraft() {
                        const data = this._draftData;
                        if (!data) return;
                        this.form = data.form;
                        this.schedules = data.schedules;
                        this.roundTrip = data.roundTrip || false;
                        this.editingMode = data.editingMode || 'outbound';
                        this.geometryReturn = data.geometryReturn || null;
                        this.draftRestored = true;
                        this.draftAvailable = false;
                        this.$nextTick(() => {
                            if (data.geometry) {
                                this.loadDrawnItems(data.geometry, data.stops);
                            }
                        });
                    },

                    loadDrawnItems(geometry, stops) {
                        this.drawnItems.clearLayers();
                        if (geometry) {
                            const geojson = L.geoJSON(geometry, {
                                style: { color: this.form.color, weight: 5 }
                            });
                            geojson.eachLayer(layer => { this.drawnItems.addLayer(layer); });
                            this.geometry = geometry;
                        }
                        // Restore return line if present
                        if (this.geometryReturn) {
                            const returnGeo = L.geoJSON(this.geometryReturn, {
                                style: { color: this.form.color, weight: 5, dashArray: '8, 8', opacity: 0.7 }
                            });
                            returnGeo.eachLayer(layer => { layer._isReturn = true; this.drawnItems.addLayer(layer); });
                        }
                        this.stops = [];
                        if (stops && stops.length > 0) {
                            stops.forEach(s => {
                                const marker = L.marker([s.latitude, s.longitude]).bindPopup(`<b>${s.name}</b>`);
                                this.drawnItems.addLayer(marker);
                                this.stops.push({ ...s, _layerId: L.stamp(marker) });
                            });
                        }
                        // Fit to all items
                        const bounds = this.drawnItems.getBounds();
                        if (bounds.isValid()) {
                            this.map.fitBounds(bounds, { padding: [50, 50] });
                        }
                        this.saveDraft();
                    },

                    discardDraft() {
                        this.clearDraft();
                        this.draftAvailable = false;
                        this._draftData = null;
                    },

                    clearDraft() {
                        localStorage.removeItem(this.storageKey);
                        this.draftSaved = false;
                    },

                    getDayLabel(type) {
                        return {
                            weekday: 'Lunes a Viernes (Días Hábiles)',
                            saturday: 'Sábados',
                            sunday: 'Domingos',
                            holiday: 'Días Festivos'
                        }[type] || type;
                    },

                    fetchTimetable(sched) {
                        if (!sched.is_active || !sched.start_time || !sched.end_time || sched.frequency_minutes <= 0) {
                            sched.timetable = [];
                            return;
                        }
                        
                        let url = `/api/calculate-schedule?start_time=${encodeURIComponent(sched.start_time)}&end_time=${encodeURIComponent(sched.end_time)}&frequency=${sched.frequency_minutes}`;
                        
                        fetch(url)
                            .then(response => response.json())
                            .then(data => {
                                sched.timetable = data.times || [];
                            })
                            .catch(error => {
                                console.error('Error fetching timetable for ' + sched.day_type, error);
                            });
                    },

                    fetchAllTimetables() {
                        this.schedules.forEach(s => {
                            this.fetchTimetable(s);
                        });
                    },

                    submitForm() {
                        if (!this.geometry) {
                            alert("Por favor dibuja la ruta de ida en el mapa antes de guardar.");
                            return;
                        }
                        if (this.roundTrip && !this.geometryReturn) {
                            alert("Marcaste que el regreso es diferente, pero no dibujaste la ruta de vuelta. Cambia a modo 'Vuelta' y dibújala.");
                            return;
                        }

                        this.clearDraft();
                        document.getElementById('route-form').submit();
                    }
                };
            }
        </script>
    @endpush
</x-app-layout>
