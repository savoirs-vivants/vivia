<aside
    class="fixed top-0 left-0 h-screen z-30 bg-[#083325] text-white
           flex flex-col
           transition-transform duration-300 ease-in-out
           w-64
           md:w-16
           lg:w-64"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
    style="box-shadow: 4px 0 24px rgba(0,0,0,0.18);">

    <div class="px-4 pt-6 pb-5 border-b border-white/5 flex items-center gap-3 shrink-0">
        <div class="w-8 h-8 rounded-lg bg-[#16A37A]/20 border border-[#16A37A]/30 flex items-center justify-center shrink-0">
            <span class="font-mono font-black text-[#16A37A] text-sm">V</span>
        </div>
        <div class="overflow-hidden transition-all duration-300 lg:opacity-100 lg:w-auto md:opacity-0 md:w-0 opacity-100 w-auto">
            <span class="font-mono font-black text-xl text-white tracking-tight whitespace-nowrap">Vivia</span>
            <div class="mt-0.5 h-0.5 w-6 rounded-full bg-[#16A37A]"></div>
        </div>
        <button
            @click="sidebarOpen = false"
            class="ml-auto lg:hidden md:hidden text-white/40 hover:text-white transition-colors p-1"
            aria-label="Fermer le menu"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    <nav class="flex-1 px-2 pt-5 space-y-0.5 overflow-y-auto">
        <p class="px-3 pb-3 text-white/40 text-[10px] font-bold uppercase tracking-widest lg:block md:hidden block">Menu</p>

        @php
            $sidebarIsGestionnaire = \Illuminate\Support\Facades\DB::table('activites_gestionnaire')
                ->where('id_users', Auth::id())
                ->exists();

            $navItems = [
                [
                    'route'  => 'dashboard',
                    'label'  => 'Dashboard',
                    'icon'   => 'M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10-3a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1v-7z',
                    'roles'  => null,
                ],
                [
                    'route'  => 'adherents.index',
                    'label'  => 'Adhérents',
                    'icon'   => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
                    'roles'  => null,
                ],
                [
                    'route'  => 'activites.index',
                    'label'  => 'Activités & Stages',
                    'icon'   => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
                    'roles'  => null,
                ],
                [
                    'route'  => 'ressourcerie.index',
                    'label'  => 'Ressourcerie',
                    'icon'   => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                    'roles'  => null,
                ],
                [
                    'route'  => 'statistiques.index',
                    'label'  => 'Statistiques',
                    'icon'   => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                    'roles'  => ['admin', 'comptable'],
                ],
                [
                    'route'  => 'backoffice',
                    'label'  => 'Équipe',
                    'icon'   => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                    'roles'  => ['admin'],
                ],
            ];
        @endphp

        @foreach ($navItems as $item)
            @if (!empty($item['roles']) && !in_array(Auth::user()->role, $item['roles']))
                @continue
            @endif
            @if ($item['route'] === 'dashboard' && in_array(Auth::user()->role, ['coordinateur', 'animateur']) && !$sidebarIsGestionnaire)
                @continue
            @endif

            @php $isActive = Route::is($item['route']); @endphp

            <div class="relative group/nav">
                <a href="{{ route($item['route']) }}"
                   @click="if(window.innerWidth < 768) sidebarOpen = false"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 relative {{ $isActive ? 'bg-[#16A37A]/15 text-white font-bold' : 'text-white/60 hover:bg-white/5 hover:text-white font-medium' }}"
                >
                    @if ($isActive)
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 rounded-r-full bg-[#16A37A]"></span>
                    @endif

                    <svg class="w-5 h-5 shrink-0 {{ $isActive ? 'text-[#16A37A]' : 'text-white/40 group-hover/nav:text-white/80' }}"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                    </svg>

                    <span class="text-sm truncate lg:block md:hidden block">
                        {{ $item['label'] }}
                    </span>
                </a>

                <div class="absolute left-full top-1/2 -translate-y-1/2 ml-3 hidden md:block lg:hidden pointer-events-none bg-[#0a3d2a] text-white text-xs font-bold px-3 py-1.5 rounded-lg whitespace-nowrap opacity-0 group-hover/nav:opacity-100 translate-x-1 group-hover/nav:translate-x-0 transition-all duration-200 shadow-lg z-50">
                    {{ $item['label'] }}
                    <span class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-[#0a3d2a]"></span>
                </div>
            </div>
        @endforeach

    </nav>
</aside>
