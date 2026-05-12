<div class="p-5 md:p-6">
    <div class="mb-5">
        <h2 class="text-xl font-bold text-gray-900">Pour quelle activité ? 🎯</h2>
        <p class="text-gray-400 mt-1 text-sm">Choisissez un ou plusieurs types d'inscription</p>
    </div>

    <form action="{{ route('adhesion.next', $token) }}" method="POST">
        @csrf
        <input type="hidden" name="current_step" value="2">

        @php
            $currentTypes = $formData['types_activite'] ?? (($formData['type_activite'] ?? '') ? [$formData['type_activite']] : []);
            if (!is_array($currentTypes)) $currentTypes = [$currentTypes];

            $statutJuridique = $formData['statut_juridique'] ?? 'personne_physique';
            $isStructureStep2 = in_array($statutJuridique, ['tpe_asso', 'esr_pme']);
            $isDejaAdherent = ($formData['is_adherent'] ?? 'non') === 'oui';
            $saisonCible = $formData['_saison_cible'] ?? 'actuelle';

            $options = [];
            $singleOnly = []; // types that can't be combined with others

            if ($isStructureStep2) {
                if ($saisonCible === 'suivante') {
                    $options[] = ['value' => 'ressourcerie', 'label' => 'Ressourcerie', 'icon' => '🤖', 'desc' => 'Louer du matériel pédagogique robotique', 'combinable' => true];
                }
                $options[] = ['value' => 'recherche', 'label' => 'Programme de recherche', 'icon' => '🔬', 'desc' => 'Participer à un programme de recherche participative' . ($isDejaAdherent ? ' (gratuit)' : ''), 'combinable' => true];
                if (!$isDejaAdherent) {
                    $options[] = ['value' => 'soutien', 'label' => 'Adhésion par soutien', 'icon' => '🤝', 'desc' => 'Soutenir financièrement l\'association', 'combinable' => false];
                }
            } else {
                if ($saisonCible === 'actuelle' && in_array(now()->month, [7, 8])) {
                    $options[] = ['value' => 'stage', 'label' => 'Inscription à un stage d\'été', 'icon' => '🏕️', 'desc' => 'Stages d\'été (Saison ' . App\Models\Saison::current() . ')', 'combinable' => false];
                } else {
                    $options[] = ['value' => 'atelier', 'label' => 'Inscription à un atelier', 'icon' => '🔧', 'desc' => 'Ateliers robotiques', 'combinable' => true];
                    $options[] = ['value' => 'recherche', 'label' => 'Recherche participative', 'icon' => '🔬', 'desc' => 'Participer à un programme' . ($isDejaAdherent ? ' (gratuit)' : ''), 'combinable' => true];
                    if (!$isDejaAdherent) {
                        $options[] = ['value' => 'soutien', 'label' => 'Inscription par soutien', 'icon' => '🤝', 'desc' => 'Soutenir financièrement l\'association', 'combinable' => false];
                    }
                    $options[] = ['value' => 'ressourcerie', 'label' => 'Ressourcerie', 'icon' => '🤖', 'desc' => 'Louer un Codey Rocky', 'combinable' => true];
                    if (!in_array(now()->month, [7, 8])) {
                        $options[] = ['value' => 'stage', 'label' => 'Inscription à un stage', 'icon' => '📚', 'desc' => 'Stages sur plusieurs jours', 'combinable' => false];
                    }
                    if (!$isDejaAdherent) {
                        $options[] = ['value' => 'club_maker', 'label' => 'Club Maker', 'icon' => '⚙️', 'desc' => 'Rejoindre le club des makers', 'combinable' => true];
                    }
                }
            }

            $nonCombinableValues = collect($options)->where('combinable', false)->pluck('value')->toArray();
            $combinableValues = collect($options)->where('combinable', true)->pluck('value')->toArray();
        @endphp

        <script>
            function activiteStep2Data() {
                return {
                    activites: @json($currentTypes),
                    nonCombinable: @json($nonCombinableValues),
                    toggle(val) {
                        const idx = this.activites.indexOf(val);
                        if (idx > -1) {
                            this.activites.splice(idx, 1);
                        } else {
                            if (this.nonCombinable.includes(val)) {
                                this.activites = [val];
                            } else {
                                this.activites = this.activites.filter(v => !this.nonCombinable.includes(v));
                                this.activites.push(val);
                            }
                        }
                    },
                    isSelected(val) { return this.activites.includes(val); }
                };
            }
        </script>
        <div x-data="activiteStep2Data()">

            @if (count(collect($options)->where('combinable', true)) > 1)
                <div class="flex items-center gap-2 mb-4 p-3 bg-teal-50 border border-teal-100 rounded-xl">
                    <span class="text-base shrink-0">💡</span>
                    <p class="text-xs text-teal-700 font-medium">Vous pouvez sélectionner <strong>plusieurs types</strong> d'inscription en même temps.</p>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                @foreach ($options as $opt)
                    <label class="cursor-pointer block group" @click.prevent="toggle('{{ $opt['value'] }}')">
                        <input type="checkbox" name="types_activite[]" value="{{ $opt['value'] }}"
                            :checked="isSelected('{{ $opt['value'] }}')"
                            class="sr-only">
                        <div :class="isSelected('{{ $opt['value'] }}') ?
                            'border-teal-600 bg-teal-50 ring-2 ring-teal-600/20' :
                            'border-gray-200 group-hover:border-teal-400'"
                            class="{{ $card }}">
                            <h3 class="font-bold text-slate-900 text-base">{{ $opt['label'] }}</h3>
                            <p class="text-gray-500 text-xs mt-1.5 flex-1 leading-relaxed">{{ $opt['desc'] }}</p>
                            @if (!$opt['combinable'])
                                <p class="text-[10px] text-gray-400 mt-1 italic">Ne peut pas être combiné</p>
                            @endif
                            <div class="mt-3 w-5 h-5 rounded-md border-2 flex items-center justify-center self-end transition-colors"
                                :class="isSelected('{{ $opt['value'] }}') ?
                                    'border-teal-600 bg-teal-600' : 'border-gray-300'">
                                <svg x-show="isSelected('{{ $opt['value'] }}')" class="w-2.5 h-2.5 text-white"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>

            <div x-show="activites.length > 1" x-transition class="mb-4 p-3 bg-gray-50 border border-gray-100 rounded-xl">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Sélection actuelle</p>
                <div class="flex flex-wrap gap-1.5 mt-1">
                    @foreach ($options as $opt)
                        <template x-if="isSelected('{{ $opt['value'] }}')">
                            <span class="text-xs font-semibold px-2 py-0.5 bg-teal-100 text-teal-700 rounded-full">{{ $opt['icon'] }} {{ $opt['label'] }}</span>
                        </template>
                    @endforeach
                </div>
            </div>

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
                <button type="submit" class="{{ $btn }}" :disabled="activites.length === 0"
                    :class="activites.length === 0 ? 'opacity-50 cursor-not-allowed grayscale' : ''">
                    Suivant
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </form>
</div>
