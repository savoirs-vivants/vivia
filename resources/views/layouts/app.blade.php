<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Vivia')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-grotesk bg-[#f3f5f8] text-sv-blue antialiased">
    @if(!Route::is('login') && !Route::is('inscription'))
    @include('components.sidebar')

    <div class="pl-[300px] pr-8 pt-4 pb-8 min-h-screen flex flex-col max-w-[1600px] mx-auto">
        @include('components.header', ['title' => $title ?? 'Tableau de bord', 'saison' => $saison ?? null])

        <main class="mt-4">
            @yield('content')
            {{ $slot ?? '' }}
        </main>
        @else
        <main>
            @yield('content')
            {{ $slot ?? '' }}
            </main>
        @endif
    </div>

    @livewireScripts
</body>
</html>
