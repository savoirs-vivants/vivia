<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
    @hasSection('title')
        Vivia - @yield('title')
    @else
        Vivia
    @endif
    </title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&family=Space+Mono:wght@400;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/password-toggle.js', 'resources/js/selection.js', 'resources/js/activites.js', 'resources/js/adherent.js', 'resources/js/statistiques.js', 'resources/js/carnet-bord.js'])
    @livewireStyles
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>

<body class="font-grotesk bg-[#f3f5f8] antialiased" x-data="{ sidebarOpen: false }">
    @if (!Route::is('login') && !Route::is('inscription') && !Route::is('password.forgot') && !Route::is('password.reset') && !Route::is('adhesion.*'))
        <div
            x-show="sidebarOpen"
            x-transition:enter="transition-opacity ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="sidebarOpen = false"
            class="fixed inset-0 bg-black/40 backdrop-blur-sm z-10 lg:hidden"
            style="display: none;"
        ></div>
        @include('components.sidebar')

        <div class="lg:ml-64 md:ml-16 transition-all duration-300 px-4 sm:px-6 lg:px-8 pt-5 pb-8 min-h-screen flex flex-col">
            @include('components.header', [
                'title' => view()->yieldContent('title' ?? ''),
                'saison' => $saison ?? null,
            ])
            <main class="flex-1 flex flex-col">
                @yield('content')
                {{ $slot ?? '' }}
            </main>
        </div>
    @else
        <main>
            @yield('content')
            {{ $slot ?? '' }}
        </main>
    @endif

    @livewireScripts
</body>

</html>
