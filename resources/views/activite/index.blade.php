{{-- ── SECTION : GRILLE DES ACTIVITÉS ────────────────────────── --}}
<div class="col-span-12">
    <div class="flex items-center justify-between mb-6 px-2">
        <h2 class="font-grotesk text-2xl font-bold text-gray-900">Programmes & Activités</h2>
        <div class="flex gap-2">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Saison {{ $saison }}</span>
        </div>
    </div>

    <div class="grid grid-cols-4 gap-6">
        @foreach ($activitesStats as $act)
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-xl hover:shadow-sv-blue/5 transition-all group relative overflow-hidden">
                {{-- Petit indicateur de type (Stage ou Activité) --}}
                <div class="absolute top-0 right-0">
                    <span class="text-[10px] font-black uppercase px-4 py-1.5 rounded-bl-2xl {{ $act->type === 'stage' ? 'bg-amber-100 text-amber-600' : 'bg-sv-blue text-white' }}">
                        {{ $act->type }}
                    </span>
                </div>

                <div class="flex flex-col h-full">
                    <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center mb-4 group-hover:bg-sv-green/10 transition-colors">
                        @if($act->type === 'stage')
                            <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        @else
                            <svg class="w-6 h-6 text-sv-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @endif
                    </div>

                    <h3 class="font-grotesk font-bold text-lg text-gray-900 leading-tight mb-1">{{ $act->nom }}</h3>
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wider mb-6">Identifiant #{{ $act->id }}</p>

                    <div class="mt-auto flex items-end justify-between">
                        <div>
                            <p class="text-4xl font-grotesk font-black text-sv-blue leading-none">{{ $act->total_inscrits }}</p>
                            <p class="text-[11px] font-bold text-gray-400 uppercase mt-1">Adhérents</p>
                        </div>

                        <a href="#" class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-sv-blue group-hover:text-white transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Carte "Ajouter" pour combler le vide --}}
        <a href="#" class="border-2 border-dashed border-gray-200 rounded-3xl p-6 flex flex-col items-center justify-center text-gray-400 hover:border-sv-green hover:text-sv-green transition-all group">
            <div class="w-12 h-12 rounded-full border-2 border-dashed border-current flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </div>
            <span class="font-grotesk font-bold text-sm uppercase tracking-widest">Nouvelle Activité</span>
        </a>
    </div>
</div>
