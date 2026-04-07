                    <div class="p-5 md:p-6">
                        <div class="mb-5">
                            <h2 class="text-xl font-bold text-gray-900">Votre profil 🏢</h2>
                            <p class="text-gray-400 mt-1 text-sm">Quel est votre statut juridique ?</p>
                        </div>

                        <form action="{{ route('adhesion.next', $token) }}" method="POST">
                            @csrf
                            <input type="hidden" name="current_step" value="12">

                            <div x-data="{ statut_juridique: '{{ $formData['statut_juridique'] ?? '' }}' }">
                                <div class="grid grid-cols-1 gap-4 mb-6">

                                    @php
                                        $statutsJuridiques = [
                                            [
                                                'value' => 'personne_physique',
                                                'label' => 'Personne physique',
                                                'icon'  => '👤',
                                                'desc'  => 'Particulier s\'inscrivant à titre personnel',
                                            ],
                                            [
                                                'value' => 'tpe_asso',
                                                'label' => 'TPE / Association',
                                                'icon'  => '🤝',
                                                'desc'  => 'Très petite entreprise ou association',
                                            ],
                                            [
                                                'value' => 'esr_pme',
                                                'label' => 'ESR / PME',
                                                'icon'  => '🏭',
                                                'desc'  => 'Établissement de Recherche Supérieur ou PME',
                                            ],
                                        ];
                                    @endphp

                                    @foreach ($statutsJuridiques as $sj)
                                        <label class="cursor-pointer block group">
                                            <input type="radio" name="statut_juridique"
                                                value="{{ $sj['value'] }}"
                                                x-model="statut_juridique" class="sr-only">
                                            <div :class="statut_juridique === '{{ $sj['value'] }}' ?
                                                    'border-teal-600 bg-teal-50 ring-2 ring-teal-600/20' :
                                                    'border-gray-200 group-hover:border-teal-400'"
                                                class="border-2 rounded-xl p-4 flex items-center gap-4 transition-all bg-white">
                                                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-xl shrink-0">
                                                    {{ $sj['icon'] }}
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h3 class="font-bold text-slate-900 text-base">{{ $sj['label'] }}</h3>
                                                    <p class="text-gray-500 text-sm mt-0.5">{{ $sj['desc'] }}</p>
                                                </div>
                                                <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors"
                                                    :class="statut_juridique === '{{ $sj['value'] }}' ?
                                                        'border-teal-600 bg-teal-600' : 'border-gray-300'">
                                                    <svg x-show="statut_juridique === '{{ $sj['value'] }}'"
                                                        class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
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
                                    <button type="submit" class="{{ $btn }}" :disabled="!statut_juridique" :class="!statut_juridique ? 'opacity-50 cursor-not-allowed grayscale' : ''">
                                        Suivant
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
