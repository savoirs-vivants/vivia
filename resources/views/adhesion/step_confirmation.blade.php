                    <div class="p-6 md:p-8 text-center">
                        <div
                            class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4 text-3xl bg-teal-50 border border-teal-100">
                            ✅
                        </div>

                        @php
                            $isAdherentExistant = ($formData['is_adherent'] ?? 'non') === 'oui';
                            $prenomNom = $isAdherentExistant
                                ? ''
                                : ($formData['prenom'] ?? '') . ' ' . ($formData['nom'] ?? '');
                        @endphp

                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Demande envoyée !</h2>

                        @if ($prenomNom)
                            <p class="text-gray-600 text-base mb-2">
                                Merci <strong class="text-teal-600">{{ $prenomNom }}</strong> !
                            </p>
                        @endif

                        <p class="text-gray-400 text-sm mb-6 max-w-md mx-auto leading-relaxed">
                            @if ($isAdherentExistant)
                                Votre demande d'inscription pour cette nouvelle activité a bien été transmise à notre
                                équipe. Elle sera traitée dans les plus brefs délais.
                            @else
                                Votre demande d'adhésion a bien été transmise à notre équipe. Elle sera traitée dans les
                                plus brefs délais. Vous recevrez un e-mail de confirmation dès la validation.
                            @endif
                        </p>

                        @if (($formData['mode_paiement'] ?? '') === 'interne')
                            <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl mb-6 text-left">
                                <h4 class="font-semibold text-gray-800 mb-2 flex items-center gap-2 text-sm">
                                    <span>💳</span> Étape suivante : règlement
                                </h4>
                                <p class="text-sm text-gray-600 mb-3">
                                    Vous avez choisi le paiement en personne. Contactez-nous pour fixer un rendez-vous :
                                </p>
                                <a href="mailto:direction@savoirsvivants.fr"
                                    class="inline-flex items-center gap-2 bg-teal-600 text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-teal-700 transition-colors">
                                    ✉️ direction@savoirsvivants.fr
                                </a>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-left mb-6">
                            <div
                                class="p-4 bg-white rounded-xl border border-gray-100 text-center {{ $isAdherentExistant ? 'sm:col-span-3 sm:max-w-xs sm:mx-auto' : '' }}">
                                <div class="text-2xl mb-1.5">📧</div>
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Email de
                                    confirmation</p>
                            </div>

                            @if (!$isAdherentExistant)
                                <div class="p-4 bg-white rounded-xl border border-gray-100 text-center">
                                    <div class="text-2xl mb-1.5">🪪</div>
                                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Numéro
                                        d'adhérent</p>
                                    <p class="text-xs font-bold text-gray-700 mt-1">À réception du mail</p>
                                </div>
                                <div class="p-4 bg-white rounded-xl border border-gray-100 text-center">
                                    <div class="text-2xl mb-1.5">🎉</div>
                                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Bienvenue !</p>
                                    <p class="text-xs font-bold text-teal-600 mt-1">Après validation</p>
                                </div>
                            @endif
                        </div>

                        <p class="text-xs text-gray-400">
                            Une question ? <a href="mailto:contact@savoirsvivants.fr"
                                class="text-teal-600 hover:text-teal-700 font-medium transition-colors">contact@savoirsvivants.fr</a>
                        </p>
                    </div>
