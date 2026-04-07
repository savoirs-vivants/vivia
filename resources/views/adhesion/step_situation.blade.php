                    <div class="p-5 md:p-6">
                        <div class="mb-5">
                            <h2 class="text-xl font-bold text-gray-900">Situation actuelle 💼</h2>
                            <p class="text-gray-400 mt-1 text-sm">Indiquez votre niveau scolaire ou situation professionnelle</p>
                        </div>

                        <form action="{{ route('adhesion.next', $token) }}" method="POST">
                            @csrf
                            <input type="hidden" name="current_step" value="5">

                            @if ($isMineur)
                                <div x-data="{ occupation: '{{ $formData['occupation'] ?? '' }}' }">
                                    <p class="text-sm font-bold text-slate-700 mb-3">📚 Niveau scolaire</p>
                                    <div class="grid grid-cols-2 gap-3 mb-5">
                                        @foreach ([['val' => 'Maternelle', 'desc' => '3 à 5 ans'], ['val' => 'Primaire', 'desc' => '6 à 10 ans'], ['val' => 'Collège', 'desc' => '11 à 14 ans'], ['val' => 'Lycée', 'desc' => '15 à 17 ans'], ['val' => 'École à la maison', 'desc' => 'Tout âge']] as $o)
                                            <label class="cursor-pointer block group">
                                                <input type="radio" name="occupation" value="{{ $o['val'] }}"
                                                    required x-model="occupation" class="sr-only">
                                                <div :class="occupation === '{{ $o['val'] }}' ?
                                                    'border-teal-600 bg-teal-50 ring-2 ring-teal-600/20' :
                                                    'border-gray-200 group-hover:border-slate-900'"
                                                    class="border-2 rounded-xl p-3 text-center transition-all">
                                                    <div class="font-bold text-sm text-slate-900">{{ $o['val'] }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 font-medium mt-1">
                                                        {{ $o['desc'] }}</div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>

                                    <div x-show="occupation !== ''" x-transition class="mb-5">
                                        <label class="{{ $label }}">🏫 Établissement scolaire</label>
                                        <input type="text" name="etablissement" required
                                            value="{{ $formData['etablissement'] ?? '' }}"
                                            placeholder="Nom de l'école / collège / lycée" class="{{ $field }}">
                                    </div>
                                </div>
                            @else
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6">
                                    @php
                                        $adulteOptions = [
                                            ['val' => 'Étudiant', 'icon' => '🎓'],
                                            ['val' => 'Sans emploi', 'icon' => '🔍'],
                                            ['val' => 'Chômeur', 'icon' => '📋'],
                                            ['val' => 'Retraité', 'icon' => '🌅'],
                                            ['val' => 'Fonctionnaire', 'icon' => '🏛️'],
                                            ['val' => 'Chef d\'entreprise', 'icon' => '🏢'],
                                            ['val' => 'Salarié', 'icon' => '💼'],
                                            ['val' => 'Cadre', 'icon' => '📊'],
                                            ['val' => 'Employé', 'icon' => '🖥️'],
                                            ['val' => 'Ouvrier', 'icon' => '🔨'],
                                            ['val' => 'Profession libérale', 'icon' => '⚖️'],
                                        ];
                                    @endphp
                                    @foreach ($adulteOptions as $o)
                                        <label class="cursor-pointer block group">
                                            <input type="radio" required name="occupation" value="{{ $o['val'] }}"
                                                {{ ($formData['occupation'] ?? '') === $o['val'] ? 'checked' : '' }}
                                                class="sr-only peer">
                                            <div
                                                class="border-2 rounded-lg p-3 text-center transition-all peer-checked:border-teal-500 peer-checked:bg-teal-50 peer-checked:ring-2 ring-teal-500/20 border-gray-200 group-hover:border-teal-400">
                                                <div class="text-2xl mb-1">{{ $o['icon'] }}</div>
                                                <div class="font-bold text-xs text-slate-900">{{ $o['val'] }}</div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>
