@extends('layouts.app')

@section('title', 'Formulaire d\'adhésion')

@section('content')

    @php
        $field =
            'w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-gray-800 bg-white focus:outline-none focus:ring-2 focus:ring-teal-500/25 focus:border-teal-500 transition-colors text-sm placeholder:text-gray-400';
        $label = 'block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide';
        $card =
            'border-2 rounded-xl p-4 transition-all duration-150 h-full flex flex-col bg-white cursor-pointer select-none';
        $btn =
            'inline-flex items-center justify-center gap-2 bg-teal-600 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-teal-700 active:scale-95 focus:ring-2 focus:ring-teal-500/30 transition text-sm shadow-sm';
        $btnBack =
            'inline-flex items-center justify-center gap-2 text-gray-500 font-medium px-4 py-2.5 rounded-lg border border-gray-200 hover:bg-gray-50 hover:text-gray-700 transition text-sm';
        $check = 'h-4 w-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500 cursor-pointer';
        $radio = 'h-4 w-4 border-gray-300 text-teal-600 focus:ring-teal-500 cursor-pointer';
    @endphp

    <div class="min-h-screen bg-gray-50 py-6 px-4" style="font-family: 'Space Grotesk', sans-serif;">
        <div class="max-w-xl mx-auto">

            <div class="text-center mb-5">
                <h1 class="text-2xl font-bold text-gray-900">📝 Formulaire d'adhésion</h1>
                <p class="text-gray-500 mt-1 text-sm">Remplissez les informations étape par étape</p>
            </div>

            @if ($step !== 11)
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 pt-4 pb-5 mb-5">

                    <div class="flex items-center justify-between mb-2.5">
                        <p class="text-xs text-gray-400 font-medium">
                            Étape <span class="font-bold text-gray-700">{{ $currentNum }}</span>
                            <span class="text-gray-300 mx-1">/</span>
                            <span class="text-gray-500">{{ $totalSteps }}</span>
                        </p>
                        <span class="text-xs font-semibold text-teal-700 bg-teal-50 border border-teal-100 px-2.5 py-1 rounded-full">
                            {{ $stepMeta[$step]['icon'] }} {{ $stepMeta[$step]['label'] }}
                        </span>
                    </div>

                    <div class="w-full bg-gray-100 rounded-full h-1.5 mb-4">
                        <div class="h-1.5 rounded-full bg-teal-500 transition-all duration-500"
                            style="width: {{ ($currentNum / $totalSteps) * 100 }}%;"></div>
                    </div>

                    <div class="flex items-center">
                        @foreach ($path as $i => $s)
                            @php
                                $pathIdx = array_search($step, $path);
                                $isDone    = $i < $pathIdx;
                                $isCurrent = $s === $step;
                            @endphp

                            @if ($i > 0)
                                <div class="h-px flex-1 min-w-[6px] transition-colors duration-300
                                    {{ $isDone ? 'bg-teal-400' : 'bg-gray-200' }}"></div>
                            @endif

                            <div class="shrink-0 rounded-full transition-all duration-300
                                {{ $isCurrent
                                    ? 'w-3 h-3 bg-teal-600 ring-2 ring-offset-1 ring-teal-300'
                                    : ($isDone
                                        ? 'w-2.5 h-2.5 bg-teal-400'
                                        : 'w-2 h-2 bg-gray-200') }}"
                                title="{{ $stepMeta[$s]['label'] }}">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                @if ($step === 1)
                    <div class="p-5 md:p-6">
                        <div class="mb-5">
                            <h2 class="text-xl font-bold text-gray-900">Bienvenue ! 👋</h2>
                            <p class="text-gray-400 mt-1 text-sm">Êtes-vous déjà adhérent·e de notre association ?</p>
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

                                <div class="flex justify-end pt-4 border-t border-gray-100 mt-1">
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
                @elseif($step === 12)
                    <div class="p-5 md:p-6">
                        <div class="mb-5">
                            <h2 class="text-xl font-bold text-gray-900">Votre profil 🏢</h2>
                            <p class="text-gray-400 mt-1 text-sm">Quel est votre statut juridique ?</p>
                        </div>

                        <form action="{{ route('adhesion.next', $token) }}" method="POST">
                            @csrf
                            <input type="hidden" name="current_step" value="12">

                            <div x-data="{ statut_juridique: '{{ $formData['statut_juridique'] ?? '' }}' }">
                                <div class="grid grid-cols-1 gap-4 mb-6">

                                    @php
                                        $statutsJuridiques = [
                                            [
                                                'value' => 'personne_physique',
                                                'label' => 'Personne physique',
                                                'icon'  => '👤',
                                                'desc'  => 'Particulier s\'inscrivant à titre personnel',
                                            ],
                                            [
                                                'value' => 'tpe_asso',
                                                'label' => 'TPE / Association',
                                                'icon'  => '🤝',
                                                'desc'  => 'Très petite entreprise ou association',
                                            ],
                                            [
                                                'value' => 'esr_pme',
                                                'label' => 'ESR / PME',
                                                'icon'  => '🏭',
                                                'desc'  => 'Établissement de Recherche Supérieur ou PME',
                                            ],
                                        ];
                                    @endphp

                                    @foreach ($statutsJuridiques as $sj)
                                        <label class="cursor-pointer block group">
                                            <input type="radio" name="statut_juridique"
                                                value="{{ $sj['value'] }}"
                                                x-model="statut_juridique" class="sr-only">
                                            <div :class="statut_juridique === '{{ $sj['value'] }}' ?
                                                    'border-teal-600 bg-teal-50 ring-2 ring-teal-600/20' :
                                                    'border-gray-200 group-hover:border-teal-400'"
                                                class="border-2 rounded-xl p-4 flex items-center gap-4 transition-all bg-white">
                                                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-xl shrink-0">
                                                    {{ $sj['icon'] }}
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h3 class="font-bold text-slate-900 text-base">{{ $sj['label'] }}</h3>
                                                    <p class="text-gray-500 text-sm mt-0.5">{{ $sj['desc'] }}</p>
                                                </div>
                                                <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors"
                                                    :class="statut_juridique === '{{ $sj['value'] }}' ?
                                                        'border-teal-600 bg-teal-600' : 'border-gray-300'">
                                                    <svg x-show="statut_juridique === '{{ $sj['value'] }}'"
                                                        class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
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
                            </div>
                        </form>
                    </div>

                @elseif($step === 2)
                    <div class="p-5 md:p-6">
                        <div class="mb-5">
                            <h2 class="text-xl font-bold text-gray-900">Pour quelle activité ? 🎯</h2>
                            <p class="text-gray-400 mt-1 text-sm">Choisissez le type d'inscription souhaité</p>
                        </div>

                        <form action="{{ route('adhesion.next', $token) }}" method="POST">
                            @csrf
                            <input type="hidden" name="current_step" value="2">

                            <div x-data="{ activite: '{{ $formData['type_activite'] ?? '' }}' }">
                                @php
                                    $statutJuridique  = $formData['statut_juridique'] ?? 'personne_physique';
                                    $isStructureStep2 = in_array($statutJuridique, ['tpe_asso', 'esr_pme']);
                                    $isDejaAdherent   = ($formData['is_adherent'] ?? 'non') === 'oui';

                                    if ($isStructureStep2) {
                                        $options = [
                                            [
                                                'value' => 'ressourcerie',
                                                'label' => 'Ressourcerie',
                                                'icon'  => '🤖',
                                                'desc'  => 'Louer du matériel pédagogique robotique',
                                            ],
                                            [
                                                'value' => 'recherche',
                                                'label' => 'Programme de recherche',
                                                'icon'  => '🔬',
                                                'desc'  => 'Participer à un programme de recherche participative' . ($isDejaAdherent ? ' (gratuit)' : ''),
                                            ],
                                        ];
                                        if (!$isDejaAdherent) {
                                            $options[] = [
                                                'value' => 'soutien',
                                                'label' => 'Adhésion par soutien',
                                                'icon'  => '🤝',
                                                'desc'  => 'Soutenir financièrement l\'association',
                                            ];
                                        }
                                    } else {
                                        $options = [
                                            [
                                                'value' => 'atelier',
                                                'label' => 'Inscription à un atelier',
                                                'icon'  => '🔧',
                                                'desc'  => 'Ateliers robotiques',
                                            ],
                                            [
                                                'value' => 'ressourcerie',
                                                'label' => 'Ressourcerie',
                                                'icon'  => '🤖',
                                                'desc'  => 'Louer un Codey Rocky',
                                            ],
                                            [
                                                'value' => 'stage',
                                                'label' => 'Inscription à un stage',
                                                'icon'  => '📚',
                                                'desc'  => 'Stages sur plusieurs jours',
                                            ],
                                            [
                                                'value' => 'recherche',
                                                'label' => 'Recherche participative',
                                                'icon'  => '🔬',
                                                'desc'  => 'Participer à un programme de recherche' . ($isDejaAdherent ? ' (gratuit)' : ''),
                                            ],
                                        ];
                                        if (!$isDejaAdherent) {
                                            $options[] = [
                                                'value' => 'club_maker',
                                                'label' => 'Club Maker',
                                                'icon'  => '⚙️',
                                                'desc'  => 'Rejoindre le club des makers',
                                            ];
                                            $options[] = [
                                                'value' => 'soutien',
                                                'label' => 'Inscription par soutien',
                                                'icon'  => '🤝',
                                                'desc'  => 'Soutenir financièrement l\'association',
                                            ];
                                        }
                                    }
                                @endphp

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                                    @foreach ($options as $opt)
                                        <label class="cursor-pointer block group">
                                            <input type="radio" name="type_activite" value="{{ $opt['value'] }}"
                                                x-model="activite" class="sr-only">
                                            <div :class="activite === '{{ $opt['value'] }}' ?
                                                'border-teal-600 bg-teal-50 ring-2 ring-teal-600/20' :
                                                'border-gray-200 group-hover:border-teal-400'"
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
                    <div class="p-5 md:p-6">
                        <div class="mb-5">
                            <h2 class="text-xl font-bold text-gray-900">Informations personnelles de l'adhérent 📋</h2>
                            <p class="text-gray-400 mt-1 text-sm">Renseignez vos coordonnées et informations d'adhésion</p>
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
                @elseif($step === 15)
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
                                <textarea name="idee_metier" rows="4"
                                    placeholder="Décris librement tes idées ou aspirations professionnelles..."
                                    class="{{ $field }}">{{ $formData['idee_metier'] ?? '' }}</textarea>
                            </div>

                            <div class="mb-5">
                                <label class="{{ $label }}">Aimerais-tu que ce que tu vas apprendre avec nous te permette de découvrir un métier ou une formation ?</label>
                                <textarea name="decouverte_metier" rows="4"
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
                @elseif($step === 4)
                    <div class="p-5 md:p-6">
                        <div class="mb-5">
                            <h2 class="text-xl font-bold text-gray-900">Informations médicales 🏥</h2>
                            <p class="text-gray-400 mt-1 text-sm">Sécurité de l'enfant lors des activités</p>
                        </div>

                        <form action="{{ route('adhesion.next', $token) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="current_step" value="4">

                            <div class="mb-5">
                                <label class="{{ $label }}">📷 Photo des vaccins sur le carnet de santé</label>
                                @php
                                    $carnetPath   = $formData['carnet_sante_path'] ?? null;
                                    $carnetUrl    = $carnetPath ? asset('storage/' . $carnetPath) : null;
                                    $carnetIsPdf  = $carnetPath && str_ends_with(strtolower($carnetPath), '.pdf');
                                @endphp
                                <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center hover:border-teal-400 hover:bg-teal-50/40 transition-all cursor-pointer group"
                                    x-data="{ preview: @js($carnetIsPdf ? null : $carnetUrl) }" @click="$refs.fileInput.click()">
                                    <input type="file" name="carnet_sante" accept="image/*,.pdf" class="hidden"
                                        x-ref="fileInput"
                                        @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                                    <template x-if="!preview">
                                        @if ($carnetIsPdf)
                                            <div>
                                                <div class="text-4xl mb-2">📄</div>
                                                <p class="text-sm font-semibold text-teal-700">PDF déjà envoyé — cliquez pour le remplacer</p>
                                                <p class="text-xs text-gray-400 mt-1">JPG, PNG ou PDF — max 10 Mo</p>
                                            </div>
                                        @else
                                            <div>
                                                <div class="text-4xl mb-2 group-hover:scale-110 transition-transform">📁</div>
                                                <p class="text-sm font-semibold text-gray-600">Cliquez pour déposer l'image</p>
                                                <p class="text-xs text-gray-400 mt-1">JPG, PNG ou PDF — max 10 Mo</p>
                                            </div>
                                        @endif
                                    </template>
                                    <template x-if="preview">
                                        <div>
                                            <img :src="preview"
                                                class="max-h-48 mx-auto rounded-xl shadow-sm object-contain border border-gray-200">
                                            <p class="text-xs text-teal-600 font-semibold mt-2">✅ Cliquez pour remplacer</p>
                                        </div>
                                    </template>
                                </div>
                                <p class="mt-2 text-xs text-gray-400 flex items-start gap-1.5">
                                    <span class="shrink-0 mt-0.5">ℹ️</span>
                                    <span>Ce champ est <strong class="text-gray-500">facultatif</strong>. Toutefois, disposer d'une copie du carnet de vaccination permet à notre équipe d'alerter rapidement les professionnels de santé compétents en cas de besoin lors d'une activité.</span>
                                </p>
                            </div>

                            <div x-data="{
                                pb_sante: @js($formData['problemes_sante'] ?? ''),
                                allergies: @js($formData['allergies'] ?? ''),
                                get hasHealthInfo() {
                                    return this.pb_sante.trim().length > 0 || this.allergies.trim().length > 0;
                                }
                            }">
                                <div class="mb-5">
                                    <label class="{{ $label }}">⚕️ Problèmes de santé à signaler</label>
                                    <textarea name="problemes_sante" rows="3"
                                        placeholder="Ex : asthme, épilepsie, diabète, problèmes cardiaques…"
                                        x-model="pb_sante"
                                        class="{{ $field }}">{{ $formData['problemes_sante'] ?? '' }}</textarea>
                                </div>

                                <div class="mb-5">
                                    <label class="{{ $label }}">🤧 Allergies connues</label>
                                    <textarea name="allergies" rows="3"
                                        placeholder="Ex : arachides, pollen, latex, médicaments…"
                                        x-model="allergies"
                                        class="{{ $field }}">{{ $formData['allergies'] ?? '' }}</textarea>
                                </div>

                                <div x-show="hasHealthInfo"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 -translate-y-1"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     class="mb-5 p-5 bg-amber-50 border border-amber-200 rounded-2xl">
                                    <div class="flex items-start gap-3 mb-3">
                                        <span class="text-xl">🚨</span>
                                        <div>
                                            <p class="text-sm font-bold text-amber-900">Protocole d'urgence</p>
                                            <p class="text-xs text-amber-700 font-medium mt-0.5">
                                                Veuillez préciser la conduite à tenir par l'encadrant en cas de survenue de ces troubles durant l'activité.
                                            </p>
                                        </div>
                                    </div>
                                    <textarea name="conduite_a_tenir" rows="3"
                                        placeholder="Ex : En cas de crise d'asthme, administrer le Ventoline disponible dans le sac de l'enfant et contacter le 15 si absence d'amélioration…"
                                        class="{{ $field }}">{{ $formData['conduite_a_tenir'] ?? '' }}</textarea>
                                </div>

                                <div class="mb-5">
                                    <label class="{{ $label }}">🍽️ Restrictions alimentaires</label>
                                    <textarea name="restrictions_alimentaires" rows="2"
                                        placeholder="Ex : végétarien, sans porc, sans gluten, halal, kosher…"
                                        class="{{ $field }}">{{ $formData['restrictions_alimentaires'] ?? '' }}</textarea>
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
                @elseif($step === 5)
                    <div class="p-5 md:p-6">
                        <div class="mb-5">
                            <h2 class="text-xl font-bold text-gray-900">Situation actuelle 💼</h2>
                            <p class="text-gray-400 mt-1 text-sm">Indiquez votre niveau scolaire ou situation professionnelle</p>
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
                                                    class="border-2 rounded-xl p-3 text-center transition-all">
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
                                                class="border-2 rounded-lg p-3 text-center transition-all peer-checked:border-teal-500 peer-checked:bg-teal-50 peer-checked:ring-2 ring-teal-500/20 border-gray-200 group-hover:border-teal-400">
                                                <div class="text-2xl mb-1">{{ $o['icon'] }}</div>
                                                <div class="font-bold text-xs text-slate-900">{{ $o['val'] }}</div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            @endif

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
                @elseif($step === 6)
                    <div class="p-5 md:p-6">
                        @php $typeActivite = $formData['type_activite'] ?? ''; @endphp
                        <div class="mb-5">
                            <h2 class="text-xl font-bold text-gray-900">
                                @if ($typeActivite === 'atelier')
                                    Choisissez votre atelier 🔧
                                @elseif ($typeActivite === 'ressourcerie')
                                    Ressourcerie 📦
                                @else
                                    Choisissez votre stage 🎭
                                @endif
                            </h2>
                            <p class="text-gray-400 mt-1 text-sm">
                                @if ($typeActivite === 'atelier')
                                    Sélectionnez le ou les ateliers auxquels vous souhaitez vous inscrire
                                @elseif ($typeActivite === 'ressourcerie')
                                    Sélectionnez les équipements que vous souhaitez louer
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
                                $selectedRessourcerie = $formData['ressourcerie_selectionnees'] ?? [];
                                if (!is_array($selectedRessourcerie)) {
                                    $selectedRessourcerie = [];
                                }
                            @endphp

                            @if ($typeActivite === 'ressourcerie')

                                @if ($ressourcerie->isEmpty())
                                    <div class="p-6 text-center bg-gray-50 rounded-xl border border-gray-200 mb-5">
                                        <div class="text-4xl mb-2">😕</div>
                                        <p class="text-gray-800 font-semibold">Aucun équipement disponible pour votre profil.</p>
                                        <p class="text-gray-400 text-sm mt-1">Contactez-nous pour plus d'informations.</p>
                                    </div>
                                @else
                                    @php
                                        $labelsTarif = [
                                            'tarif_particulier' => ['label' => 'Tarif particulier', 'color' => 'bg-sky-50 text-sky-700 border-sky-200'],
                                            'tarif_structure'   => ['label' => 'Tarif structure',   'color' => 'bg-violet-50 text-violet-700 border-violet-200'],
                                            'tarif_scolaire'    => ['label' => 'Tarif scolaire',    'color' => 'bg-amber-50 text-amber-700 border-amber-200'],
                                        ];
                                        $groupes = $ressourcerie->groupBy('type_tarif');
                                    @endphp
                                    <div class="space-y-6 mb-6">
                                        @foreach ($groupes as $typeTarif => $items)
                                            @php $meta = $labelsTarif[$typeTarif] ?? ['label' => $typeTarif, 'color' => 'bg-gray-100 text-gray-600 border-gray-200']; @endphp
                                            @if ($groupes->count() > 1)
                                                <div class="flex items-center gap-2 mb-2">
                                                    <span class="text-xs font-bold px-2.5 py-1 rounded-full border {{ $meta['color'] }}">{{ $meta['label'] }}</span>
                                                    <div class="flex-1 h-px bg-gray-200"></div>
                                                </div>
                                            @endif
                                            <div class="space-y-3">
                                                @foreach ($items as $item)
                                                    <div x-data="{ checked: {{ in_array($item->id, $selectedRessourcerie) ? 'true' : 'false' }} }">
                                                        <input type="checkbox" name="ressourcerie_selectionnees[]"
                                                            value="{{ $item->id }}" x-model="checked" class="hidden">
                                                        <div @click="checked = !checked"
                                                            :class="checked
                                                                ? 'border-teal-600 bg-teal-50 ring-2 ring-teal-600/20'
                                                                : 'border-gray-200 hover:border-slate-400 bg-white'"
                                                            class="border-2 rounded-xl p-4 transition-all cursor-pointer shadow-sm hover:shadow-md">
                                                            <div class="flex items-start justify-between gap-4">
                                                                <div class="flex-1 min-w-0">
                                                                    <div class="flex items-center gap-2 flex-wrap">
                                                                        <h4 class="font-bold text-slate-900 text-sm leading-tight">{{ $item->nom }}</h4>
                                                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full border {{ $meta['color'] }}">
                                                                            {{ $meta['label'] }}
                                                                        </span>
                                                                    </div>
                                                                    @if ($item->description)
                                                                        <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">{{ $item->description }}</p>
                                                                    @endif
                                                                    @if ($item->condition_location)
                                                                        <div class="mt-2 flex items-start gap-1.5">
                                                                            <span class="text-xs shrink-0 mt-0.5">📋</span>
                                                                            <p class="text-xs text-gray-400 italic leading-relaxed">{{ $item->condition_location }}</p>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                <div class="flex flex-col items-end gap-3 shrink-0">
                                                                    <p class="text-base font-black text-teal-600">{{ $item->prix_format }}</p>
                                                                    <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-colors"
                                                                        :class="checked ? 'border-teal-600 bg-teal-600' : 'border-gray-300'">
                                                                        <svg x-show="checked" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                                        </svg>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                            @elseif ($classeAdherent)
                                <div class="flex items-center gap-2 mb-4 px-1">
                                    <span class="text-base">🎓</span>
                                    <p class="text-xs text-gray-500">
                                        Activités adaptées à la classe
                                        <strong class="text-gray-700">{{ $classeAdherent }}</strong>.
                                    </p>
                                </div>
                            @endif

                            @if ($typeActivite !== 'ressourcerie')
                            @if ($liste->isEmpty())
                                <div class="p-6 text-center bg-gray-50 rounded-xl border border-gray-200 mb-5">
                                    <div class="text-4xl mb-2">😕</div>
                                    <p class="text-gray-800 font-semibold">Aucune activité disponible pour votre niveau.</p>
                                    <p class="text-gray-400 text-sm mt-1">Contactez-nous pour plus d'informations.</p>
                                </div>
                            @elseif ($typeActivite === 'atelier')
                                @php
                                    $ateliersParVille = $liste->groupBy('ville')->sortKeys();
                                @endphp
                                <div x-data="{ openVilles: [] }" class="space-y-3 mb-6">
                                    @foreach ($ateliersParVille as $ville => $villeActivites)
                                        @php
                                            $villeKey = Str::slug($ville);
                                            $hasSelected = $villeActivites->contains(fn($a) => in_array($a->id, $selectedActivites));
                                        @endphp
                                        <div class="border rounded-xl overflow-hidden transition-all
                                            {{ $hasSelected ? 'border-teal-400' : 'border-gray-200' }}">

                                            <button type="button"
                                                @click="openVilles.includes('{{ $villeKey }}')
                                                    ? openVilles = openVilles.filter(v => v !== '{{ $villeKey }}')
                                                    : openVilles.push('{{ $villeKey }}')"
                                                class="w-full flex items-center justify-between px-4 py-3 bg-white hover:bg-gray-50 transition-colors">
                                                <div class="flex items-center gap-3">
                                                    <span class="text-xl">📍</span>
                                                    <span class="font-bold text-slate-900 text-base">{{ $ville }}</span>
                                                    <span class="text-xs font-semibold text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">
                                                        {{ $villeActivites->count() }} activité{{ $villeActivites->count() > 1 ? 's' : '' }}
                                                    </span>
                                                    @if ($hasSelected)
                                                        <span class="text-xs font-bold text-teal-700 bg-teal-50 border border-teal-200 px-2 py-0.5 rounded-full">
                                                            Sélectionnée
                                                        </span>
                                                    @endif
                                                </div>
                                                <svg class="w-5 h-5 text-gray-400 transition-transform duration-200"
                                                    :class="openVilles.includes('{{ $villeKey }}') ? 'rotate-180' : ''"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>

                                            <div x-show="openVilles.includes('{{ $villeKey }}')"
                                                 x-transition:enter="transition ease-out duration-200"
                                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                                 x-transition:enter-end="opacity-100 translate-y-0"
                                                 class="px-3 pb-3 pt-2 bg-gray-50/50 space-y-2 border-t border-gray-100">
                                                @foreach ($villeActivites as $activite)
                                                    @php
                                                        $horaires = is_string($activite->horaires)
                                                            ? json_decode($activite->horaires, true)
                                                            : $activite->horaires ?? [];
                                                        $firstJour  = array_key_first($horaires ?? []);
                                                        $firstHeure = $horaires[$firstJour] ?? null;
                                                    @endphp
                                                    <div x-data="{ checked: {{ in_array($activite->id, $selectedActivites) ? 'true' : 'false' }} }">
                                                        <input type="checkbox" name="activites_selectionnees[]"
                                                            value="{{ $activite->id }}" x-model="checked" class="hidden">
                                                        <div @click="checked = !checked"
                                                            :class="checked
                                                                ? 'border-teal-600 bg-teal-50 ring-2 ring-teal-600/20'
                                                                : 'border-gray-200 hover:border-slate-400 bg-white'"
                                                            class="border-2 rounded-xl p-4 transition-all flex items-center gap-4 cursor-pointer shadow-sm hover:shadow-md">
                                                            <div class="flex-1 min-w-0">
                                                                <h4 class="font-bold text-slate-900 text-sm leading-tight">{{ $activite->nom }}</h4>
                                                                @if ($firstJour && $firstHeure)
                                                                    <p class="text-xs text-gray-500 font-medium mt-1.5 flex items-center gap-1.5">
                                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                        </svg>
                                                                        {{ $firstJour }} · {{ $firstHeure }}
                                                                    </p>
                                                                @endif
                                                                @if ($activite->tarif !== null)
                                                                    <p class="text-xs font-black text-teal-600 mt-1">
                                                                        {{ $activite->tarif > 0 ? number_format($activite->tarif, 0, ',', ' ') . ' €' : 'Gratuit' }}
                                                                    </p>
                                                                @endif
                                                            </div>
                                                            <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors"
                                                                :class="checked ? 'border-teal-600 bg-teal-600' : 'border-gray-300'">
                                                                <svg x-show="checked" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                                </svg>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="space-y-4 mb-6">
                                    @foreach ($liste as $activite)
                                        @php
                                            $horaires = is_string($activite->horaires)
                                                ? json_decode($activite->horaires, true)
                                                : $activite->horaires ?? [];
                                        @endphp
                                        <div x-data="{ checked: {{ in_array($activite->id, $selectedActivites) ? 'true' : 'false' }} }">
                                            <input type="checkbox" name="activites_selectionnees[]"
                                                value="{{ $activite->id }}" x-model="checked" class="hidden">
                                            <div :class="checked ? 'border-teal-600 bg-teal-50 ring-2 ring-teal-600/20' :
                                                'border-gray-200 hover:border-slate-900 bg-white'"
                                                class="border-2 rounded-2xl p-5 transition-all flex items-start gap-4 shadow-sm hover:shadow-md cursor-pointer"
                                                @click="checked = !checked">
                                                <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 text-2xl bg-slate-900 text-white shadow-sm">🎭</div>
                                                <div class="flex-1">
                                                    <h4 class="font-bold text-slate-900 text-lg">{{ $activite->nom }}</h4>
                                                    @if ($activite->adresse)
                                                        <p class="text-sm text-gray-500 font-medium mt-1">📍 {{ $activite->adresse }}, {{ $activite->ville }}</p>
                                                    @endif
                                                    @if ($horaires && count($horaires) > 0)
                                                        <div class="flex flex-wrap gap-2 mt-3">
                                                            @foreach ($horaires as $jour => $heure)
                                                                <span class="inline-block bg-white border border-gray-200 text-slate-700 font-semibold text-xs px-2.5 py-1 rounded-lg shadow-sm">
                                                                    🕐 {{ $jour }} {{ $heure }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                    @if ($activite->tarif !== null)
                                                        <p class="text-sm font-black text-teal-600 mt-3">
                                                            {{ $activite->tarif > 0 ? number_format($activite->tarif, 0, ',', ' ') . ' €' : 'Gratuit' }}
                                                        </p>
                                                    @endif
                                                </div>
                                                <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors mt-1"
                                                    :class="checked ? 'border-teal-600 bg-teal-600' : 'border-gray-300'">
                                                    <svg x-show="checked" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            @endif {{-- fin @if ($typeActivite !== 'ressourcerie') --}}

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
                @elseif($step === 7)
                    <div class="p-5 md:p-6">
                        <div class="mb-5">
                            <h2 class="text-xl font-bold text-gray-900">Implication bénévole 🤝</h2>
                            <p class="text-gray-400 mt-1 text-sm">Souhaitez-vous vous investir dans l'association ? (facultatif)
                            </p>
                        </div>

                        <form action="{{ route('adhesion.next', $token) }}" method="POST">
                            @csrf
                            <input type="hidden" name="current_step" value="7">

                            <div class="mb-8">
                                <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-3">
                                    <span
                                        class="w-7 h-7 rounded-lg bg-teal-600 text-white text-xs flex items-center justify-center font-bold">1</span>
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
                                            class="flex items-center gap-3 p-3.5 border border-gray-200 rounded-lg cursor-pointer hover:border-teal-400 hover:bg-teal-50/60 transition-all group">
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

                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 mb-5">
                                <h3 class="font-bold text-slate-900 mb-3 flex items-center gap-3">
                                    <span
                                        class="w-7 h-7 rounded-lg bg-teal-600 text-white text-xs flex items-center justify-center font-bold">2</span>
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
                                        class="flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-lg cursor-pointer hover:border-teal-400 transition-all flex-1">
                                        <input type="radio" name="participation_manif" value="1"
                                            {{ ($formData['participation_manif'] ?? '') === '1' ? 'checked' : '' }}
                                            class="{{ $radio }}">
                                        <span class="text-sm font-bold text-slate-900">✅ Oui, intéressé·e</span>
                                    </label>
                                    <label
                                        class="flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-lg cursor-pointer hover:border-gray-400 transition-all flex-1">
                                        <input type="radio" name="participation_manif" value="0"
                                            {{ ($formData['participation_manif'] ?? '') === '0' ? 'checked' : '' }}
                                            class="{{ $radio }}">
                                        <span class="text-sm font-bold text-slate-900">❌ Non, merci</span>
                                    </label>
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
                @elseif($step === 8)
                    <div class="p-5 md:p-6">
                        <div class="mb-5">
                            <h2 class="text-xl font-bold text-gray-900">Fiche parents et/ou tuteur·trice·s 👨‍👩‍👧</h2>
                            <p class="text-gray-400 mt-1 text-sm">Responsables légaux de <strong
                                    class="text-slate-900">{{ ($formData['prenom'] ?? '') . ' ' . ($formData['nom'] ?? '') }}</strong>
                            </p>
                        </div>

                        <form action="{{ route('adhesion.next', $token) }}" method="POST" id="form-tuteurs">
                            @csrf
                            <input type="hidden" name="current_step" value="8">

                            <div x-data="tuteurManager()" x-init="init()">
                                <template x-for="(tuteur, i) in tuteurs" :key="i">
                                    <div class="border-2 rounded-xl p-4 mb-4 bg-white relative transition-colors"
                                        :class="{
                                            'border-slate-300 hover:border-slate-400': tuteur.type === 'parent_tuteur',
                                            'border-teal-300 hover:border-teal-400 bg-teal-50/30': tuteur.type === 'autre_autorise',
                                            'border-red-200 hover:border-red-300 bg-red-50/20': tuteur.type === 'non_autorise'
                                        }">

                                        <div class="flex items-center justify-between mb-5">
                                            <h3 class="font-bold text-slate-900 text-lg flex items-center gap-3">
                                                <span class="w-10 h-10 rounded-xl text-white flex items-center justify-center font-bold shadow-md text-sm"
                                                    :class="{
                                                        'bg-slate-900': tuteur.type === 'parent_tuteur',
                                                        'bg-teal-600': tuteur.type === 'autre_autorise',
                                                        'bg-red-500': tuteur.type === 'non_autorise'
                                                    }"
                                                    x-text="tuteur.type === 'parent_tuteur' ? '👨‍👩‍👧' : (tuteur.type === 'autre_autorise' ? '✅' : '🚫')"></span>
                                                <span>
                                                    <span x-show="tuteur.type === 'parent_tuteur'" class="text-slate-900">Parent / Tuteur·trice</span>
                                                    <span x-show="tuteur.type === 'autre_autorise'" class="text-teal-700">Personne autorisée à récupérer l'enfant</span>
                                                    <span x-show="tuteur.type === 'non_autorise'" class="text-red-600">Personne non autorisée à récupérer l'enfant</span>
                                                </span>
                                            </h3>
                                            <button type="button" @click="removeTuteur(i)"
                                                class="text-red-500 bg-red-50 px-3 py-1.5 rounded-lg font-bold text-sm hover:bg-red-500 hover:text-white transition-colors flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Retirer
                                            </button>
                                        </div>

                                        <input type="hidden" :name="'tuteurs[' + i + '][type]'" :value="tuteur.type">

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

                                        <template x-if="tuteur.type === 'parent_tuteur'">
                                            <div>
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
                                    </div>
                                </template>

                                <div class="grid grid-cols-3 gap-2 mt-3">
                                    <button type="button" @click="addTuteur('parent_tuteur')"
                                        class="border border-dashed border-gray-300 text-gray-600 bg-gray-50 font-semibold rounded-lg py-3 px-2 hover:bg-gray-900 hover:text-white hover:border-gray-900 transition-colors flex flex-col items-center gap-1 text-xs text-center">
                                        <span class="text-lg">👨‍👩‍👧</span>
                                        Parent / tuteur
                                    </button>
                                    <button type="button" @click="addTuteur('autre_autorise')"
                                        class="border border-dashed border-teal-300 text-teal-700 bg-teal-50 font-semibold rounded-lg py-3 px-2 hover:bg-teal-600 hover:text-white hover:border-teal-600 transition-colors flex flex-col items-center gap-1 text-xs text-center">
                                        <span class="text-lg">✅</span>
                                        Personne autorisée
                                    </button>
                                    <button type="button" @click="addTuteur('non_autorise')"
                                        class="border border-dashed border-red-300 text-red-600 bg-red-50 font-semibold rounded-lg py-3 px-2 hover:bg-red-500 hover:text-white hover:border-red-500 transition-colors flex flex-col items-center gap-1 text-xs text-center">
                                        <span class="text-lg">🚫</span>
                                        Non autorisée
                                    </button>
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
                @elseif($step === 13)
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
                                <label class="{{ $label }}">📅 Date de création</label>
                                <input type="date" name="date_creation_structure"
                                    value="{{ $formData['date_creation_structure'] ?? '' }}"
                                    class="{{ $field }} max-w-xs">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="{{ $label }}">📞 Téléphone fixe</label>
                                    <input type="tel" name="tel_structure"
                                        value="{{ $formData['tel_structure'] ?? '' }}"
                                        placeholder="03 88 00 00 00" class="{{ $field }}">
                                </div>
                                <div>
                                    <label class="{{ $label }}">📱 Téléphone portable</label>
                                    <input type="tel" name="tel_portable_structure"
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
                                    <label class="{{ $label }}">🌐 Site web</label>
                                    <input type="url" name="site_web"
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

                @elseif($step === 14)
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

                @elseif($step === 9)
                    <div class="p-5 md:p-6">
                        <div class="mb-5">
                            <h2 class="text-xl font-bold text-gray-900">Votre signature ✍️</h2>
                            <p class="text-gray-400 mt-1 text-sm">Signez ci-dessous pour valider la fiche d'adhésion</p>
                        </div>

                        <form action="{{ route('adhesion.next', $token) }}" method="POST" id="form-signature">
                            @csrf
                            <input type="hidden" name="current_step" value="9">

                            <div class="p-4 bg-amber-50 rounded-xl border border-amber-100 mb-5 flex items-start gap-3">
                                <span class="text-2xl">⚠️</span>
                                <p class="text-sm font-bold text-amber-800 leading-relaxed">
                                    En signant ce formulaire, vous certifiez l'exactitude des informations renseignées et
                                    acceptez le règlement intérieur de l'association.
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
                @elseif($step === 10)
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
                @elseif($step === 11)
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
                                <a href="mailto:contact@savoirsvivants.fr"
                                    class="inline-flex items-center gap-2 bg-teal-600 text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-teal-700 transition-colors">
                                    ✉️ contact@savoirsvivants.fr
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
                @endif

            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/4.1.7/signature_pad.umd.min.js"></script>

    <script>

        function cotisationPaiement() {
                        return {
                            loading: false,
                            dejaClique: {{ !empty($formData['_via_url_checkout']) ? 'true' : 'false' }},
                            init() {},
                            async ouvrirHelloAsso() {
                                this.loading = true;
                                try {
                                    const response = await fetch('{{ route('adhesion.helloasso2.checkout', $token) }}', {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'Accept': 'application/json',
                                            'X-Requested-With': 'XMLHttpRequest',
                                        },
                                    });
                                    const data = await response.json();
                                    if (data.url) {
                                        window.open(data.url, '_blank');
                                        this.dejaClique = true;
                                    }
                                } catch (e) {
                                    console.error(e);
                                } finally {
                                    this.loading = false;
                                }
                            }
                        }
                    }

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
                        this.tuteurs = [this.emptyTuteur('parent_tuteur')];
                    }
                    this.$nextTick(() => {
                        this.tuteurs.forEach((t, i) => {
                            if (t.type === 'parent_tuteur') this.initSigPad(i);
                        });
                    });
                },

                emptyTuteur(type) {
                    const base = {
                        type: type,
                        nom: '',
                        prenom: '',
                        tel: '',
                        mail: '',
                    };
                    if (type === 'parent_tuteur') {
                        Object.assign(base, {
                            nom_enfant: '{{ ($formData['prenom'] ?? '') . ' ' . ($formData['nom'] ?? '') }}',
                            adhere: false,
                            rentre_fin: false,
                            rentre_annul: false,
                            date_signature: new Date().toISOString().split('T')[0],
                            signature: ''
                        });
                    }
                    return base;
                },

                addTuteur(type) {
                    this.tuteurs.push(this.emptyTuteur(type));
                    const newIdx = this.tuteurs.length - 1;
                    if (type === 'parent_tuteur') {
                        this.$nextTick(() => this.initSigPad(newIdx));
                    }
                },

                removeTuteur(i) {
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
