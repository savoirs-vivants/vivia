@extends('layouts.app')

@section('title', 'Adhérents')

@section('content')

<div x-data="adherentOverlay()" @keydown.escape.window="close()">

    <div x-show="open"
         x-transition:enter="transition duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/30 backdrop-blur-sm z-40"
         style="display:none"
         @click="close()">
    </div>

    <div x-show="open"
         x-transition:enter="transition duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 pointer-events-none"
         style="display:none">

        <div class="bg-white rounded-2xl shadow-[0_20px_60px_rgba(0,0,0,0.15)] w-full max-w-md pointer-events-auto overflow-hidden"
             @click.stop>

            <div class="flex items-center justify-between p-5 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-xs font-black shrink-0"
                         :style="'background-color:' + adherent.couleur">
                        <span x-text="adherent.initiales"></span>
                    </div>
                    <div>
                        <p class="font-bold text-sm text-[#0F143A]" x-text="adherent.nom"></p>
                        <p class="text-xs text-gray-400" x-text="adherent.meta"></p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold"
                          :class="adherent.sourceClass"
                          x-text="adherent.source"></span>
                    <button @click="close()" class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-300 hover:text-gray-500 hover:bg-gray-100 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <div class="p-5 space-y-4 max-h-[60vh] overflow-y-auto">

                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Détail Inscription</p>
                    <div class="space-y-2.5">
                        <template x-for="activite in adherent.activites" :key="activite.nom">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-[#0F143A]" x-text="activite.nom"></p>
                                    <p class="text-xs text-gray-400" x-text="activite.info"></p>
                                </div>
                                <p class="text-sm font-black text-[#0F143A]" x-text="activite.tarif"></p>
                            </div>
                        </template>
                    </div>
                    <div class="mt-3 pt-2 border-t border-gray-50 flex justify-end" x-show="!adherent.isStructure">
                        <p class="text-xs text-gray-400">+ Adhésion annuelle : <span class="font-semibold">10,00 €</span></p>
                    </div>
                </div>

                <template x-if="adherent.source === 'HelloAsso'">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-[#16987C]/8 rounded-xl border border-[#16987C]/15">
                            <span class="text-sm font-bold text-[#16987C]">Total réglé sur HelloAsso</span>
                            <span class="font-grotesk text-base font-black text-[#16987C]" x-text="adherent.montant"></span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-rose-50 rounded-xl border border-rose-100">
                            <span class="text-sm font-semibold text-gray-600">Statut actuel</span>
                            <span class="flex items-center gap-1.5 text-xs font-bold text-rose-500">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                En attente de validation
                            </span>
                        </div>
                    </div>
                </template>

                <template x-if="adherent.source !== 'HelloAsso'">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-amber-50 rounded-xl border border-amber-100">
                            <span class="text-sm font-bold text-amber-600">Total à régler</span>
                            <span class="font-grotesk text-base font-black text-amber-600" x-text="adherent.montant"></span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-rose-50 rounded-xl border border-rose-100">
                            <span class="text-sm font-semibold text-gray-600">Statut actuel</span>
                            <span class="flex items-center gap-1.5 text-xs font-bold text-rose-500">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                En attente de validation
                            </span>
                        </div>

                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 space-y-3" x-show="!adherent.isStructure">
                            <button type="button"
                                    @click="plusieursVersements = !plusieursVersements"
                                    class="w-full flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <span class="text-sm font-bold text-gray-600">Paiement en plusieurs fois</span>
                                </div>
                                <div class="w-9 h-5 rounded-full transition-colors duration-200 relative"
                                     :class="plusieursVersements ? 'bg-[#16987C]' : 'bg-gray-200'">
                                    <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200"
                                         :class="plusieursVersements ? 'translate-x-4' : 'translate-x-0'"></div>
                                </div>
                            </button>

                            <div x-show="plusieursVersements"
                                 x-transition:enter="transition duration-150"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="space-y-2 pt-1 border-t border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div class="flex-1">
                                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">1er versement reçu</label>
                                        <div class="relative">
                                            <input type="number"
                                                   x-model="montantRecu"
                                                   @input="calculerReste()"
                                                   step="0.01" min="0"
                                                   placeholder="0,00"
                                                   class="w-full pl-3 pr-8 py-2 bg-white border border-gray-200 rounded-lg text-sm font-semibold text-[#0F143A] focus:outline-none focus:ring-2 focus:ring-[#16987C]/30 focus:border-[#16987C]/40">
                                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-bold">€</span>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Reste dû</label>
                                        <div class="relative">
                                            <input type="text"
                                                   :value="resteFormate"
                                                   readonly
                                                   class="w-full pl-3 pr-8 py-2 bg-amber-50 border border-amber-100 rounded-lg text-sm font-black text-amber-600 cursor-not-allowed">
                                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-amber-400 font-bold">€</span>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-400">Le statut passera en <span class="font-bold text-amber-600">Partiel</span> jusqu'au solde complet.</p>
                            </div>
                        </div>
                    </div>
                </template>

            </div>

            <div class="flex items-center justify-between px-5 py-4 border-t border-gray-100 bg-gray-50/50">
                <button @click="close()"
                        class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition-all">
                    Annuler
                </button>
                <form :action="actionUrl" method="POST">
                    @csrf
                    <input type="hidden" name="commentaire" :value="commentaire">
                    <input type="hidden" name="plusieurs_versements" :value="plusieursVersements ? '1' : '0'">
                    <input type="hidden" name="montant_recu" :value="montantRecu">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2 text-white text-sm font-bold rounded-xl transition-all shadow-sm"
                            :class="plusieursVersements ? 'bg-amber-500 hover:bg-amber-600' : 'bg-[#16987C] hover:bg-[#138a6f]'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span x-text="plusieursVersements ? 'Valider (partiel)' : 'Valider l\'adhésion'"></span>
                    </button>
                </form>
            </div>

        </div>
    </div>

    <div class="flex items-center justify-between mb-6 pl-2">
        <div>
            <p class="text-sm text-gray-400 font-medium">
                @if ($tab === 'payes')
                    <span class="font-bold text-gray-600">{{ $adherentsPayes->total() }}</span> adhérents ayant payé cette saison
                @elseif ($tab === 'partiel')
                    <span class="font-bold text-amber-600">{{ $adherentsPartiel->total() }}</span> adhérents en paiement partiel
                @else
                    <span class="font-bold text-rose-500">{{ $adherentsEnAttente->total() }}</span> adhérents en attente de paiement
                @endif
            </p>
        </div>
        <a href="#"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#222A60] hover:bg-[#1a2050] text-white text-sm font-bold rounded-xl transition-all duration-200 shadow-sm hover:shadow-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            Modifier le formulaire
        </a>
    </div>

    <div class="pl-2 mb-0">
        <div class="flex items-center gap-1 bg-gray-100/80 p-1 rounded-xl w-fit">
            <a href="{{ route('adherents.index', ['tab' => 'payes'] + request()->except('tab', 'page')) }}"
               class="flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-bold transition-all duration-200 {{ $tab === 'payes' ? 'bg-white text-[#222A60] shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                Adhérents
                <span class="px-2 py-0.5 rounded-full text-xs font-black {{ $tab === 'payes' ? 'bg-[#16987C]/10 text-[#16987C]' : 'bg-gray-200 text-gray-500' }}">{{ $countPayes }}</span>
            </a>
            <a href="{{ route('adherents.index', ['tab' => 'partiel'] + request()->except('tab', 'page')) }}"
               class="flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-bold transition-all duration-200 {{ $tab === 'partiel' ? 'bg-white text-[#222A60] shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                Partiel
                <span class="px-2 py-0.5 rounded-full text-xs font-black {{ $tab === 'partiel' ? 'bg-amber-100 text-amber-600' : 'bg-gray-200 text-gray-500' }}">{{ $countPartiel }}</span>
            </a>
            <a href="{{ route('adherents.index', ['tab' => 'attente'] + request()->except('tab', 'page')) }}"
               class="flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-bold transition-all duration-200 {{ $tab === 'attente' ? 'bg-white text-[#222A60] shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                En attente
                <span class="px-2 py-0.5 rounded-full text-xs font-black {{ $tab === 'attente' ? 'bg-rose-100 text-rose-500' : 'bg-gray-200 text-gray-500' }}">{{ $countAttente }}</span>
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_24px_rgba(0,0,0,0.04)] overflow-hidden">

        @php
            $filterType = request('type', 'tous');
            $structuresList = match($tab) {
                'payes' => $structuresPayees ?? collect(),
                'attente' => $structuresEnAttente ?? collect(),
                default => collect(),
            };

            $items = match($tab) {
                'payes'   => $adherentsPayes,
                'partiel' => $adherentsPartiel,
                default   => $adherentsEnAttente,
            };
        @endphp

        <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center gap-4">

            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mr-1">Type</span>
                @foreach (['tous' => 'Tous', 'physique' => 'Personnes physiques', 'structure' => 'Structures'] as $val => $label)
                    <a href="{{ route('adherents.index', array_merge(request()->all(), ['type' => $val === 'tous' ? null : $val, 'page' => 1])) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all duration-150 {{ $filterType === $val ? 'bg-[#222A60] text-white shadow-sm' : 'bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-700' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
            <div class="w-px h-5 bg-gray-200 hidden sm:block"></div>

            @if ($tab === 'attente')
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mr-1">Source</span>
                    @foreach (['Tous', 'HelloAsso', 'Interne', 'Pass Culture'] as $src)
                        <a href="{{ route('adherents.index', array_merge(request()->all(), ['source' => $src === 'Tous' ? null : $src, 'page' => 1])) }}"
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
                    <input type="text" name="q" value="{{ $search ?? '' }}"
                           placeholder="Rechercher..."
                           class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-100 rounded-xl text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-[#16987C]/30 focus:border-[#16987C]/40 transition-all">
                </form>
            </div>

            <span class="ml-auto text-xs font-medium text-gray-400 whitespace-nowrap hidden lg:block">
                Affichage de <span class="font-bold text-gray-600">
                @if ($filterType === 'structure')
                    {{ $structuresList->count() }}
                @else
                    {{ match($tab) { 'payes' => $adherentsPayes->total(), 'partiel' => $adherentsPartiel->total(), default => $adherentsEnAttente->total() } }}
                @endif
                </span> résultats
            </span>
        </div>

        @if ($filterType === 'structure')
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="px-6 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Structure</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Statut</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Correspondant</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Contact</th>
                            <th class="px-4 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Montant</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Inscription</th>
                            <th class="px-6 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Statut / Actions</th>
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
                                    @php
                                        $sjLabel = match($structure->statut_juridique) {
                                            'tpe_asso' => ['label' => 'TPE/Asso', 'class' => 'bg-indigo-50 text-indigo-600'],
                                            'esr_pme'  => ['label' => 'ESR/PME',  'class' => 'bg-purple-50 text-purple-600'],
                                            default    => ['label' => $structure->statut_juridique, 'class' => 'bg-gray-100 text-gray-500'],
                                        };
                                    @endphp
                                    <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-bold {{ $sjLabel['class'] }}">{{ $sjLabel['label'] }}</span>
                                    @if ($structure->statut)
                                        <span class="mt-1 inline-flex px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-500">{{ ucfirst($structure->statut) }}</span>
                                    @endif
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
                                    <span class="font-black text-sm text-[#0F143A]">{{ number_format((float)($structure->inscription->montant ?? $structure->montant_adhesion ?? 0), 2, ',', ' ') }} €</span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="text-sm text-gray-500">{{ $structure->inscription?->date_inscription?->isoFormat('D MMM YYYY') ?? '—' }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($tab === 'payes')
                                        <span class="inline-flex px-3 py-1.5 bg-teal-100 text-teal-700 rounded-lg text-xs font-bold">Payée</span>
                                    @else
                                        @php
                                            $modalSource = 'Interne';
                                            if (isset($structure->paiements) && $structure->paiements->isNotEmpty()) {
                                                $modalSource = $structure->paiements->first()->source;
                                            } elseif (isset($structure->inscription->source)) {
                                                $modalSource = $structure->inscription->source;
                                            } elseif (isset($structure->source)) {
                                                $modalSource = $structure->source;
                                            }
                                            if (isset($structure->inscription->mode_paiement) && strtolower($structure->inscription->mode_paiement) === 'helloasso') {
                                                $modalSource = 'HelloAsso';
                                            }

                                            $montantFinal = $structure->montant_adhesion;

                                            $activitesStructure = [];
                                            $tarifAdhesion = match($structure->statut_juridique) {
                                                'tpe_asso' => 50,
                                                'esr_pme' => 200,
                                                default => 0
                                            };
                                            if ($tarifAdhesion > 0) {
                                                $activitesStructure[] = [
                                                    'nom' => 'Cotisation annuelle',
                                                    'tarif' => number_format($tarifAdhesion, 2, ',', ' ') . ' €'
                                                ];
                                            }
                                            if ($structure->statut === 'ressourcerie') {
                                                $activitesStructure[] = [
                                                    'nom' => 'Ressourcerie Codey Rocky',
                                                    'tarif' => '50,00 €'
                                                ];
                                            }
                                            if (empty($activitesStructure)) {
                                                $activitesStructure[] = [
                                                    'nom' => 'Adhésion',
                                                    'tarif' => number_format((float) $montantFinal, 2, ',', ' ') . ' €'
                                                ];
                                            }
                                        @endphp
                                        <button
                                            @click="ouvrirModal({{ json_encode([
                                                'actionUrl'   => '/structures/' . $structure->id . '/valider',
                                                'isStructure' => true,
                                                'id'          => $structure->id,
                                                'nom'         => $structure->nom,
                                                'initiales'   => $structure->sigle ?: mb_substr($structure->nom, 0, 2),
                                                'couleur'     => '#222A60',
                                                'meta'        => 'Structure · Inscrite le ' . ($structure->inscription?->date_inscription?->isoFormat('D MMM YYYY') ?? ''),
                                                'source'      => $modalSource,
                                                'sourceClass' => match($modalSource) {
                                                    'HelloAsso'    => 'bg-[#16987C]/10 text-[#16987C]',
                                                    'Virement'     => 'bg-blue-50 text-blue-600',
                                                    'Chèque'       => 'bg-amber-50 text-amber-600',
                                                    'Espèces'      => 'bg-orange-50 text-orange-600',
                                                    'Pass Culture' => 'bg-purple-50 text-purple-600',
                                                    default        => 'bg-gray-100 text-gray-600',
                                                },
                                                'montant'      => number_format((float) $montantFinal, 2, ',', ' ') . ' €',
                                                'datePaiement' => '',
                                                'activites'    => $activitesStructure,
                                            ]) }})"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#16987C] hover:bg-[#138a6f] text-white rounded-lg text-xs font-bold transition-all duration-150 shadow-sm">
                                            Valider
                                            <svg class="w-3 h-3 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-20 text-center">
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
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="px-6 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Adhérent</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Âge</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Activités</th>
                            @if ($tab === 'attente' || $tab === 'partiel')
                                <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Source paiement</th>
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
                                        <div>
                                            <p class="font-bold text-sm text-[#0F143A]">{{ $adherent->prenom }} {{ $adherent->nom }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-4">
                                    @php
                                        $tranche = match ($adherent->tranche_age) {
                                            'Enfant'     => ['label' => 'Enfant',     'class' => 'bg-sky-50 text-sky-600'],
                                            'Adolescent' => ['label' => 'Adolescent', 'class' => 'bg-violet-50 text-violet-600'],
                                            'Adulte'     => ['label' => 'Adulte',     'class' => 'bg-emerald-50 text-emerald-600'],
                                            default      => null,
                                        };
                                    @endphp
                                    @if ($tranche)
                                        <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-bold {{ $tranche['class'] }}">{{ $tranche['label'] }}</span>
                                    @else
                                        <span class="text-xs text-gray-300">—</span>
                                    @endif
                                </td>

                                <td class="px-4 py-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse ($adherent->activitesActives as $activite)
                                            <span class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-600 rounded-lg text-xs font-semibold">{{ $activite->nom }}</span>
                                        @empty
                                            <span class="text-xs text-gray-300">—</span>
                                        @endforelse
                                    </div>
                                </td>

                                @if ($tab === 'attente' || $tab === 'partiel')
                                    <td class="px-4 py-4">
                                        @php
                                            $source = $adherent->paiements->first()?->source ?: 'Interne';
                                            $sourceStyle = match ($source) {
                                                'HelloAsso'    => 'bg-[#16987C]/10 text-[#16987C]',
                                                'Virement'     => 'bg-blue-50 text-blue-600',
                                                'Chèque'       => 'bg-amber-50 text-amber-600',
                                                'Espèces'      => 'bg-orange-50 text-orange-600',
                                                'Pass Culture' => 'bg-purple-50 text-purple-600',
                                                default        => 'bg-gray-100 text-gray-600',
                                            };
                                        @endphp
                                        <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-bold {{ $sourceStyle }}">{{ $source }}</span>
                                    </td>

                                    <td class="px-4 py-4 text-right">
                                        @if ($tab === 'partiel')
                                            @php
                                                $verse        = (float) $adherent->paiements->sum('montant');
                                                $totalAttendu = (float) ($adherent->inscription?->montant ?? $adherent->montant_total);
                                            @endphp
                                            <span class="font-black text-sm text-amber-600">
                                                {{ number_format($verse, 2, ',', ' ') }} €
                                            </span>
                                            <span class="text-xs text-gray-400">
                                                / {{ number_format($totalAttendu, 2, ',', ' ') }} €
                                            </span>
                                        @else
                                            @if ($adherent->inscription && $adherent->inscription->montant > 0)
                                                <span class="font-black text-sm text-[#0F143A]">{{ number_format($adherent->inscription->montant, 2, ',', ' ') }} €</span>
                                            @else
                                                <span class="text-xs text-gray-300">—</span>
                                            @endif
                                        @endif
                                    </td>

                                    <td class="px-4 py-4">
                                        <span class="text-sm text-gray-500">{{ $adherent->inscription?->date_inscription?->isoFormat('D MMM YYYY') ?? '—' }}</span>
                                    </td>
                                @endif

                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        @if ($tab === 'attente' || $tab === 'partiel')
                                        @php
                                                $modalSource = $adherent->paiements->first()?->source ?: 'Interne';
                                        @endphp
                                            <button
                                                @click="ouvrirModal({{ json_encode([
                                                    'actionUrl'   => '/adherents/' . $adherent->id . '/valider',
                                                    'isStructure' => false,
                                                    'id'          => $adherent->id,
                                                    'nom'         => $adherent->prenom . ' ' . $adherent->nom,
                                                    'initiales'   => $adherent->initiales,
                                                    'couleur'     => $adherent->couleur_avatar,
                                                    'meta'        => ($adherent->tranche_age ?? 'Adulte') . ' · Inscrit le ' . ($adherent->inscription?->date_inscription?->isoFormat('D MMM YYYY') ?? ''),
                                                    'source'      => $modalSource,
                                                    'sourceClass' => match($modalSource) {
                                                        'HelloAsso'    => 'bg-[#16987C]/10 text-[#16987C]',
                                                        'Virement'     => 'bg-blue-50 text-blue-600',
                                                        'Chèque'       => 'bg-amber-50 text-amber-600',
                                                        'Espèces'      => 'bg-orange-50 text-orange-600',
                                                        'Pass Culture' => 'bg-purple-50 text-purple-600',
                                                        default        => 'bg-gray-100 text-gray-600',
                                                    },
                                                    'montant'      => number_format((float) ($adherent->inscription?->montant ?? 0), 2, ',', ' ') . ' €',
                                                    'refFacture'   => $adherent->paiements->first()?->ref_facture ?? '',
                                                    'datePaiement' => $adherent->paiements->first()?->date_paiement?->isoFormat('D MMM YYYY') ?? '',
                                                    'commentaire'  => $adherent->commentaire ?? '',
                                                    'activites'    => $adherent->activitesActives->map(fn($a) => [
                                                        'nom'   => $a->nom,
                                                        'info'  => collect($a->horaires_list)->first() ?? '',
                                                        'tarif' => number_format((float)$a->tarif, 2, ',', ' ') . ' €',
                                                    ])->values()->toArray(),
                                                ]) }})"
                                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#16987C] hover:bg-[#138a6f] text-white rounded-lg text-xs font-bold transition-all duration-150 shadow-sm">
                                                Valider
                                                <svg class="w-3 h-3 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                            </button>
                                        @else
                                            <a href="{{ route('adherents.show', $adherent) }}"
                                               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#222A60] hover:bg-[#1a2050] text-white rounded-lg text-xs font-bold transition-all duration-150 shadow-sm">
                                                Détails
                                                <svg class="w-3 h-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
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
                                            <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
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
                            <a href="{{ $items->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg text-xs font-bold text-gray-500 hover:bg-gray-100 transition-colors">← Précédent</a>
                        @endif
                        @foreach ($items->getUrlRange(max(1, $items->currentPage()-2), min($items->lastPage(), $items->currentPage()+2)) as $page => $url)
                            <a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold transition-colors {{ $page === $items->currentPage() ? 'bg-[#222A60] text-white' : 'text-gray-500 hover:bg-gray-100' }}">{{ $page }}</a>
                        @endforeach
                        @if ($items->hasMorePages())
                            <a href="{{ $items->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-xs font-bold text-gray-500 hover:bg-gray-100 transition-colors">Suivant →</a>
                        @else
                            <span class="px-3 py-1.5 rounded-lg text-xs font-bold text-gray-300 cursor-not-allowed bg-gray-50">Suivant →</span>
                        @endif
                    </div>
                </div>
            @endif

        @endif
    </div>

    @if ($filterType === 'tous')

        @if ($tab === 'payes' && $structuresPayees->isNotEmpty())
            <div class="mt-6 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                    <span class="text-lg">🏛️</span>
                    <h3 class="font-bold text-gray-800 text-sm">Structures adhérentes</h3>
                    <span class="ml-auto px-2.5 py-0.5 bg-teal-100 text-teal-600 text-xs font-black rounded-full">{{ $structuresPayees->count() }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50/70">
                                <th class="px-6 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Structure</th>
                                <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Statut</th>
                                <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Correspondant</th>
                                <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Contact</th>
                                <th class="px-4 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Montant</th>
                                <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Date</th>
                                <th class="px-6 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Statut paiement</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($structuresPayees as $structure)
                                <tr class="hover:bg-gray-50/70 transition-colors duration-100">
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-sm text-[#0F143A]">{{ $structure->nom }}</p>
                                        @if ($structure->sigle)
                                            <p class="text-xs text-gray-400">{{ $structure->sigle }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        @php
                                            $sjLabel = match($structure->statut_juridique) {
                                                'tpe_asso' => ['label' => 'TPE/Asso', 'class' => 'bg-indigo-50 text-indigo-600'],
                                                'esr_pme'  => ['label' => 'ESR/PME',  'class' => 'bg-purple-50 text-purple-600'],
                                                default    => ['label' => $structure->statut_juridique, 'class' => 'bg-gray-100 text-gray-500'],
                                            };
                                        @endphp
                                        <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-bold {{ $sjLabel['class'] }}">{{ $sjLabel['label'] }}</span>
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
                                        <span class="font-black text-sm text-[#0F143A]">{{ number_format((float) $structure->montant_adhesion, 2, ',', ' ') }} €</span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="text-sm text-gray-500">{{ $structure->inscription?->date_inscription?->isoFormat('D MMM YYYY') ?? '—' }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-3 py-1.5 bg-teal-100 text-teal-700 rounded-lg text-xs font-bold">Payée</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('structures.show', $structure) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-[#222A60]/5 hover:bg-[#222A60]/10 text-[#222A60] rounded-lg text-xs font-bold transition-all">
                                            Voir la fiche
                                            <svg class="w-3 h-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if ($tab === 'attente' && $structuresEnAttente->isNotEmpty())
            <div class="mt-6 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                    <span class="text-lg">🏛️</span>
                    <h3 class="font-bold text-gray-800 text-sm">Structures en attente de validation</h3>
                    <span class="ml-auto px-2.5 py-0.5 bg-rose-100 text-rose-600 text-xs font-black rounded-full">{{ $structuresEnAttente->count() }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50/70">
                                <th class="px-6 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Structure</th>
                                <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Statut</th>
                                <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Correspondant</th>
                                <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Contact</th>
                                <th class="px-4 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Montant</th>
                                <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Inscription</th>
                                <th class="px-6 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($structuresEnAttente as $structure)
                                <tr class="hover:bg-gray-50/70 transition-colors duration-100">
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-sm text-[#0F143A]">{{ $structure->nom }}</p>
                                        @if ($structure->sigle)
                                            <p class="text-xs text-gray-400">{{ $structure->sigle }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        @php
                                            $sjLabel = match($structure->statut_juridique) {
                                                'tpe_asso' => ['label' => 'TPE/Asso', 'class' => 'bg-indigo-50 text-indigo-600'],
                                                'esr_pme'  => ['label' => 'ESR/PME',  'class' => 'bg-purple-50 text-purple-600'],
                                                default    => ['label' => $structure->statut_juridique, 'class' => 'bg-gray-100 text-gray-500'],
                                            };
                                        @endphp
                                        <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-bold {{ $sjLabel['class'] }}">{{ $sjLabel['label'] }}</span>
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
                                        <span class="font-black text-sm text-[#0F143A]">{{ number_format((float) $structure->montant_adhesion, 2, ',', ' ') }} €</span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="text-sm text-gray-500">{{ $structure->inscription?->date_inscription?->isoFormat('D MMM YYYY') ?? '—' }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @php
                                            $modalSource = 'Interne';
                                            if (isset($structure->paiements) && $structure->paiements->isNotEmpty()) {
                                                $modalSource = $structure->paiements->first()->source;
                                            } elseif (isset($structure->inscription->source)) {
                                                $modalSource = $structure->inscription->source;
                                            } elseif (isset($structure->source)) {
                                                $modalSource = $structure->source;
                                            }
                                            if (isset($structure->inscription->mode_paiement) && strtolower($structure->inscription->mode_paiement) === 'helloasso') {
                                                $modalSource = 'HelloAsso';
                                            }

                                            $montantFinal = $structure->montant_adhesion;

                                            $activitesStructure = [];
                                            $tarifAdhesion = match($structure->statut_juridique) {
                                                'tpe_asso' => 50,
                                                'esr_pme' => 200,
                                                default => 0
                                            };
                                            if ($tarifAdhesion > 0) {
                                                $activitesStructure[] = [
                                                    'nom' => 'Cotisation annuelle',
                                                    'tarif' => number_format($tarifAdhesion, 2, ',', ' ') . ' €'
                                                ];
                                            }
                                            if ($structure->statut === 'ressourcerie') {
                                                $activitesStructure[] = [
                                                    'nom' => 'Ressourcerie Codey Rocky',
                                                    'tarif' => '50,00 €'
                                                ];
                                            }
                                        @endphp
                                        <button
                                            @click="ouvrirModal({{ json_encode([
                                                'actionUrl'   => '/structures/' . $structure->id . '/valider',
                                                'isStructure' => true,
                                                'id'          => $structure->id,
                                                'nom'         => $structure->nom,
                                                'initiales'   => $structure->sigle ?: mb_substr($structure->nom, 0, 2),
                                                'couleur'     => '#222A60',
                                                'meta'        => 'Structure · Inscrite le ' . ($structure->inscription?->date_inscription?->isoFormat('D MMM YYYY') ?? ''),
                                                'source'      => $modalSource,
                                                'sourceClass' => match($modalSource) {
                                                    'HelloAsso'    => 'bg-[#16987C]/10 text-[#16987C]',
                                                    'Virement'     => 'bg-blue-50 text-blue-600',
                                                    'Chèque'       => 'bg-amber-50 text-amber-600',
                                                    'Espèces'      => 'bg-orange-50 text-orange-600',
                                                    'Pass Culture' => 'bg-purple-50 text-purple-600',
                                                    default        => 'bg-gray-100 text-gray-600',
                                                },
                                                'montant'      => number_format((float) $montantFinal, 2, ',', ' ') . ' €',
                                                'refFacture'   => '',
                                                'datePaiement' => '',
                                                'commentaire'  => '',
                                                'activites'    => $activitesStructure,
                                            ]) }})"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#16987C] hover:bg-[#138a6f] text-white rounded-lg text-xs font-bold transition-all duration-150 shadow-sm">
                                            Valider
                                            <svg class="w-3 h-3 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                        </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    @endif
</div>

<script>
function adherentOverlay() {
    return {
        open: false,
        actionUrl: '',
        commentaire: '',
        plusieursVersements: false,
        montantRecu: '',
        resteFormate: '0,00',

        ouvrirModal(data) {
            this.adherent           = data;
            this.actionUrl          = data.actionUrl;
            this.commentaire        = data.commentaire || '';
            this.plusieursVersements = false;
            this.montantRecu        = '';
            this.resteFormate       = '0,00';
            this.open               = true;
            document.body.style.overflow = 'hidden';
        },

        calculerReste() {
            const total  = parseFloat(this.adherent.montant.replace(/\s/g, '').replace(',', '.').replace('€', '')) || 0;
            const recu   = parseFloat(this.montantRecu) || 0;
            const reste  = Math.max(0, total - recu);
            this.resteFormate = reste.toFixed(2).replace('.', ',');
        },

        close() {
            this.open = false;
            document.body.style.overflow = '';
        },

        adherent: {
            isStructure: false, id: null, nom: '', initiales: '', couleur: '', meta: '',
            source: '', sourceClass: '', montant: '', refFacture: '',
            datePaiement: '', activites: [], commentaire: '',
        },
    }
}
</script>

@endsection
