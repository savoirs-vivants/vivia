                    <div class="p-5 md:p-6">
                        <div class="mb-5">
                            <h2 class="text-xl font-bold text-gray-900">Bienvenue ! 👋</h2>
                            <p class="text-gray-400 mt-1 text-sm">Êtes-vous déjà adhérent·e de notre association ?</p>
                        </div>

                        <form action="{{ route('adhesion.next', $token) }}" method="POST">
                            @csrf
                            <input type="hidden" name="current_step" value="1" >

                            <div x-data="{ statut: '{{ $formData['is_adherent'] ?? '' }}' }">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                                    <label class="cursor-pointer block group">
                                        <input type="radio" name="is_adherent" value="oui" x-model="statut"
                                            class="sr-only">
                                        <div :class="statut === 'oui' ? 'border-teal-600 bg-teal-50 ring-2 ring-teal-600/20' :
                                            'border-gray-200 group-hover:border-teal-400'"
                                            class="{{ $card }} items-center text-center">
                                            <div class="text-5xl mb-3">🪪</div>
                                            <h3 class="text-lg font-bold text-slate-900">Oui, je suis adhérent·e</h3>
                                            <p class="text-gray-500 text-sm mt-2">J'ai un numéro d'adhérent</p>
                                            <div class="mt-4 w-6 h-6 rounded-full mx-auto border-2 flex items-center justify-center transition-colors"
                                                :class="statut === 'oui' ? 'border-teal-600 bg-teal-600' :
                                                    'border-gray-300'">
                                                <svg x-show="statut === 'oui'" class="w-3 h-3 text-white"
                                                    fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                    </label>

                                    <label class="cursor-pointer block group">
                                        <input type="radio" name="is_adherent" value="non" x-model="statut"
                                            class="sr-only">
                                        <div :class="statut === 'non' ? 'border-teal-600 bg-teal-50 ring-2 ring-teal-600/20' :
                                            'border-gray-200 group-hover:border-teal-400'"
                                            class="{{ $card }} items-center text-center">
                                            <div class="text-5xl mb-3">🆕</div>
                                            <h3 class="text-lg font-bold text-slate-900">Non, première inscription</h3>
                                            <p class="text-gray-500 text-sm mt-2">Je n'ai pas encore de numéro</p>
                                            <div class="mt-4 w-6 h-6 rounded-full mx-auto border-2 flex items-center justify-center transition-colors"
                                                :class="statut === 'non' ? 'border-teal-600 bg-teal-600' :
                                                    'border-gray-300'">
                                                <svg x-show="statut === 'non'" class="w-3 h-3 text-white"
                                                    fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                    </label>
                                </div>

                                <div x-show="statut === 'oui'" x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 -translate-y-2"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    class="mb-5 p-4 bg-teal-50 rounded-xl border border-teal-100">

                                    <div class="flex justify-between items-end mb-2">
                                        <label class="{{ $label }} !mb-0">🔢 Numéro d'adhérent</label>
                                    </div>

                                    <input type="text" name="numero_adherent"
                                        value="{{ $formData['numero_adherent'] ?? '' }}"
                                        placeholder="Ex : ADH-26-XXXX" class="{{ $field }}">

                                    @error('numero_adherent')
                                        <p class="text-sm font-bold text-red-500 mt-2">❌ {{ $message }}</p>
                                    @enderror

                                    <div x-data="{
                                        showRecup: false,
                                        emailRecup: '',
                                        loadingRecup: false,
                                        messageRecup: '',
                                        envoyerDemande() {
                                            if (!this.emailRecup) return;
                                            this.loadingRecup = true;
                                            this.messageRecup = '';

                                            fetch('{{ route('adhesion.recup') }}', {
                                                    method: 'POST',
                                                    headers: {
                                                        'Content-Type': 'application/json',
                                                        'Accept': 'application/json',
                                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                    },
                                                    body: JSON.stringify({ email: this.emailRecup })
                                                })
                                                .then(res => {
                                                    const contentType = res.headers.get('content-type');
                                                    if (!contentType || !contentType.includes('application/json')) {
                                                        throw new Error(`Réponse inattendue du serveur (HTTP ${res.status})`);
                                                    }
                                                    return res.json();
                                                })
                                                .then(data => {
                                                    this.messageRecup = data.message;
                                                    this.loadingRecup = false;
                                                    this.emailRecup = '';
                                                })
                                                .catch(err => {
                                                    console.error('Recup error:', err);
                                                    this.messageRecup = 'Une erreur est survenue. Vérifiez votre connexion.';
                                                    this.loadingRecup = false;
                                                });
                                        }
                                    }" class="mt-3">

                                        <button type="button" @click="showRecup = !showRecup"
                                            class="text-sm font-bold text-teal-700 hover:text-slate-900 transition-colors underline decoration-teal-300 underline-offset-4">
                                            Numéro perdu ?
                                        </button>

                                        <div x-show="showRecup" x-transition
                                            class="mt-4 p-5 bg-white rounded-xl border border-gray-200 shadow-sm">
                                            <p class="text-sm text-gray-600 mb-3 font-medium">Entrez l'adresse e-mail
                                                associée à votre compte. Nous vous enverrons un code temporaire.</p>

                                            <div class="flex flex-col sm:flex-row gap-2">
                                                <input type="email" x-model="emailRecup" placeholder="votre@email.com"
                                                    class="flex-1 rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-teal-600 focus:outline-none text-sm">
                                                <button type="button" @click="envoyerDemande()" :disabled="loadingRecup"
                                                    class="bg-slate-900 text-white font-bold px-5 py-2.5 rounded-lg hover:bg-teal-600 transition-colors disabled:opacity-50 text-sm whitespace-nowrap">
                                                    <span x-show="!loadingRecup">Recevoir le code</span>
                                                    <span x-show="loadingRecup">Envoi en cours...</span>
                                                </button>
                                            </div>

                                            <p x-show="messageRecup" x-text="messageRecup"
                                                class="mt-3 text-sm font-bold text-teal-600"></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end pt-4 border-t border-gray-100 mt-1">
                                    <button type="submit" class="{{ $btn }}" :disabled="!statut" :class="!statut ? 'opacity-50 cursor-not-allowed grayscale' : ''">
                                        Suivant
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
