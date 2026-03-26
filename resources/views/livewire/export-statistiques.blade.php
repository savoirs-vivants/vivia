<div x-data="{ open: false }" class="relative w-full sm:w-auto z-5">

    <button @click="open = !open" @click.outside="open = false"
        class="w-full sm:w-auto flex items-center justify-center gap-2 bg-[#222A60] hover:bg-[#1a2050] transition-colors text-white font-bold text-sm px-5 py-2.5 rounded-xl shadow-sm">

        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
        </svg>

        <span>Exporter</span>

        <svg class="w-4 h-4 shrink-0 transition-transform duration-200 ml-1" :class="open ? 'rotate-180' : ''" fill="none"
            stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <div x-show="open" x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute left-0 right-0 sm:left-auto sm:right-0 mt-2 sm:w-52 bg-white rounded-xl shadow-xl border border-gray-100 z-20 overflow-hidden origin-top sm:origin-top-right">

        <div class="py-1 p-1">
            <button wire:click="exportExcel" @click="open = false"
                class="group w-full text-left px-3 py-2.5 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 rounded-lg flex items-center gap-3 transition-all duration-200">
                <svg class="w-4 h-4 text-emerald-600 group-hover:scale-110 transition-transform duration-200 shrink-0"
                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125-1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0112 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0c.621 0 1.125.504 1.125 1.125v1.5" />
                </svg>
                <span class="font-bold truncate">Format Excel</span>
            </button>

            <div class="h-px bg-gray-100 my-1 mx-2"></div>

            <button wire:click="exportCsv" @click="open = false"
                class="group w-full text-left px-3 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg flex items-center gap-3 transition-all duration-200">
                <svg class="w-4 h-4 text-blue-600 group-hover:scale-110 transition-transform duration-200 shrink-0"
                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                <span class="font-bold truncate">Format CSV</span>
            </button>
        </div>
    </div>
</div>
