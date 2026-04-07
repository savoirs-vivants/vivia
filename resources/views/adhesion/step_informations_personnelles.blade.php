                    <div class="p-5 md:p-6">
                        <div class="mb-5">
                            <h2 class="text-xl font-bold text-gray-900">Informations personnelles de l'adhérent 📋</h2>
                            <p class="text-gray-400 mt-1 text-sm">Renseignez vos coordonnées et informations d'adhésion</p>
                        </div>

                        <form action="{{ route('adhesion.next', $token) }}" method="POST">
                            @csrf
                            <input type="hidden" name="current_step" value="3">

                            <div class="mb-5">
                                <label class="{{ $label }}">Genre *</label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    @foreach (['Homme' => '🧔', 'Femme' => '👩', 'Autre' => '🧑'] as $val => $icon)
                                        <label class="cursor-pointer block group">
                                            <input type="radio" name="genre" required value="{{ $val }}"
                                                {{ ($formData['genre'] ?? '') === $val ? 'checked' : '' }}
                                                class="sr-only peer">
                                            <div
                                                class="border-2 rounded-lg p-3 text-center peer-checked:border-teal-500 peer-checked:bg-teal-50 peer-checked:ring-2 ring-teal-500/20 border-gray-200 group-hover:border-teal-400 transition-all">
                                                <div class="text-2xl mb-1">{{ $icon }}</div>
                                                <span class="text-xs font-bold text-slate-700">{{ $val }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                                <div>
                                    <label class="{{ $label }}">Nom *</label>
                                    <input type="text" name="nom" value="{{ $formData['nom'] ?? '' }}"
                                        placeholder="Votre nom" required class="{{ $field }}">
                                </div>
                                <div>
                                    <label class="{{ $label }}">Prénom *</label>
                                    <input type="text" name="prenom" value="{{ $formData['prenom'] ?? '' }}"
                                        placeholder="Votre prénom" required class="{{ $field }}">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="{{ $label }}">Adresse *</label>
                                <input type="text" name="adresse" value="{{ $formData['adresse'] ?? '' }}"
                                    placeholder="Numéro et nom de rue" required class="{{ $field }}">
                            </div>
                            <div class="grid grid-cols-2 gap-4 mb-5">
                                <div>
                                    <label class="{{ $label }}">Code postal *</label>
                                    <input type="text" name="code_postal"
                                        value="{{ $formData['code_postal'] ?? '' }}" placeholder="67000" required
                                        class="{{ $field }}">
                                </div>
                                <div>
                                    <label class="{{ $label }}">Ville *</label>
                                    <input type="text" name="ville" value="{{ $formData['ville'] ?? '' }}"
                                        placeholder="Strasbourg" required class="{{ $field }}">
                                </div>
                            </div>

                            <div class="mb-5">
                                <label class="{{ $label }}">🎂 Date de naissance *</label>
                                <input type="date" name="date_naiss" value="{{ $formData['date_naiss'] ?? '' }}"
                                    required class="{{ $field }}">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                                <div>
                                    <label class="{{ $label }}">📞 Téléphone *</label>
                                    <input type="tel" name="tel" required value="{{ $formData['tel'] ?? '' }}"
                                        placeholder="06 00 00 00 00" class="{{ $field }}">
                                </div>
                                <div>
                                    <label class="{{ $label }}">📧 Email *</label>
                                    <input type="email" name="mail" required value="{{ $formData['mail'] ?? '' }}"
                                        placeholder="exemple@mail.com" class="{{ $field }}">
                                </div>
                            </div>

                            <div class="mb-5">
                                <label class="{{ $label }}">🏛️ Régime social *</label>
                                <select name="regime_social" required class="{{ $field }}">
                                    <option value="">— Sélectionnez votre régime —</option>
                                    @foreach (['Sécurité sociale générale', 'Mutuelle complémentaire', 'CSS / CMU-C', 'MSA (agricole)', 'RSI / Indépendants', 'Autre'] as $r)
                                        <option value="{{ $r }}"
                                            {{ ($formData['regime_social'] ?? '') === $r ? 'selected' : '' }}>
                                            {{ $r }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 mb-5 space-y-3">
                                <p class="text-sm font-bold text-slate-900 mb-1">Autorisations & communications</p>
                                <label class="flex items-start gap-3 cursor-pointer group">
                                    <input type="checkbox" name="bulletin" value="1"
                                        {{ !empty($formData['bulletin'] ?? '') ? 'checked' : '' }}
                                        class="{{ $check }} mt-0.5">
                                    <span class="text-sm text-gray-700 leading-relaxed">
                                        <strong class="text-slate-900">Bulletin d'information</strong> — J'accepte de
                                        recevoir les bulletins et actualités de l'association par email.
                                    </span>
                                </label>
                                <label class="flex items-start gap-3 cursor-pointer group">
                                    <input type="checkbox" name="communication" value="1"
                                        {{ !empty($formData['communication'] ?? '') ? 'checked' : '' }}
                                        class="{{ $check }} mt-0.5">
                                    <span class="text-sm text-gray-700 leading-relaxed">
                                        <strong class="text-slate-900">Droit à l'image</strong> — J'autorise l'association
                                        à photographier l'adhérent·e et à diffuser ces images.
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
                                    Suivant
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>
