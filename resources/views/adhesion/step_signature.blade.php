                    <div class="p-5 md:p-6">
                        <div class="mb-5">
                            <h2 class="text-xl font-bold text-gray-900">Votre signature ✍️</h2>
                            <p class="text-gray-400 mt-1 text-sm">En tant qu'adhérent, signez ci-dessous pour valider la fiche d'adhésion</p>
                        </div>

                        <form action="{{ route('adhesion.next', $token) }}" method="POST" id="form-signature">
                            @csrf
                            <input type="hidden" name="current_step" value="9">

                            <div class="p-4 bg-amber-50 rounded-xl border border-amber-100 mb-5 flex items-start gap-3">
                                <span class="text-2xl">⚠️</span>
                                <p class="text-sm font-bold text-amber-800 leading-relaxed">
                                    En signant ce formulaire, vous certifiez l'exactitude des informations renseignées.
                                </p>
                            </div>

                            <div class="mb-5">
                                <label class="{{ $label }}">📅 Date *</label>
                                <input type="date" name="date_signature_adherent"
                                    value="{{ $formData['date_signature_adherent'] ?? date('Y-m-d') }}" required
                                    class="{{ $field }} max-w-xs">
                            </div>

                            <div class="mb-8">
                                <label class="{{ $label }}">✍️ Signature *</label>
                                <div class="border-2 border-dashed border-gray-300 rounded-2xl bg-gray-50 p-2 relative"
                                    style="max-width: 500px;">
                                    <canvas id="canvas-adherent"
                                        class="w-full touch-none bg-white rounded-xl cursor-crosshair block border border-gray-100 shadow-sm"
                                        style="height: 180px;"></canvas>
                                    <button type="button" id="clear-sig-adherent"
                                        class="absolute top-4 right-4 bg-white border border-gray-200 text-xs font-bold text-gray-500 hover:text-red-500 hover:border-red-200 px-3 py-1.5 rounded-lg shadow-sm transition">
                                        Effacer
                                    </button>
                                </div>
                                <input type="hidden" name="signature_adherent" id="sig-data-adherent"
                                    value="{{ $formData['signature_adherent'] ?? '' }}">
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
                                    @if (($formData['type_activite'] ?? '') === 'club_maker')
                                        Terminer l'inscription ✓
                                    @else
                                        Suivant — Paiement
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    @endif
                                </button>
                            </div>
                        </form>
                    </div>
