@php
$maxWidth = $attributes->get('maxWidth', 'md');
$maxWidthClass = match ($maxWidth) {
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
    '3xl' => 'sm:max-w-3xl',
    '4xl' => 'sm:max-w-4xl',
    '5xl' => 'sm:max-w-5xl',
    default => 'sm:max-w-md',
};
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'RutasWiki') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script>
            if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>
    </head>
    <body class="font-sans antialiased text-gray-900 dark:text-gray-100 bg-[#f8f9fa] dark:bg-gray-950">
        <div class="min-h-screen flex flex-col justify-center items-center p-4 sm:p-6 md:p-8">
            
            <!-- Logo Section -->
            <div class="mb-6 text-center">
                <a href="/" class="inline-flex flex-col items-center gap-1 group">
                    <div class="p-2.5 bg-white dark:bg-gray-900 rounded-sm border border-gray-300 dark:border-gray-700 shadow-sm group-hover:border-blue-500 transition-colors duration-200">
                        <x-application-logo class="w-12 h-12 text-blue-600 dark:text-blue-400 group-hover:scale-105 transition-transform duration-200" />
                    </div>
                    <span class="font-serif text-3xl font-bold tracking-tight text-gray-900 dark:text-white mt-3">
                        Rutas<span class="text-blue-600 dark:text-blue-400">Wiki</span>
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-sans tracking-wide uppercase mt-0.5">
                        La enciclopedia del transporte público
                    </span>
                </a>
            </div>

            <!-- Card Container -->
            <div class="w-full {{ $maxWidthClass }} bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-800 shadow-sm rounded-sm border-t-4 border-t-blue-600 dark:border-t-blue-500 overflow-hidden transition-all duration-300">
                <div class="p-6 sm:p-8">
                    {{ $slot }}
                </div>
            </div>
            
            <!-- Footer -->
            <div class="mt-8 text-center text-[11px] text-gray-400 dark:text-gray-600 max-w-md leading-relaxed">
                RutasWiki es un proyecto libre e independiente. El contenido de la enciclopedia está disponible bajo la licencia Creative Commons Atribución-CompartirIgual.
            </div>
        </div>
    </body>
</html>

