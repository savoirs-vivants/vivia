                    <div class="p-5 md:p-6">
                        <div class="mb-5">
                            <h2 class="text-xl font-bold text-gray-900">Informations de la structure 🏛️</h2>
                            <p class="text-gray-400 mt-1 text-sm">Renseignez les coordonnées de votre organisation</p>
                        </div>

                        <form action="{{ route('adhesion.next', $token) }}" method="POST">
                            @csrf
                            <input type="hidden" name="current_step" value="13">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="{{ $label }}">Nom de la structure *</label>
                                    <input type="text" name="nom_structure"
                                        value="{{ $formData['nom_structure'] ?? '' }}"
                                        placeholder="Nom officiel" required class="{{ $field }}">
                                </div>
                                <div>
                                    <label class="{{ $label }}">Sigle <span class="font-normal normal-case text-gray-400">(si applicable)</span></label>
                                    <input type="text" name="sigle"
                                        value="{{ $formData['sigle'] ?? '' }}"
                                        placeholder="Ex : VIVIA" class="{{ $field }}">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="{{ $label }}">Adresse *</label>
                                <input type="text" name="adresse_structure"
                                    value="{{ $formData['adresse_structure'] ?? '' }}"
                                    placeholder="Numéro et nom de rue" required class="{{ $field }}">
                            </div>
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="{{ $label }}">Code postal *</label>
                                    <input type="text" name="code_postal_structure"
                                        value="{{ $formData['code_postal_structure'] ?? '' }}"
                                        placeholder="67000" required class="{{ $field }}">
                                </div>
                                <div>
                                    <label class="{{ $label }}">Ville *</label>
                                    <input type="text" name="ville_structure"
                                        value="{{ $formData['ville_structure'] ?? '' }}"
                                        placeholder="Strasbourg" required class="{{ $field }}">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="{{ $label }}">📅 Date de création *</label>
                                <input type="date" required name="date_creation_structure"
                                    value="{{ $formData['date_creation_structure'] ?? '' }}"
                                    class="{{ $field }} max-w-xs">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="{{ $label }}">📞 Téléphone fixe *</label>
                                    <input type="tel" required name="tel_structure"
                                        value="{{ $formData['tel_structure'] ?? '' }}"
                                        placeholder="03 88 00 00 00" class="{{ $field }}">
                                </div>
                                <div>
                                    <label class="{{ $label }}">📱 Téléphone portable *</label>
                                    <input type="tel" required name="tel_portable_structure"
                                        value="{{ $formData['tel_portable_structure'] ?? '' }}"
                                        placeholder="06 00 00 00 00" class="{{ $field }}">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="{{ $label }}">📧 Adresse mail *</label>
                                    <input type="email" name="mail_structure"
                                        value="{{ $formData['mail_structure'] ?? '' }}"
                                        placeholder="contact@structure.fr" required class="{{ $field }}">
                                </div>
                                <div>
                                    <label class="{{ $label }}">🌐 Site web *</label>
                                    <input type="url" required name="site_web"
                                        value="{{ $formData['site_web'] ?? '' }}"
                                        placeholder="https://www.structure.fr" class="{{ $field }}">
                                </div>
                            </div>

                            <div class="border-t border-gray-100 pt-4 mb-4">
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">Correspondant(e)</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="{{ $label }}">Nom du/de la correspondant(e) *</label>
                                        <input type="text" name="nom_correspondant"
                                            value="{{ $formData['nom_correspondant'] ?? '' }}"
                                            placeholder="Prénom Nom" required class="{{ $field }}">
                                    </div>
                                    <div>
                                        <label class="{{ $label }}">Tél. si différent</label>
                                        <input type="tel" name="tel_correspondant"
                                            value="{{ $formData['tel_correspondant'] ?? '' }}"
                                            placeholder="06 00 00 00 00" class="{{ $field }}">
                                    </div>
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
