<div class="p-5 md:p-6">
    <div class="mb-5">
        <h2 class="text-xl font-bold text-gray-900">Pour quelle activité ? 🎯</h2>
        <p class="text-gray-400 mt-1 text-sm">Choisissez le type d'inscription souhaité</p>
    </div>

    <form action="{{ route('adhesion.next', $token) }}" method="POST">
        @csrf
        <input type="hidden" name="current_step" value="2">

        <div x-data="{ activite: '{{ $formData['type_activite'] ?? '' }}' }">
            @php
                $statutJuridique = $formData['statut_juridique'] ?? 'personne_physique';
                $isStructureStep2 = in_array($statutJuridique, ['tpe_asso', 'esr_pme']);
                $isDejaAdherent = ($formData['is_adherent'] ?? 'non') === 'oui';

                $saisonCible = $formData['_saison_cible'] ?? 'actuelle';

                $options = [];

                if ($isStructureStep2) {
                    if ($saisonCible === 'suivante') {
                        $options[] = [
                            'value' => 'ressourcerie',
                            'label' => 'Ressourcerie',
                            'icon' => '🤖',
                            'desc' => 'Louer du matériel pédagogique robotique',
                        ];
                    }
                    $options[] = [
                        'value' => 'recherche',
                        'label' => 'Programme de recherche',
                        'icon' => '🔬',
                        'desc' =>
                            'Participer à un programme de recherche participative' .
                            ($isDejaAdherent ? ' (gratuit)' : ''),
                    ];
                    if (!$isDejaAdherent) {
                        $options[] = [
                            'value' => 'soutien',
                            'label' => 'Adhésion par soutien',
                            'icon' => '🤝',
                            'desc' => 'Soutenir financièrement l\'association',
                        ];
                    }
                } else {
                    if ($saisonCible === 'actuelle' && in_array(now()->month, [7, 8])) {
                        $options[] = [
                            'value' => 'stage',
                            'label' => 'Inscription à un stage d\'été',
                            'icon' => '🏕️',
                            'desc' => 'Stages d\'été (Saison ' . App\Models\Saison::current() . ')',
                        ];
                    } else {
                        $options[] = [
                            'value' => 'atelier',
                            'label' => 'Inscription à un atelier',
                            'icon' => '🔧',
                            'desc' => 'Ateliers robotiques',
                        ];
                        $options[] = [
                            'value' => 'recherche',
                            'label' => 'Recherche participative',
                            'icon' => '🔬',
                            'desc' => 'Participer à un programme' . ($isDejaAdherent ? ' (gratuit)' : ''),
                        ];

                        if (!$isDejaAdherent) {
                            $options[] = [
                                'value' => 'soutien',
                                'label' => 'Inscription par soutien',
                                'icon' => '🤝',
                                'desc' => 'Soutenir financièrement l\'association',
                            ];
                        }

                        $options[] = [
                            'value' => 'ressourcerie',
                            'label' => 'Ressourcerie',
                            'icon' => '🤖',
                            'desc' => 'Louer un Codey Rocky',
                        ];

                        if (!in_array(now()->month, [7, 8])) {
                            $options[] = [
                                'value' => 'stage',
                                'label' => 'Inscription à un stage',
                                'icon' => '📚',
                                'desc' => 'Stages sur plusieurs jours',
                            ];
                        }

                        if (!$isDejaAdherent) {
                            $options[] = [
                                'value' => 'club_maker',
                                'label' => 'Club Maker',
                                'icon' => '⚙️',
                                'desc' => 'Rejoindre le club des makers',
                            ];
                        }
                    }
                }
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                @foreach ($options as $opt)
                    <label class="cursor-pointer block group">
                        <input type="radio" name="type_activite" value="{{ $opt['value'] }}" x-model="activite"
                            class="sr-only">
                        <div :class="activite === '{{ $opt['value'] }}' ?
                            'border-teal-600 bg-teal-50 ring-2 ring-teal-600/20' :
                            'border-gray-200 group-hover:border-teal-400'"
                            class="{{ $card }}">
                            <h3 class="font-bold text-slate-900 text-base">{{ $opt['label'] }}</h3>
                            <p class="text-gray-500 text-xs mt-1.5 flex-1 leading-relaxed">
                                {{ $opt['desc'] }}</p>
                            <div class="mt-3 w-5 h-5 rounded-full border-2 flex items-center justify-center self-end transition-colors"
                                :class="activite === '{{ $opt['value'] }}' ?
                                    'border-teal-600 bg-teal-600' : 'border-gray-300'">
                                <svg x-show="activite === '{{ $opt['value'] }}'" class="w-2.5 h-2.5 text-white"
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
                <button type="submit" class="{{ $btn }}" :disabled="!activite"
                    :class="!activite ? 'opacity-50 cursor-not-allowed grayscale' : ''">
                    Suivant
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </form>
</div>
