@props(['title' => 'Vue d\'ensemble', 'saison' => null])

<header class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 mb-8 pl-2">
    {{-- Le Titre (Détaché et minimaliste) --}}
    <div>
        <h1 class="font-grotesk text-4xl font-black text-[#0F143A] tracking-tighter">{{ $title }}</h1>
    </div>

    {{-- L'Îlot (Dynamic Island) --}}
    <div class="flex items-center bg-white p-1.5 rounded-full shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 gap-1">

        {{-- Badge Saison --}}
        @if ($saison)
            <div class="hidden sm:flex items-center gap-2 px-4 py-2 bg-gray-50 rounded-full">
                <span class="w-2 h-2 rounded-full bg-sv-green"></span>
                <span class="font-grotesk text-[10px] font-black text-gray-500 uppercase tracking-widest">Saison {{ $saison }}</span>
            </div>
            <div class="w-px h-6 bg-gray-200 mx-1 hidden sm:block"></div>
        @endif

        {{-- Cloche Notif --}}
        <button class="w-10 h-10 flex items-center justify-center rounded-full text-gray-400 hover:text-[#0F143A] hover:bg-gray-50 transition-all relative">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <span class="absolute top-2.5 right-2.5 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
        </button>

        {{-- Dropdown Profil --}}
        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <button @click="open = !open" class="flex items-center gap-2 pl-2 pr-4 py-1 hover:bg-gray-50 rounded-full transition-all focus:outline-none">
                <div class="w-8 h-8 rounded-full bg-[#0F143A] text-white font-black text-xs uppercase flex items-center justify-center">
                    {{ substr(Auth::user()->firstname ?? Auth::user()->name ?? 'U', 0, 1) }}
                </div>
                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg>
            </button>

            {{-- Menu Flottant Arrondi --}}
            <div x-show="open" style="display: none;"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute right-0 top-full mt-4 w-64 bg-white rounded-[2rem] border border-gray-100 shadow-[0_20px_40px_rgb(0,0,0,0.08)] p-3 z-50">

                <div class="px-4 py-3 bg-gray-50/50 rounded-2xl mb-3">
                    <p class="font-grotesk font-black text-[#0F143A] text-sm truncate">{{ Auth::user()->firstname }} {{ Auth::user()->name }}</p>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest truncate mt-0.5">{{ Auth::user()->email }}</p>
                </div>

                <div class="space-y-1">
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold text-gray-500 hover:bg-gray-50 hover:text-[#0F143A] transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Modifier mon profil
                    </a>
                </div>

                <div class="space-y-1">
                    <a href="{{ route('profile.logs') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold text-gray-500 hover:bg-gray-50 hover:text-[#0F143A] transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Journal d'erreurs
                    </a>
                </div>

                <div class="h-px bg-gray-100 my-2 mx-4"></div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold text-red-500 hover:bg-red-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Déconnexion
                    </button>
                </form>
            </div>
        </div>

    </div>
</header>
