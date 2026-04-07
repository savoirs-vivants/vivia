                    <div class="p-5 md:p-6">
                        <div class="mb-5">
                            <h2 class="text-xl font-bold text-gray-900">Autorisations & communication 📜</h2>
                            <p class="text-gray-400 mt-1 text-sm">Gérez vos préférences de contact et autorisations</p>
                        </div>

                        <form action="{{ route('adhesion.next', $token) }}" method="POST">
                            @csrf
                            <input type="hidden" name="current_step" value="14">

                            <div class="space-y-3 mb-6">
                                <label class="flex items-start gap-3 p-4 border-2 rounded-xl cursor-pointer transition-all
                                    {{ ($formData['bulletin'] ?? false) ? 'border-teal-500 bg-teal-50' : 'border-gray-200 hover:border-teal-300' }}">
                                    <input type="checkbox" name="bulletin" value="1"
                                        {{ ($formData['bulletin'] ?? false) ? 'checked' : '' }}
                                        class="{{ $check }} mt-0.5">
                                    <div>
                                        <span class="font-bold text-gray-800 text-sm">📰 Bulletin d'information</span>
                                        <p class="text-gray-500 text-xs mt-0.5">Je souhaite recevoir le bulletin de l'association</p>
                                    </div>
                                </label>
                            </div>

                            <div class="p-4 bg-amber-50 border border-amber-100 rounded-xl mb-5">
                                <h3 class="font-bold text-amber-900 text-sm mb-3 flex items-center gap-2">
                                    <span>📸</span> Autorisation image
                                </h3>
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input type="checkbox" name="autorisation_photo" value="1"
                                        {{ ($formData['autorisation_photo'] ?? 0) == 1 ? 'checked' : '' }}
                                        class="{{ $check }} mt-0.5">
                                    <span class="text-sm text-amber-800 leading-relaxed">
                                        J'autorise les membres de ma structure à être photographiés et filmés lors des
                                        activités de l'association, et j'accepte que ces images puissent être utilisées
                                        à des fins de communication non commerciale.
                                    </span>
                                </label>
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
                                    Suivant — Signature
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>
