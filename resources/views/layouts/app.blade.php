<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Vivia')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&family=Space+Mono:wght@400;700&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/password-toggle.js'])
    @livewireStyles
</head>

<body class="font-grotesk bg-[#f3f5f8] antialiased">
    @if (!Route::is('login') && !Route::is('inscription'))
        @include('components.sidebar')

        <div class="ml-[292px] px-8 pt-6 pb-8 min-h-screen flex flex-col">
            @include('components.header', [
                'title' => view()->yieldContent('title', 'Tableau de bord'),
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
