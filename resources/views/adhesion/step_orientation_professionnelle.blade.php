                    <div class="p-5 md:p-6">
                        <div class="mb-5">
                            <h2 class="text-xl font-bold text-gray-900">Orientation professionnelle 🎓</h2>
                            <p class="text-gray-400 mt-1 text-sm">Ces informations nous aident à personnaliser ton parcours</p>
                        </div>

                        <form action="{{ route('adhesion.next', $token) }}" method="POST">
                            @csrf
                            <input type="hidden" name="current_step" value="15">

                            <div class="mb-5">
                                <label class="{{ $label }}">As-tu déjà une idée de métier que tu aimerais exercer ?</label>
                                <textarea name="idee_metier" rows="4" required
                                    placeholder="Décris librement tes idées ou aspirations professionnelles..."
                                    class="{{ $field }}">{{ $formData['idee_metier'] ?? '' }}</textarea>
                            </div>

                            <div class="mb-5">
                                <label class="{{ $label }}">Aimerais-tu que ce que tu vas apprendre avec nous te permette de découvrir un métier ou une formation ?</label>
                                <textarea name="decouverte_metier" rows="4" required
                                    placeholder="Dis-nous ce que tu espères découvrir ou apprendre..."
                                    class="{{ $field }}">{{ $formData['decouverte_metier'] ?? '' }}</textarea>
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
