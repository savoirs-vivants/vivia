@php
    $filterType = request('type', 'tous');
    $structuresList = match ($tab) {
        'payes' => $structuresPayees ?? collect(),
        'attente' => $structuresEnAttente ?? collect(),
        default => collect(),
    };

    $items = match ($tab) {
        'payes' => $adherentsPayes,
        'partiel' => $adherentsPartiel,
        default => $adherentsEnAttente,
    };
@endphp

<div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_24px_rgba(0,0,0,0.04)] overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center gap-4">
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mr-1">Type</span>
            @php
                $typeOptions =
                    Auth::user()->role === 'animateur'
                        ? ['tous' => 'Tous', 'physique' => 'Personnes physiques']
                        : ['tous' => 'Tous', 'physique' => 'Personnes physiques', 'structure' => 'Structures'];
            @endphp
            @foreach ($typeOptions as $val => $label)
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
                @if ($filterType && $filterType !== 'tous')
                    <input type="hidden" name="type" value="{{ $filterType }}">
                @endif
                <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Rechercher..."
                    class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-100 rounded-xl text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-[#16987C]/30 focus:border-[#16987C]/40 transition-all">
            </form>
        </div>

        <span class="ml-auto text-xs font-medium text-gray-400 whitespace-nowrap hidden lg:block">
            Affichage de <span class="font-bold text-gray-600">
                @if ($filterType === 'structure')
                    {{ $structuresList->count() }}
                @else
                    {{ match ($tab) {'payes' => $adherentsPayes->total(),'partiel' => $adherentsPartiel->total(),default => $adherentsEnAttente->total()} }}
                @endif
            </span> résultats
        </span>
    </div>

    @if ($filterType === 'structure')
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/70 border-b border-gray-100">
                        <th class="px-6 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            Structure</th>
                        <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            Statut</th>
                        <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            Correspondant</th>
                        <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            Contact</th>
                        <th class="px-4 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            Montant</th>
                        <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            {{ $tab === 'payes' ? 'Date' : 'Inscription' }}</th>
                        @if ($tab === 'payes')
                            <th
                                class="px-6 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                Statut paiement</th>
                            <th class="px-6 py-3"></th>
                        @else
                            <th
                                class="px-6 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                Actions</th>
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
                                @php
                                    $sjLabel = match ($structure->statut_juridique) {
                                        'tpe_asso' => [
                                            'label' => 'TPE/Asso',
                                            'class' => 'bg-indigo-50 text-indigo-600',
                                        ],
                                        'esr_pme' => ['label' => 'ESR/PME', 'class' => 'bg-purple-50 text-purple-600'],
                                        default => [
                                            'label' => $structure->statut_juridique,
                                            'class' => 'bg-gray-100 text-gray-500',
                                        ],
                                    };
                                @endphp
                                <span
                                    class="inline-flex px-2.5 py-1 rounded-lg text-xs font-bold {{ $sjLabel['class'] }}">{{ $sjLabel['label'] }}</span>
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-sm font-semibold text-gray-700">
                                    {{ $structure->nom_correspondant ?? '—' }}</p>
                            </td>
                            <td class="px-4 py-4">
                                @if ($structure->mail)
                                    <a href="mailto:{{ $structure->mail }}"
                                        class="text-xs text-teal-600 hover:underline">{{ $structure->mail }}</a>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right"><span
                                    class="font-black text-sm text-[#0F143A]">{{ number_format((float) ($structure->inscription->montant ?? ($structure->montant_adhesion ?? 0)), 2, ',', ' ') }}
                                    €</span></td>
                            <td class="px-4 py-4"><span
                                    class="text-sm text-gray-500">{{ $structure->inscription?->date_inscription?->isoFormat('D MMM YYYY') ?? '—' }}</span>
                            </td>

                            @if ($tab === 'payes')
                                <td class="px-6 py-4"><span
                                        class="inline-flex px-3 py-1.5 bg-teal-100 text-teal-700 rounded-lg text-xs font-bold">Payée</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('structures.show', $structure) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-[#222A60]/5 hover:bg-[#222A60]/10 text-[#222A60] rounded-lg text-xs font-bold transition-all">
                                        Voir la fiche <svg class="w-3 h-3 opacity-60" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </td>
                            @else
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
                                        if (
                                            isset($structure->inscription->mode_paiement) &&
                                            strtolower($structure->inscription->mode_paiement) === 'helloasso'
                                        ) {
                                            $modalSource = 'HelloAsso';
                                        }

                                        $montantFinal = $structure->montant_adhesion;
                                        $activitesStructure = [];
                                        $tarifAdhesion = match ($structure->statut_juridique) {
                                            'tpe_asso' => 50,
                                            'esr_pme' => 200,
                                            default => 0,
                                        };
                                        if ($tarifAdhesion > 0) {
                                            $activitesStructure[] = [
                                                'nom' => 'Cotisation annuelle',
                                                'tarif' => number_format($tarifAdhesion, 2, ',', ' ') . ' €',
                                            ];
                                        }
                                        if ($structure->statut === 'ressourcerie') {
                                            $activitesStructure[] = [
                                                'nom' => 'Ressourcerie Codey Rocky',
                                                'tarif' => '50,00 €',
                                            ];
                                        }
                                        if (empty($activitesStructure)) {
                                            $activitesStructure[] = [
                                                'nom' => 'Adhésion',
                                                'tarif' => number_format((float) $montantFinal, 2, ',', ' ') . ' €',
                                            ];
                                        }
                                    @endphp
                                    <button
                                        @click="ouvrirModal({{ json_encode([
                                            'actionUrl' => '/structures/' . $structure->id . '/valider',
                                            'isStructure' => true,
                                            'id' => $structure->id,
                                            'nom' => $structure->nom,
                                            'initiales' => $structure->sigle ?: mb_substr($structure->nom, 0, 2),
                                            'couleur' => '#222A60',
                                            'meta' => 'Structure · Inscrite le ' . ($structure->inscription?->date_inscription?->isoFormat('D MMM YYYY') ?? ''),
                                            'source' => $modalSource,
                                            'sourceClass' => match ($modalSource) {
                                                'HelloAsso' => 'bg-[#16987C]/10 text-[#16987C]',
                                                'Virement' => 'bg-blue-50 text-blue-600',
                                                'Chèque' => 'bg-amber-50 text-amber-600',
                                                'Espèces' => 'bg-orange-50 text-orange-600',
                                                'Pass Culture' => 'bg-purple-50 text-purple-600',
                                                default => 'bg-gray-100 text-gray-600',
                                            },
                                            'montant' => number_format((float) $montantFinal, 2, ',', ' ') . ' €',
                                            'activites' => $activitesStructure,
                                        ]) }})"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#16987C] hover:bg-[#138a6f] text-white rounded-lg text-xs font-bold transition-all duration-150 shadow-sm">
                                        Valider <svg class="w-3 h-3 opacity-80" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M9 5l7 7-7 7" />
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
                        <th class="px-6 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            Adhérent</th>
                        <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            Âge</th>
                        <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            Activités</th>
                        @if ($tab === 'attente' || $tab === 'partiel')
                            <th
                                class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                Source paiement</th>
                            <th
                                class="px-4 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                {{ $tab === 'partiel' ? 'Versé / Total' : 'Montant' }}</th>
                            <th
                                class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                Inscrit le</th>
                        @endif
                        <th
                            class="px-6 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($items as $adherent)
                        <tr class="group hover:bg-gray-50/70 transition-colors duration-100">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-black shrink-0"
                                        style="background-color: {{ $adherent->couleur_avatar }}">
                                        {{ $adherent->initiales }}</div>
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
                                        class="inline-flex px-2.5 py-1 rounded-lg text-xs font-bold {{ $tranche['class'] }}">{{ $tranche['label'] }}</span>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse ($adherent->activitesActives as $activite)
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-600 rounded-lg text-xs font-semibold">{{ $activite->nom }}</span>
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
                                            'HelloAsso' => 'bg-[#16987C]/10 text-[#16987C]',
                                            'Interne' => 'bg-blue-50 text-blue-600',
                                            'Pass Culture' => 'bg-purple-50 text-purple-600',
                                            default => 'bg-gray-100 text-gray-600',
                                        };
                                    @endphp
                                    <span
                                        class="inline-flex px-2.5 py-1 rounded-lg text-xs font-bold {{ $sourceStyle }}">{{ $source }}</span>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    @if ($tab === 'partiel')
                                        @php
                                            $versePartiel = (float) $adherent->paiements->sum('montant');
                                            $totalPartiel = (float) ($adherent->inscription?->montant ?? 0);
                                            $restePartiel = max(0, $totalPartiel - $versePartiel);
                                            $pctPartiel =
                                                $totalPartiel > 0
                                                    ? min(100, round(($versePartiel / $totalPartiel) * 100))
                                                    : 0;
                                        @endphp
                                        <div class="flex flex-col items-end gap-1">
                                            <div><span
                                                    class="font-black text-sm text-emerald-600">{{ number_format($versePartiel, 2, ',', ' ') }}
                                                    €</span><span class="text-xs text-gray-400"> /
                                                    {{ number_format($totalPartiel, 2, ',', ' ') }} €</span></div>
                                            <div class="w-20 bg-gray-100 rounded-full h-1">
                                                <div class="bg-[#16987C] h-1 rounded-full"
                                                    style="width: {{ $pctPartiel }}%"></div>
                                            </div>
                                            <span class="text-[10px] text-amber-500 font-bold">reste
                                                {{ number_format($restePartiel, 2, ',', ' ') }} €</span>
                                        </div>
                                    @else
                                        @php
                                            $isReinscription = $adherent->inscriptions
                                                ->where(
                                                    'saison',
                                                    $saison ??
                                                        (now()->month >= 9 ? now()->year : now()->year - 1) .
                                                            '-' .
                                                            ((now()->month >= 9 ? now()->year : now()->year - 1) + 1),
                                                )
                                                ->where('a_paye', \App\Models\Inscription::PAYE)
                                                ->isNotEmpty();
                                            $montantAffiche =
                                                $isReinscription && $adherent->inscription
                                                    ? $adherent->inscription->montant
                                                    : ($adherent->paiements->isNotEmpty()
                                                        ? $adherent->paiements->sum('montant')
                                                        : $adherent->inscription?->montant ?? 0);
                                        @endphp
                                        <span class="font-black text-sm text-[#0F143A]">
                                            {{ number_format((float) $montantAffiche, 2, ',', ' ') }} €
                                            @if ($isReinscription)
                                                <span class="text-xs text-amber-500">(ré-insc.)</span>
                                            @endif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4"><span
                                        class="text-sm text-gray-500">{{ $adherent->inscription?->date_inscription?->isoFormat('D MMM YYYY') ?? '—' }}</span>
                                </td>
                            @endif

                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($tab === 'attente' || $tab === 'partiel')
                                        @php
                                            $saison = \App\Models\Saison::current();

                                            $isReinscription = $adherent->inscriptions
                                                ->where('saison', $saison)
                                                ->where('a_paye', \App\Models\Inscription::PAYE)
                                                ->isNotEmpty();

                                            $modalSource = $adherent->paiements->first()?->source ?: 'Interne';
                                            $totalModal = (float) ($adherent->inscription?->montant ?? 0);
                                            $verseModal = (float) $adherent->paiements->sum('montant');
                                            $resteModal = max(0, $totalModal - $verseModal);

                                            $dateInscriptionEnAttente = $adherent->inscription?->created_at;
                                            $activitesModal =
                                                $isReinscription && $dateInscriptionEnAttente
                                                    ? $adherent->activitesActives
                                                        ->filter(
                                                            fn($a) => $a->pivot->created_at >=
                                                                $dateInscriptionEnAttente->startOfDay(),
                                                        )
                                                        ->map(
                                                            fn($a) => [
                                                                'nom' => $a->nom,
                                                                'info' => collect($a->horaires_list)->first() ?? '',
                                                                'tarif' =>
                                                                    number_format((float) $a->tarif, 2, ',', ' ') .
                                                                    ' €',
                                                            ],
                                                        )
                                                        ->values()
                                                        ->toArray()
                                                    : $adherent->activitesActives
                                                        ->map(
                                                            fn($a) => [
                                                                'nom' => $a->nom,
                                                                'info' => collect($a->horaires_list)->first() ?? '',
                                                                'tarif' =>
                                                                    number_format((float) $a->tarif, 2, ',', ' ') .
                                                                    ' €',
                                                            ],
                                                        )
                                                        ->values()
                                                        ->toArray();
                                        @endphp

                                        <button
                                            @click="ouvrirModal({{ json_encode([
                                                'actionUrl' => '/adherents/' . $adherent->id . '/valider',
                                                'versementUrl' => '/adherents/' . $adherent->id . '/versement',
                                                'isPartiel' => $tab === 'partiel',
                                                'isStructure' => false,
                                                'isReinscription' => $isReinscription,
                                                'id' => $adherent->id,
                                                'nom' => $adherent->prenom . ' ' . $adherent->nom,
                                                'initiales' => $adherent->initiales,
                                                'couleur' => $adherent->couleur_avatar,
                                                'meta' =>
                                                    ($adherent->tranche_age ?? 'Adulte') .
                                                    ($isReinscription ? ' · Ré-inscription' : ' · Inscrit le ') .
                                                    ($isReinscription ? '' : $adherent->inscription?->date_inscription?->isoFormat('D MMM YYYY') ?? ''),
                                                'source' => $modalSource,
                                                'sourceClass' => match ($modalSource) {
                                                    'HelloAsso' => 'bg-[#16987C]/10 text-[#16987C]',
                                                    'Interne' => 'bg-blue-50 text-blue-600',
                                                    'Pass Culture' => 'bg-purple-50 text-purple-600',
                                                    default => 'bg-gray-100 text-gray-600',
                                                },
                                                'montant' => number_format($totalModal, 2, ',', ' ') . ' €',
                                                'montantBrut' => $totalModal,
                                                'dejaVerse' => number_format($verseModal, 2, ',', ' ') . ' €',
                                                'dejaVerseBrut' => $verseModal,
                                                'resteDu' => number_format($resteModal, 2, ',', ' ') . ' €',
                                                'resteDuBrut' => $resteModal,
                                                'activites' => $activitesModal,
                                            ]) }})"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#16987C] hover:bg-[#138a6f] text-white rounded-lg text-xs font-bold transition-all duration-150 shadow-sm">
                                            {{ $isReinscription ? 'Valider (ré-inscription)' : 'Valider' }}
                                            <svg class="w-3 h-3 opacity-80" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2.5" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                    @else
                                        <a href="{{ route('adherents.show', $adherent) }}"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#222A60] hover:bg-[#1a2050] text-white rounded-lg text-xs font-bold transition-all duration-150 shadow-sm">
                                            Détails <svg class="w-3 h-3 opacity-60" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2.5" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ in_array($tab, ['attente', 'partiel']) ? 7 : 4 }}"
                                class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center">
                                        <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
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
                <p class="text-xs text-gray-400">Page <span
                        class="font-bold text-gray-600">{{ $items->currentPage() }}</span> sur <span
                        class="font-bold text-gray-600">{{ $items->lastPage() }}</span></p>
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
                            class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold transition-colors {{ $page === $items->currentPage() ? 'bg-[#222A60] text-white' : 'text-gray-500 hover:bg-gray-100' }}">{{ $page }}</a>
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
    @endif
</div>

@if ($filterType === 'tous')
    @if ($tab === 'payes' && $structuresPayees->isNotEmpty())
        <div class="mt-6 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <span class="text-lg">🏛️</span>
                <h3 class="font-bold text-gray-800 text-sm">Structures adhérentes</h3>
            </div>
        </div>
    @endif

    @if ($tab === 'attente' && $structuresEnAttente->isNotEmpty())
        <div class="mt-6 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <span class="text-lg">🏛️</span>
                <h3 class="font-bold text-gray-800 text-sm">Structures en attente de validation</h3>
            </div>
        </div>
    @endif
@endif
