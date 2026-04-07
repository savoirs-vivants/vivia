                    @if ($isStructure)
                    <div class="p-5 md:p-6" x-data="{ modalCotisation: {{ $paiement1Done ? 'true' : 'false' }} }">

                        <div x-show="modalCotisation" x-transition
                             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
                             style="display: none;">
                            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6" @click.outside="false">
                                <div class="text-center mb-5">
                                    <div class="w-14 h-14 rounded-2xl bg-teal-50 border border-teal-100 flex items-center justify-center text-3xl mx-auto mb-3">🏛️</div>
                                    <h3 class="text-lg font-bold text-gray-900">
                                        @if (($formData['type_activite'] ?? '') === 'ressourcerie')
                                            Ressourcerie réglée !
                                        @else
                                            Cotisation {{ ($formData['statut_juridique'] ?? '') === 'esr_pme' ? 'ESR/PME' : 'TPE/Asso' }}
                                        @endif
                                    </h3>
                                    <p class="text-gray-500 text-sm mt-1">
                                        Réglez la cotisation annuelle <strong>({{ $montantStructure }} €)</strong> via le formulaire HelloAsso dédié.
                                    </p>
                                </div>

                                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-5 flex items-start gap-3">
                                    <span class="text-xl shrink-0">📋</span>
                                    <div>
                                        <p class="text-sm font-semibold text-amber-900">Paiement dans un nouvel onglet</p>
                                        <p class="text-sm text-amber-700 mt-0.5 leading-relaxed">
                                            Cliquez sur le bouton ci-dessous, payez sur HelloAsso, puis revenez ici pour valider.
                                        </p>
                                    </div>
                                </div>

                                @error('helloasso2')
                                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 font-medium">
                                        ❌ {{ $message }}
                                    </div>
                                @enderror

                                <div x-data="cotisationPaiement()" x-init="init()">
                                    <button
                                        @click="ouvrirHelloAsso()"
                                        :disabled="loading"
                                        x-show="!dejaClique"
                                        class="w-full inline-flex items-center justify-center gap-2 bg-teal-600 text-white font-semibold px-6 py-3 rounded-lg hover:bg-teal-700 transition text-sm shadow-sm disabled:opacity-60">
                                        <span x-show="!loading">Payer la cotisation sur HelloAsso →</span>
                                        <span x-show="loading">Chargement…</span>
                                    </button>

                                    <div x-show="dejaClique" x-transition class="mt-4 space-y-3">
                                        <div class="p-4 bg-teal-50 border border-teal-200 rounded-xl text-sm text-teal-800">
                                            <p class="font-semibold mb-1">La page HelloAsso s'est ouverte dans un nouvel onglet.</p>
                                            <p class="text-xs text-teal-700">Une fois le paiement finalisé, revenez ici et cliquez sur le bouton ci-dessous.</p>
                                        </div>
                                        <form action="{{ route('adhesion.verifier.cotisation', $token) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="w-full inline-flex items-center justify-center gap-2 bg-emerald-600 text-white font-semibold px-6 py-3 rounded-lg hover:bg-emerald-700 transition text-sm">
                                                ✅ J'ai payé — vérifier et continuer
                                            </button>
                                        </form>
                                        <button @click="dejaClique = false"
                                            class="w-full text-xs text-gray-400 hover:text-gray-600 underline py-1">
                                            ← Rouvrir la page HelloAsso
                                        </button>
                                    </div>
                                </div>

                                <p class="text-center text-xs text-gray-400 mt-3">Paiement sécurisé via HelloAsso</p>
                            </div>
                        </div>

                        <div class="mb-5">
                            <h2 class="text-xl font-bold text-gray-900">Paiement 💳</h2>
                            <p class="text-gray-400 mt-1 text-sm">
                                @if ($totalRessourcerieStructure !== null)
                                    Réglez votre accès à la ressourcerie via HelloAsso
                                @else
                                    Réglez votre cotisation annuelle via HelloAsso
                                @endif
                            </p>
                        </div>

                        @error('helloasso')
                            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl flex items-start gap-3">
                                <span class="text-xl">❌</span>
                                <p class="text-sm font-bold text-red-800">{{ $message }}</p>
                            </div>
                        @enderror

                        @if ($totalRessourcerieStructure !== null)
                            {{-- Phase 1 : items ressourcerie --}}
                            <div class="p-5 bg-teal-50 border border-teal-200 rounded-2xl mb-6">
                                <p class="text-xs font-bold text-teal-700 uppercase tracking-wide mb-3">Items sélectionnés</p>
                                @foreach ($ressourcerieSelectionnees as $item)
                                    <div class="flex items-center justify-between py-1.5 border-b border-teal-100 last:border-0">
                                        <span class="text-sm text-teal-900">{{ $item->nom }}</span>
                                        <span class="text-sm font-bold text-teal-700">{{ $item->prix }} €</span>
                                    </div>
                                @endforeach
                                <p class="text-xs text-teal-600 mt-2">La cotisation annuelle ({{ $montantStructure }} €) sera réglée dans un second temps.</p>
                            </div>
                        @else
                            {{-- Cotisation structure --}}
                            <div class="p-5 bg-teal-50 border border-teal-200 rounded-2xl mb-6">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-bold text-teal-900">Cotisation annuelle</span>
                                    <span class="text-2xl font-bold text-teal-700">{{ $montantStructure }} €</span>
                                </div>
                                <p class="text-xs text-teal-700">
                                    {{ ($formData['statut_juridique'] ?? '') === 'esr_pme' ? 'Tarif ESR / PME' : 'Tarif TPE / Association' }}
                                    — paiement via formulaire HelloAsso dédié
                                </p>
                            </div>
                        @endif

                        <form action="{{ route('adhesion.next', $token) }}" method="POST">
                            @csrf
                            <input type="hidden" name="current_step" value="10">
                            <input type="hidden" name="mode_paiement" value="helloasso">

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
                                    @if ($totalRessourcerieStructure !== null)
                                        Payer {{ $totalRessourcerieStructure }} € (ressourcerie) 🔒
                                    @else
                                        Payer via HelloAsso 🔒
                                    @endif
                                </button>
                            </div>
                        </form>
                    </div>
                    @else
                    <div class="p-5 md:p-6" x-data="{ modalAdhesion: {{ $paiement1Done ? 'true' : 'false' }} }">

                        <div x-show="modalAdhesion" x-transition
                             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
                             style="display: none;">
                            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6" @click.outside="false">
                                @php
                                    $typeAct      = $formData['type_activite'] ?? '';
                                    $modalIcon    = match($typeAct) {
                                        'soutien'      => '🤝',
                                        'recherche'    => '🔬',
                                        'ressourcerie' => '🤖',
                                        'stage'        => '📚',
                                        default        => '✅',
                                    };
                                    $modalTitre   = match($typeAct) {
                                        'soutien'      => 'Adhésion par soutien',
                                        'recherche'    => 'Recherche participative',
                                        'ressourcerie' => 'Ressourcerie enregistrée !',
                                        'stage'        => 'Stage enregistré !',
                                        default        => 'Activité enregistrée !',
                                    };
                                    $modalSous    = match($typeAct) {
                                        'soutien'      => 'Merci pour votre soutien à l\'association.',
                                        'recherche'    => 'Votre participation au programme de recherche est enregistrée.',
                                        'ressourcerie' => 'Votre accès à la ressourcerie est bien enregistré.',
                                        'stage'        => 'Votre inscription au stage est bien enregistrée.',
                                        default        => 'Votre inscription à l\'atelier est bien enregistrée.',
                                    };
                                    $modalMessage = in_array($typeAct, ['soutien', 'recherche'])
                                        ? 'Pour finaliser votre adhésion, réglez la <strong>cotisation annuelle</strong> via la page officielle HelloAsso.'
                                        : 'Pour être pleinement membre de l\'association, réglez également la <strong>cotisation annuelle</strong> via HelloAsso.';
                                @endphp
                                <div class="text-center mb-5">
                                    <div class="w-14 h-14 rounded-2xl bg-teal-50 border border-teal-100 flex items-center justify-center text-3xl mx-auto mb-3">{{ $modalIcon }}</div>
                                    <h3 class="text-lg font-bold text-gray-900">{{ $modalTitre }}</h3>
                                    <p class="text-gray-500 text-sm mt-1">{{ $modalSous }}</p>
                                </div>

                                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-5 flex items-start gap-3">
                                    <span class="text-xl shrink-0">📋</span>
                                    <div>
                                        <p class="text-sm font-semibold text-amber-900">Une dernière étape</p>
                                        <p class="text-sm text-amber-700 mt-0.5 leading-relaxed">{!! $modalMessage !!}</p>
                                    </div>
                                </div>

                                @error('helloasso2')
                                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 font-medium">
                                        ❌ {{ $message }}
                                    </div>
                                @enderror

                                <div x-data="cotisationPaiement()" x-init="init()">
                                    <button
                                        @click="ouvrirHelloAsso()"
                                        :disabled="loading"
                                        x-show="!dejaClique"
                                        class="w-full inline-flex items-center justify-center gap-2 bg-teal-600 text-white font-semibold px-6 py-3 rounded-lg hover:bg-teal-700 transition text-sm shadow-sm disabled:opacity-60">
                                        <span x-show="!loading">Payer la cotisation sur HelloAsso →</span>
                                        <span x-show="loading">Chargement…</span>
                                    </button>

                                    <div x-show="dejaClique" x-transition class="mt-4 space-y-3">
                                        <div class="p-4 bg-teal-50 border border-teal-200 rounded-xl text-sm text-teal-800">
                                            <p class="font-semibold mb-1">La page HelloAsso s'est ouverte dans un nouvel onglet.</p>
                                            <p class="text-xs text-teal-700">Une fois le paiement finalisé sur HelloAsso, revenez ici et cliquez sur le bouton ci-dessous.</p>
                                        </div>

                                        <form action="{{ route('adhesion.verifier.cotisation', $token) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="w-full inline-flex items-center justify-center gap-2 bg-emerald-600 text-white font-semibold px-6 py-3 rounded-lg hover:bg-emerald-700 transition text-sm">
                                                ✅ J'ai payé — vérifier et continuer
                                            </button>
                                        </form>

                                        <button
                                            @click="dejaClique = false"
                                            class="w-full text-xs text-gray-400 hover:text-gray-600 underline py-1">
                                            ← Rouvrir la page HelloAsso
                                        </button>
                                    </div>
                                </div>

                                <p class="text-center text-xs text-gray-400 mt-3">Paiement sécurisé via HelloAsso</p>
                            </div>
                        </div>

                        <div class="mb-5">
                            <h2 class="text-xl font-bold text-gray-900">Choix du paiement 💳</h2>
                            <p class="text-gray-400 mt-1 text-sm">Comment souhaitez-vous régler votre adhésion ?</p>
                        </div>

                        @error('helloasso')
                            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl flex items-start gap-3">
                                <span class="text-xl">❌</span>
                                <p class="text-sm font-bold text-red-800">{{ $message }}</p>
                            </div>
                        @enderror

                        <form action="{{ route('adhesion.next', $token) }}" method="POST">
                            @csrf
                            <input type="hidden" name="current_step" value="10">

                            <div x-data="{ modePaiement: '{{ $formData['mode_paiement'] ?? 'helloasso' }}' }">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">

                                    <label class="cursor-pointer block group">
                                        <input type="radio" name="mode_paiement" value="helloasso"
                                            x-model="modePaiement" class="sr-only">
                                        <div :class="modePaiement === 'helloasso' ?
                                            'border-teal-600 bg-teal-50 ring-2 ring-teal-600/20' :
                                            'border-gray-200 group-hover:border-teal-400'"
                                            class="{{ $card }} items-center text-center">
                                            <div class="text-4xl mb-3">🌐</div>
                                            <h3 class="text-lg font-bold text-slate-900">HelloAsso</h3>
                                            <p class="text-gray-500 text-sm mt-2">Paiement en ligne sécurisé</p>
                                            <span
                                                class="inline-block mt-4 text-xs font-bold bg-teal-100 text-teal-700 px-3 py-1.5 rounded-full uppercase tracking-wider">Recommandé</span>
                                        </div>
                                    </label>

                                    <label class="cursor-pointer block group">
                                        <input type="radio" name="mode_paiement" value="interne"
                                            x-model="modePaiement" class="sr-only">
                                        <div :class="modePaiement === 'interne' ?
                                            'border-teal-600 bg-teal-50 ring-2 ring-teal-600/20' :
                                            'border-gray-200 group-hover:border-teal-400'"
                                            class="{{ $card }} items-center text-center">
                                            <div class="text-4xl mb-3">🤝</div>
                                            <h3 class="text-lg font-bold text-slate-900">Paiement en personne</h3>
                                            <p class="text-gray-500 text-sm mt-2">Chèque, espèces ou virement</p>
                                        </div>
                                    </label>

                                    <label class="cursor-pointer block group opacity-70">
                                        <input type="radio" name="mode_paiement" value="pass_culture"
                                            x-model="modePaiement" class="sr-only" disabled>
                                        <div :class="modePaiement === 'pass_culture' ?
                                            'border-purple-600 bg-purple-50 ring-2 ring-purple-600/20' :
                                            'border-gray-200 group-hover:border-gray-300 cursor-not-allowed'"
                                            class="{{ $card }} items-center text-center relative">
                                            <div class="text-4xl mb-3">🎭</div>
                                            <h3 class="text-lg font-bold text-slate-900">Pass Culture</h3>
                                            <p class="text-gray-500 text-sm mt-2">Utiliser votre Pass Culture</p>
                                            <span
                                                class="inline-block mt-4 text-xs font-bold bg-purple-100 text-purple-700 px-3 py-1.5 rounded-full uppercase tracking-wider">Fonctionnalité à venir</span>
                                        </div>
                                    </label>
                                </div>

                                <div x-show="modePaiement === 'interne'" x-transition style="display: none;"
                                    class="p-6 bg-slate-50 border border-slate-200 rounded-2xl mb-6">
                                    <h4 class="font-bold text-slate-900 mb-3 flex items-center gap-2 text-lg">
                                        <span>📬</span> Comment procéder ?
                                    </h4>
                                    <p class="text-sm font-medium text-slate-700 mb-4 leading-relaxed">
                                        Pour finaliser votre adhésion, merci de contacter notre équipe afin de convenir d'un
                                        rendez-vous :
                                    </p>
                                    <a href="mailto:direction@savoirsvivants.fr"
                                        class="inline-flex items-center gap-2 bg-slate-900 text-white text-sm font-bold px-5 py-3 rounded-xl hover:bg-teal-600 shadow-md transition-colors">
                                        ✉️ direction@savoirsvivants.fr
                                    </a>
                                    <p class="text-xs text-gray-500 mt-4 font-semibold">Modes acceptés : chèque, espèces,
                                        ou virement bancaire.</p>
                                </div>

                                <div x-show="modePaiement === 'helloasso'" x-transition style="display: none;"
                                    class="p-5 bg-teal-50 border border-teal-200 rounded-2xl mb-6">
                                    <p class="text-sm font-bold text-slate-900 flex items-center gap-3 leading-relaxed">
                                        <span class="text-xl">🔒</span>
                                        Vous allez être redirigé·e vers la plateforme sécurisée HelloAsso pour procéder au
                                        paiement.
                                    </p>
                                </div>

                                <div class="flex items-center justify-between pt-4 border-t border-gray-100 mt-1">
                                    @if ($hasPrev)
                                        <a href="{{ route('adhesion.show', ['token' => $token, 'step' => $prevStep]) }}"
                                            class="{{ $btnBack }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                    d="M15 19l-7-7 7-7" />
                                            </svg>
                                            Précédent
                                        </a>
                                    @else
                                        <div></div>
                                    @endif

                                    <button type="submit" class="{{ $btn }}">
                                        <span x-show="modePaiement === 'helloasso'">Continuer vers HelloAsso 🔒</span>
                                        <span x-show="modePaiement === 'interne'">Valider mon inscription ✓</span>
                                        <span x-show="modePaiement === 'pass_culture'">Valider mon inscription ✓</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    @endif
