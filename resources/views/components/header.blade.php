<header class="h-24 flex items-center justify-between px-2">
    <div>
        <h1 class="font-grotesk text-3xl font-bold text-gray-900 tracking-tight">{{ $title }}</h1>
    </div>

    <div class="flex items-center gap-4">
        {{-- Badge Saison --}}
        @if ($saison)
            <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-full shadow-sm border border-gray-100">
                <span class="w-2 h-2 rounded-full bg-sv-green animate-pulse"></span>
                <span class="text-sm font-grotesk font-bold text-gray-700">Saison {{ $saison }}</span>
            </div>
        @endif

        {{-- Bouton Notification --}}
        <button class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm border border-gray-100 text-gray-400 hover:text-sv-blue hover:border-sv-blue/20 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
        </button>

        {{-- Dropdown Profil (Alpine.js) --}}
        <div class="relative" x-data="{ open: false }" @click.outside="open = false">

            {{-- Avatar déclencheur --}}
            <button @click="open = !open"
                class="w-10 h-10 rounded-full bg-sv-blue text-white font-grotesk font-bold flex items-center justify-center shadow-sm shadow-sv-blue/30 hover:shadow-md hover:scale-105 transition-all focus:outline-none">
                {{ strtoupper(substr(Auth::user()->firstname ?? Auth::user()->name ?? 'U', 0, 1)) }}{{ strtoupper(substr(Auth::user()->name ?? '', 0, 1)) }}
            </button>

            {{-- Menu déroulant --}}
            <div x-show="open" style="display: none;"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute right-0 top-full mt-3 w-64 bg-white rounded-3xl shadow-xl shadow-gray-200/60 border border-gray-100 p-2 z-50">

                {{-- En-tête du menu (Nom & Rôle) --}}
                <div class="px-4 py-3.5 mb-2 bg-gray-50/80 rounded-2xl border border-gray-100">
                    <p class="font-grotesk font-bold text-gray-900 text-sm truncate">
                        {{ Auth::user()->firstname }} {{ Auth::user()->name }}
                    </p>
                    <p class="text-xs text-gray-500 mt-0.5 truncate capitalize font-medium">
                        {{ Auth::user()->role === 'travailleur' ? 'Travailleur social' : (Auth::user()->role ?? 'Utilisateur') }}
                    </p>
                </div>

                {{-- Liens d'action --}}
                <div class="space-y-1">
                    <a href="#" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50 hover:text-sv-blue transition-colors">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Mon profil
                    </a>
                </div>

                <div class="h-px bg-gray-100 my-1 mx-2"></div>

                {{-- Bouton Déconnexion --}}
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-semibold text-red-600 hover:bg-red-50 transition-colors">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Déconnexion
                    </button>
                </form>
            </div>
        </div>

    </div>
</header>
