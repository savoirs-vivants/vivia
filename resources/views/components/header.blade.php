@props(['title' => 'Vue d\'ensemble', 'saison' => null])

<header class="flex items-center justify-between gap-3 mb-6 sm:mb-8">
    <div class="flex items-center gap-3 min-w-0">
        <button
            @click="sidebarOpen = !sidebarOpen"
            class="md:hidden shrink-0 w-10 h-10 flex items-center justify-center rounded-xl
                   bg-white border border-gray-200 shadow-sm text-gray-600
                   hover:bg-gray-50 hover:text-[#0F143A] transition-all active:scale-95"
            aria-label="Ouvrir le menu"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                      d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <div class="min-w-0">
            <h1 class="font-grotesk font-black text-[#0F143A] tracking-tighter leading-none truncate
                       text-2xl sm:text-3xl lg:text-4xl">
                {{ $title }}
            </h1>
        </div>
    </div>

    <div class="flex items-center bg-white p-1 sm:p-1.5 rounded-full
                shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 gap-1 shrink-0">

        @if ($saison)
            <div class="hidden sm:flex items-center gap-2 px-3 sm:px-4 py-2 bg-gray-50 rounded-full">
                <span class="w-2 h-2 rounded-full bg-[#16A37A]"></span>
                <span class="font-grotesk text-[10px] font-black text-gray-500 uppercase tracking-widest">
                    Saison {{ $saison }}
                </span>
            </div>
            <div class="w-px h-6 bg-gray-200 mx-1 hidden sm:block"></div>
        @endif

        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <button @click="open = !open"
                    class="flex items-center gap-1.5 pl-1.5 sm:pl-2 pr-2 sm:pr-4 py-1
                           hover:bg-gray-50 rounded-full transition-all focus:outline-none">
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-[#0F143A] text-white
                            font-black text-xs uppercase flex items-center justify-center">
                    {{ substr(Auth::user()->firstname ?? Auth::user()->name ?? 'U', 0, 1) }}
                </div>
                <svg class="w-3 h-3 text-gray-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" style="display: none;"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute right-0 top-full mt-3 w-64 bg-white rounded-[2rem]
                        border border-gray-100 shadow-[0_20px_40px_rgb(0,0,0,0.08)] p-3 z-50">

                <div class="px-4 py-3 bg-gray-50/50 rounded-2xl mb-3">
                    <p class="font-grotesk font-black text-[#0F143A] text-sm truncate">
                        {{ Auth::user()->firstname }} {{ Auth::user()->name }}
                    </p>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest truncate mt-0.5">
                        {{ Auth::user()->email }}
                    </p>
                </div>

                <div class="space-y-1">
                    <a href="{{ route('profile.edit') }}"
                       class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold
                              text-gray-500 hover:bg-gray-50 hover:text-[#0F143A] transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Modifier mon profil
                    </a>
                    @if(auth()->user()->role === 'admin')
                    <a href="{{ route('profile.logs') }}"
                       class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold
                              text-gray-500 hover:bg-gray-50 hover:text-[#0F143A] transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Journal d'erreurs
                    </a>
                    @endif
                </div>

                <div class="h-px bg-gray-100 my-2 mx-4"></div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold
                                   text-red-500 hover:bg-red-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Déconnexion
                    </button>
                </form>
            </div>
        </div>

    </div>
</header>
