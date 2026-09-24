@extends('layouts.app')

@section('title', 'Modifier ma fiche')

@section('content')

    @php
        $field = 'w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-gray-800 bg-white focus:outline-none focus:ring-2 focus:ring-teal-500/25 focus:border-teal-500 transition-colors text-sm placeholder:text-gray-400';
        $label = 'block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide';
        $check = 'h-4 w-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500 cursor-pointer';
        $btn = 'inline-flex items-center justify-center gap-2 bg-teal-600 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-teal-700 active:scale-95 focus:ring-2 focus:ring-teal-500/30 transition text-sm shadow-sm';
        $card = 'bg-white rounded-2xl border border-gray-100 shadow-sm p-5 md:p-6 mb-6';
        $sectionTitle = 'text-lg font-bold text-gray-900 mb-4';
        $bulletinsAdh = is_array($adherent->bulletin) ? $adherent->bulletin : [];
    @endphp

    <div class="max-w-3xl mx-auto py-6 px-4">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Modifier ma fiche ✏️</h1>
            <p class="text-gray-400 mt-1 text-sm">Mettez à jour vos informations. Les changements sont enregistrés immédiatement.</p>
        </div>

        @if ($errors->any())
            <div class="mb-5 p-4 bg-rose-50 border border-rose-200 rounded-xl text-sm text-rose-700 font-medium">
                <p class="font-bold mb-1">Merci de corriger les champs suivants :</p>
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('adhesion.modifier.update', $token) }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Informations personnelles --}}
            <div class="{{ $card }}">
                <h2 class="{{ $sectionTitle }}">📋 Informations personnelles</h2>

                <div class="mb-4">
                    <label class="{{ $label }}">Genre</label>
                    <div class="grid grid-cols-2 gap-3 max-w-xs">
                        @foreach (['Homme' => '🧔', 'Femme' => '👩'] as $val => $icon)
                            <label class="cursor-pointer block group">
                                <input type="radio" name="genre" value="{{ $val }}"
                                    {{ $adherent->genre === $val ? 'checked' : '' }} class="sr-only peer">
                                <div class="border-2 rounded-lg p-3 text-center peer-checked:border-teal-500 peer-checked:bg-teal-50 border-gray-200 transition-all">
                                    <div class="text-xl mb-1">{{ $icon }}</div>
                                    <span class="text-xs font-bold text-slate-700">{{ $val }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="{{ $label }}">Nom *</label>
                        <input type="text" name="nom" value="{{ old('nom', $adherent->nom) }}" required class="{{ $field }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">Prénom *</label>
                        <input type="text" name="prenom" value="{{ old('prenom', $adherent->prenom) }}" required class="{{ $field }}">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="{{ $label }}">Adresse</label>
                    <input type="text" name="adresse" value="{{ old('adresse', $adherent->adresse) }}" class="{{ $field }}">
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="{{ $label }}">Code postal</label>
                        <input type="text" name="code_postal" value="{{ old('code_postal', $adherent->code_postal) }}" class="{{ $field }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">Ville</label>
                        <input type="text" name="ville" value="{{ old('ville', $adherent->ville) }}" class="{{ $field }}">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="{{ $label }}">🎂 Date de naissance</label>
                    <input type="date" name="date_naiss" value="{{ old('date_naiss', $adherent->date_naiss?->format('Y-m-d')) }}" class="{{ $field }} max-w-xs">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="{{ $label }}">📞 Téléphone</label>
                        <input type="tel" name="tel" value="{{ old('tel', $adherent->tel) }}" class="{{ $field }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">📧 Email</label>
                        <input type="email" name="mail" value="{{ old('mail', $adherent->mail) }}" class="{{ $field }}">
                    </div>
                </div>

                <div class="mb-2">
                    <label class="{{ $label }}">🏛️ Régime social</label>
                    <select name="regime_social" class="{{ $field }}">
                        <option value="">— Sélectionnez —</option>
                        @foreach (['Sécurité sociale générale', 'Mutuelle complémentaire', 'CSS / CMU-C', 'MSA (agricole)', 'RSI / Indépendants', 'Autre'] as $r)
                            <option value="{{ $r }}" {{ old('regime_social', $adherent->regime_social) === $r ? 'selected' : '' }}>{{ $r }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Situation --}}
            <div class="{{ $card }}">
                <h2 class="{{ $sectionTitle }}">💼 Situation actuelle</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-2">
                    <div>
                        <label class="{{ $label }}">Occupation</label>
                        <input type="text" name="occupation" value="{{ old('occupation', $adherent->occupation) }}"
                            placeholder="Ex : Collège, Étudiant, Salarié…" class="{{ $field }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">Établissement (si scolarisé·e)</label>
                        <input type="text" name="etablissement" value="{{ old('etablissement', $adherent->etablissement) }}" class="{{ $field }}">
                    </div>
                </div>
            </div>

            {{-- Médical --}}
            <div class="{{ $card }}">
                <h2 class="{{ $sectionTitle }}">🏥 Informations médicales</h2>

                <div class="mb-4">
                    <label class="{{ $label }}">📷 Carnet de santé / vaccins</label>
                    @if ($adherent->carnet)
                        <p class="text-xs text-emerald-600 font-semibold mb-2">✅ Un document est déjà enregistré. Vous pouvez le remplacer ci-dessous.</p>
                    @endif
                    <input type="file" name="carnet_sante" accept="image/*,.pdf" class="{{ $field }}">
                </div>

                <div class="mb-4">
                    <label class="{{ $label }}">⚕️ Problèmes de santé à signaler</label>
                    <textarea name="problemes_sante" rows="3" class="{{ $field }}">{{ old('problemes_sante', $adherent->problemes_sante) }}</textarea>
                </div>
                <div class="mb-4">
                    <label class="{{ $label }}">🤧 Allergies connues</label>
                    <textarea name="allergies" rows="3" class="{{ $field }}">{{ old('allergies', $adherent->allergies) }}</textarea>
                </div>
                <div class="mb-4">
                    <label class="{{ $label }}">🚨 Conduite à tenir en cas de crise</label>
                    <textarea name="conduite_a_tenir" rows="3" class="{{ $field }}">{{ old('conduite_a_tenir', $adherent->conduite_a_tenir) }}</textarea>
                </div>
                <div>
                    <label class="{{ $label }}">🍽️ Restrictions alimentaires</label>
                    <textarea name="restrictions_alimentaires" rows="2" class="{{ $field }}">{{ old('restrictions_alimentaires', $adherent->restrictions_alimentaires) }}</textarea>
                </div>
            </div>

            {{-- Orientation professionnelle --}}
            <div class="{{ $card }}">
                <h2 class="{{ $sectionTitle }}">🎓 Orientation professionnelle</h2>
                <div class="mb-4">
                    <label class="{{ $label }}">As-tu déjà une idée de métier que tu aimerais exercer ?</label>
                    <textarea name="idee_metier" rows="3" class="{{ $field }}">{{ old('idee_metier', $adherent->idee_metier) }}</textarea>
                </div>
                <div>
                    <label class="{{ $label }}">Qu'aimerais-tu découvrir ou apprendre ?</label>
                    <textarea name="decouverte_metier" rows="3" class="{{ $field }}">{{ old('decouverte_metier', $adherent->decouverte_metier) }}</textarea>
                </div>
            </div>

            {{-- Autorisations & communication --}}
            <div class="{{ $card }}">
                <h2 class="{{ $sectionTitle }}">📜 Autorisations & communication</h2>

                <input type="hidden" name="bulletin" value="">
                <div class="space-y-2 mb-4">
                    <p class="text-sm text-gray-700 font-semibold">Bulletins d'information souhaités</p>
                    @foreach (['general' => 'Association', 'creabot' => 'Créabot', 'schlouk_sciences' => 'Schlouk de sciences'] as $val => $lbl)
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="bulletin[]" value="{{ $val }}"
                                {{ in_array($val, old('bulletin', $bulletinsAdh)) ? 'checked' : '' }} class="{{ $check }}">
                            <span class="text-sm text-gray-700">{{ $lbl }}</span>
                        </label>
                    @endforeach
                </div>

                <label class="flex items-center gap-3 cursor-pointer border-t border-gray-100 pt-3 mb-3">
                    <input type="checkbox" name="communication" value="1"
                        {{ old('communication', $adherent->communication) ? 'checked' : '' }} class="{{ $check }}">
                    <span class="text-sm text-gray-700"><strong>Droit à l'image</strong> — J'autorise l'association à photographier et diffuser des images de l'adhérent·e.</span>
                </label>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="manif" value="1"
                        {{ old('manif', $adherent->manif) ? 'checked' : '' }} class="{{ $check }}">
                    <span class="text-sm text-gray-700"><strong>Participation aux manifestations</strong> de l'association.</span>
                </label>
            </div>

            {{-- Tuteurs --}}
            <div class="{{ $card }}" x-data="{
                tuteurs: {{ \Illuminate\Support\Js::from($adherent->tousLesTuteurs->map(fn($t) => [
                    'id' => $t->id, 'type' => $t->type, 'nom' => $t->nom, 'prenom' => $t->prenom,
                    'tel' => $t->tel, 'mail' => $t->mail, 'profession' => $t->profession,
                    'rentre_fin' => (bool) $t->rentre_fin, 'rentre_annul' => (bool) $t->rentre_annul,
                ])) }},
                addTuteur(type) {
                    this.tuteurs.push({ id: null, type: type, nom: '', prenom: '', tel: '', mail: '', profession: '', rentre_fin: false, rentre_annul: false });
                },
                removeTuteur(i) { this.tuteurs.splice(i, 1); }
            }">
                <h2 class="{{ $sectionTitle }}">👨‍👩‍👧 Représentants & tuteurs</h2>

                <template x-for="(tuteur, i) in tuteurs" :key="i">
                    <div class="border-2 rounded-xl p-4 mb-4 bg-white relative"
                        :class="{
                            'border-slate-300': tuteur.type === 'parent_tuteur',
                            'border-teal-300 bg-teal-50/30': tuteur.type === 'autre_autorise',
                            'border-red-200 bg-red-50/20': tuteur.type === 'non_autorise'
                        }">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm font-bold" x-text="tuteur.type === 'parent_tuteur' ? '👨‍👩‍👧 Parent / Tuteur·trice' : (tuteur.type === 'autre_autorise' ? '✅ Personne autorisée' : '🚫 Personne non autorisée')"></span>
                            <button type="button" @click="removeTuteur(i)"
                                class="text-red-500 bg-red-50 px-3 py-1 rounded-lg font-bold text-xs hover:bg-red-500 hover:text-white transition-colors">
                                Retirer
                            </button>
                        </div>

                        <input type="hidden" :name="'tuteurs[' + i + '][id]'" :value="tuteur.id">
                        <input type="hidden" :name="'tuteurs[' + i + '][type]'" :value="tuteur.type">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="{{ $label }}">Nom</label>
                                <input type="text" :name="'tuteurs[' + i + '][nom]'" x-model="tuteur.nom" class="{{ $field }}">
                            </div>
                            <div>
                                <label class="{{ $label }}">Prénom</label>
                                <input type="text" :name="'tuteurs[' + i + '][prenom]'" x-model="tuteur.prenom" class="{{ $field }}">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="{{ $label }}">📞 Téléphone</label>
                                <input type="tel" :name="'tuteurs[' + i + '][tel]'" x-model="tuteur.tel" class="{{ $field }}">
                            </div>
                            <div>
                                <label class="{{ $label }}">📧 Email</label>
                                <input type="email" :name="'tuteurs[' + i + '][mail]'" x-model="tuteur.mail" class="{{ $field }}">
                            </div>
                        </div>

                        <template x-if="tuteur.type === 'parent_tuteur'">
                            <div>
                                <div class="mb-4">
                                    <label class="{{ $label }}">💼 Profession (CSP)</label>
                                    <select :name="'tuteurs[' + i + '][profession]'" x-model="tuteur.profession" class="{{ $field }}">
                                        <option value="">— Sélectionner —</option>
                                        @foreach (['Agriculteur exploitant', "Artisan, commerçant, chef d'entreprise", 'Cadre et profession intellectuelle supérieure', 'Profession intermédiaire', 'Employé', 'Ouvrier', 'Retraité', 'Sans activité professionnelle', 'Autre'] as $p)
                                            <option value="{{ $p }}">{{ $p }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="space-y-3 p-4 bg-slate-50 border border-slate-200 rounded-xl">
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="checkbox" :name="'tuteurs[' + i + '][rentre_fin]'" value="1"
                                            :checked="tuteur.rentre_fin" @change="tuteur.rentre_fin = $event.target.checked" class="{{ $check }}">
                                        <span class="text-sm font-semibold text-slate-800">J'autorise mon enfant à rentrer seul·e à la fin de l'activité</span>
                                    </label>
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="checkbox" :name="'tuteurs[' + i + '][rentre_annul]'" value="1"
                                            :checked="tuteur.rentre_annul" @change="tuteur.rentre_annul = $event.target.checked" class="{{ $check }}">
                                        <span class="text-sm font-semibold text-slate-800">J'autorise mon enfant à rentrer seul·e en cas d'annulation</span>
                                    </label>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <div class="grid grid-cols-3 gap-2 mt-3">
                    <button type="button" @click="addTuteur('parent_tuteur')"
                        class="border border-dashed border-gray-300 text-gray-600 bg-gray-50 font-semibold rounded-lg py-3 px-2 hover:bg-gray-900 hover:text-white transition-colors text-xs text-center">
                        👨‍👩‍👧 Parent / tuteur
                    </button>
                    <button type="button" @click="addTuteur('autre_autorise')"
                        class="border border-dashed border-teal-300 text-teal-700 bg-teal-50 font-semibold rounded-lg py-3 px-2 hover:bg-teal-600 hover:text-white transition-colors text-xs text-center">
                        ✅ Personne autorisée
                    </button>
                    <button type="button" @click="addTuteur('non_autorise')"
                        class="border border-dashed border-red-300 text-red-600 bg-red-50 font-semibold rounded-lg py-3 px-2 hover:bg-red-500 hover:text-white transition-colors text-xs text-center">
                        🚫 Non autorisée
                    </button>
                </div>
                <p class="text-xs text-gray-400 mt-3">Retirer un tuteur ici le détache de cette fiche (il n'est pas supprimé s'il est aussi lié à un autre enfant de la famille).</p>
            </div>

            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('adhesion.choix', ['token' => $token]) }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">Annuler</a>
                <button type="submit" class="{{ $btn }}">
                    Enregistrer les modifications
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </button>
            </div>
        </form>
    </div>

@endsection
