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

                        @if (($formData['mode_paiement'] ?? '') === 'interne' && !$isStructure && $ticket)
                            <div
                                class="bg-white border border-gray-200 shadow-sm rounded-2xl mb-6 text-left overflow-hidden relative max-w-sm mx-auto sm:max-w-none">
                                <div class="h-1.5 w-full bg-[#16987C]"></div>

                                <div class="p-6">
                                    <div class="flex items-start justify-between mb-6">
                                        <div>
                                            <h4 class="font-black text-gray-900 text-lg flex items-center gap-2">
                                                <span>🧾</span> Fiche de règlement
                                            </h4>
                                            <p class="text-xs text-gray-400 font-medium mt-1">Paiement en personne ou
                                                virement</p>
                                        </div>
                                        <span
                                            class="inline-flex px-2.5 py-1 bg-amber-50 border border-amber-100 text-amber-600 text-[10px] font-black uppercase tracking-widest rounded-lg">
                                            En attente
                                        </span>
                                    </div>

                                    <div class="space-y-3 mb-5 px-1">
                                        @foreach ($ticket['lignes'] as $ligne)
                                            <div
                                                class="flex justify-between items-start gap-4 border-b border-gray-50 pb-2 last:border-0 last:pb-0">
                                                <span
                                                    class="text-sm text-gray-700 font-medium leading-tight">{{ $ligne['nom'] }}</span>
                                                <span
                                                    class="text-sm font-bold text-gray-900 whitespace-nowrap">{{ number_format($ligne['prix'], 2, ',', ' ') }}
                                                    €</span>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="relative py-3">
                                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                            <div class="w-full border-t-2 border-dashed border-gray-200"></div>
                                        </div>
                                    </div>

                                    <div class="mb-6 mt-2">
                                        <div
                                            class="flex justify-between items-center bg-gray-50 rounded-xl p-4 border border-gray-100">
                                            <span class="text-sm font-bold text-gray-600">Total à régler</span>
                                            <span
                                                class="text-2xl font-black text-[#16987C]">{{ number_format($ticket['total'], 2, ',', ' ') }}
                                                €</span>
                                        </div>
                                    </div>

                                    <div>
                                        <p class="text-sm text-gray-600 mb-4 leading-relaxed font-medium">
                                            Veuillez contacter la direction pour fixer un rendez-vous et remettre votre
                                            règlement.
                                        </p>
                                        <a href="mailto:direction@savoirsvivants.fr"
                                            class="w-full flex justify-center items-center gap-2 bg-[#222A60] text-white text-sm font-bold px-4 py-3.5 rounded-xl hover:bg-[#181d44] transition-colors shadow-md hover:shadow-lg">
                                            ✉️ direction@savoirsvivants.fr
                                        </a>
                                    </div>
                                </div>
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
                                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Bienvenue !
                                    </p>
                                    <p class="text-xs font-bold text-teal-600 mt-1">Après validation</p>
                                </div>
                            @endif
                        </div>

                        <p class="text-xs text-gray-400">
                            Une question ? <a href="mailto:contact@savoirsvivants.fr"
                                class="text-teal-600 hover:text-teal-700 font-medium transition-colors">contact@savoirsvivants.fr</a>
                        </p>
                    </div>
