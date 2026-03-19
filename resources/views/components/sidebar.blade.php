<aside
    class="w-[260px] fixed top-4 bottom-4 left-4 z-20 bg-sv-blue rounded-3xl flex flex-col overflow-hidden shadow-2xl shadow-sv-blue/20">

    {{-- Logo Area avec effet Glassmorphism --}}
    <div
        class="px-8 h-24 flex items-center bg-white/5 backdrop-blur-sm border-b border-white/10 relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-20 h-20 bg-sv-green/30 blur-2xl rounded-full"></div>
        <span class="font-grotesk font-bold text-white text-3xl tracking-tight relative z-10">Vivia</span>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-4 py-8 space-y-2 overflow-y-auto">
        @php
            $navItems = [
                [
                    'route' => 'dashboard',
                    'label' => 'Tableau de bord',
                    'icon' =>
                        'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z',
                ],
                [
                    'route' => '#',
                    'label' => 'Base Adhérents',
                    'icon' =>
                        'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                ],
                [
                    'route' => '#',
                    'label' => 'Activités & Stages',
                    'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                ],
                [
                    'route' => '#',
                    'label' => 'Statistiques',
                    'icon' =>
                        'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                ],
                [
                    'route' => 'backoffice',
                    'label' => 'Équipe',
                    'icon' =>
                        'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                ],
            ];
        @endphp

        @foreach ($navItems as $item)
            @if (!isset($item['roles']) || in_array(Auth::user()->role, $item['roles']))
                @php $active = Route::is($item['route']); @endphp
                <a href="#"
                    class="flex items-center gap-4 px-4 py-3.5 rounded-2xl transition-all duration-300
                    {{ $active
                        ? 'bg-sv-green text-white shadow-lg shadow-sv-green/20'
                        : 'text-white hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}" />
                    </svg>
                    <span class="font-grotesk font-semibold text-sm tracking-wide">{{ $item['label'] }}</span>
                </a>
            @endif
        @endforeach
    </nav>

    </div>
</aside>
