<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - RutasWiki</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Tailwind CSS (via Vite) -->
    @vite(['resources/css/app.css'])
    
    <script>
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen flex flex-col justify-between">
    
    <!-- Top Header Style Bar -->
    <header class="bg-white dark:bg-gray-800 border-b border-gray-250 dark:border-gray-700 shadow-sm py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <a href="/" class="flex items-center gap-2">
                <span class="font-serif font-bold text-lg text-gray-900 dark:text-white tracking-tight flex items-center gap-1">
                    <span class="text-blue-600 dark:text-blue-550">🗺️</span> Rutas<span class="text-blue-600 dark:text-blue-550 font-normal">Wiki</span>
                </span>
            </a>
            <a href="/" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">Volver a la portada</a>
        </div>
    </header>

    <!-- Main Content Box -->
    <main class="flex-1 flex items-center justify-center p-6">
        <div class="w-full max-w-xl bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 shadow-lg rounded-sm overflow-hidden p-8 border-t-4 border-t-blue-600 dark:border-t-blue-500">
            <div class="flex items-start gap-4">
                <div class="text-4xl select-none">
                    @yield('icon')
                </div>
                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl font-serif font-normal text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4 tracking-tight">
                        @yield('code'): @yield('message')
                    </h1>
                    
                    <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed mb-6">
                        @yield('description')
                    </p>

                    <div class="flex items-center gap-4">
                        <a href="/" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-xs font-bold rounded-sm text-white bg-blue-600 hover:bg-blue-700 shadow transition">
                            Ir a la Página de Inicio
                        </a>
                        <button onclick="window.history.back()" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-700 text-xs font-bold rounded-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            Volver Atrás
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Wikipedia style Footer -->
    <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 py-6 text-center text-xs text-gray-500">
        <div class="max-w-7xl mx-auto px-4">
            <p>Este sitio es una enciclopedia libre de transporte de la comunidad.</p>
            <p class="mt-1">&copy; {{ date('Y') }} RutasWiki. Licencia MIT.</p>
        </div>
    </footer>

</body>
</html>
