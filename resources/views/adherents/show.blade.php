@extends('layouts.app')

@section('title')
    {!! $adherent->prenom !!} {!! $adherent->nom !!}
@endsection

@section('content')

    @if (session('success'))
        <div
            class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-xl text-sm font-semibold text-emerald-600 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div x-data="{ editMode: false }">

        <form id="form-update-adherent" action="{{ route('adherents.update-fiche', $adherent) }}" method="POST">
            @csrf
            @method('PUT')
        </form>

        <div class="mb-8">

            <div class="flex items-center gap-2 text-xs text-gray-400 mb-5 pl-1">
                <a href="{{ route('adherents.index') }}"
                    class="hover:text-[#222A60] transition-colors font-medium">Adhérents</a>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-gray-600 font-semibold">{{ $adherent->prenom }} {{ $adherent->nom }}</span>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_24px_rgba(0,0,0,0.04)] overflow-hidden">

                <div class="h-1.5 bg-gradient-to-r from-[#222A60] via-[#16987C] to-[#16987C]/40"></div>

                <div class="p-6">
                    <div class="flex flex-col lg:flex-row lg:items-start gap-6">

                        <div class="flex items-start gap-5 flex-1">
                            <div class="relative shrink-0">
                                <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-white text-xl font-black shadow-lg"
                                    style="background-color: {{ $adherent->couleur_avatar }}">
                                    {{ $adherent->initiales }}
                                </div>
                                @if ($adherent->inscription?->a_paye === 'Payé')
                                    <span
                                        class="absolute -bottom-1 -right-1 w-5 h-5 bg-[#16987C] rounded-full border-2 border-white flex items-center justify-center">
                                        <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                @endif
                            </div>

                            <div class="min-w-0 w-full">
                                <div class="font-grotesk text-2xl font-black text-[#0F143A] tracking-tight">
                                    <span x-show="!editMode">{{ $adherent->prenom }} {{ $adherent->nom }}</span>

                                    <div x-show="editMode" x-cloak class="flex items-center gap-3 mb-2 w-full max-w-md">
                                        <input type="text" name="prenom" value="{{ $adherent->prenom }}" form="form-update-adherent" placeholder="Prénom"
                                            class="w-1/2 bg-white border border-gray-200 rounded-lg text-lg px-3 py-1.5 focus:ring-2 focus:ring-[#222A60] outline-none transition-all">
                                        <input type="text" name="nom" value="{{ $adherent->nom }}" form="form-update-adherent" placeholder="Nom"
                                            class="w-1/2 bg-white border border-gray-200 rounded-lg text-lg px-3 py-1.5 focus:ring-2 focus:ring-[#222A60] outline-none transition-all">
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1 text-sm text-gray-500">
                                    @if ($adherent->adresse)
                                        <span>{{ $adherent->adresse }} · {{ $adherent->ville }}
                                            {{ $adherent->code_postal }}</span>
                                        <span class="text-gray-200">|</span>
                                    @endif
                                    @if ($adherent->mail)
                                        <span>{{ $adherent->mail }}</span>
                                        <span class="text-gray-200">|</span>
                                    @endif
                                    @if ($adherent->tel)
                                        <span>{{ $adherent->tel }}</span>
                                    @endif
                                </div>

                                <div class="flex flex-wrap gap-2 mt-3">
                                    @if ($adherent->tranche_age)
                                        @php
                                            $badgeAge = match ($adherent->tranche_age) {
                                                'Enfant' => 'bg-sky-50 text-sky-600 border border-sky-100',
                                                'Adolescent' => 'bg-violet-50 text-violet-600 border border-violet-100',
                                                default => 'bg-emerald-50 text-emerald-600 border border-emerald-100',
                                            };
                                        @endphp
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold {{ $badgeAge }}">
                                            {{ $adherent->tranche_age }}
                                            @if ($adherent->age_courant)
                                                · {{ $adherent->age_courant }} ans
                                            @endif
                                        </span>
                                    @endif

                                    @if ($adherent->inscription)
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold {{ $adherent->inscription->badge_class }} border {{ $adherent->inscription->a_paye === 'Payé' ? 'border-emerald-100' : 'border-amber-100' }}">
                                            <span
                                                class="w-1.5 h-1.5 rounded-full {{ $adherent->inscription->badge_dot_class }}"></span>
                                            {{ $adherent->inscription->a_paye }}
                                            @if ($paiementPrincipal?->source)
                                                · {{ $paiementPrincipal->source }}
                                            @endif
                                        </span>
                                    @endif

                                    @if ($adherent->inscription)
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-gray-50 text-gray-400 border border-gray-100">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            Inscrit le {{ $adherent->inscription->date_inscription->isoFormat('D MMM YYYY') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col items-end gap-4 shrink-0">
                            <div class="flex items-center gap-2">
                                <button @click.prevent="editMode = !editMode"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-[#222A60] hover:bg-[#1a2050] text-white text-sm font-bold rounded-xl transition-all shadow-sm">
                                    <svg x-show="!editMode" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                    <svg x-show="editMode" x-cloak class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    <span x-text="editMode ? 'Annuler' : 'Modifier la fiche'"></span>
                                </button>

                                <button type="submit" form="form-update-adherent" x-show="editMode" x-cloak
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-bold rounded-xl transition-all shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                    Enregistrer
                                </button>

                                <a href="{{ route('adherents.pdf', $adherent) }}" x-show="!editMode"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 text-sm font-bold rounded-xl transition-all shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Télécharger
                                </a>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 w-full">
                                <div
                                    class="text-center px-3 sm:px-4 py-2.5 bg-[#16987C]/10 rounded-xl border border-[#16987C]/15 flex flex-col justify-center">
                                    <p class="font-grotesk text-lg sm:text-xl font-black text-[#16987C] leading-none">
                                        {{ $nbPresences }}</p>
                                    <p
                                        class="text-[9px] sm:text-[10px] font-bold text-gray-500 uppercase tracking-widest mt-1.5">
                                        Présences</p>
                                </div>
                                <div
                                    class="text-center px-3 sm:px-4 py-2.5 bg-rose-50 rounded-xl border border-rose-100 flex flex-col justify-center">
                                    <p class="font-grotesk text-lg sm:text-xl font-black text-rose-500 leading-none">
                                        {{ $nbAbsences }}</p>
                                    <p
                                        class="text-[9px] sm:text-[10px] font-bold text-gray-500 uppercase tracking-widest mt-1.5">
                                        Absences</p>
                                </div>
                                <div
                                    class="text-center px-3 sm:px-4 py-2.5 bg-gray-50 rounded-xl border border-gray-100 flex flex-col justify-center">
                                    <p class="font-grotesk text-lg sm:text-xl font-black text-[#0F143A] leading-none">
                                        {{ $tauxPresence }}%</p>
                                    <p
                                        class="text-[9px] sm:text-[10px] font-bold text-gray-500 uppercase tracking-widest mt-1.5">
                                        Assiduité</p>
                                </div>
                                <div
                                    class="text-center px-3 sm:px-4 py-2.5 bg-[#222A60]/5 rounded-xl border border-[#222A60]/10 flex flex-col justify-center">
                                    <p class="font-grotesk text-lg sm:text-xl font-black text-[#222A60] leading-none">
                                        {{ number_format($adherent->inscriptions->sum('montant'), 0, ',', ' ') }} €</p>
                                    <p
                                        class="text-[9px] sm:text-[10px] font-bold text-gray-500 uppercase tracking-widest mt-1.5">
                                        Encaissé</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            <div class="xl:col-span-2 space-y-6">

                <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.03)] overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#222A60]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest">Infos personnelles</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-0">
                            @php
                                $infos = [
                                    ['name' => 'date_naiss', 'type' => 'date', 'label' => 'Date de naissance', 'display' => $adherent->date_naiss?->isoFormat('D MMMM YYYY'), 'value' => $adherent->date_naiss?->format('Y-m-d')],
                                    ['name' => 'genre', 'type' => 'text', 'label' => 'Genre', 'display' => $adherent->genre, 'value' => $adherent->genre],
                                    ['name' => 'adresse', 'type' => 'text', 'label' => 'Adresse', 'display' => $adherent->adresse, 'value' => $adherent->adresse],
                                    ['name' => 'code_postal', 'type' => 'text', 'label' => 'Code Postal', 'display' => $adherent->code_postal, 'value' => $adherent->code_postal],
                                    ['name' => 'ville', 'type' => 'text', 'label' => 'Ville', 'display' => $adherent->ville, 'value' => $adherent->ville],
                                    ['name' => 'tel', 'type' => 'text', 'label' => 'Téléphone', 'display' => $adherent->tel, 'value' => $adherent->tel],
                                    ['name' => 'mail', 'type' => 'email', 'label' => 'Email', 'display' => $adherent->mail, 'value' => $adherent->mail],
                                    ['name' => 'occupation', 'type' => 'text', 'label' => 'Situation scolaire', 'display' => $adherent->occupation, 'value' => $adherent->occupation],
                                    ['name' => 'etablissement', 'type' => 'text', 'label' => 'Établissement', 'display' => $adherent->etablissement, 'value' => $adherent->etablissement],
                                    ['name' => 'regime_social', 'type' => 'text', 'label' => 'Couverture sociale', 'display' => $adherent->regime_social, 'value' => $adherent->regime_social],
                                ];
                            @endphp
                            @foreach ($infos as $info)
                                <div class="flex items-baseline justify-between py-3 border-b border-gray-50 last:border-0">
                                    <span class="text-xs font-semibold text-gray-400 shrink-0 mr-4">{{ $info['label'] }}</span>

                                    <span x-show="!editMode" class="text-sm font-semibold text-[#0F143A] text-right">{{ $info['display'] ?? '—' }}</span>

                                    <div x-show="editMode" x-cloak class="w-2/3 text-right">
                                        <input type="{{ $info['type'] }}" name="{{ $info['name'] }}" value="{{ $info['value'] }}" form="form-update-adherent"
                                            class="w-full bg-white border border-gray-200 rounded-lg text-sm px-3 py-1.5 focus:ring-2 focus:ring-[#222A60] outline-none transition-all">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div x-show="editMode || {{ ($adherent->idee_metier || $adherent->decouverte_metier) ? 'true' : 'false' }}"
                    class="bg-white rounded-2xl border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.03)] overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-2">
                        <span class="text-indigo-400 text-sm">🎓</span>
                        <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest">Orientation professionnelle</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div x-show="editMode || {{ $adherent->idee_metier ? 'true' : 'false' }}">
                            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Idée de métier</p>
                            <p x-show="!editMode" class="text-sm text-[#0F143A] font-medium leading-relaxed">{{ $adherent->idee_metier }}</p>
                            <textarea x-show="editMode" x-cloak name="idee_metier" form="form-update-adherent" rows="2"
                                class="w-full bg-white border border-gray-200 rounded-lg text-sm p-3 focus:ring-2 focus:ring-[#222A60] outline-none">{{ $adherent->idee_metier }}</textarea>
                        </div>
                        <div x-show="editMode || {{ $adherent->decouverte_metier ? 'true' : 'false' }}">
                            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Découverte métier / formation</p>
                            <p x-show="!editMode" class="text-sm text-[#0F143A] font-medium leading-relaxed">{{ $adherent->decouverte_metier }}</p>
                            <textarea x-show="editMode" x-cloak name="decouverte_metier" form="form-update-adherent" rows="2"
                                class="w-full bg-white border border-gray-200 rounded-lg text-sm p-3 focus:ring-2 focus:ring-[#222A60] outline-none">{{ $adherent->decouverte_metier }}</textarea>
                        </div>
                    </div>
                </div>

                <div x-show="editMode || {{ ($adherent->problemes_sante || $adherent->allergies || $adherent->conduite_a_tenir || $adherent->restrictions_alimentaires || $adherent->carnet) ? 'true' : 'false' }}"
                    class="bg-white rounded-2xl border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.03)] overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-2">
                        <span class="text-rose-400 text-sm">🏥</span>
                        <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest">Informations Médicales & Santé</h2>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-6">

                        <div x-show="editMode || {{ $adherent->problemes_sante ? 'true' : 'false' }}">
                            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Problèmes de santé</p>
                            <p x-show="!editMode" class="text-sm text-[#0F143A] font-medium leading-relaxed">{{ $adherent->problemes_sante }}</p>
                            <textarea x-show="editMode" x-cloak name="problemes_sante" form="form-update-adherent" rows="2"
                                class="w-full bg-white border border-gray-200 rounded-lg text-sm p-3 focus:ring-2 focus:ring-rose-400 outline-none">{{ $adherent->problemes_sante }}</textarea>
                        </div>

                        <div x-show="editMode || {{ $adherent->allergies ? 'true' : 'false' }}">
                            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Allergies</p>
                            <p x-show="!editMode" class="text-sm text-[#0F143A] font-medium leading-relaxed">{{ $adherent->allergies }}</p>
                            <textarea x-show="editMode" x-cloak name="allergies" form="form-update-adherent" rows="2"
                                class="w-full bg-white border border-gray-200 rounded-lg text-sm p-3 focus:ring-2 focus:ring-rose-400 outline-none">{{ $adherent->allergies }}</textarea>
                        </div>

                        <div x-show="editMode || {{ $adherent->conduite_a_tenir ? 'true' : 'false' }}" class="sm:col-span-2">
                            <div class="p-4 bg-amber-50 rounded-xl border border-amber-100">
                                <p class="text-[11px] font-bold text-amber-700 uppercase tracking-widest mb-1.5 flex items-center gap-1.5">
                                    <span class="text-base leading-none">🚨</span> Protocole d'urgence (Conduite à tenir)
                                </p>
                                <p x-show="!editMode" class="text-sm text-amber-900 font-semibold leading-relaxed">{{ $adherent->conduite_a_tenir }}</p>
                                <textarea x-show="editMode" x-cloak name="conduite_a_tenir" form="form-update-adherent" rows="3"
                                    class="w-full bg-white border border-amber-200 rounded-lg text-sm p-3 focus:ring-2 focus:ring-amber-400 outline-none mt-2">{{ $adherent->conduite_a_tenir }}</textarea>
                            </div>
                        </div>

                        <div x-show="editMode || {{ $adherent->restrictions_alimentaires ? 'true' : 'false' }}" class="sm:col-span-2">
                            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Restrictions alimentaires</p>
                            <p x-show="!editMode" class="text-sm text-[#0F143A] font-medium leading-relaxed">{{ $adherent->restrictions_alimentaires }}</p>
                            <textarea x-show="editMode" x-cloak name="restrictions_alimentaires" form="form-update-adherent" rows="2"
                                class="w-full bg-white border border-gray-200 rounded-lg text-sm p-3 focus:ring-2 focus:ring-rose-400 outline-none">{{ $adherent->restrictions_alimentaires }}</textarea>
                        </div>

                        @if ($adherent->carnet)
                            <div class="sm:col-span-2 pt-2" x-show="!editMode">
                                <a href="{{ asset('storage/' . $adherent->carnet) }}" target="_blank"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-teal-50 text-teal-700 text-sm font-bold rounded-xl border border-teal-100 hover:bg-teal-100 transition-colors">
                                    <span class="text-lg">📷</span> Consulter la copie des vaccins
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.03)] overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#222A60]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest">Activités inscrites</h2>
                    </div>
                    <div class="divide-y divide-gray-50">
                        @php
                            $year = now()->month >= 9 ? now()->year : now()->year - 1;
                            $saison = $year . '-' . ($year + 1);
                            $derniereInscription = $adherent->inscriptions
                                ->where('saison', $saison)
                                ->sortByDesc('created_at')
                                ->first();
                            $isReinscription = $adherent->inscriptions
                                ->where('saison', $saison)
                                ->where('a_paye', \App\Models\Inscription::PAYE)
                                ->where('id', '!=', $derniereInscription?->id ?? 0)
                                ->isNotEmpty();
                            $activitesReins = $isReinscription && $derniereInscription
                                ? $adherent->activitesActives->filter(fn($a) => $a->pivot->created_at >= $derniereInscription->created_at)
                                : collect();
                        @endphp

                        @if($isReinscription && $activitesReins->isNotEmpty())
                            <div class="px-6 py-3 bg-amber-50 border-b border-amber-200">
                                <p class="text-xs font-bold text-amber-700 flex items-center gap-1.5">
                                    <span>🔄</span> Ré-inscription récente ({{ $activitesReins->pluck('nom')->implode(', ') }})
                                </p>
                            </div>
                        @endif

                        @forelse($adherent->activitesActives as $activite)
                            @php
                                $nbSeances = $activite->seances()->count();
                                $nbAbsentActivite = \App\Models\Presence::where('id_adherent', $adherent->id)
                                    ->whereIn('id_seance', $activite->seances()->pluck('id_seance'))
                                    ->whereRaw("LOWER(statut) = 'absent'")
                                    ->count();
                                $nbPresent = max(0, $nbSeances - $nbAbsentActivite);
                                $progression = $nbSeances > 0 ? round(($nbPresent / $nbSeances) * 100) : 0;
                                $isNew = $activitesReins->contains($activite);
                            @endphp
                            <div class="px-6 py-4 {{ $isNew ? 'bg-amber-50/30 border-r-4 border-amber-300' : '' }}">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-0.5 w-2 h-2 rounded-full shrink-0 {{ $activite->est_stage ? 'bg-violet-400' : 'bg-[#16987C]' }} {{ $isNew ? 'animate-pulse' : '' }}"></div>
                                        <div>
                                            <p class="font-bold text-sm text-[#0F143A]">{{ $activite->nom }}</p>
                                            @if($isNew)
                                                <p class="text-xs font-semibold text-amber-600 mt-1 px-2 py-0.5 bg-amber-100 rounded">NOUVEAU</p>
                                            @endif
                                            <p class="text-xs text-gray-400 mt-0.5">
                                                @if ($activite->adresse)
                                                    {{ $activite->adresse }} ·
                                                @endif
                                                @foreach ($activite->horaires_list as $h)
                                                    {{ $h }}
                                                @endforeach
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="font-black text-sm text-[#0F143A]">{{ $activite->tarif_format }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-8 text-center text-sm text-gray-300 font-medium">Aucune activité active</div>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.03)] overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#222A60]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest">Historique des présences
                            </h2>
                        </div>
                    </div>

                    <div class="divide-y divide-gray-50">
                        @forelse($presences as $seance)
                            @php
                                $statut = strtolower($seance->statut_presence);
                                $estAbsent = $statut === 'absent';
                                $badgeClass = $estAbsent ? 'bg-rose-50 text-rose-500' : 'bg-emerald-50 text-emerald-600';
                                $dotClass = $estAbsent ? 'bg-rose-400' : 'bg-emerald-500';
                                $label = $estAbsent ? 'Absent' : 'Présent';
                            @endphp
                            <div class="flex items-center justify-between px-6 py-3 hover:bg-gray-50/60 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-semibold text-gray-400 w-28 shrink-0">
                                        {{ $seance->date->isoFormat('D MMM YYYY') }}
                                    </span>
                                    <span class="text-sm font-semibold text-[#0F143A]">
                                        {{ $seance->activite->nom }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold {{ $badgeClass }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
                                        {{ $label }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-8 text-center text-sm text-gray-300 font-medium">Aucune séance enregistrée
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

            <div class="space-y-6">

                @if ($adherent->tousLesTuteurs && $adherent->tousLesTuteurs->count() > 0)
                    <div
                        class="bg-white rounded-2xl border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.03)] overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#222A60]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest">Représentants & Tuteurs</h2>
                        </div>
                        <div class="divide-y divide-gray-50">
                            @foreach ($adherent->tousLesTuteurs as $tuteur)
                                @php
                                    $typeInfo = match ($tuteur->type) {
                                        'autre_autorise' => [
                                            'icon' => '✅',
                                            'color' => 'bg-teal-50 text-teal-700',
                                            'label' => 'Personne autorisée',
                                        ],
                                        'non_autorise' => [
                                            'icon' => '🚫',
                                            'color' => 'bg-rose-50 text-rose-700',
                                            'label' => 'Non autorisé(e)',
                                        ],
                                        default => [
                                            'icon' => '👨‍👩‍👧',
                                            'color' => 'bg-[#222A60]/10 text-[#222A60]',
                                            'label' => 'Représentant légal',
                                        ],
                                    };
                                @endphp
                                <div class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-9 h-9 rounded-xl {{ $typeInfo['color'] }} flex items-center justify-center text-xs font-black shrink-0">
                                            {{ $typeInfo['icon'] }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-sm text-[#0F143A]">{{ $tuteur->nom_complet }}</p>
                                            <p
                                                class="text-[11px] font-semibold uppercase tracking-widest {{ str_contains($typeInfo['color'], 'rose') ? 'text-rose-500' : 'text-gray-400' }} truncate">
                                                {{ $typeInfo['label'] }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-3 ml-12 space-y-1">
                                        @if ($tuteur->tel)
                                            <p class="text-xs text-gray-500"><span class="font-semibold">Tél :</span>
                                                {{ $tuteur->tel }}</p>
                                        @endif
                                        @if ($tuteur->mail)
                                            <p class="text-xs text-gray-500 truncate"><span class="font-semibold">Email
                                                    :</span> {{ $tuteur->mail }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.03)] overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-2">
                        <span class="text-teal-500 text-sm">📜</span>
                        <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest">Engagements & Autorisations</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="space-y-4">

                            <div class="flex items-center gap-3">
                                <span x-show="!editMode"
                                    class="w-6 h-6 flex items-center justify-center rounded-full shrink-0 text-sm {{ $adherent->communication ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-500' }}">
                                    @if ($adherent->communication) ✓ @else ✕ @endif
                                </span>
                                <div x-show="editMode" x-cloak class="flex items-center h-6 shrink-0">
                                    <input type="hidden" name="communication" value="0" form="form-update-adherent">
                                    <input type="checkbox" name="communication" value="1" form="form-update-adherent" {{ $adherent->communication ? 'checked' : '' }}
                                        class="w-5 h-5 rounded border-gray-300 text-[#16987C] focus:ring-[#16987C] transition-all cursor-pointer">
                                </div>
                                <span class="text-sm text-[#0F143A] font-medium leading-tight">Droit à l'image accordé</span>
                            </div>

                            <div class="flex items-center gap-3">
                                <span x-show="!editMode"
                                    class="w-6 h-6 flex items-center justify-center rounded-full shrink-0 text-sm {{ $adherent->bulletin ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-400' }}">
                                    @if ($adherent->bulletin) ✓ @else - @endif
                                </span>
                                <div x-show="editMode" x-cloak class="flex items-center h-6 shrink-0">
                                    <input type="hidden" name="bulletin" value="0" form="form-update-adherent">
                                    <input type="checkbox" name="bulletin" value="1" form="form-update-adherent" {{ $adherent->bulletin ? 'checked' : '' }}
                                        class="w-5 h-5 rounded border-gray-300 text-[#16987C] focus:ring-[#16987C] transition-all cursor-pointer">
                                </div>
                                <span class="text-sm text-[#0F143A] font-medium leading-tight">Abonné au bulletin d'information</span>
                            </div>

                            <div class="flex items-center gap-3">
                                <span x-show="!editMode"
                                    class="w-6 h-6 flex items-center justify-center rounded-full shrink-0 text-sm {{ $adherent->manif ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-400' }}">
                                    @if ($adherent->manif) ✓ @else - @endif
                                </span>
                                <div x-show="editMode" x-cloak class="flex items-center h-6 shrink-0">
                                    <input type="hidden" name="manif" value="0" form="form-update-adherent">
                                    <input type="checkbox" name="manif" value="1" form="form-update-adherent" {{ $adherent->manif ? 'checked' : '' }}
                                        class="w-5 h-5 rounded border-gray-300 text-[#16987C] focus:ring-[#16987C] transition-all cursor-pointer">
                                </div>
                                <span class="text-sm text-[#0F143A] font-medium leading-tight">Participation aux manifestations</span>
                            </div>
                        </div>

                        @php
                            $actions = is_string($adherent->actions)
                                ? json_decode($adherent->actions, true)
                                : $adherent->actions;
                        @endphp
                        @if (!empty($actions) && is_array($actions) && count($actions) > 0)
                            <div class="pt-4 border-t border-gray-50">
                                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2.5">Implication
                                    bénévole</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($actions as $action)
                                        <span
                                            class="inline-flex px-3 py-1.5 bg-[#222A60]/5 border border-[#222A60]/10 text-[#222A60] rounded-lg text-xs font-semibold">
                                            {{ $action }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                @php
                    $year = now()->month >= 9 ? now()->year : now()->year - 1;
                    $saisonActuelle = $year . '-' . ($year + 1);
                    $saisonsPrecedentes = $saisons->filter(fn($s) => $s->saison !== $saisonActuelle);
                @endphp
                @if ($saisonsPrecedentes->count() > 0)
                    <div
                        class="bg-white rounded-2xl border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.03)] overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#222A60]/40" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest">Saisons précédentes</h2>
                        </div>
                        <div class="divide-y divide-gray-50">
                            @foreach ($saisonsPrecedentes as $inscription)
                                <div class="flex items-center justify-between px-6 py-3">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="w-1.5 h-1.5 rounded-full {{ $inscription->a_paye === 'Payé' ? 'bg-[#16987C]' : 'bg-gray-300' }}"></span>
                                        <span
                                            class="text-sm font-semibold text-gray-600">{{ $inscription->saison }}</span>
                                    </div>
                                    <div class="text-right">
                                        @if ($inscription->a_paye === 'Payé')
                                            <span class="text-xs font-bold text-[#16987C]">Inscrit</span>
                                            @if ($inscription->montant)
                                                <span class="text-xs text-gray-400 ml-1.5">·
                                                    {{ number_format($inscription->montant, 2, ',', ' ') }} €</span>
                                            @endif
                                        @else
                                            <span class="text-xs font-bold text-gray-300">Non inscrit</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($adherent->paiements->count())
                    <div
                        class="bg-white rounded-2xl border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.03)] overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#222A60]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest">Paiement
                                {{ $paiementPrincipal?->source ?? '' }}</h2>
                        </div>

                        @if ($paiementPrincipal)
                            <div class="px-5 mt-3 space-y-1">
                                @foreach ($adherent->activitesActives as $activite)
                                    <div class="flex items-center justify-between py-1.5">
                                        <span class="text-sm text-gray-500">{{ $activite->nom }}</span>
                                        <span
                                            class="text-sm font-semibold text-[#0F143A]">{{ $activite->tarif_format }}</span>
                                    </div>
                                @endforeach
                                <div class="flex items-center justify-between py-1.5">
                                    <span class="text-sm text-gray-500">Adhésion annuelle</span>
                                    <span class="text-sm font-semibold text-[#0F143A]">10,00 €</span>
                                </div>
                            </div>
                        @endif

                        <div
                            class="mx-5 my-4 p-4 bg-[#16987C]/8 rounded-xl border border-[#16987C]/15 flex items-center justify-between">
                            <span class="text-sm font-bold text-[#16987C]">Total encaissé</span>
                            <span
                                class="font-grotesk text-lg font-black text-[#16987C]">{{ number_format($adherent->montant_total, 2, ',', ' ') }}
                                €</span>
                        </div>
                    </div>
                @endif

                <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.03)] overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#222A60]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                        </svg>
                        <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest">Commentaire gestionnaire</h2>
                    </div>

                    @if ($adherent->commentaire)
                        <div class="mx-5 mt-4 p-4 bg-amber-50/60 rounded-xl border border-amber-100">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-[10px] font-black text-amber-600 uppercase tracking-widest">Note
                                    interne</span>
                            </div>
                            <p class="text-sm text-gray-600 leading-relaxed">{{ $adherent->commentaire }}</p>
                        </div>
                    @endif

                    <form action="{{ route('adherents.commentaire', $adherent) }}" method="POST" class="px-5 py-4">
                        @csrf
                        <textarea name="commentaire" rows="3" placeholder="Ajouter une note..."
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-[#16987C]/30 focus:border-[#16987C]/40 transition-all resize-none"></textarea>
                        <div class="flex justify-end mt-2">
                            <button type="submit"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#222A60] hover:bg-[#1a2050] text-white text-xs font-bold rounded-xl transition-all">
                                Enregistrer
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
    @endsection
