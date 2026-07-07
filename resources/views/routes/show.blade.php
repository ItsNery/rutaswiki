<x-app-layout>
    @push('meta')
        <meta property="og:title" content="{{ $route->route_number ? '[' . $route->route_number . '] ' : '' }}{{ $route->name }} · {{ $city->name }}">
        <meta property="og:description" content="{{ $route->description ? mb_substr($route->description, 0, 200) : 'Ruta de ' . $city->name . ', ' . $city->state . '. ' . ucfirst($route->transport_type) . '. Consulta horarios, paradas y mapa del trayecto en RutasWiki.' }}">
        <meta property="og:url" content="{{ route('routes.show', [$city, $route]) }}">
        <meta name="description" content="{{ $route->description ? mb_substr($route->description, 0, 200) : 'Ruta de ' . $city->name . ', ' . $city->state . '. ' . ucfirst($route->transport_type) . '. Consulta horarios, paradas y mapa del trayecto en RutasWiki.' }}">
    @endpush
    @section('title', ($route->route_number ? '[' . $route->route_number . '] ' : '') . $route->name . ' · ' . $city->name)
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <style>
            #infobox-map {
                height: 250px;
                border-bottom: 1px solid #ccc;
            }
            .wiki-heading {
                font-family: Georgia, serif;
                border-b: 1px solid #a2a9b1;
            }
            /* Wikipedia Infobox Styles */
            .wiki-infobox {
                background-color: #f8f9fa;
                border: 1px solid #a2a9b1;
                font-size: 88%;
                line-height: 1.5em;
                width: 22em;
            }
            .dark .wiki-infobox {
                background-color: #202122;
                border-color: #72777d;
            }
            .wiki-infobox-header {
                background-color: #eaecf0;
                font-size: 125%;
                font-weight: bold;
                text-align: center;
            }
            .dark .wiki-infobox-header {
                background-color: #27292d;
            }
        </style>
    @endpush

    <div class="py-6 bg-white dark:bg-gray-900 min-h-screen text-gray-800 dark:text-gray-200"
         x-data="{ 
            activeTab: 'article', 
            score: {{ $route->vote_score }}, 
            userVote: @json($userVote),
            async vote(val) {
                @guest
                    window.location.href = '{{ route('login') }}';
                    return;
                @endguest

                const response = await fetch('{{ route('routes.vote', $route) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ value: val })
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.score = data.vote_score;
                    this.userVote = this.userVote === val ? null : val;
                }
            }
         }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Breadcrumbs -->
            <div class="mb-4 text-xs text-gray-500 dark:text-gray-400">
                <a href="{{ route('home') }}" class="hover:underline text-blue-600 dark:text-blue-400">Inicio</a>
                <span class="mx-1">&gt;</span>
                <a href="{{ route('cities.show', $city) }}" class="hover:underline text-blue-600 dark:text-blue-400">{{ $city->name }}</a>
                <span class="mx-1">&gt;</span>
                <span class="text-gray-900 dark:text-white font-semibold">
                    {{ $route->route_number ? '[' . $route->route_number . '] ' : '' }}{{ $route->name }}
                </span>
            </div>

            <!-- Wikipedia Top Article Controls -->
            <div class="flex justify-between items-end border-b border-gray-300 dark:border-gray-700 mb-4 pb-0 flex-wrap gap-2">
                <!-- Left Tabs: Article vs Discussion -->
                <div class="flex gap-1 -mb-[1px]">
                    <button @click="activeTab = 'article'"
                            :class="activeTab === 'article' ? 'bg-white dark:bg-gray-900 border-t border-l border-r border-gray-300 dark:border-gray-700 font-semibold text-gray-900 dark:text-white' : 'text-blue-600 dark:text-blue-400 hover:bg-gray-150'"
                            class="px-4 py-2 text-sm rounded-t-sm transition focus:outline-none">
                        Ruta
                    </button>
                    <button @click="activeTab = 'discussion'"
                            :class="activeTab === 'discussion' ? 'bg-white dark:bg-gray-900 border-t border-l border-r border-gray-300 dark:border-gray-700 font-semibold text-gray-900 dark:text-white' : 'text-blue-600 dark:text-blue-400 hover:bg-gray-150'"
                            class="px-4 py-2 text-sm rounded-t-sm transition focus:outline-none flex items-center gap-1.5">
                        Discusión
                        @if($route->comments->count() > 0)
                            <span class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-[10px] px-1.5 py-0.5 rounded-full font-bold">
                                {{ $route->comments->count() }}
                            </span>
                        @endif
                    </button>
                </div>

                <!-- Right Action Tabs: Read, Edit, History -->
                <div class="flex gap-4 text-xs font-medium pb-2 select-none">
                    <button @click="activeTab = 'article'" class="text-gray-900 dark:text-white border-b-2 border-transparent hover:border-gray-400 pb-1 font-semibold">Leer</button>
                    <a href="{{ route('routes.edit', [$city, $route]) }}" class="text-blue-600 dark:text-blue-400 hover:underline">Editar</a>
                    <a href="{{ route('routes.history', [$city, $route]) }}" class="text-blue-600 dark:text-blue-400 hover:underline">Ver historial</a>
                </div>
            </div>

            <!-- Wikipedia Banner / Notice Template -->
            <div class="bg-gray-50 dark:bg-gray-800 border-l-4 border-amber-500 p-4 mb-6 rounded-r-sm text-xs flex gap-3 items-center">
                <svg class="w-6 h-6 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <div>
                    <p class="font-bold text-gray-900 dark:text-white">Este artículo de transporte público es colaborativo.</p>
                    <p class="text-gray-600 dark:text-gray-400 mt-0.5">
                        Cualquier persona puede editar el trayecto o agregar paradas. Si viajas frecuentemente en este camión, por favor ayuda a validar el recorrido votando a favor o en contra.
                    </p>
                </div>
            </div>

            <!-- Tab Content: Article (Main Wiki Page) -->
            <div x-show="activeTab === 'article'" class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                
                <!-- Main Article Description & Headings (Left) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Title -->
                    <h1 class="text-3xl font-normal font-serif border-b border-gray-300 dark:border-gray-700 pb-2 mb-4 tracking-tight">
                        {{ $route->route_number ? 'Ruta ' . $route->route_number . ': ' : '' }}{{ $route->name }}
                    </h1>

                    <!-- Article Body / Description -->
                    <div class="prose dark:prose-invert max-w-none text-sm leading-relaxed whitespace-pre-line">
                        {{ $route->description ?: 'Este artículo todavía no tiene una descripción detallada. Puedes ayudar redactando información relevante como los horarios habituales de paso, los costos del pasaje, el nombre de la concesionaria u operador, y recomendaciones especiales de seguridad.' }}
                    </div>

                    <!-- == Trayecto == -->
                    <div class="mt-8">
                        <h2 class="text-xl font-normal font-serif border-b border-gray-300 dark:border-gray-700 pb-1 mb-4 flex justify-between items-center">
                            <span>Trayecto</span>
                            <a href="{{ route('routes.edit', [$city, $route]) }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-normal font-sans">[editar]</a>
                        </h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            El recorrido de la línea pasa por las siguientes vialidades principales. Para ver paradas exactas, consulta la lista a continuación o el mapa del infobox.
                        </p>
                    </div>

                    <!-- == Horarios y Frecuencia == -->
                    <div class="mt-8">
                        <h2 class="text-xl font-normal font-serif border-b border-gray-300 dark:border-gray-700 pb-1 mb-4 flex justify-between items-center">
                            <span>Horarios y Frecuencia de Paso</span>
                            <a href="{{ route('routes.edit', [$city, $route]) }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-normal font-sans">[editar]</a>
                        </h2>
                        
                        @if($route->schedules->count() > 0)
                            <div class="space-y-4" x-data="{ activeTab: '{{ $route->schedules->first()->day_type }}' }">
                                <!-- Tabs Headers -->
                                <div class="flex border-b border-gray-200 dark:border-gray-700 overflow-x-auto gap-2">
                                    @foreach($route->schedules as $sched)
                                        <button @click="activeTab = '{{ $sched->day_type }}'"
                                                :class="activeTab === '{{ $sched->day_type }}' ? 'border-blue-500 text-blue-600 dark:text-blue-400 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                                                class="px-3 py-1.5 border-b-2 text-xs font-semibold focus:outline-none transition capitalize whitespace-nowrap">
                                            @if($sched->day_type === 'weekday') Lunes a Viernes @elseif($sched->day_type === 'saturday') Sábados @elseif($sched->day_type === 'sunday') Domingos @else Festivos @endif
                                        </button>
                                    @endforeach
                                </div>

                                <!-- Tabs Contents -->
                                @foreach($route->schedules as $sched)
                                    <div x-show="activeTab === '{{ $sched->day_type }}'" x-transition class="bg-gray-55 dark:bg-gray-800/40 border border-gray-250 dark:border-gray-750 rounded p-4 space-y-4 text-sm shadow-sm">
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                            <div>
                                                <span class="block text-xs font-bold text-gray-500 dark:text-gray-400">Horario de Servicio</span>
                                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $sched->start_time }} hrs. a {{ $sched->end_time }} hrs.</span>
                                            </div>
                                            <div>
                                                <span class="block text-xs font-bold text-gray-500 dark:text-gray-400">Frecuencia de Paso</span>
                                                <span class="text-sm font-semibold text-gray-900 dark:text-white">Cada {{ $sched->frequency_minutes }} minutos</span>
                                            </div>
                                            <div>
                                                <span class="block text-xs font-bold text-gray-500 dark:text-gray-400">Tipo de Servicio</span>
                                                <span class="text-xs px-2 py-0.5 bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800 rounded-full font-semibold capitalize mt-1 inline-block">
                                                    @if($sched->day_type === 'weekday') Habitual @else Especial @endif
                                                </span>
                                            </div>
                                        </div>

                                        <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                                            <span class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Corridas Diarias Estimadas</span>
                                            <div class="flex flex-wrap gap-1.5 font-mono text-xs max-h-36 overflow-y-auto p-1.5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-755 rounded">
                                                @php
                                                    $times = [];
                                                    try {
                                                        $start = \Carbon\Carbon::createFromFormat('H:i', $sched->start_time);
                                                        $end = \Carbon\Carbon::createFromFormat('H:i', $sched->end_time);
                                                        if ($end->lessThanOrEqualTo($start)) {
                                                            $end->addDay();
                                                        }
                                                        $current = $start->copy();
                                                        $max = 300;
                                                        while ($current->lessThanOrEqualTo($end) && $max > 0) {
                                                            $times[] = $current->format('H:i');
                                                            $current->addMinutes($sched->frequency_minutes);
                                                            $max--;
                                                        }
                                                    } catch (\Exception $e) {}
                                                    $now = \Carbon\Carbon::now();
                                                    $nextDeparture = null;
                                                    foreach ($times as $time) {
                                                        if (\Carbon\Carbon::createFromFormat('H:i', $time)->greaterThanOrEqualTo($now)) {
                                                            $nextDeparture = $time;
                                                            break;
                                                        }
                                                    }
                                                @endphp
                                                @forelse($times as $time)
                                                    <span class="px-2 py-0.5 bg-gray-55 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded shadow-xs {{ $time === $nextDeparture ? 'font-bold text-blue-600 dark:text-blue-400 ring-2 ring-blue-400' : '' }}" title="{{ $time === $nextDeparture ? 'Próxima corrida' : 'Corrida estimada' }}">{{ $time }}</span>
                                                @empty
                                                    <span class="text-gray-500 italic">No se pudo calcular el horario estimado</span>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="bg-gray-55 dark:bg-gray-800/40 border border-gray-250 dark:border-gray-750 rounded p-4 text-sm shadow-sm text-center text-gray-500 italic">
                                No hay horarios ni frecuencias registrados para esta ruta.
                            </div>
                        @endif
                    </div>

                    <!-- == Paradas == -->
                    <div class="mt-8">
                        <h2 class="text-xl font-normal font-serif border-b border-gray-300 dark:border-gray-700 pb-1 mb-4 flex justify-between items-center">
                            <span class="flex items-center gap-2">
                                Paradas
                                @if($route->has_designated_stops)
                                    <span class="text-[10px] font-bold font-sans uppercase tracking-wider px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">Designadas</span>
                                @else
                                    <span class="text-[10px] font-bold font-sans uppercase tracking-wider px-1.5 py-0.5 rounded bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300">A solicitud</span>
                                @endif
                            </span>
                            <a href="{{ route('routes.edit', [$city, $route]) }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-normal font-sans">[editar]</a>
                        </h2>
                        
                        @if(!$route->has_designated_stops && $route->stops->count() > 0)
                            <div class="mb-4 p-2 bg-yellow-50 dark:bg-yellow-950/30 border border-yellow-300 dark:border-yellow-700 rounded text-xs text-yellow-800 dark:text-yellow-200">
                                Esta ruta no tiene paradas designadas. Los pasajeros pueden solicitar paradas adicionales directamente al conductor con anticipación.
                            </div>
                        @endif
                        
                        <div class="relative pl-6 border-l-2 border-blue-500 dark:border-blue-700 space-y-6">
                            @forelse($route->stops as $stop)
                                <div class="relative">
                                    <span class="absolute -left-[31px] top-1 flex items-center justify-center w-4 h-4 rounded-full bg-blue-500 text-white font-mono text-[9px] font-bold ring-4 ring-white dark:ring-gray-900">
                                        {{ $stop->order }}
                                    </span>
                                    <h3 class="font-bold text-gray-900 dark:text-white text-sm">{{ $stop->name }}</h3>
                                    @if($stop->description)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $stop->description }}</p>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-6 text-sm text-gray-400 dark:text-gray-500 pl-0">
                                    Esta ruta no cuenta con paradas registradas en el mapa.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Wikipedia Infobox (Right) -->
                <div class="lg:col-span-1 flex justify-center">
                    <div class="wiki-infobox rounded-sm shadow-sm overflow-hidden border border-gray-300 dark:border-gray-700 w-full max-w-sm">
                        
                        <!-- Header / Title -->
                        <div class="wiki-infobox-header py-2 text-gray-900 dark:text-white border-b border-gray-300 dark:border-gray-700">
                            {{ $route->route_number ?: 'Ruta sin código' }}
                            <p class="text-xs font-normal text-gray-500 dark:text-gray-400 mt-0.5">{{ $route->name }}</p>
                        </div>

                        <!-- Leaflet Map Embedded directly inside the Infobox -->
                        <div id="infobox-map"></div>

                        <!-- Data table inside Infobox -->
                        <table class="w-full text-xs text-left divide-y divide-gray-200 dark:divide-gray-700">
                            <tbody>
                                <tr class="bg-gray-50 dark:bg-gray-800/40">
                                    <th class="px-4 py-2.5 font-semibold text-gray-500 dark:text-gray-400 w-1/3">Ciudad</th>
                                    <td class="px-4 py-2.5 text-gray-900 dark:text-white">
                                        <a href="{{ route('cities.show', $city) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-semibold">{{ $city->name }}</a>
                                    </td>
                                </tr>
                                @php $otherCities = $route->cities()->where('city_id', '!=', $city->id)->get(); @endphp
                                @if($otherCities->isNotEmpty())
                                <tr>
                                    <th class="px-4 py-2.5 font-semibold text-gray-500 dark:text-gray-400">También pasa</th>
                                    <td class="px-4 py-2.5 text-gray-900 dark:text-white">
                                        @foreach($otherCities as $oc)
                                            <a href="{{ route('cities.show', $oc) }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ $oc->name }}</a>@if(!$loop->last), @endif
                                        @endforeach
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <th class="px-4 py-2.5 font-semibold text-gray-500 dark:text-gray-400">Tipo</th>
                                    <td class="px-4 py-2.5 text-gray-900 dark:text-white capitalize">{{ $route->transport_type }}</td>
                                </tr>
                                <tr class="bg-gray-50 dark:bg-gray-800/40">
                                    <th class="px-4 py-2.5 font-semibold text-gray-500 dark:text-gray-400">Identificador</th>
                                    <td class="px-4 py-2.5 text-gray-900 dark:text-white flex items-center gap-2">
                                        <span class="w-4 h-4 rounded-sm border border-gray-300 dark:border-gray-600 block shrink-0" style="background-color: {{ $route->color }}"></span>
                                        <span class="font-mono">{{ strtoupper($route->color) }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="px-4 py-2.5 font-semibold text-gray-500 dark:text-gray-400">Paradas</th>
                                    <td class="px-4 py-2.5 text-gray-900 dark:text-white">
                                        {{ $route->stops->count() }} estaciones
                                        @if($route->has_designated_stops)
                                            <span class="text-[10px] font-bold ml-1 text-blue-600 dark:text-blue-400">(designadas)</span>
                                        @else
                                            <span class="text-[10px] font-bold ml-1 text-yellow-600 dark:text-yellow-400">(a solicitud)</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($route->round_trip)
                                <tr class="bg-gray-50 dark:bg-gray-800/40">
                                    <th class="px-4 py-2.5 font-semibold text-gray-500 dark:text-gray-400">Trayecto</th>
                                    <td class="px-4 py-2.5 text-gray-900 dark:text-white">
                                        Ida y Vuelta
                                        <span class="text-[10px] text-gray-400 ml-1">(diferente)</span>
                                    </td>
                                </tr>
                                @endif
                                @if($route->schedules->count() > 0)
                                    @foreach($route->schedules as $sched)
                                        <tr class="{{ $loop->even ? 'bg-gray-50 dark:bg-gray-800/40' : '' }}">
                                            <th class="px-4 py-2.5 font-semibold text-gray-500 dark:text-gray-400 capitalize">
                                                @if($sched->day_type === 'weekday') Lun-Vie @elseif($sched->day_type === 'saturday') Sáb @elseif($sched->day_type === 'sunday') Dom @else Fest @endif
                                            </th>
                                            <td class="px-4 py-2.5 text-gray-900 dark:text-white">
                                                {{ $sched->start_time }} - {{ $sched->end_time }} <span class="text-[10px] text-gray-400">(cada {{ $sched->frequency_minutes }}m)</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="bg-gray-50 dark:bg-gray-800/40">
                                        <th class="px-4 py-2.5 font-semibold text-gray-500 dark:text-gray-400">Horarios</th>
                                        <td class="px-4 py-2.5 text-gray-400 italic">Sin horarios registrados</td>
                                    </tr>
                                @endif
                                <tr class="bg-gray-50 dark:bg-gray-800/40">
                                    <th class="px-4 py-2.5 font-semibold text-gray-500 dark:text-gray-400">Editor original</th>
                                    <td class="px-4 py-2.5 text-gray-900 dark:text-white">{{ $route->user?->name ?? 'Anónimo' }}</td>
                                </tr>
                                <tr>
                                    <th class="px-4 py-2.5 font-semibold text-gray-500 dark:text-gray-400">Revisiones</th>
                                    <td class="px-4 py-2.5 text-gray-900 dark:text-white flex items-center gap-2">
                                        <span>{{ $route->revision_count }} versiones</span>
                                        <a href="{{ route('routes.history', [$city, $route]) }}" class="text-[10px] text-blue-600 dark:text-blue-400 hover:underline">(historial)</a>
                                    </td>
                                </tr>
                                <tr class="bg-gray-50 dark:bg-gray-800/40 border-t border-gray-300 dark:border-gray-700">
                                    <th class="px-4 py-3 font-semibold text-gray-500 dark:text-gray-400">Confianza</th>
                                    <td class="px-4 py-3 flex items-center gap-2">
                                        <!-- Interactive Up/Down voting inside Infobox -->
                                        <div class="flex items-center gap-1.5">
                                            <button @click="vote(1)" 
                                                    :class="userVote === 1 ? 'text-green-600 bg-green-50 dark:bg-green-950/20' : 'text-gray-400 hover:text-green-600'"
                                                    class="p-0.5 rounded transition focus:outline-none" title="Validar (+1)">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                            </button>
                                            <span class="font-bold text-sm text-gray-900 dark:text-white" x-text="score"></span>
                                            <button @click="vote(-1)" 
                                                    :class="userVote === -1 ? 'text-red-600 bg-red-50 dark:bg-red-950/20' : 'text-gray-400 hover:text-red-600'"
                                                    class="p-0.5 rounded transition focus:outline-none" title="Reportar error (-1)">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Tab Content: Discussion (Wikipedia Talk page style) -->
            <div x-show="activeTab === 'discussion'" class="space-y-6 max-w-4xl mx-auto">
                <h1 class="text-3xl font-normal font-serif border-b border-gray-300 dark:border-gray-700 pb-2 mb-4 tracking-tight">
                    Discusión: {{ $route->name }}
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Esta es la página de discusión para debatir y reportar cambios sobre el trayecto de la ruta. Por favor mantén el respeto vecinal.
                </p>

                <!-- Comment submission -->
                @auth
                    <form action="{{ route('routes.comment', $route) }}" method="POST" class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-sm p-4 shadow-sm">
                        @csrf
                        <div>
                            <label for="body" class="block text-sm font-bold text-gray-900 dark:text-white">Agregar nuevo tema de discusión</label>
                            <textarea id="body" name="body" rows="3" required
                                      class="mt-2 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 text-sm"
                                      placeholder="Reporta si el camión ya no pasa por una avenida, si el cobro aumentó, etc."></textarea>
                        </div>
                        <div class="mt-3 flex justify-end">
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-1.5 border border-transparent text-sm font-semibold rounded-sm text-white bg-blue-600 hover:bg-blue-700 transition shadow-sm">
                                Publicar Comentario
                            </button>
                        </div>
                    </form>
                @else
                    <div class="bg-gray-50 dark:bg-gray-800 p-4 border border-gray-300 dark:border-gray-700 rounded-sm text-center text-sm">
                        <a href="{{ route('login') }}" class="font-bold text-blue-600 dark:text-blue-400 hover:underline">Inicia sesión</a> para unirte a la discusión y reportar cambios.
                    </div>
                @endauth

                <!-- Comments/Topics Thread -->
                <div class="space-y-4">
                    @forelse($route->comments as $comment)
                        <div class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-sm p-5 shadow-sm">
                            <div class="flex justify-between items-center border-b border-gray-150 dark:border-gray-700 pb-2 mb-3">
                                <span class="font-bold text-sm text-gray-900 dark:text-white flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    {{ $comment->user->name }}
                                </span>
                                <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed whitespace-pre-line">{{ $comment->body }}</p>
                        </div>
                    @empty
                        <div class="text-center py-12 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-sm text-gray-400 dark:text-gray-500 text-sm">
                            No hay temas de discusión activos todavía. ¡Empieza la conversación!
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const map = L.map('infobox-map');

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OSM'
                }).addTo(map);

                fetch('{{ route('api.routes.show', $route) }}')
                    .then(res => res.json())
                    .then(routeData => {
                        const combinedGroup = L.featureGroup();

                        const polyline = L.geoJSON(routeData.geometry, {
                            style: {
                                color: routeData.color || '#3b82f6',
                                weight: 5,
                                opacity: 0.9
                            }
                        }).addTo(map);
                        combinedGroup.addLayer(polyline);

                        // Draw return line if round trip
                        if (routeData.round_trip && routeData.geometry_return) {
                            const returnLine = L.geoJSON(routeData.geometry_return, {
                                style: {
                                    color: routeData.color || '#3b82f6',
                                    weight: 5,
                                    opacity: 0.7,
                                    dashArray: '8, 8'
                                }
                            }).addTo(map);
                            combinedGroup.addLayer(returnLine);
                        }

                        const markersGroup = L.featureGroup();
                        if (routeData.stops && routeData.stops.length > 0) {
                            routeData.stops.forEach(stop => {
                                const marker = L.marker([stop.latitude, stop.longitude])
                                    .bindPopup(`<b>Parada ${stop.order}: ${stop.name}</b>`);
                                markersGroup.addLayer(marker);
                            });
                            markersGroup.addTo(map);
                            combinedGroup.addLayer(markersGroup);
                        }

                        map.fitBounds(combinedGroup.getBounds(), { padding: [20, 20] });
                    });
            });
        </script>
    @endpush
</x-app-layout>
