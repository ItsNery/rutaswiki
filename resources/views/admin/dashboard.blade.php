<x-app-layout>
    @section('title', 'Admin · Dashboard')

    <div class="py-12 bg-gray-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Panel de Administración</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Bienvenido, administrador.</p>
            </div>

            @if (session('success'))
                <div class="mb-6 bg-green-50 dark:bg-green-950/30 border border-green-300 dark:border-green-700 p-4 rounded-sm text-green-800 dark:text-green-200 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['total_cities'] }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Ciudades</p>
                    @if($stats['trashed_cities'] > 0)
                        <p class="text-xs text-red-500 mt-1">{{ $stats['trashed_cities'] }} eliminadas</p>
                    @endif
                </div>
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['total_routes'] }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Rutas</p>
                    @if($stats['trashed_routes'] > 0)
                        <p class="text-xs text-red-500 mt-1">{{ $stats['trashed_routes'] }} eliminadas</p>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <a href="{{ route('admin.cities') }}" class="block bg-white dark:bg-gray-800 shadow rounded-lg p-6 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Gestionar Ciudades</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Ver, eliminar y restaurar ciudades.</p>
                </a>
                <a href="{{ route('admin.routes') }}" class="block bg-white dark:bg-gray-800 shadow rounded-lg p-6 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Gestionar Rutas</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Ver, eliminar y restaurar rutas.</p>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
