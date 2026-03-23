@extends('layouts.app')

@section('title', 'Adhérents')

@section('content')

    <div class="flex items-center justify-between mb-6 pl-2">
        <div>
            <p class="text-sm text-gray-400 font-medium">
                @if ($tab === 'payes')
                    <span class="font-bold text-gray-600">{{ $adherentsPayes->total() }}</span> adhérents ayant payé cette
                    saison
                @else
                    <span class="font-bold text-amber-600">{{ $adherentsEnAttente->total() }}</span> adhérents en attente de
                    paiement
                @endif
            </p>
        </div>
        <a href="#"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#222A60] hover:bg-[#1a2050] text-white text-sm font-bold rounded-xl transition-all duration-200 shadow-sm hover:shadow-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
            Modifier le formulaire
        </a>
    </div>

    <div class="pl-2 mb-0">
        <div class="flex items-center gap-1 bg-gray-100/80 p-1 rounded-xl w-fit">
            <a href="{{ route('adherents.index', ['tab' => 'payes'] + request()->except('tab', 'page')) }}"
                class="flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-bold transition-all duration-200
           {{ $tab === 'payes' ? 'bg-white text-[#222A60] shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                Adhérents payés
                <span
                    class="px-2 py-0.5 rounded-full text-xs font-black
                {{ $tab === 'payes' ? 'bg-[#16987C]/10 text-[#16987C]' : 'bg-gray-200 text-gray-500' }}">
                    {{ $countPayes }}
                </span>
            </a>
            <a href="{{ route('adherents.index', ['tab' => 'attente'] + request()->except('tab', 'page')) }}"
                class="flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-bold transition-all duration-200
           {{ $tab === 'attente' ? 'bg-white text-[#222A60] shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                En attente
                <span
                    class="px-2 py-0.5 rounded-full text-xs font-black
                {{ $tab === 'attente' ? 'bg-amber-100 text-amber-600' : 'bg-gray-200 text-gray-500' }}">
                    {{ $countAttente }}
                </span>
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_24px_rgba(0,0,0,0.04)] overflow-hidden">

        <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center gap-4">

            @if ($tab === 'attente')
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mr-1">Source</span>
                    @foreach (['Tous', 'HelloAsso', 'Espèces', 'Chèque', 'Virement'] as $src)
                        <a href="{{ route('adherents.index', array_merge(request()->all(), ['source' => $src === 'Tous' ? null : $src, 'page' => 1])) }}"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all duration-150
                       {{ ($filterSource ?? 'Tous') === $src
                           ? 'bg-[#222A60] text-white shadow-sm'
                           : 'bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-700' }}">
                            {{ $src }}
                        </a>
                    @endforeach
                </div>
                <div class="w-px h-5 bg-gray-200 hidden sm:block"></div>
            @endif

            <div class="flex-1 min-w-[220px] max-w-sm relative">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-300" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
                </svg>
                <form method="GET" action="{{ route('adherents.index') }}">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    @if ($filterSource && $filterSource !== 'Tous')
                        <input type="hidden" name="source" value="{{ $filterSource }}">
                    @endif
                    <input type="text" name="q" value="{{ $search ?? '' }}"
                        placeholder="Rechercher un adhérent..."
                        class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-100 rounded-xl text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-[#16987C]/30 focus:border-[#16987C]/40 transition-all">
                </form>
            </div>

            <span class="ml-auto text-xs font-medium text-gray-400 whitespace-nowrap hidden lg:block">
                Affichage de
                <span class="font-bold text-gray-600">
                    {{ $tab === 'payes' ? $adherentsPayes->total() : $adherentsEnAttente->total() }}
                </span>
                résultats
            </span>

        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-6 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            Adhérent</th>
                        <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Âge
                        </th>
                        <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            Activités</th>
                        @if ($tab === 'attente')
                            <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                Source</th>
                            <th class="px-4 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                Montant</th>
                        @endif
                        <th class="px-6 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">

                    @php
                        $items = $tab === 'payes' ? $adherentsPayes : $adherentsEnAttente;
                    @endphp

                    @forelse ($items as $adherent)
                        <tr class="group hover:bg-gray-50/70 transition-colors duration-100">

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-black shrink-0"
                                        style="background-color: {{ $adherent->couleur_avatar }}">
                                        {{ $adherent->initiales }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-sm text-[#0F143A]">{{ $adherent->prenom }}
                                            {{ $adherent->nom }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                @php
                                    $tranche = match ($adherent->tranche_age) {
                                        'Enfant' => ['label' => 'Enfant', 'class' => 'bg-sky-50 text-sky-600'],
                                        'Adolescent' => [
                                            'label' => 'Adolescent',
                                            'class' => 'bg-violet-50 text-violet-600',
                                        ],
                                        'Adulte' => ['label' => 'Adulte', 'class' => 'bg-emerald-50 text-emerald-600'],
                                        default => null,
                                    };
                                @endphp
                                @if ($tranche)
                                    <span
                                        class="inline-flex px-2.5 py-1 rounded-lg text-xs font-bold {{ $tranche['class'] }}">
                                        {{ $tranche['label'] }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>

                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse ($adherent->activitesActives as $activite)
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-600 rounded-lg text-xs font-semibold">
                                            {{ $activite->nom }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-gray-300">—</span>
                                    @endforelse
                                </div>
                            </td>

                            @if ($tab === 'attente')
                                <td class="px-4 py-4">
                                    @php
                                        $source = $adherent->paiements->first()?->source;
                                        $sourceStyle = match ($source) {
                                            'HelloAsso' => 'bg-[#16987C]/10 text-[#16987C]',
                                            'Virement' => 'bg-blue-50 text-blue-600',
                                            'Chèque' => 'bg-amber-50 text-amber-600',
                                            'Espèces' => 'bg-orange-50 text-orange-600',
                                            default => null,
                                        };
                                    @endphp
                                    @if ($source && $sourceStyle)
                                        <span
                                            class="inline-flex px-2.5 py-1 rounded-lg text-xs font-bold {{ $sourceStyle }}">
                                            {{ $source }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-300">—</span>
                                    @endif
                                </td>

                                <td class="px-4 py-4 text-right">
                                    @if ($adherent->montant_total > 0)
                                        <span class="font-black text-sm text-[#0F143A]">
                                            {{ number_format($adherent->montant_total, 2, ',', ' ') }} €
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-300">—</span>
                                    @endif
                                </td>
                            @endif

                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="#"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#222A60] hover:bg-[#1a2050] text-white rounded-lg text-xs font-bold transition-all duration-150 shadow-sm">
                                        Détails
                                        <svg class="w-3 h-3 opacity-60" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $tab === 'attente' ? 6 : 5 }}" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center">
                                        <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <p class="font-bold text-gray-400">Aucun adhérent trouvé</p>
                                    @if ($search)
                                        <p class="text-sm text-gray-300">pour « {{ $search }} »</p>
                                    @endif
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
                        <span
                            class="px-3 py-1.5 rounded-lg text-xs font-bold text-gray-300 cursor-not-allowed bg-gray-50">←
                            Précédent</span>
                    @else
                        <a href="{{ $items->previousPageUrl() }}"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold text-gray-500 hover:bg-gray-100 transition-colors">←
                            Précédent</a>
                    @endif

                    @foreach ($items->getUrlRange(max(1, $items->currentPage() - 2), min($items->lastPage(), $items->currentPage() + 2)) as $page => $url)
                        <a href="{{ $url }}"
                            class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold transition-colors
                       {{ $page === $items->currentPage() ? 'bg-[#222A60] text-white' : 'text-gray-500 hover:bg-gray-100' }}">
                            {{ $page }}
                        </a>
                    @endforeach

                    @if ($items->hasMorePages())
                        <a href="{{ $items->nextPageUrl() }}"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold text-gray-500 hover:bg-gray-100 transition-colors">Suivant
                            →</a>
                    @else
                        <span
                            class="px-3 py-1.5 rounded-lg text-xs font-bold text-gray-300 cursor-not-allowed bg-gray-50">Suivant
                            →</span>
                    @endif
                </div>
            </div>
        @endif

    </div>

@endsection
