@extends('layouts.app')

@section('title', 'Formulaire d\'adhésion')

@section('content')

    @php
        $field =
            'w-full rounded-xl border border-gray-300 px-4 py-3.5 text-gray-800 bg-white focus:outline-none focus:ring-4 focus:ring-teal-600/20 focus:border-teal-600 transition-all shadow-sm text-base';
        $label = 'block text-sm font-bold text-slate-700 mb-2';
        $card =
            'border-2 rounded-2xl p-5 transition-all duration-200 h-full flex flex-col bg-white hover:shadow-md cursor-pointer';
        $btn =
            'inline-flex items-center justify-center gap-2 bg-slate-900 text-white font-bold px-8 py-3.5 rounded-xl hover:bg-teal-600 focus:ring-4 focus:ring-teal-600/30 transition-all duration-200 shadow-md hover:shadow-lg text-sm';
        $btnBack =
            'inline-flex items-center justify-center gap-2 text-slate-600 font-semibold px-5 py-3.5 rounded-xl border border-gray-200 hover:bg-gray-100 hover:text-slate-900 transition-all duration-200 text-sm';
        $check = 'h-5 w-5 rounded border-gray-300 text-teal-600 focus:ring-teal-600 cursor-pointer transition-colors';
        $radio = 'h-5 w-5 border-gray-300 text-teal-600 focus:ring-teal-600 cursor-pointer transition-colors';
    @endphp

    <div class="min-h-screen bg-slate-50 py-8 px-4" style="font-family: 'Space Grotesk', sans-serif;">
        <div class="max-w-2xl mx-auto">

            <div class="text-center mb-8">
                <div
                    class="inline-flex items-center justify-center w-14 h-14 rounded-2xl mb-4 bg-slate-900 shadow-lg text-white">
                    <span class="text-2xl">📝</span>
                </div>
                <h1 class="text-3xl font-bold text-slate-900">Formulaire d'adhésion</h1>
                <p class="text-gray-500 mt-2 text-sm">Remplissez les informations étape par étape</p>
            </div>

            @if ($step !== 11)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm text-gray-500">Étape <span
                                class="font-bold text-slate-900">{{ $currentNum }}</span> sur <span
                                class="font-bold">{{ $totalSteps }}</span></span>
                        <span class="text-sm font-bold text-teal-600 bg-teal-50 px-3 py-1 rounded-full">
                            {{ $stepMeta[$step]['icon'] }} {{ $stepMeta[$step]['label'] }}
                        </span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all duration-500 bg-teal-600"
                            style="width: {{ ($currentNum / $totalSteps) * 100 }}%;"></div>
                    </div>
                    <div class="hidden md:block mt-8 mb-12 px-4">
                        <div class="relative flex items-center justify-between w-full">
                            <div class="absolute top-4 left-0 w-full h-0.5 bg-gray-200 -z-10"></div>
                            <div class="absolute top-4 left-0 h-0.5 bg-teal-600 transition-all duration-500 -z-10"
                                style="width: {{ (array_search($step, $path) / (count($path) - 1)) * 100 }}%">
                            </div>
                            @foreach ($path as $i => $s)
                                @php
                                    $pathIdx = array_search($step, $path);
                                    $isDone = $i < $pathIdx;
                                    $isCurrent = $s === $step;
                                @endphp

                                <div class="relative flex flex-col items-center">
                                    <div
                                        class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300 border-2
                    {{ $isCurrent ? 'border-slate-900 bg-white text-slate-900 scale-125 z-10 shadow-lg' : '' }}
                    {{ $isDone ? 'border-teal-600 bg-teal-600 text-white' : '' }}
                    {{ !$isCurrent && !$isDone ? 'border-gray-300 bg-white text-gray-400' : '' }}">
                                        @if ($isDone)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        @else
                                            {{ $i + 1 }}
                                        @endif
                                    </div>
                                    <div class="absolute top-10 whitespace-nowrap flex flex-col items-center">
                                        <span
                                            class="text-[10px] uppercase tracking-tighter transition-all duration-300
                        {{ $isCurrent ? 'text-slate-900 font-bold opacity-100' : 'text-gray-400 font-medium opacity-60' }}
                        {{ count($path) > 7 && !$isCurrent ? 'hidden lg:block' : '' }}">
                                            {{ $stepMeta[$s]['label'] }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">

                @if ($step === 1)
                    <div class="p-6 md:p-8">
                        <div class="mb-6">
                            <h2 class="text-2xl font-bold text-slate-900">Bienvenue ! 👋</h2>
                            <p class="text-gray-500 mt-1">Êtes-vous déjà adhérent·e de notre association ?</p>
                        </div>

                        <form action="{{ route('adhesion.next', $token) }}" method="POST">
                            @csrf
                            <input type="hidden" name="current_step" value="1">

                            <div x-data="{ statut: '{{ $formData['is_adherent'] ?? '' }}' }">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                                    <label class="cursor-pointer block group">
                                        <input type="radio" name="is_adherent" value="oui" x-model="statut"
                                            class="sr-only">
                                        <div :class="statut === 'oui' ? 'border-teal-600 bg-teal-50 ring-2 ring-teal-600/20' :
                                            'border-gray-200 group-hover:border-slate-900'"
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
                                            'border-gray-200 group-hover:border-slate-900'"
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
                                    class="mb-6 p-5 bg-teal-50 rounded-2xl border border-teal-200">

                                    <div class="flex justify-between items-end mb-2">
                                        <label class="{{ $label }} !mb-0">🔢 Numéro d'adhérent <span
                                                class="font-normal text-xs text-gray-500">(ou code
                                                temporaire)</span></label>
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

                                <div class="flex justify-end pt-5 border-t border-gray-100 mt-2">
                                    <button type="submit" class="{{ $btn }}">
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
                @elseif($step === 2)
                    <div class="p-6 md:p-8">
                        <div class="mb-6">
                            <h2 class="text-2xl font-bold text-slate-900">Pour quelle activité ? 🎯</h2>
                            <p class="text-gray-500 mt-1">Choisissez le type d'inscription souhaité</p>
                        </div>

                        <form action="{{ route('adhesion.next', $token) }}" method="POST">
                            @csrf
                            <input type="hidden" name="current_step" value="2">

                            <div x-data="{ activite: '{{ $formData['type_activite'] ?? '' }}' }">
                                @php
                                    $options = [
                                        [
                                            'value' => 'atelier',
                                            'label' => 'Inscription à un atelier',
                                            'icon' => '🔧',
                                            'desc' => 'Ateliers robotiques',
                                        ],
                                        [
                                            'value' => 'ressourcerie',
                                            'label' => 'Ressourcerie',
                                            'desc' => 'Louer un Codey Rocky',
                                        ],
                                        [
                                            'value' => 'stage',
                                            'label' => 'Inscription à un stage',
                                            'desc' => 'Stages sur plusieurs jours',
                                        ],
                                        [
                                            'value' => 'club_maker',
                                            'label' => 'Club Maker',
                                            'desc' => 'Rejoindre le club des makers',
                                        ],
                                        [
                                            'value' => 'soutien',
                                            'label' => 'Inscription par soutien',
                                            'desc' => 'Soutenir financièrement l\'association',
                                        ],
                                        [
                                            'value' => 'recherche',
                                            'label' => 'Recherche participative',
                                            'desc' => 'Participer à un programme de recherche',
                                        ],
                                    ];
                                @endphp

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                                    @foreach ($options as $opt)
                                        <label class="cursor-pointer block group">
                                            <input type="radio" name="type_activite" value="{{ $opt['value'] }}"
                                                x-model="activite" class="sr-only">
                                            <div :class="activite === '{{ $opt['value'] }}' ?
                                                'border-teal-600 bg-teal-50 ring-2 ring-teal-600/20' :
                                                'border-gray-200 group-hover:border-slate-900'"
                                                class="{{ $card }}">
                                                <h3 class="font-bold text-slate-900 text-base">{{ $opt['label'] }}</h3>
                                                <p class="text-gray-500 text-xs mt-1.5 flex-1 leading-relaxed">
                                                    {{ $opt['desc'] }}</p>
                                                <div class="mt-3 w-5 h-5 rounded-full border-2 flex items-center justify-center self-end transition-colors"
                                                    :class="activite === '{{ $opt['value'] }}' ?
                                                        'border-teal-600 bg-teal-600' : 'border-gray-300'">
                                                    <svg x-show="activite === '{{ $opt['value'] }}'"
                                                        class="w-2.5 h-2.5 text-white" fill="currentColor"
                                                        viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>

                                <div class="flex items-center justify-between pt-5 border-t border-gray-100">
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
                @elseif($step === 3)
                    <div class="p-6 md:p-8">
                        <div class="mb-6">
                            <h2 class="text-2xl font-bold text-slate-900">Informations personnelles de l'adhérent 📋</h2>
                            <p class="text-gray-500 mt-1">Renseignez vos coordonnées et informations d'adhésion</p>
                        </div>

                        <form action="{{ route('adhesion.next', $token) }}" method="POST">
                            @csrf
                            <input type="hidden" name="current_step" value="3">

                            <div class="mb-5">
                                <label class="{{ $label }}">Genre</label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    @foreach (['Homme' => '🧔', 'Femme' => '👩', 'Autre' => '🧑'] as $val => $icon)
                                        <label class="cursor-pointer block group">
                                            <input type="radio" name="genre" value="{{ $val }}"
                                                {{ ($formData['genre'] ?? '') === $val ? 'checked' : '' }}
                                                class="sr-only peer">
                                            <div
                                                class="border-2 rounded-xl p-3 text-center peer-checked:border-teal-600 peer-checked:bg-teal-50 peer-checked:ring-2 ring-teal-600/20 border-gray-200 group-hover:border-slate-900 transition-all">
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
                                    <label class="{{ $label }}">📞 Téléphone</label>
                                    <input type="tel" name="tel" value="{{ $formData['tel'] ?? '' }}"
                                        placeholder="06 00 00 00 00" class="{{ $field }}">
                                </div>
                                <div>
                                    <label class="{{ $label }}">📧 Email</label>
                                    <input type="email" name="mail" value="{{ $formData['mail'] ?? '' }}"
                                        placeholder="exemple@mail.com" class="{{ $field }}">
                                </div>
                            </div>

                            <div class="mb-5">
                                <label class="{{ $label }}">🏛️ Régime social</label>
                                <select name="regime_social" class="{{ $field }}">
                                    <option value="">— Sélectionnez votre régime —</option>
                                    @foreach (['Sécurité sociale générale', 'Mutuelle complémentaire', 'CSS / CMU-C', 'MSA (agricole)', 'RSI / Indépendants', 'Autre'] as $r)
                                        <option value="{{ $r }}"
                                            {{ ($formData['regime_social'] ?? '') === $r ? 'selected' : '' }}>
                                            {{ $r }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="p-5 bg-slate-50 rounded-2xl border border-slate-200 mb-6 space-y-4">
                                <p class="text-sm font-bold text-slate-900 mb-1">Autorisations & communications</p>
                                <label class="flex items-start gap-3 cursor-pointer group">
                                    <input type="checkbox" name="bulletin" value="1"
                                        {{ !empty($formData['bulletin']) ? 'checked' : '' }}
                                        class="{{ $check }} mt-0.5">
                                    <span class="text-sm text-gray-700 leading-relaxed">
                                        <strong class="text-slate-900">Bulletin d'information</strong> — J'accepte de
                                        recevoir les bulletins et actualités de l'association par email.
                                    </span>
                                </label>
                                <label class="flex items-start gap-3 cursor-pointer group">
                                    <input type="checkbox" name="communication" value="1"
                                        {{ !empty($formData['communication']) ? 'checked' : '' }}
                                        class="{{ $check }} mt-0.5">
                                    <span class="text-sm text-gray-700 leading-relaxed">
                                        <strong class="text-slate-900">Droit à l'image</strong> — J'autorise l'association
                                        à photographier l'adhérent·e et à diffuser ces images.
                                    </span>
                                </label>
                            </div>

                            <div class="flex items-center justify-between pt-5 border-t border-gray-100">
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
                @elseif($step === 4)
                    <div class="p-6 md:p-8">
                        <div class="mb-6">
                            <h2 class="text-2xl font-bold text-slate-900">Informations médicales 🏥</h2>
                            <p class="text-gray-500 mt-1">Sécurité de l'enfant lors des activités</p>
                        </div>

                        <form action="{{ route('adhesion.next', $token) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="current_step" value="4">

                            <div class="mb-6">
                                <label class="{{ $label }}">📷 Photo des vaccins sur le carnet de santé</label>
                                @if (!empty($formData['carnet_sante_path']))
                                    <div
                                        class="mb-4 p-4 bg-teal-50 border border-teal-200 rounded-xl flex items-center gap-3">
                                        <span class="text-teal-600 text-xl">✅</span>
                                        <span class="text-sm font-semibold text-teal-800">Fichier déjà envoyé. Vous pouvez
                                            le remplacer ci-dessous.</span>
                                    </div>
                                @endif
                                <div class="border-2 border-dashed border-gray-300 rounded-2xl p-8 text-center hover:border-teal-600 hover:bg-teal-50/50 transition-all cursor-pointer group"
                                    x-data="{ preview: null }" @click="$refs.fileInput.click()">
                                    <input type="file" name="carnet_sante" accept="image/*,.pdf" class="hidden"
                                        x-ref="fileInput"
                                        @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                                    <template x-if="!preview">
                                        <div>
                                            <div class="text-5xl mb-3 group-hover:scale-110 transition-transform">📁</div>
                                            <p class="text-base font-bold text-slate-700">Cliquez pour déposer l'image
                                            </p>
                                            <p class="text-sm text-gray-400 mt-1 font-medium">JPG, PNG ou PDF — max 10 Mo
                                            </p>
                                        </div>
                                    </template>
                                    <template x-if="preview">
                                        <img :src="preview"
                                            class="max-h-48 mx-auto rounded-xl shadow-sm object-contain border border-gray-200">
                                    </template>
                                </div>
                            </div>

                            <div class="mb-5">
                                <label class="{{ $label }}">⚕️ Problèmes de santé à signaler</label>
                                <textarea name="problemes_sante" rows="3" placeholder="Ex : asthme, épilepsie, diabète, problèmes cardiaques…"
                                    class="{{ $field }}">{{ $formData['problemes_sante'] ?? '' }}</textarea>
                            </div>
                            <div class="mb-6">
                                <label class="{{ $label }}">🤧 Allergies connues</label>
                                <textarea name="allergies" rows="3" placeholder="Ex : arachides, pollen, latex, médicaments…"
                                    class="{{ $field }}">{{ $formData['allergies'] ?? '' }}</textarea>
                            </div>

                            <div class="flex items-center justify-between pt-5 border-t border-gray-100">
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
                @elseif($step === 5)
                    <div class="p-6 md:p-8">
                        <div class="mb-6">
                            <h2 class="text-2xl font-bold text-slate-900">Situation actuelle 💼</h2>
                            <p class="text-gray-500 mt-1">Indiquez votre niveau scolaire ou situation professionnelle</p>
                        </div>

                        <form action="{{ route('adhesion.next', $token) }}" method="POST">
                            @csrf
                            <input type="hidden" name="current_step" value="5">

                            @if ($isMineur)
                                <div x-data="{ occupation: '{{ $formData['occupation'] ?? '' }}' }">
                                    <p class="text-sm font-bold text-slate-700 mb-3">📚 Niveau scolaire</p>
                                    <div class="grid grid-cols-2 gap-3 mb-5">
                                        @foreach ([['val' => 'Maternelle', 'desc' => '3 à 5 ans'], ['val' => 'Primaire', 'desc' => '6 à 10 ans'], ['val' => 'Collège', 'desc' => '11 à 14 ans'], ['val' => 'Lycée', 'desc' => '15 à 17 ans'], ['val' => 'École à la maison', 'desc' => 'Tout âge']] as $o)
                                            <label class="cursor-pointer block group">
                                                <input type="radio" name="occupation" value="{{ $o['val'] }}"
                                                    x-model="occupation" class="sr-only">
                                                <div :class="occupation === '{{ $o['val'] }}' ?
                                                    'border-teal-600 bg-teal-50 ring-2 ring-teal-600/20' :
                                                    'border-gray-200 group-hover:border-slate-900'"
                                                    class="border-2 rounded-2xl p-4 text-center transition-all">
                                                    <div class="font-bold text-sm text-slate-900">{{ $o['val'] }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 font-medium mt-1">
                                                        {{ $o['desc'] }}</div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>

                                    <div x-show="occupation !== ''" x-transition class="mb-5">
                                        <label class="{{ $label }}">🏫 Établissement scolaire</label>
                                        <input type="text" name="etablissement"
                                            value="{{ $formData['etablissement'] ?? '' }}"
                                            placeholder="Nom de l'école / collège / lycée" class="{{ $field }}">
                                    </div>
                                </div>
                            @else
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6">
                                    @php
                                        $adulteOptions = [
                                            ['val' => 'Étudiant', 'icon' => '🎓'],
                                            ['val' => 'Sans emploi', 'icon' => '🔍'],
                                            ['val' => 'Chômeur', 'icon' => '📋'],
                                            ['val' => 'Retraité', 'icon' => '🌅'],
                                            ['val' => 'Fonctionnaire', 'icon' => '🏛️'],
                                            ['val' => 'Chef d\'entreprise', 'icon' => '🏢'],
                                            ['val' => 'Salarié', 'icon' => '💼'],
                                            ['val' => 'Cadre', 'icon' => '📊'],
                                            ['val' => 'Employé', 'icon' => '🖥️'],
                                            ['val' => 'Ouvrier', 'icon' => '🔨'],
                                            ['val' => 'Profession libérale', 'icon' => '⚖️'],
                                        ];
                                    @endphp
                                    @foreach ($adulteOptions as $o)
                                        <label class="cursor-pointer block group">
                                            <input type="radio" name="occupation" value="{{ $o['val'] }}"
                                                {{ ($formData['occupation'] ?? '') === $o['val'] ? 'checked' : '' }}
                                                class="sr-only peer">
                                            <div
                                                class="border-2 rounded-xl p-3 text-center transition-all peer-checked:border-teal-600 peer-checked:bg-teal-50 peer-checked:ring-2 ring-teal-600/20 border-gray-200 group-hover:border-slate-900">
                                                <div class="text-2xl mb-1">{{ $o['icon'] }}</div>
                                                <div class="font-bold text-xs text-slate-900">{{ $o['val'] }}</div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            @endif

                            <div class="flex items-center justify-between pt-5 border-t border-gray-100">
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
                @elseif($step === 6)
                    <div class="p-6 md:p-8">
                        @php $typeActivite = $formData['type_activite'] ?? ''; @endphp
                        <div class="mb-6">
                            <h2 class="text-2xl font-bold text-slate-900">
                                @if ($typeActivite === 'atelier')
                                    Choisissez votre atelier 🔧
                                @else
                                    Choisissez votre stage 🎭
                                @endif
                            </h2>
                            <p class="text-gray-500 mt-1">
                                @if ($typeActivite === 'atelier')
                                    Sélectionnez le ou les ateliers auxquels vous souhaitez vous inscrire
                                @else
                                    Sélectionnez le stage qui vous intéresse
                                @endif
                            </p>
                        </div>

                        <form action="{{ route('adhesion.next', $token) }}" method="POST">
                            @csrf
                            <input type="hidden" name="current_step" value="6">

                            @php
                                $liste = $typeActivite === 'stage' ? $stages : $ateliers;
                                $selectedActivites = $formData['activites_selectionnees'] ?? [];
                                if (!is_array($selectedActivites)) {
                                    $selectedActivites = [];
                                }
                            @endphp

                            @if ($liste->isEmpty())
                                <div class="p-8 text-center bg-slate-50 rounded-2xl border border-slate-200 mb-6">
                                    <div class="text-5xl mb-3">😕</div>
                                    <p class="text-slate-900 font-bold text-lg">Aucune activité disponible pour le moment.
                                    </p>
                                    <p class="text-gray-500 text-sm mt-2">Contactez-nous pour plus d'informations.</p>
                                </div>
                            @else
                                <div class="space-y-4 mb-6">
                                    @foreach ($liste as $activite)
                                        @php
                                            $horaires = is_string($activite->horaires)
                                                ? json_decode($activite->horaires, true)
                                                : $activite->horaires ?? [];
                                            $hasMultipleSlots =
                                                $typeActivite === 'atelier' &&
                                                is_array($horaires) &&
                                                count($horaires) > 1;
                                        @endphp
                                        <div x-data="{ checked: {{ in_array($activite->id, $selectedActivites) ? 'true' : 'false' }} }">
                                            <input type="checkbox" name="activites_selectionnees[]"
                                                value="{{ $activite->id }}" x-model="checked" class="hidden">
                                            <div :class="checked ? 'border-teal-600 bg-teal-50 ring-2 ring-teal-600/20' :
                                                'border-gray-200 hover:border-slate-900 bg-white'"
                                                class="border-2 rounded-2xl p-5 transition-all flex flex-col gap-4 shadow-sm hover:shadow-md">
                                                <div @click="checked = !checked"
                                                    class="flex items-start gap-4 cursor-pointer w-full">
                                                    <div
                                                        class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 text-2xl bg-slate-900 text-white shadow-sm">
                                                        {{ $typeActivite === 'stage' ? '🎭' : '🔧' }}
                                                    </div>
                                                    <div class="flex-1">
                                                        <h4 class="font-bold text-slate-900 text-lg">{{ $activite->nom }}
                                                        </h4>
                                                        @if ($activite->adresse)
                                                            <p class="text-sm text-gray-500 font-medium mt-1">📍
                                                                {{ $activite->adresse }}, {{ $activite->ville }}</p>
                                                        @endif

                                                        @if ($horaires && count($horaires) > 0)
                                                            <div class="flex flex-wrap gap-2 mt-3">
                                                                @foreach ($horaires as $jour => $heure)
                                                                    <span
                                                                        class="inline-block bg-white border border-gray-200 text-slate-700 font-semibold text-xs px-2.5 py-1 rounded-lg shadow-sm">
                                                                        🕐 {{ $jour }} {{ $heure }}
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        @endif

                                                        @if ($activite->tarif !== null)
                                                            <p class="text-sm font-black text-teal-600 mt-3">
                                                                {{ $activite->tarif > 0 ? number_format($activite->tarif, 2, ',', ' ') . ' €' : 'Gratuit' }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                    <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors mt-1"
                                                        :class="checked ? 'border-teal-600 bg-teal-600' : 'border-gray-300'">
                                                        <svg x-show="checked" class="w-3 h-3 text-white"
                                                            fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd"
                                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                    </div>
                                                </div>

                                                @if ($hasMultipleSlots)
                                                    <div x-show="checked" x-transition
                                                        class="w-full pt-4 border-t border-teal-200/60 mt-1">
                                                        <p class="text-sm font-bold text-slate-800 mb-3">📅 Choisissez
                                                            votre créneau pour cet atelier :</p>
                                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                            @foreach ($horaires as $jour => $heure)
                                                                <label
                                                                    class="flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-xl cursor-pointer hover:border-teal-600 transition-all has-[:checked]:border-teal-600 has-[:checked]:ring-1 has-[:checked]:ring-teal-600">
                                                                    <input type="radio"
                                                                        name="horaires_selectionnes[{{ $activite->id }}]"
                                                                        value="{{ $jour }} - {{ $heure }}"
                                                                        {{ ($formData['horaires_selectionnes'][$activite->id] ?? '') === "$jour - $heure" ? 'checked' : '' }}
                                                                        class="{{ $radio }}"
                                                                        :disabled="!checked" required>
                                                                    <span class="text-sm font-semibold text-slate-700">
                                                                        {{ $jour }} <span
                                                                            class="text-teal-600">{{ $heure }}</span>
                                                                    </span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @elseif($typeActivite === 'atelier' && count($horaires) === 1)
                                                    @php $uniqueSlot = array_key_first($horaires) . ' - ' . $horaires[array_key_first($horaires)]; @endphp
                                                    <input type="hidden"
                                                        name="horaires_selectionnes[{{ $activite->id }}]"
                                                        value="{{ $uniqueSlot }}" :disabled="!checked">
                                                @endif

                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="flex items-center justify-between pt-5 border-t border-gray-100">
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
                @elseif($step === 7)
                    <div class="p-6 md:p-8">
                        <div class="mb-6">
                            <h2 class="text-2xl font-bold text-slate-900">Implication bénévole 🤝</h2>
                            <p class="text-gray-500 mt-1">Souhaitez-vous vous investir dans l'association ? (facultatif)
                            </p>
                        </div>

                        <form action="{{ route('adhesion.next', $token) }}" method="POST">
                            @csrf
                            <input type="hidden" name="current_step" value="7">

                            <div class="mb-8">
                                <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-3">
                                    <span
                                        class="w-8 h-8 rounded-xl bg-slate-900 text-white text-sm flex items-center justify-center font-bold shadow-md">1</span>
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
                                            class="flex items-center gap-4 p-4 border-2 border-gray-100 rounded-xl cursor-pointer hover:border-teal-600 hover:bg-teal-50 transition-all group">
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

                            <div class="p-6 bg-slate-50 rounded-2xl border border-slate-200 mb-6">
                                <h3 class="font-bold text-slate-900 mb-3 flex items-center gap-3">
                                    <span
                                        class="w-8 h-8 rounded-xl bg-slate-900 text-white text-sm flex items-center justify-center font-bold shadow-md">2</span>
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
                                        class="flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-xl cursor-pointer hover:border-teal-600 transition-all flex-1">
                                        <input type="radio" name="participation_manif" value="1"
                                            {{ ($formData['participation_manif'] ?? '') === '1' ? 'checked' : '' }}
                                            class="{{ $radio }}">
                                        <span class="text-sm font-bold text-slate-900">✅ Oui, intéressé·e</span>
                                    </label>
                                    <label
                                        class="flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-xl cursor-pointer hover:border-slate-900 transition-all flex-1">
                                        <input type="radio" name="participation_manif" value="0"
                                            {{ ($formData['participation_manif'] ?? '') === '0' ? 'checked' : '' }}
                                            class="{{ $radio }}">
                                        <span class="text-sm font-bold text-slate-900">❌ Non, merci</span>
                                    </label>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-5 border-t border-gray-100">
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
                @elseif($step === 8)
                    <div class="p-6 md:p-8">
                        <div class="mb-6">
                            <h2 class="text-2xl font-bold text-slate-900">Fiche parents et/ou tuteur·trice·s 👨‍👩‍👧</h2>
                            <p class="text-gray-500 mt-1">Responsables légaux de <strong
                                    class="text-slate-900">{{ ($formData['prenom'] ?? '') . ' ' . ($formData['nom'] ?? '') }}</strong>
                            </p>
                        </div>

                        <form action="{{ route('adhesion.next', $token) }}" method="POST" id="form-tuteurs">
                            @csrf
                            <input type="hidden" name="current_step" value="8">

                            <div x-data="tuteurManager()" x-init="init()">
                                <template x-for="(tuteur, i) in tuteurs" :key="i">
                                    <div
                                        class="border-2 border-gray-200 rounded-3xl p-6 mb-5 bg-white relative hover:border-slate-300 transition-colors">
                                        <div class="flex items-center justify-between mb-5">
                                            <h3 class="font-bold text-slate-900 text-lg flex items-center gap-3">
                                                <span
                                                    class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold shadow-md"
                                                    x-text="'P' + (i+1)"></span>
                                                <span x-text="'Personne ' + (i+1)"></span>
                                            </h3>
                                            <button type="button" @click="removeTuteur(i)" x-show="tuteurs.length > 1"
                                                class="text-red-500 bg-red-50 px-3 py-1.5 rounded-lg font-bold text-sm hover:bg-red-500 hover:text-white transition-colors flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Retirer
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                            <div>
                                                <label class="{{ $label }}">Nom *</label>
                                                <input type="text" :name="'tuteurs[' + i + '][nom]'"
                                                    x-model="tuteur.nom" placeholder="Nom de famille"
                                                    class="{{ $field }}" required>
                                            </div>
                                            <div>
                                                <label class="{{ $label }}">Prénom *</label>
                                                <input type="text" :name="'tuteurs[' + i + '][prenom]'"
                                                    x-model="tuteur.prenom" placeholder="Prénom"
                                                    class="{{ $field }}" required>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                                            <div>
                                                <label class="{{ $label }}">📞 Téléphone</label>
                                                <input type="tel" :name="'tuteurs[' + i + '][tel]'"
                                                    x-model="tuteur.tel" placeholder="06 00 00 00 00"
                                                    class="{{ $field }}">
                                            </div>
                                            <div>
                                                <label class="{{ $label }}">📧 Email</label>
                                                <input type="email" :name="'tuteurs[' + i + '][mail]'"
                                                    x-model="tuteur.mail" placeholder="email@exemple.com"
                                                    class="{{ $field }}">
                                            </div>
                                        </div>

                                        <div class="space-y-3 mb-6 p-5 bg-slate-50 border border-slate-200 rounded-2xl">
                                            <label class="flex items-center gap-3 cursor-pointer group">
                                                <input type="checkbox" :name="'tuteurs[' + i + '][adhere]'"
                                                    value="1" :checked="tuteur.adhere"
                                                    @change="tuteur.adhere = $event.target.checked"
                                                    class="{{ $check }}">
                                                <span class="text-sm font-semibold text-slate-800">J'autorise mon enfant à
                                                    adhérer à l'association Savoirs Vivants</span>
                                            </label>
                                            <label class="flex items-center gap-3 cursor-pointer group">
                                                <input type="checkbox" :name="'tuteurs[' + i + '][rentre_fin]'"
                                                    value="1" :checked="tuteur.rentre_fin"
                                                    @change="tuteur.rentre_fin = $event.target.checked"
                                                    class="{{ $check }}">
                                                <span class="text-sm font-semibold text-slate-800">J'autorise mon enfant à
                                                    rentrer seul·e à la fin de l'activité</span>
                                            </label>
                                            <label class="flex items-center gap-3 cursor-pointer group">
                                                <input type="checkbox" :name="'tuteurs[' + i + '][rentre_annul]'"
                                                    value="1" :checked="tuteur.rentre_annul"
                                                    @change="tuteur.rentre_annul = $event.target.checked"
                                                    class="{{ $check }}">
                                                <span class="text-sm font-semibold text-slate-800">J'autorise mon enfant à
                                                    rentrer seul·e en cas d'annulation</span>
                                            </label>
                                        </div>

                                        <div class="mb-5">
                                            <label class="{{ $label }}">📅 Date</label>
                                            <input type="date" :name="'tuteurs[' + i + '][date_signature]'"
                                                x-model="tuteur.date_signature" class="{{ $field }} max-w-xs">
                                        </div>

                                        <div>
                                            <label class="{{ $label }}">✍️ Signature du/de la tuteur·trice</label>
                                            <div class="border-2 border-dashed border-gray-300 rounded-2xl p-2 bg-gray-50 relative overflow-hidden"
                                                style="max-width: 400px;">
                                                <canvas :id="'canvas-tuteur-' + i"
                                                    class="w-full touch-none bg-white rounded-xl cursor-crosshair shadow-sm border border-gray-100"
                                                    style="height: 120px; display: block;"></canvas>
                                                <button type="button" @click="clearCanvas(i)"
                                                    class="absolute top-4 right-4 bg-white border border-gray-200 text-xs font-bold text-gray-500 hover:text-red-500 hover:border-red-200 px-2 py-1 rounded shadow-sm transition">
                                                    Effacer
                                                </button>
                                            </div>
                                            <input type="hidden" :name="'tuteurs[' + i + '][signature]'"
                                                :id="'sig-data-tuteur-' + i" x-model="tuteur.signature">
                                        </div>
                                    </div>
                                </template>

                                <button type="button" @click="addTuteur()"
                                    class="w-full border-2 border-dashed border-teal-600 text-teal-700 bg-teal-50 font-bold rounded-2xl py-4 px-4 hover:bg-teal-600 hover:text-white transition-colors flex items-center justify-center gap-2 mb-4">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Ajouter un·e parent / tuteur·trice
                                </button>

                                <button type="button" @click="addTuteur()"
                                    class="w-full border-2 border-dashed border-teal-600 text-teal-700 bg-teal-50 font-bold rounded-2xl py-4 px-4 hover:bg-teal-600 hover:text-white transition-colors flex items-center justify-center gap-2 mb-4">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Ajouter un·e adulte autorisé à récupérer l'enfant
                                </button>

                                <button type="button" @click="addTuteur()"
                                    class="w-full border-2 border-dashed border-teal-600 text-teal-700 bg-teal-50 font-bold rounded-2xl py-4 px-4 hover:bg-teal-600 hover:text-white transition-colors flex items-center justify-center gap-2 mb-4">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Ajouter un·e adulte non autorisé à récupérer l'enfant
                                </button>
                            </div>

                            <div class="flex items-center justify-between pt-5 border-t border-gray-100">
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
                @elseif($step === 9)
                    <div class="p-6 md:p-8">
                        <div class="mb-6">
                            <h2 class="text-2xl font-bold text-slate-900">Votre signature ✍️</h2>
                            <p class="text-gray-500 mt-1">Signez ci-dessous pour valider la fiche d'adhésion</p>
                        </div>

                        <form action="{{ route('adhesion.next', $token) }}" method="POST" id="form-signature">
                            @csrf
                            <input type="hidden" name="current_step" value="9">

                            <div class="p-5 bg-amber-50 rounded-2xl border border-amber-200 mb-6 flex items-start gap-4">
                                <span class="text-2xl">⚠️</span>
                                <p class="text-sm font-bold text-amber-800 leading-relaxed">
                                    En signant ce formulaire, vous certifiez l'exactitude des informations renseignées et
                                    acceptez le règlement intérieur de l'association.
                                </p>
                            </div>

                            <div class="mb-6">
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

                            <div class="flex items-center justify-between pt-5 border-t border-gray-100">
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
                @elseif($step === 10)
                    <div class="p-6 md:p-8">
                        <div class="mb-6">
                            <h2 class="text-2xl font-bold text-slate-900">Choix du paiement 💳</h2>
                            <p class="text-gray-500 mt-1">Comment souhaitez-vous régler votre adhésion ?</p>
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
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">

                                    <label class="cursor-pointer block group">
                                        <input type="radio" name="mode_paiement" value="helloasso"
                                            x-model="modePaiement" class="sr-only">
                                        <div :class="modePaiement === 'helloasso' ?
                                            'border-teal-600 bg-teal-50 ring-2 ring-teal-600/20' :
                                            'border-gray-200 group-hover:border-slate-900'"
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
                                            'border-gray-200 group-hover:border-slate-900'"
                                            class="{{ $card }} items-center text-center">
                                            <div class="text-4xl mb-3">🤝</div>
                                            <h3 class="text-lg font-bold text-slate-900">Paiement en personne</h3>
                                            <p class="text-gray-500 text-sm mt-2">Chèque, espèces ou virement</p>
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

                                <div class="flex items-center justify-between pt-5 border-t border-gray-100">
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
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                @elseif($step === 11)
                    <div class="p-8 md:p-12 text-center">
                        <div
                            class="w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6 text-5xl bg-teal-50 shadow-inner">
                            ✅
                        </div>

                        @php
                            $isAdherentExistant = ($formData['is_adherent'] ?? 'non') === 'oui';
                            $prenomNom = $isAdherentExistant
                                ? ''
                                : ($formData['prenom'] ?? '') . ' ' . ($formData['nom'] ?? '');
                        @endphp

                        <h2 class="text-3xl font-black text-slate-900 mb-3">Demande envoyée !</h2>

                        @if ($prenomNom)
                            <p class="text-slate-600 text-lg mb-2">
                                Merci <strong class="text-teal-600">{{ $prenomNom }}</strong> !
                            </p>
                        @endif

                        <p class="text-gray-500 font-medium mb-8 max-w-lg mx-auto leading-relaxed">
                            @if ($isAdherentExistant)
                                Votre demande d'inscription pour cette nouvelle activité a bien été transmise à notre
                                équipe. Elle sera traitée dans les plus brefs délais.
                            @else
                                Votre demande d'adhésion a bien été transmise à notre équipe. Elle sera traitée dans les
                                plus brefs délais. Vous recevrez un e-mail de confirmation dès la validation.
                            @endif
                        </p>

                        @if (($formData['mode_paiement'] ?? '') === 'interne')
                            <div class="p-6 bg-slate-50 border border-slate-200 rounded-3xl mb-8 text-left">
                                <h4 class="font-bold text-slate-900 mb-2 flex items-center gap-2 text-lg">
                                    <span>💳</span> Étape suivante : règlement
                                </h4>
                                <p class="text-sm font-medium text-slate-700 mb-4">
                                    Vous avez choisi le paiement en personne. Contactez-nous pour fixer un rendez-vous :
                                </p>
                                <a href="mailto:contact@savoirsvivants.fr"
                                    class="inline-flex items-center gap-2 bg-slate-900 text-white text-sm font-bold px-5 py-3 rounded-xl hover:bg-teal-600 shadow-md transition-colors">
                                    ✉️ contact@savoirsvivants.fr
                                </a>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-left mb-8">
                            <div
                                class="p-5 bg-white rounded-2xl border border-gray-200 text-center shadow-sm {{ $isAdherentExistant ? 'sm:col-span-3 sm:max-w-xs sm:mx-auto' : '' }}">
                                <div class="text-3xl mb-2">📧</div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Email de
                                    confirmation</p>
                            </div>

                            @if (!$isAdherentExistant)
                                <div class="p-5 bg-white rounded-2xl border border-gray-200 text-center shadow-sm">
                                    <div class="text-3xl mb-2">🪪</div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Numéro
                                        d'adhérent</p>
                                    <p class="text-sm font-bold text-slate-900 mt-1">À réception du mail</p>
                                </div>
                                <div class="p-5 bg-white rounded-2xl border border-gray-200 text-center shadow-sm">
                                    <div class="text-3xl mb-2">🎉</div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Bienvenue !</p>
                                    <p class="text-sm font-bold text-teal-600 mt-1">Après validation</p>
                                </div>
                            @endif
                        </div>

                        <p class="text-sm font-bold text-gray-400">
                            Une question ? Écrivez-nous à <a href="mailto:contact@savoirsvivants.fr"
                                class="text-teal-600 hover:text-slate-900 transition-colors">contact@savoirsvivants.fr</a>
                        </p>
                    </div>
                @endif

            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/4.1.7/signature_pad.umd.min.js"></script>

    <script>
        (function() {
            const canvas = document.getElementById('canvas-adherent');
            if (!canvas) return;

            const sigPad = new SignaturePad(canvas, {
                penColor: '#0f172a',
                backgroundColor: 'rgba(255,255,255,1)'
            });

            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                const data = sigPad.toData();
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext('2d').scale(ratio, ratio);
                sigPad.clear();
                sigPad.fromData(data);
            }
            window.addEventListener('resize', resizeCanvas);
            resizeCanvas();

            const existingData = document.getElementById('sig-data-adherent').value;
            if (existingData && existingData.startsWith('data:')) {
                sigPad.fromDataURL(existingData);
            }

            document.getElementById('form-signature')?.addEventListener('submit', function() {
                if (!sigPad.isEmpty()) {
                    document.getElementById('sig-data-adherent').value = sigPad.toDataURL();
                }
            });

            document.getElementById('clear-sig-adherent')?.addEventListener('click', function() {
                sigPad.clear();
                document.getElementById('sig-data-adherent').value = '';
            });
        })();

        function tuteurManager() {
            const existing = @json($formData['tuteurs'] ?? null);

            return {
                tuteurs: [],
                sigPads: {},

                init() {
                    if (existing && Array.isArray(existing) && existing.length > 0) {
                        this.tuteurs = existing;
                    } else {
                        this.tuteurs = [this.emptyTuteur()];
                    }
                    this.$nextTick(() => {
                        this.tuteurs.forEach((_, i) => this.initSigPad(i));
                    });
                },

                emptyTuteur() {
                    return {
                        nom: '',
                        prenom: '',
                        tel: '',
                        mail: '',
                        nom_enfant: '{{ ($formData['prenom'] ?? '') . ' ' . ($formData['nom'] ?? '') }}',
                        adhere: false,
                        rentre_fin: false,
                        rentre_annul: false,
                        date_signature: new Date().toISOString().split('T')[0],
                        signature: ''
                    };
                },

                addTuteur() {
                    this.tuteurs.push(this.emptyTuteur());
                    const newIdx = this.tuteurs.length - 1;
                    this.$nextTick(() => this.initSigPad(newIdx));
                },

                removeTuteur(i) {
                    if (this.tuteurs.length <= 1) return;
                    if (this.sigPads[i]) {
                        delete this.sigPads[i];
                    }
                    this.tuteurs.splice(i, 1);
                },

                initSigPad(i) {
                    const canvas = document.getElementById('canvas-tuteur-' + i);
                    if (!canvas || this.sigPads[i]) return;

                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    canvas.width = canvas.offsetWidth * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    canvas.getContext('2d').scale(ratio, ratio);

                    const sp = new SignaturePad(canvas, {
                        penColor: '#0f172a',
                        backgroundColor: 'rgba(255,255,255,1)'
                    });
                    this.sigPads[i] = sp;

                    if (this.tuteurs[i]?.signature) {
                        sp.fromDataURL(this.tuteurs[i].signature);
                    }

                    sp.addEventListener('endStroke', () => {
                        document.getElementById('sig-data-tuteur-' + i).value = sp.toDataURL();
                    });
                },

                clearCanvas(i) {
                    if (this.sigPads[i]) {
                        this.sigPads[i].clear();
                        this.tuteurs[i].signature = '';
                        document.getElementById('sig-data-tuteur-' + i).value = '';
                    }
                },
            };
        }
    </script>

@endsection
