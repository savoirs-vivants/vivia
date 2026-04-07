                    <div class="p-5 md:p-6">
                        <div class="mb-5">
                            <h2 class="text-xl font-bold text-gray-900">Implication bénévole 🤝</h2>
                            <p class="text-gray-400 mt-1 text-sm">Souhaitez-vous vous investir dans l'association ? (facultatif)
                            </p>
                        </div>

                        <form action="{{ route('adhesion.next', $token) }}" method="POST">
                            @csrf
                            <input type="hidden" name="current_step" value="7">

                            <div class="mb-8">
                                <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-3">
                                    <span
                                        class="w-7 h-7 rounded-lg bg-teal-600 text-white text-xs flex items-center justify-center font-bold">1</span>
                                    Actions bénévoles
                                </h3>
                                @php
                                    $benevoleOptions = [
                                        'Accompagnement atelier robotique' => '🤖',
                                        'Administration (ex : mailing, secrétariat…)' => '📬',
                                        'Animations régulières (programme de recherche)' => '🔄',
                                        'Animations ponctuelles (fête de la science...)' => '🎉',
                                    ];
                                    $actionsSelectionnees = $formData['actions_benevoles'] ?? [];
                                    if (!is_array($actionsSelectionnees)) {
                                        $actionsSelectionnees = [];
                                    }
                                @endphp
                                <div class="space-y-3">
                                    @foreach ($benevoleOptions as $option => $icon)
                                        <label
                                            class="flex items-center gap-3 p-3.5 border border-gray-200 rounded-lg cursor-pointer hover:border-teal-400 hover:bg-teal-50/60 transition-all group">
                                            <input type="checkbox" name="actions_benevoles[]"
                                                value="{{ $option }}"
                                                {{ in_array($option, $actionsSelectionnees) ? 'checked' : '' }}
                                                class="{{ $check }}">
                                            <span class="text-sm font-semibold text-slate-700 flex items-center gap-3">
                                                <span class="text-2xl">{{ $icon }}</span>
                                                {{ $option }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 mb-5">
                                <h3 class="font-bold text-slate-900 mb-3 flex items-center gap-3">
                                    <span
                                        class="w-7 h-7 rounded-lg bg-teal-600 text-white text-xs flex items-center justify-center font-bold">2</span>
                                    Participation aux manifestations
                                </h3>
                                <p class="text-sm text-gray-600 mb-5 font-medium leading-relaxed">
                                    Chaque année, Savoirs Vivants organise plusieurs manifestations. Ces manfiestations
                                    rassemblent les activités proposées dans l'année. Pour que celles-ci soient réussies,
                                    nous avons besoin qu'un grand nombre d'entre nous y prenne part (plusieurs rencontres
                                    seront nécessaires pendant et après les manifestations)
                                </p>
                                <div class="flex flex-col sm:flex-row gap-4">
                                    <label
                                        class="flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-lg cursor-pointer hover:border-teal-400 transition-all flex-1">
                                        <input type="radio" name="participation_manif" value="1"
                                            {{ ($formData['participation_manif'] ?? '') === '1' ? 'checked' : '' }}
                                            class="{{ $radio }}">
                                        <span class="text-sm font-bold text-slate-900">✅ Oui, intéressé·e</span>
                                    </label>
                                    <label
                                        class="flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-lg cursor-pointer hover:border-gray-400 transition-all flex-1">
                                        <input type="radio" required name="participation_manif" value="0"
                                            {{ ($formData['participation_manif'] ?? '') === '0' ? 'checked' : '' }}
                                            class="{{ $radio }}">
                                        <span class="text-sm font-bold text-slate-900">❌ Non, merci</span>
                                    </label>
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
                                <button type="submit" class="{{ $btn }}" :disabled="!participation_manif" :class="!participation_manif ? 'opacity-50 cursor-not-allowed grayscale' : ''">
                                    Suivant
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>
