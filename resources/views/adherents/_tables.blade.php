<div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_24px_rgba(0,0,0,0.04)] overflow-hidden">

    <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center gap-4">

        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mr-1">Type</span>

            @foreach (Auth::user()->role === 'animateur' ? ['tous' => 'Tous', 'physique' => 'Personnes physiques'] : ['tous' => 'Tous', 'physique' => 'Personnes physiques', 'structure' => 'Structures'] as $val => $label)
                <a href="{{ route('adherents.index', array_merge(request()->except('type', 'page'), ['type' => $val === 'tous' ? null : $val, 'page' => 1])) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all duration-150 {{ $filterType === $val ? 'bg-[#222A60] text-white shadow-sm' : 'bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-700' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div class="w-px h-5 bg-gray-200 hidden sm:block"></div>

        @if ($tab === 'attente')
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mr-1">Source</span>
                @foreach (['Tous', 'HelloAsso', 'Interne'] as $src)
                    <a href="{{ route('adherents.index', array_merge(request()->except('source', 'page'), ['source' => $src === 'Tous' ? null : $src, 'page' => 1])) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all duration-150 {{ ($filterSource ?? 'Tous') === $src ? 'bg-[#222A60] text-white shadow-sm' : 'bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-700' }}">
                        {{ $src }}
                    </a>
                @endforeach
            </div>
            <div class="w-px h-5 bg-gray-200 hidden sm:block"></div>
        @endif

        <div class="flex-1 min-w-[220px] max-w-sm relative">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
            </svg>
            <form method="GET" action="{{ route('adherents.index') }}">
                <input type="hidden" name="tab" value="{{ $tab }}">
                @if ($filterSource && $filterSource !== 'Tous')
                    <input type="hidden" name="source" value="{{ $filterSource }}">
                @endif
                @if ($filterType && $filterType !== 'tous')
                    <input type="hidden" name="type" value="{{ $filterType }}">
                @endif
                <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Rechercher..."
                       class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-100 rounded-xl text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-[#16987C]/30 focus:border-[#16987C]/40 transition-all">
            </form>
        </div>

        <span class="ml-auto text-xs font-medium text-gray-400 whitespace-nowrap hidden lg:block">
            Affichage de <span class="font-bold text-gray-600">
                {{ $filterType === 'structure' ? $structuresList->count() : $items->total() }}
            </span> résultats
        </span>
    </div>

    @if ($filterType === 'structure')
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/70 border-b border-gray-100">
                        @foreach (['Structure', 'Statut', 'Correspondant', 'Contact'] as $col)
                            <th class="px-{{ $loop->first ? '6' : '4' }} py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $col }}</th>
                        @endforeach
                        <th class="px-4 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Montant</th>
                        <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $tab === 'payes' ? 'Date' : 'Inscription' }}</th>
                        @if ($tab === 'payes')
                            <th class="px-6 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Statut paiement</th>
                            <th class="px-6 py-3"></th>
                        @else
                            <th class="px-6 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($structuresList as $structure)
                        <tr class="hover:bg-gray-50/70 transition-colors duration-100">
                            <td class="px-6 py-4">
                                <p class="font-bold text-sm text-[#0F143A]">{{ $structure->nom }}</p>
                                @if ($structure->sigle)
                                    <p class="text-xs text-gray-400">{{ $structure->sigle }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-bold {{ $structure->statutJuridiqueClass }}">
                                    {{ $structure->statutJuridiqueLabel }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-sm font-semibold text-gray-700">{{ $structure->nom_correspondant ?? '—' }}</p>
                            </td>
                            <td class="px-4 py-4">
                                @if ($structure->mail)
                                    <a href="mailto:{{ $structure->mail }}" class="text-xs text-teal-600 hover:underline">{{ $structure->mail }}</a>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right">
                                <span class="font-black text-sm text-[#0F143A]">
                                    {{ number_format((float) ($structure->inscription->montant ?? $structure->montant_adhesion ?? 0), 2, ',', ' ') }} €
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <span class="text-sm text-gray-500">
                                    {{ $structure->inscription?->date_inscription?->isoFormat('D MMM YYYY') ?? '—' }}
                                </span>
                            </td>

                            @if ($tab === 'payes')
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-3 py-1.5 bg-teal-100 text-teal-700 rounded-lg text-xs font-bold">Payée</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('structures.show', $structure) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-[#222A60]/5 hover:bg-[#222A60]/10 text-[#222A60] rounded-lg text-xs font-bold transition-all">
                                        Voir la fiche
                                        <svg class="w-3 h-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </td>
                            @else
                                <td class="px-6 py-4 text-right">
                                    <button @click="ouvrirModal({{ json_encode($structure->modalData()) }})"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#16987C] hover:bg-[#138a6f] text-white rounded-lg text-xs font-bold transition-all duration-150 shadow-sm">
                                        Valider
                                        <svg class="w-3 h-3 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center">
                                        <span class="text-2xl">🏛️</span>
                                    </div>
                                    <p class="font-bold text-gray-400">Aucune structure trouvée</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    @else
        <div class="overflow-x-auto">
            <table class="w-full table-fixed">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-6 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Adhérent</th>
                        <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Âge</th>
                        <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Activités</th>
                        @if (in_array($tab, ['attente', 'partiel']))
                            <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Source</th>
                            <th class="px-4 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                {{ $tab === 'partiel' ? 'Versé / Total' : 'Montant' }}
                            </th>
                            <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Inscrit le</th>
                        @endif
                        <th class="px-6 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($items as $adherent)
                        <tr class="group hover:bg-gray-50/70 transition-colors duration-100">

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-black shrink-0"
                                         style="background-color: {{ $adherent->couleur_avatar }}">
                                        {{ $adherent->initiales }}
                                    </div>
                                    <p class="font-bold text-sm text-[#0F143A]">{{ $adherent->prenom }} {{ $adherent->nom }}</p>
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                @if ($adherent->tranche_age)
                                    <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-bold {{ $adherent->trancheAgeClass }}">
                                        {{ $adherent->tranche_age }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>

                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse ($adherent->activitesActives as $activite)
                                        <span class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-600 rounded-lg text-xs font-semibold">
                                            {{ $activite->nom }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-gray-300">—</span>
                                    @endforelse
                                </div>
                            </td>

                            @if (in_array($tab, ['attente', 'partiel']))
                                <td class="px-4 py-4">
                                    <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-bold {{ $adherent->sourceClass }}">
                                        {{ $adherent->sourceLabel }}
                                    </span>
                                </td>

                                <td class="px-4 py-4 text-right">
                                    @if ($tab === 'partiel')
                                        @php
                                            $verse  = (float) $adherent->paiements->sum('montant');
                                            $total  = (float) ($adherent->inscription?->montant ?? 0);
                                            $reste  = max(0, $total - $verse);
                                            $pct    = $total > 0 ? min(100, round($verse / $total * 100)) : 0;
                                        @endphp
                                        <div class="flex flex-col items-end gap-1">
                                            <div>
                                                <span class="font-black text-sm text-emerald-600">{{ number_format($verse, 2, ',', ' ') }} €</span>
                                                <span class="text-xs text-gray-400"> / {{ number_format($total, 2, ',', ' ') }} €</span>
                                            </div>
                                            <div class="w-20 bg-gray-100 rounded-full h-1">
                                                <div class="bg-[#16987C] h-1 rounded-full" style="width: {{ $pct }}%"></div>
                                            </div>
                                            <span class="text-[10px] text-amber-500 font-bold">reste {{ number_format($reste, 2, ',', ' ') }} €</span>
                                        </div>
                                    @else
                                        <span class="font-black text-sm text-[#0F143A]">
                                            {{ number_format((float) $adherent->inscription?->montant, 2, ',', ' ') }} €
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-4">
                                    <span class="text-sm text-gray-500">
                                        {{ $adherent->inscription?->date_inscription?->isoFormat('D MMM YYYY') ?? '—' }}
                                    </span>
                                </td>
                            @endif

                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    @if (in_array($tab, ['attente', 'partiel']))
                                        <button @click="ouvrirModal({{ json_encode($adherent->modalData($tab)) }})"
                                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#16987C] hover:bg-[#138a6f] text-white rounded-lg text-xs font-bold transition-all duration-150 shadow-sm">
                                            {{ $adherent->isReinscription ? 'Valider (ré-inscription)' : 'Valider' }}
                                            <svg class="w-3 h-3 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </button>
                                    @else
                                        <a href="{{ route('adherents.show', $adherent) }}"
                                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#222A60] hover:bg-[#1a2050] text-white rounded-lg text-xs font-bold transition-all duration-150 shadow-sm">
                                            Détails
                                            <svg class="w-3 h-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ in_array($tab, ['attente', 'partiel']) ? 7 : 4 }}" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center">
                                        <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>
                                    <p class="font-bold text-gray-400">Aucun adhérent trouvé</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($items->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                <p class="text-xs text-gray-400">
                    Page <span class="font-bold text-gray-600">{{ $items->currentPage() }}</span>
                    sur <span class="font-bold text-gray-600">{{ $items->lastPage() }}</span>
                </p>
                <div class="flex items-center gap-1">
                    @if ($items->onFirstPage())
                        <span class="px-3 py-1.5 rounded-lg text-xs font-bold text-gray-300 cursor-not-allowed bg-gray-50">← Précédent</span>
                    @else
                        <a href="{{ $items->previousPageUrl() }}"
                           class="px-3 py-1.5 rounded-lg text-xs font-bold text-gray-500 hover:bg-gray-100 transition-colors">← Précédent</a>
                    @endif

                    @foreach ($items->getUrlRange(max(1, $items->currentPage() - 2), min($items->lastPage(), $items->currentPage() + 2)) as $page => $url)
                        <a href="{{ $url }}"
                           class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold transition-colors {{ $page === $items->currentPage() ? 'bg-[#222A60] text-white' : 'text-gray-500 hover:bg-gray-100' }}">
                            {{ $page }}
                        </a>
                    @endforeach

                    @if ($items->hasMorePages())
                        <a href="{{ $items->nextPageUrl() }}"
                           class="px-3 py-1.5 rounded-lg text-xs font-bold text-gray-500 hover:bg-gray-100 transition-colors">Suivant →</a>
                    @else
                        <span class="px-3 py-1.5 rounded-lg text-xs font-bold text-gray-300 cursor-not-allowed bg-gray-50">Suivant →</span>
                    @endif
                </div>
            </div>
        @endif
    @endif

</div>

@if ($filterType === 'tous' && $structuresList->isNotEmpty())
<div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_24px_rgba(0,0,0,0.04)] overflow-hidden mt-6">

    <div class="px-6 py-4 border-b border-gray-100">
        <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Structures</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/70 border-b border-gray-100">
                    @foreach (['Structure', 'Statut', 'Correspondant', 'Contact'] as $col)
                        <th class="px-{{ $loop->first ? '6' : '4' }} py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $col }}</th>
                    @endforeach
                    <th class="px-4 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Montant</th>
                    <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $tab === 'payes' ? 'Date' : 'Inscription' }}</th>
                    @if ($tab === 'payes')
                        <th class="px-6 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Statut paiement</th>
                        <th class="px-6 py-3"></th>
                    @else
                        <th class="px-6 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach ($structuresList as $structure)
                    <tr class="hover:bg-gray-50/70 transition-colors duration-100">
                        <td class="px-6 py-4">
                            <p class="font-bold text-sm text-[#0F143A]">{{ $structure->nom }}</p>
                            @if ($structure->sigle)
                                <p class="text-xs text-gray-400">{{ $structure->sigle }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-bold {{ $structure->statutJuridiqueClass }}">
                                {{ $structure->statutJuridiqueLabel }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <p class="text-sm font-semibold text-gray-700">{{ $structure->nom_correspondant ?? '—' }}</p>
                        </td>
                        <td class="px-4 py-4">
                            @if ($structure->mail)
                                <a href="mailto:{{ $structure->mail }}" class="text-xs text-teal-600 hover:underline">{{ $structure->mail }}</a>
                            @else
                                <span class="text-xs text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-right">
                            <span class="font-black text-sm text-[#0F143A]">
                                {{ number_format((float) ($structure->inscription->montant ?? $structure->montant_adhesion ?? 0), 2, ',', ' ') }} €
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <span class="text-sm text-gray-500">
                                {{ $structure->inscription?->date_inscription?->isoFormat('D MMM YYYY') ?? '—' }}
                            </span>
                        </td>
                        @if ($tab === 'payes')
                            <td class="px-6 py-4">
                                <span class="inline-flex px-3 py-1.5 bg-teal-100 text-teal-700 rounded-lg text-xs font-bold">Payée</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('structures.show', $structure) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 bg-[#222A60]/5 hover:bg-[#222A60]/10 text-[#222A60] rounded-lg text-xs font-bold transition-all">
                                    Voir la fiche
                                    <svg class="w-3 h-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </td>
                        @else
                            <td class="px-6 py-4 text-right">
                                <button @click="ouvrirModal({{ json_encode($structure->modalData()) }})"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#16987C] hover:bg-[#138a6f] text-white rounded-lg text-xs font-bold transition-all duration-150 shadow-sm">
                                    Valider
                                    <svg class="w-3 h-3 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
