<div class="p-5 md:p-6">
    @php $typeActivite = $formData['type_activite'] ?? ''; @endphp
    <div class="mb-5">
        <h2 class="text-xl font-bold text-gray-900">
            @if ($typeActivite === 'atelier')
                Choisissez votre atelier 🔧
            @elseif ($typeActivite === 'ressourcerie')
                Ressourcerie 📦
            @else
                Choisissez votre stage 🎭
            @endif
        </h2>
        <p class="text-gray-400 mt-1 text-sm">
            @if ($typeActivite === 'atelier')
                Sélectionnez le ou les ateliers auxquels vous souhaitez vous inscrire
            @elseif ($typeActivite === 'ressourcerie')
                Sélectionnez les équipements que vous souhaitez louer
            @else
                Sélectionnez le stage qui vous intéresse
            @endif
        </p>
    </div>

    <form action="{{ route('adhesion.next', $token) }}" method="POST">
        @csrf
        <input type="hidden" name="current_step" value="6">

        @php
            $liste = match ($typeActivite) {
                'stage' => $stages,
                'club_maker' => $clubMakerActivites,
                default => $ateliers,
            };

            $selectedActivites = $formData['activites_selectionnees'] ?? [];
            if (!is_array($selectedActivites)) {
                $selectedActivites = [];
            }

            $selectedRessourcerie = $formData['ressourcerie_selectionnees'] ?? [];
            if (!is_array($selectedRessourcerie)) {
                $selectedRessourcerie = [];
            }
        @endphp

        @if ($typeActivite === 'ressourcerie')

            @if ($ressourcerie->isEmpty())
                <div class="p-6 text-center bg-gray-50 rounded-xl border border-gray-200 mb-5">
                    <div class="text-4xl mb-2">😕</div>
                    <p class="text-gray-800 font-semibold">Aucun équipement disponible pour votre
                        profil.</p>
                    <p class="text-gray-400 text-sm mt-1">Contactez-nous pour plus d'informations.
                    </p>
                </div>
            @else
                @php
                    $labelsTarif = [
                        'tarif_particulier' => [
                            'label' => 'Tarif particulier',
                            'color' => 'bg-sky-50 text-sky-700 border-sky-200',
                        ],
                        'tarif_structure' => [
                            'label' => 'Tarif structure',
                            'color' => 'bg-violet-50 text-violet-700 border-violet-200',
                        ],
                        'tarif_scolaire' => [
                            'label' => 'Tarif scolaire',
                            'color' => 'bg-amber-50 text-amber-700 border-amber-200',
                        ],
                    ];
                    $groupes = $ressourcerie->groupBy('type_tarif');
                @endphp
                <div class="space-y-6 mb-6">
                    @foreach ($groupes as $typeTarif => $items)
                        @php $meta = $labelsTarif[$typeTarif] ?? ['label' => $typeTarif, 'color' => 'bg-gray-100 text-gray-600 border-gray-200']; @endphp
                        @if ($groupes->count() > 1)
                            <div class="flex items-center gap-2 mb-2">
                                <span
                                    class="text-xs font-bold px-2.5 py-1 rounded-full border {{ $meta['color'] }}">{{ $meta['label'] }}</span>
                                <div class="flex-1 h-px bg-gray-200"></div>
                            </div>
                        @endif
                        <div class="space-y-3">
                            @foreach ($items as $item)
                                <div x-data="{ checked: {{ in_array($item->id, $selectedRessourcerie) ? 'true' : 'false' }} }">
                                    <input type="checkbox" name="ressourcerie_selectionnees[]"
                                        value="{{ $item->id }}" x-model="checked" class="hidden">
                                    <div @click="checked = !checked"
                                        :class="checked
                                            ?
                                            'border-teal-600 bg-teal-50 ring-2 ring-teal-600/20' :
                                            'border-gray-200 hover:border-slate-400 bg-white'"
                                        class="border-2 rounded-xl p-4 transition-all cursor-pointer shadow-sm hover:shadow-md">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <h4 class="font-bold text-slate-900 text-sm leading-tight">
                                                        {{ $item->nom }}</h4>
                                                    <span
                                                        class="text-xs font-semibold px-2 py-0.5 rounded-full border {{ $meta['color'] }}">
                                                        {{ $meta['label'] }}
                                                    </span>
                                                </div>
                                                @if ($item->description)
                                                    <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                                                        {{ $item->description }}</p>
                                                @endif
                                                @if ($item->condition_location)
                                                    <div class="mt-2 flex items-start gap-1.5">
                                                        <span class="text-xs shrink-0 mt-0.5">📋</span>
                                                        <p class="text-xs text-gray-400 italic leading-relaxed">
                                                            {{ $item->condition_location }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex flex-col items-end gap-3 shrink-0">
                                                <p class="text-base font-black text-teal-600">
                                                    {{ $item->prix_format }}</p>
                                                <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-colors"
                                                    :class="checked ? 'border-teal-600 bg-teal-600' :
                                                        'border-gray-300'">
                                                    <svg x-show="checked" class="w-3 h-3 text-white" fill="currentColor"
                                                        viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endif
        @elseif ($classesEligibles)
            <div class="flex items-center gap-2 mb-4 px-1">
                <span class="text-base">🎓</span>
                <p class="text-xs text-gray-500">
                    Nous avons filtré les activités pour qu'elles correspondent au niveau :
                    <strong class="text-gray-700">{{ implode(', ', $classesEligibles) }}</strong>.
                </p>
            </div>
        @elseif (str_contains(strtolower($formData['occupation'] ?? ''), 'maison'))
            <div class="flex items-center gap-2 mb-4 px-1">
                <span class="text-base">🏠</span>
                <p class="text-xs text-gray-500">
                    École à la maison : <strong class="text-gray-700">toutes les activités</strong>
                    vous sont proposées.
                </p>
            </div>
        @endif

        @if ($typeActivite !== 'ressourcerie')
            @if ($liste->isEmpty())
                <div class="p-6 text-center bg-gray-50 rounded-xl border border-gray-200 mb-5">
                    <div class="text-4xl mb-2">😕</div>
                    <p class="text-gray-800 font-semibold">Aucune activité disponible pour votre
                        niveau.</p>
                    <p class="text-gray-400 text-sm mt-1">Contactez-nous pour plus d'informations.
                    </p>
                </div>
            @elseif ($typeActivite === 'atelier')
                @php
                    $ateliersParVille = $liste->groupBy('ville')->sortKeys();
                @endphp
                <div x-data="{ openVilles: [] }" class="space-y-3 mb-6">
                    @foreach ($ateliersParVille as $ville => $villeActivites)
                        @php
                            $villeKey = Str::slug($ville);
                            $hasSelected = $villeActivites->contains(fn($a) => in_array($a->id, $selectedActivites));
                        @endphp
                        <div
                            class="border rounded-xl overflow-hidden transition-all
                                            {{ $hasSelected ? 'border-teal-400' : 'border-gray-200' }}">

                            <button type="button"
                                @click="openVilles.includes('{{ $villeKey }}')
                                                    ? openVilles = openVilles.filter(v => v !== '{{ $villeKey }}')
                                                    : openVilles.push('{{ $villeKey }}')"
                                class="w-full flex items-center justify-between px-4 py-3 bg-white hover:bg-gray-50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="text-xl">📍</span>
                                    <span class="font-bold text-slate-900 text-base">{{ $ville }}</span>
                                    <span
                                        class="text-xs font-semibold text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">
                                        {{ $villeActivites->count() }}
                                        activité{{ $villeActivites->count() > 1 ? 's' : '' }}
                                    </span>
                                    @if ($hasSelected)
                                        <span
                                            class="text-xs font-bold text-teal-700 bg-teal-50 border border-teal-200 px-2 py-0.5 rounded-full">
                                            Sélectionnée
                                        </span>
                                    @endif
                                </div>
                                <svg class="w-5 h-5 text-gray-400 transition-transform duration-200"
                                    :class="openVilles.includes('{{ $villeKey }}') ? 'rotate-180' : ''"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="openVilles.includes('{{ $villeKey }}')"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="px-3 pb-3 pt-2 bg-gray-50/50 space-y-2 border-t border-gray-100">
                                @foreach ($villeActivites as $activite)
                                    @php
                                        $horaires = is_string($activite->horaires)
                                            ? json_decode($activite->horaires, true)
                                            : $activite->horaires ?? [];
                                        $isStage = isset($horaires['stage']);
                                    @endphp

                                    <div x-data="{ checked: {{ in_array($activite->id, $selectedActivites) ? 'true' : 'false' }} }">
                                        <input type="checkbox" name="activites_selectionnees[]"
                                            value="{{ $activite->id }}" x-model="checked" class="hidden">
                                        <div @click="checked = !checked"
                                            :class="checked
                                                ?
                                                'border-teal-600 bg-teal-50 ring-2 ring-teal-600/20' :
                                                'border-gray-200 hover:border-slate-400 bg-white'"
                                            class="border-2 rounded-xl p-4 transition-all flex items-center gap-4 cursor-pointer shadow-sm hover:shadow-md">
                                            <div class="flex-1 min-w-0">
                                                <h4 class="font-bold text-slate-900 text-sm leading-tight">
                                                    {{ $activite->nom }}</h4>
                                                <p
                                                    class="text-[11px] font-semibold text-indigo-600 bg-indigo-50 border border-indigo-100 px-2 py-0.5 rounded inline-block mt-1">
                                                    🎓 {{ implode(', ', (array) $activite->classes) }}
                                                </p>
                                                @if ($isStage)
                                                    <div class="flex flex-wrap gap-1.5 mt-2.5">
                                                        <span
                                                            class="inline-flex items-center gap-1 bg-white border border-gray-200 text-slate-600 font-medium text-[11px] px-2 py-0.5 rounded-md shadow-sm">
                                                            📅 Du
                                                            {{ \Carbon\Carbon::parse($horaires['stage']['date_debut'])->format('d/m') }}
                                                            au
                                                            {{ \Carbon\Carbon::parse($horaires['stage']['date_fin'])->format('d/m/Y') }}
                                                        </span>
                                                        <span
                                                            class="inline-flex items-center gap-1 bg-white border border-gray-200 text-slate-600 font-medium text-[11px] px-2 py-0.5 rounded-md shadow-sm">
                                                            🕐 {{ $horaires['stage']['heure_debut'] }} -
                                                            {{ $horaires['stage']['heure_fin'] }}
                                                        </span>
                                                    </div>
                                                @elseif ($horaires && count($horaires) > 0)
                                                    <div class="flex flex-wrap gap-1.5 mt-2.5">
                                                        @foreach ($horaires as $jour => $heure)
                                                            @if (is_string($heure))
                                                                <span
                                                                    class="inline-flex items-center gap-1 bg-white border border-gray-200 text-slate-600 font-medium text-[11px] px-2 py-0.5 rounded-md shadow-sm">
                                                                    🕐 {{ $jour }} ·
                                                                    {{ $heure }}
                                                                </span>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @endif

                                                @if ($activite->tarif !== null)
                                                    <p class="text-xs font-black text-teal-600 mt-1">
                                                        {{ $activite->tarif > 0 ? number_format($activite->tarif, 0, ',', ' ') . ' €' : 'Gratuit' }}
                                                    </p>
                                                @endif
                                            </div>
                                            <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors"
                                                :class="checked ? 'border-teal-600 bg-teal-600' :
                                                    'border-gray-300'">
                                                <svg x-show="checked" class="w-3 h-3 text-white" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @elseif ($typeActivite === 'club_maker')
                <div x-data="{
                    selected: @json($formData['activites_selectionnees'] ?? []),
                    max: 2,
                    toggle(id) {
                        const idx = this.selected.indexOf(id);
                        if (idx > -1) {
                            this.selected.splice(idx, 1);
                        } else if (this.selected.length < this.max) {
                            this.selected.push(id);
                        }
                    },
                    isSelected(id) { return this.selected.includes(id); }
                }" class="space-y-3 mb-6">
                    <div class="flex items-center gap-2 mb-3 px-1 p-3 bg-amber-50 border border-amber-200 rounded-xl">
                        <span class="text-base">⚠️</span>
                        <p class="text-xs text-amber-700 font-semibold">
                            Vous pouvez choisir <strong>au maximum 2 activités</strong> dans le
                            cadre du Club Maker.
                        </p>
                    </div>

                    <p class="text-xs text-gray-500 px-1"
                        x-text="`${selected.length} / 2 activité(s) sélectionnée(s)`"></p>

                    @foreach ($clubMakerActivites as $activite)
                        @php
                            $horaires = is_string($activite->horaires)
                                ? json_decode($activite->horaires, true)
                                : $activite->horaires ?? [];
                            $isStage = isset($horaires['stage']);
                        @endphp
                        <template x-if="isSelected({{ $activite->id }})">
                            <input type="hidden" name="activites_selectionnees[]" value="{{ $activite->id }}">
                        </template>
                        <div @click="toggle({{ $activite->id }})"
                            :class="isSelected({{ $activite->id }}) ?
                                'border-teal-600 bg-teal-50 ring-2 ring-teal-600/20' :
                                (selected.length >= max && !isSelected({{ $activite->id }})) ?
                                'border-gray-200 bg-gray-100 opacity-50 cursor-not-allowed' :
                                'border-gray-200 hover:border-slate-400 bg-white cursor-pointer'"
                            class="border-2 rounded-xl p-4 transition-all flex items-start gap-4 shadow-sm">

                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-slate-900 text-sm leading-tight">
                                    {{ $activite->nom }}
                                </h4>
                                <p
                                    class="text-[11px] font-semibold text-indigo-600 bg-indigo-50 border border-indigo-100 px-2 py-0.5 rounded inline-block mt-1">
                                    🎓 {{ implode(', ', (array) $activite->classes) }}
                                </p>
                                @if ($activite->adresse)
                                    <p class="text-xs text-gray-500 font-medium mt-1.5 flex items-start gap-1">
                                        <span class="text-xs shrink-0 mt-0.5">📍</span>
                                        <span>{{ $activite->adresse }},
                                            {{ $activite->ville }}</span>
                                    </p>
                                @endif

                                @if ($isStage)
                                    <div class="flex flex-wrap gap-1.5 mt-2.5">
                                        <span
                                            class="inline-flex items-center gap-1 bg-white border border-gray-200 text-slate-600 font-medium text-[11px] px-2 py-0.5 rounded-md shadow-sm">
                                            📅 Du
                                            {{ \Carbon\Carbon::parse($horaires['stage']['date_debut'])->format('d/m') }}
                                            au
                                            {{ \Carbon\Carbon::parse($horaires['stage']['date_fin'])->format('d/m/Y') }}
                                        </span>
                                        <span
                                            class="inline-flex items-center gap-1 bg-white border border-gray-200 text-slate-600 font-medium text-[11px] px-2 py-0.5 rounded-md shadow-sm">
                                            🕐 {{ $horaires['stage']['heure_debut'] }} -
                                            {{ $horaires['stage']['heure_fin'] }}
                                        </span>
                                    </div>
                                @elseif ($horaires && count($horaires) > 0)
                                    <div class="flex flex-wrap gap-1.5 mt-2.5">
                                        @foreach ($horaires as $jour => $heure)
                                            @if (is_string($heure))
                                                <span
                                                    class="inline-flex items-center gap-1 bg-white border border-gray-200 text-slate-600 font-medium text-[11px] px-2 py-0.5 rounded-md shadow-sm">
                                                    <span>🕐</span> {{ $jour }} {{ $heure }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif

                                @if ($activite->tarif !== null)
                                    <p class="text-xs font-black text-teal-600 mt-2.5">
                                        {{ $activite->tarif > 0 ? number_format($activite->tarif, 0, ',', ' ') . ' €' : 'Gratuit' }}
                                    </p>
                                @endif
                            </div>

                            <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors mt-0.5"
                                :class="isSelected({{ $activite->id }}) ? 'border-teal-600 bg-teal-600' :
                                    'border-gray-300'">
                                <svg x-show="isSelected({{ $activite->id }})" class="w-3 h-3 text-white"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="space-y-4 mb-6">
                    @foreach ($liste as $activite)
                        @php
                            $horaires = is_string($activite->horaires)
                                ? json_decode($activite->horaires, true)
                                : $activite->horaires ?? [];
                            $isStage = isset($horaires['stage']);
                        @endphp

                        <div x-data="{ checked: {{ in_array($activite->id, $selectedActivites) ? 'true' : 'false' }} }">
                            <input type="checkbox" name="activites_selectionnees[]" value="{{ $activite->id }}"
                                x-model="checked" class="hidden">
                            <div :class="checked ? 'border-teal-600 bg-teal-50 ring-2 ring-teal-600/20' :
                                'border-gray-200 hover:border-slate-900 bg-white'"
                                class="border-2 rounded-2xl p-5 transition-all flex items-start gap-4 shadow-sm hover:shadow-md cursor-pointer"
                                @click="checked = !checked">
                                <div
                                    class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 text-2xl bg-slate-900 text-white shadow-sm">
                                    🎭</div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-slate-900 text-lg">
                                        {{ $activite->nom }}</h4>
                                    <p
                                        class="text-xs font-semibold text-indigo-600 bg-indigo-50 border border-indigo-100 px-2 py-0.5 rounded inline-block mt-1.5">
                                        🎓 {{ implode(', ', (array) $activite->classes) }}
                                    </p>
                                    @if ($activite->adresse)
                                        <p class="text-sm text-gray-500 font-medium mt-1">📍
                                            {{ $activite->adresse }}, {{ $activite->ville }}</p>
                                    @endif

                                    @if ($isStage)
                                        <div class="flex flex-wrap gap-1.5 mt-2.5">
                                            <span
                                                class="inline-flex items-center gap-1 bg-white border border-gray-200 text-slate-600 font-medium text-[11px] px-2 py-0.5 rounded-md shadow-sm">
                                                📅 Du
                                                {{ \Carbon\Carbon::parse($horaires['stage']['date_debut'])->format('d/m') }}
                                                au
                                                {{ \Carbon\Carbon::parse($horaires['stage']['date_fin'])->format('d/m/Y') }}
                                            </span>
                                            <span
                                                class="inline-flex items-center gap-1 bg-white border border-gray-200 text-slate-600 font-medium text-[11px] px-2 py-0.5 rounded-md shadow-sm">
                                                🕐 {{ $horaires['stage']['heure_debut'] }} -
                                                {{ $horaires['stage']['heure_fin'] }}
                                            </span>
                                        </div>
                                    @elseif ($horaires && count($horaires) > 0)
                                        <div class="flex flex-wrap gap-2 mt-3">
                                            @foreach ($horaires as $jour => $heure)
                                                @if (is_string($heure))
                                                    <span
                                                        class="inline-block bg-white border border-gray-200 text-slate-700 font-semibold text-xs px-2.5 py-1 rounded-lg shadow-sm">
                                                        🕐 {{ $jour }} {{ $heure }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif

                                    @if ($activite->tarif !== null)
                                        <p class="text-sm font-black text-teal-600 mt-3">
                                            {{ $activite->tarif > 0 ? number_format($activite->tarif, 0, ',', ' ') . ' €' : 'Gratuit' }}
                                        </p>
                                    @endif
                                </div>
                                <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors mt-1"
                                    :class="checked ? 'border-teal-600 bg-teal-600' : 'border-gray-300'">
                                    <svg x-show="checked" class="w-3 h-3 text-white" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
        <div class="flex items-center justify-between pt-4 border-t border-gray-100 mt-1">
            @if ($hasPrev)
                <a href="{{ route('adhesion.show', ['token' => $token, 'step' => $prevStep]) }}"
                    class="{{ $btnBack }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M15 19l-7-7 7-7" />
                    </svg>
                    Précédent
                </a>
            @else
                <div></div>
            @endif
            <button type="submit" class="{{ $btn }}">
                Suivant
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
    </form>
</div>
