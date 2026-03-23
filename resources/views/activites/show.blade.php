@extends('layouts.app')

@section('title', $activite->nom)

@section('content')

    <div class="mb-8">
        <div class="flex items-center gap-2 text-xs text-gray-400 mb-5 pl-1">
            <a href="{{ route('activites.index') }}" class="hover:text-[#222A60] transition-colors font-medium">Activités</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-600 font-semibold">{{ $activite->nom }}</span>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_24px_rgba(0,0,0,0.04)] overflow-hidden">
            <div class="h-1.5 bg-gradient-to-r from-[#222A60] via-[#16987C] to-[#16987C]/40"></div>
            <div class="p-6">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                    <div class="flex items-start gap-4">
                        <div
                            class="w-14 h-14 rounded-xl flex items-center justify-center shrink-0 {{ $activite->est_stage ? 'bg-amber-50 text-amber-500' : 'bg-[#222A60]/8 text-[#222A60]' }}">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if ($activite->est_stage)
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                @endif
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span
                                    class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-widest {{ $activite->est_stage ? 'bg-amber-100 text-amber-600' : 'bg-blue-50 text-blue-600' }}">
                                    {{ $activite->est_stage ? 'Stage' : 'En cours' }}
                                </span>
                            </div>
                            <h1 class="font-grotesk text-2xl font-black text-[#0F143A] tracking-tight">{{ $activite->nom }}
                            </h1>
                            <p class="text-sm text-gray-500 mt-1 flex flex-wrap gap-2">
                                @foreach ($activite->horaires_list as $h)
                                    <span>{{ $h }}</span>
                                @endforeach
                                @if ($activite->adresse || $activite->ville)
                                    <span>· {{ $activite->adresse }} {{ $activite->ville }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col items-end gap-3 shrink-0">
                        <div class="text-right">
                            <p class="text-sm text-gray-400">Responsable</p>
                            <p class="font-bold text-[#0F143A]">
                                {{ $activite->gestionnaires->first()?->name ?? 'Non assigné' }}
                            </p>
                        </div>
                        <a href="{{ route('activites.edit', $activite) }}"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-600 text-xs font-bold rounded-xl transition-all shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                            Modifier l'activité
                        </a>
                    </div>
                </div>
            </div>

            <div class="flex border-t border-gray-100 px-6">
                <button onclick="switchTab('presences')" id="tab-presences"
                    class="px-6 py-4 text-sm font-bold border-b-2 border-[#222A60] text-[#222A60] transition-colors">Présences</button>
                <button onclick="switchTab('adherents')" id="tab-adherents"
                    class="px-6 py-4 text-sm font-bold border-b-2 border-transparent text-gray-400 hover:text-gray-600 transition-colors">Adhérents</button>
                <button onclick="switchTab('statistiques')" id="tab-statistiques"
                    class="px-6 py-4 text-sm font-bold border-b-2 border-transparent text-gray-400 hover:text-gray-600 transition-colors">Statistiques</button>
            </div>
        </div>
    </div>

    {{-- ONGLET PRÉSENCES --}}
    <div id="content-presences" class="space-y-4">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-black text-[#0F143A]">Séances</h2>
        </div>

        @forelse($seances as $seance)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-4">
                <button onclick="toggleSeance({{ $seance->id_seance }})"
                    class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-4">
                        <div class="text-left">
                            <p class="font-bold text-[#0F143A] capitalize">{{ $seance->date->isoFormat('dddd') }}</p>
                            <p class="text-xs text-gray-400">{{ $seance->date->isoFormat('D MMM') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        @php
                            $nbPresents = $adherentsStats->count();
                        @endphp
                        <span
                            class="text-sm font-bold text-gray-500">{{ $nbPresents }}/{{ $adherentsStats->count() }}</span>
                        <svg id="icon-seance-{{ $seance->id_seance }}"
                            class="w-5 h-5 text-gray-400 transform transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </button>

                <div id="content-seance-{{ $seance->id_seance }}"
                    class="hidden border-t border-gray-100 bg-gray-50/30 p-6">
                    <form
                        action="{{ route('activites.presences.store', ['activite' => $activite->id, 'seance' => $seance->id_seance]) }}"
                        method="POST">
                        @csrf
                        <div class="space-y-3 mb-6">
                            @foreach ($adherentsStats as $adherent)
                                @php
                                    $presence = $seance->presences->where('id_adherent', $adherent->id)->first();
                                    $estAbsent = $presence !== null;
                                @endphp
                                <div
                                    class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-white rounded-xl border border-gray-100 shadow-[0_2px_8px_rgba(0,0,0,0.02)] gap-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-full bg-[#222A60]/10 flex items-center justify-center text-[#222A60] text-xs font-black">
                                            {{ $adherent->initiales }}
                                        </div>
                                        <span class="text-sm font-bold text-[#0F143A]">{{ $adherent->nom_complet }}</span>
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <input type="text" id="raison-{{ $seance->id_seance }}-{{ $adherent->id }}"
                                            name="presences[{{ $adherent->id }}][raison]"
                                            value="{{ $presence->raison ?? '' }}" placeholder="Motif d'absence..."
                                            class="{{ $estAbsent ? '' : 'hidden' }} text-xs px-3 py-1.5 border border-gray-200 rounded-lg bg-gray-50 text-gray-600 focus:outline-none focus:border-rose-300 w-48">

                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="presences[{{ $adherent->id }}][statut]"
                                                value="present" class="sr-only peer" {{ $estAbsent ? '' : 'checked' }}
                                                onchange="toggleRaison(this, 'raison-{{ $seance->id_seance }}-{{ $adherent->id }}')">
                                            <div
                                                class="w-11 h-6 bg-rose-100 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#16987C]">
                                            </div>
                                            <span
                                                class="ml-3 text-xs font-bold peer-checked:text-[#16987C] {{ $estAbsent ? 'text-rose-500' : 'text-gray-500' }}"
                                                id="label-{{ $seance->id_seance }}-{{ $adherent->id }}">
                                                {{ $estAbsent ? 'Absent' : 'Présent' }}
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="flex justify-end">
                            <button type="submit"
                                class="px-5 py-2 bg-[#222A60] hover:bg-[#1a2050] text-white text-xs font-bold rounded-xl transition-all shadow-sm">
                                Enregistrer les présences
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <div class="p-8 text-center bg-white rounded-2xl border border-gray-100 text-gray-400 text-sm font-medium">
                Aucune séance planifiée pour le moment.
            </div>
        @endforelse
    </div>

    {{-- ONGLET ADHÉRENTS --}}
    <div id="content-adherents" class="hidden">
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm font-bold text-gray-500">{{ $adherentsStats->count() }} adhérents actifs</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach ($adherentsStats as $adherent)
                @php
                    $colorClass = match (true) {
                        $adherent->taux_presence >= 75 => 'bg-emerald-50 text-emerald-600',
                        $adherent->taux_presence >= 50 => 'bg-amber-50 text-amber-600',
                        default => 'bg-rose-50 text-rose-500',
                    };
                @endphp

                {{-- Conteneur de l'adhérent (avec position relative pour l'action d'abandon) --}}
                <div class="relative p-4 bg-white rounded-2xl border border-gray-100 shadow-[0_2px_8px_rgba(0,0,0,0.02)] flex flex-col gap-3 group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white text-sm font-black shadow-sm"
                            style="background-color: {{ $adherent->couleur_avatar }}">
                            {{ $adherent->initiales }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('adherents.show', $adherent) }}"
                                class="font-bold text-sm text-[#0F143A] hover:text-[#16987C] transition-colors truncate block">
                                {{ $adherent->nom_complet }}
                            </a>
                            <div class="mt-1 inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black {{ $colorClass }}">
                                {{ $adherent->taux_presence }}% de présence
                            </div>
                        </div>

                        {{-- Bouton pour afficher le formulaire d'abandon (Croix) --}}
                        <button type="button" onclick="toggleAbandonForm({{ $adherent->id }})" title="Marquer comme abandon"
                            class="opacity-0 group-hover:opacity-100 transition-opacity p-2 text-gray-300 hover:text-rose-500 hover:bg-rose-50 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Formulaire d'abandon (caché par défaut) --}}
                    <div id="abandon-form-{{ $adherent->id }}" class="hidden pt-3 border-t border-gray-50 mt-1">
                        <form action="{{ route('activites.abandonner', ['activite' => $activite->id, 'adherent' => $adherent->id]) }}" method="POST" class="flex flex-col gap-2">
                            @csrf
                            <p class="text-[10px] font-bold text-rose-500 uppercase tracking-widest">Confirmer l'abandon</p>
                            <input type="text" name="motif_sortie" required placeholder="Motif (ex: Blessure, Horaire...)"
                                   class="text-xs px-3 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-600 focus:outline-none focus:border-rose-300 w-full">
                            <div class="flex justify-end gap-2 mt-1">
                                <button type="button" onclick="toggleAbandonForm({{ $adherent->id }})" class="text-xs font-bold text-gray-400 hover:text-gray-600">Annuler</button>
                                <button type="submit" class="px-3 py-1.5 bg-rose-500 hover:bg-rose-600 text-white text-xs font-bold rounded-lg transition-colors">Valider</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ONGLET STATISTIQUES --}}
    <div id="content-statistiques" class="hidden space-y-6">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-6 bg-white rounded-2xl border border-gray-100 shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Taux de présence moyen</p>
                <p class="font-grotesk text-3xl font-black text-[#0F143A]">{{ $tauxMoyen }}%</p>
                <p class="text-xs text-gray-400 mt-1">sur les {{ $nbSeancesPassees }} dernières séances</p>
            </div>
            <div class="p-6 bg-white rounded-2xl border border-gray-100 shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Adhérents actifs</p>
                <p class="font-grotesk text-3xl font-black text-[#0F143A]">{{ $actifs }} <span
                        class="text-lg text-gray-300">/ {{ $adherentsStats->count() }}</span></p>
                <p class="text-xs text-gray-400 mt-1">présents ≥ 75%</p>
            </div>
            <div class="p-6 bg-white rounded-2xl border border-gray-100 shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Taux d'abandon</p>
                <p class="font-grotesk text-3xl font-black text-rose-500">{{ $tauxAbandon }}%</p>
                <p class="text-xs text-gray-400 mt-1">{{ $nbAbandons }} abandon(s) cette saison</p>
            </div>
        </div>

        <div class="p-6 bg-white rounded-2xl border border-gray-100 shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-6">Présences par séance</p>

            @if ($graphiqueSeances->count() > 0)
                <div class="flex items-end gap-2 h-48 pt-8">
                    @foreach ($graphiqueSeances as $stat)
                        <div class="flex-1 h-full flex flex-col justify-end items-center gap-1 group relative">
                            <div
                                class="opacity-0 group-hover:opacity-100 transition-opacity absolute -top-8 bg-gray-800 text-white text-[10px] py-1 px-2 rounded font-bold whitespace-nowrap z-10 pointer-events-none">
                                {{ $stat['presents'] }} / {{ $stat['total'] }}
                            </div>
                            @if ($stat['pourcentage'] > 0)
                                <span class="text-xs font-bold text-[#0F143A]">{{ $stat['presents'] }}</span>
                                <div class="w-full bg-[#16987C] rounded-t-md transition-all duration-500 hover:bg-[#117a63]"
                                    style="height: {{ $stat['pourcentage'] }}%;"></div>
                            @else
                                <span class="text-xs font-bold text-gray-300">0</span>
                                <div class="w-full bg-gray-100 rounded-t-md" style="height: 4px;"></div>
                            @endif

                            <span
                                class="text-[10px] text-gray-400 font-medium whitespace-nowrap">{{ $stat['date'] }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="h-48 flex items-center justify-center text-gray-400 text-sm font-medium">
                    Pas encore de séance réalisée.
                </div>
            @endif
        </div>

    </div>

    <script>
        function switchTab(tabName) {
            ['presences', 'adherents', 'statistiques'].forEach(name => {
                document.getElementById('content-' + name).classList.add('hidden');
                let btn = document.getElementById('tab-' + name);
                btn.classList.remove('border-[#222A60]', 'text-[#222A60]');
                btn.classList.add('border-transparent', 'text-gray-400');
            });

            document.getElementById('content-' + tabName).classList.remove('hidden');
            let activeBtn = document.getElementById('tab-' + tabName);
            activeBtn.classList.remove('border-transparent', 'text-gray-400');
            activeBtn.classList.add('border-[#222A60]', 'text-[#222A60]');
        }

        function toggleSeance(id) {
            const content = document.getElementById('content-seance-' + id);
            const icon = document.getElementById('icon-seance-' + id);

            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                content.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        }

        function toggleRaison(checkbox, raisonId) {
            const raisonInput = document.getElementById(raisonId);
            const label = document.getElementById(raisonId.replace('raison-', 'label-'));

            if (checkbox.checked) {
                raisonInput.classList.add('hidden');
                raisonInput.value = '';
                label.textContent = "Présent";
                label.classList.remove('text-rose-500');
                label.classList.add('text-[#16987C]');
            } else {
                raisonInput.classList.remove('hidden');
                raisonInput.focus();
                label.textContent = "Absent";
                label.classList.remove('text-[#16987C]');
                label.classList.add('text-rose-500');
            }
        }

        function toggleAbandonForm(adherentId) {
            const form = document.getElementById('abandon-form-' + adherentId);
            if (form.classList.contains('hidden')) {
                form.classList.remove('hidden');
            } else {
                form.classList.add('hidden');
            }
        }
    </script>

@endsection 
