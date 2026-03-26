@extends('layouts.app')

@section('title', 'Nouvelle activité')

@section('content')

    <div class="max-w-3xl mx-auto">
        <div class="flex items-center gap-2 text-xs text-gray-400 mb-6 pl-1">
            <a href="{{ route('activites.index') }}" class="hover:text-[#222A60] transition-colors font-medium">Activités</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-600 font-semibold">Nouvelle création</span>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_24px_rgba(0,0,0,0.04)] overflow-hidden">
            <div class="h-1.5 bg-gradient-to-r from-[#222A60] via-[#16987C] to-[#16987C]/40"></div>

            <div class="p-8">
                <div class="mb-8">
                    <h1 class="font-grotesk text-2xl font-black text-[#0F143A] tracking-tight">Créer une activité ou un
                        stage</h1>
                    <p class="text-sm text-gray-400 mt-1">Remplissez les informations ci-dessous pour ajouter une nouvelle
                        entrée au catalogue.</p>
                </div>

                <form action="{{ route('activites.store') }}" method="POST" class="space-y-8">
                    @csrf

                    <div>
                        <span class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Type d'événement
                            <span class="text-rose-500">*</span></span>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="type" value="activite" class="peer sr-only" checked
                                    onchange="updateTypeUI()">
                                <div class="p-4 rounded-xl border-2 border-gray-100 bg-white transition-all peer-checked:border-[#222A60] peer-checked:bg-[#222A60]/5 hover:border-gray-200"
                                    id="card-activite">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center transition-colors bg-[#222A60]/10 text-[#222A60]"
                                            id="icon-activite">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-[#0F143A]">Activité régulière</p>
                                            <p class="text-xs text-gray-400">Cours hebdomadaire, annuel...</p>
                                        </div>
                                    </div>
                                </div>
                            </label>

                            <label class="relative cursor-pointer group">
                                <input type="radio" name="type" value="stage" class="peer sr-only"
                                    onchange="updateTypeUI()">
                                <div class="p-4 rounded-xl border-2 border-gray-100 bg-white transition-all peer-checked:border-amber-500 peer-checked:bg-amber-50 hover:border-gray-200"
                                    id="card-stage">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center transition-colors bg-gray-100 text-gray-400"
                                            id="icon-stage">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-[#0F143A]">Stage ponctuel</p>
                                            <p class="text-xs text-gray-400">Événement sur une courte durée</p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Nom de
                                l'événement <span class="text-rose-500">*</span></label>
                            <input type="text" name="nom" value="{{ old('nom') }}"
                                placeholder="Ex: Atelier Poterie" required
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-[#16987C]/30 focus:border-[#16987C]/40 transition-all">
                            @error('nom')
                                <span class="text-xs text-rose-500 mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="md:col-span-2" x-data="gestionnaireSearch()">
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">
                                Gestionnaire(s) associé(s) <span class="text-rose-500">*</span>
                            </label>

                            <div class="flex flex-wrap gap-2 mb-3">
                                <template x-for="user in selectedUsers" :key="user.id">
                                    <div
                                        class="inline-flex items-center gap-2 px-3 py-1.5 bg-[#222A60] text-white text-xs font-bold rounded-lg shadow-sm">
                                        <span x-text="user.firstname + ' ' + user.name"></span>
                                        <button type="button" @click="removeUser(user.id)"
                                            class="hover:text-rose-400 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                        <input type="hidden" name="gestionnaires[]" :value="user.id">
                                    </div>
                                </template>
                            </div>

                            <div class="relative">
                                <div
                                    class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input type="text" x-model="query" @input.debounce.300ms="search()"
                                    @keydown.escape="results = []" placeholder="Taper un nom ou un prénom..."
                                    class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-[#16987C]/30 focus:border-[#16987C]/40 transition-all">

                                <div x-show="results.length > 0" @click.away="results = []"
                                    class="absolute z-50 w-full mt-2 bg-white border border-gray-100 rounded-xl shadow-xl overflow-hidden">
                                    <template x-for="user in results" :key="user.id">
                                        <button type="button" @click="addUser(user)"
                                            class="w-full px-4 py-3 text-left text-sm hover:bg-gray-50 flex items-center justify-between group transition-colors">
                                            <div>
                                                <span class="font-bold text-[#0F143A]"
                                                    x-text="user.firstname + ' ' + user.name"></span>
                                            </div>
                                            <svg class="w-4 h-4 text-gray-300 group-hover:text-[#16987C] transition-colors"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            @error('gestionnaires')
                                <span class="text-xs text-rose-500 mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Tarif
                                (€)</label>
                            <input type="number" step="0.01" min="0" name="tarif"
                                value="{{ old('tarif') }}" placeholder="Ex: 150.00"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-[#16987C]/30 focus:border-[#16987C]/40 transition-all">
                            @error('tarif')
                                <span class="text-xs text-rose-500 mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Ville</label>
                            <input type="text" name="ville" value="{{ old('ville') }}"
                                placeholder="Ex: Strasbourg"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-[#16987C]/30 focus:border-[#16987C]/40 transition-all">
                            @error('ville')
                                <span class="text-xs text-rose-500 mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label
                                class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Adresse</label>
                            <input type="text" name="adresse" value="{{ old('adresse') }}"
                                placeholder="Ex: 12 Rue des Arts"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-[#16987C]/30 focus:border-[#16987C]/40 transition-all">
                            @error('adresse')
                                <span class="text-xs text-rose-500 mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Horaires dynamiques --}}
                        <div class="md:col-span-2 p-5 bg-gray-50/50 rounded-xl border border-gray-100">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-bold text-[#0F143A]">Créneaux horaires</h3>
                                <button type="button" id="btn-add-horaire"
                                    class="inline-flex items-center gap-1 text-xs font-bold text-[#16987C] hover:text-[#117a63] transition-colors bg-[#16987C]/10 px-3 py-1.5 rounded-lg">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                    Ajouter un créneau
                                </button>
                            </div>

                            <div id="horaires-container" class="space-y-3">
                                {{-- Ligne de base (index 0) --}}
                                <div
                                    class="horaire-row flex flex-wrap sm:flex-nowrap items-center gap-3 bg-white p-3 rounded-lg border border-gray-200">
                                    <div class="w-full sm:w-1/3">
                                        <select name="jours[]"
                                            class="w-full px-3 py-2 bg-gray-50 border border-gray-100 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#16987C]/30 focus:border-[#16987C]/40">
                                            <option value="">Choisir un jour...</option>
                                            <option value="Lundi">Lundi</option>
                                            <option value="Mardi">Mardi</option>
                                            <option value="Mercredi">Mercredi</option>
                                            <option value="Jeudi">Jeudi</option>
                                            <option value="Vendredi">Vendredi</option>
                                            <option value="Samedi">Samedi</option>
                                            <option value="Dimanche">Dimanche</option>
                                            <option value="Tous les jours">Tous les jours</option>
                                            <option value="Lundi au Vendredi">Lundi au Vendredi</option>
                                        </select>
                                    </div>
                                    <div class="flex items-center gap-2 w-full sm:w-2/3">
                                        <span class="text-xs font-bold text-gray-400">De</span>
                                        <input type="time" name="debuts[]"
                                            class="flex-1 px-3 py-2 bg-gray-50 border border-gray-100 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#16987C]/30 focus:border-[#16987C]/40">
                                        <span class="text-xs font-bold text-gray-400">à</span>
                                        <input type="time" name="fins[]"
                                            class="flex-1 px-3 py-2 bg-gray-50 border border-gray-100 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#16987C]/30 focus:border-[#16987C]/40">
                                        <button type="button"
                                            class="btn-remove-horaire p-2 text-gray-300 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-colors invisible">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @include('activites._classes_picker', ['selectedClasses' => old('classes', [])])

                    <div x-data="{ dossierAction: '{{ old('dossier_action', 'none') }}' }">
                        <span class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Dossier</span>
                        <div class="space-y-2 p-4 bg-gray-50/50 rounded-xl border border-gray-100">

                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="radio" name="dossier_action" value="none" x-model="dossierAction" class="sr-only peer">
                                <div class="w-4 h-4 rounded-full border-2 border-gray-300 peer-checked:border-[#222A60] peer-checked:bg-[#222A60] flex items-center justify-center flex-shrink-0 transition-colors">
                                    <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                                </div>
                                <span class="text-sm text-gray-600 font-medium">Aucun dossier</span>
                            </label>

                            @if($dossiers->isNotEmpty())
                                <label class="flex items-start gap-3 cursor-pointer group">
                                    <input type="radio" name="dossier_action" value="existing" x-model="dossierAction" class="sr-only peer">
                                    <div class="w-4 h-4 rounded-full border-2 border-gray-300 peer-checked:border-[#222A60] peer-checked:bg-[#222A60] flex items-center justify-center flex-shrink-0 transition-colors mt-0.5">
                                        <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                                    </div>
                                    <div class="flex-1">
                                        <span class="text-sm text-gray-600 font-medium">Dossier existant</span>
                                        <div x-show="dossierAction === 'existing'" class="mt-2">
                                            <select name="id_dossier"
                                                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#16987C]/30 focus:border-[#16987C]/40">
                                                <option value="">Choisir un dossier...</option>
                                                @foreach($dossiers as $d)
                                                    <option value="{{ $d->id }}" {{ old('id_dossier') == $d->id ? 'selected' : '' }}>{{ $d->nom }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </label>
                            @endif

                            <label class="flex items-start gap-3 cursor-pointer group">
                                <input type="radio" name="dossier_action" value="new" x-model="dossierAction" class="sr-only peer">
                                <div class="w-4 h-4 rounded-full border-2 border-gray-300 peer-checked:border-[#222A60] peer-checked:bg-[#222A60] flex items-center justify-center flex-shrink-0 transition-colors mt-0.5">
                                    <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                                </div>
                                <div class="flex-1">
                                    <span class="text-sm text-gray-600 font-medium">Créer un nouveau dossier</span>
                                    <div x-show="dossierAction === 'new'" class="mt-2">
                                        <input type="text" name="nouveau_dossier" value="{{ old('nouveau_dossier') }}"
                                            placeholder="Nom du dossier..."
                                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-[#16987C]/30 focus:border-[#16987C]/40">
                                    </div>
                                </div>
                            </label>

                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-50 mt-8">
                        <a href="{{ route('activites.index') }}"
                            class="px-5 py-2.5 text-sm font-bold text-gray-500 hover:text-[#0F143A] transition-colors">
                            Annuler
                        </a>
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#222A60] hover:bg-[#1a2050] text-white text-sm font-bold rounded-xl transition-all shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Créer l'événement
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script>
        function updateTypeUI() {
            const isStage = document.querySelector('input[name="type"]:checked').value === 'stage';
            const iconActivite = document.getElementById('icon-activite');
            const iconStage = document.getElementById('icon-stage');

            if (isStage) {
                iconStage.className =
                    "w-10 h-10 rounded-lg flex items-center justify-center transition-colors bg-amber-500/20 text-amber-600";
                iconActivite.className =
                    "w-10 h-10 rounded-lg flex items-center justify-center transition-colors bg-gray-100 text-gray-400";
            } else {
                iconActivite.className =
                    "w-10 h-10 rounded-lg flex items-center justify-center transition-colors bg-[#222A60]/10 text-[#222A60]";
                iconStage.className =
                    "w-10 h-10 rounded-lg flex items-center justify-center transition-colors bg-gray-100 text-gray-400";
            }
        }
        document.addEventListener('DOMContentLoaded', updateTypeUI);

        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('horaires-container');
            const btnAdd = document.getElementById('btn-add-horaire');

            btnAdd.addEventListener('click', function() {
                const firstRow = container.querySelector('.horaire-row');
                const newRow = firstRow.cloneNode(true);

                newRow.querySelector('select').value = '';
                const inputs = newRow.querySelectorAll('input[type="time"]');
                inputs.forEach(input => input.value = '');

                const removeBtn = newRow.querySelector('.btn-remove-horaire');
                removeBtn.classList.remove('invisible');

                removeBtn.addEventListener('click', function() {
                    newRow.remove();
                });

                container.appendChild(newRow);
            });
        });

        function gestionnaireSearch() {
            return {
                query: '',
                results: [],
                selectedUsers: @json(old('gestionnaires_full', [])),

                search() {
                    if (this.query.length < 2) {
                        this.results = [];
                        return;
                    }
                    fetch(`{{ route('users.search') }}?q=${this.query}`)
                        .then(response => response.json())
                        .then(data => {
                            this.results = data.filter(user => !this.selectedUsers.find(s => s.id === user.id));
                        });
                },

                addUser(user) {
                    this.selectedUsers.push(user);
                    this.query = '';
                    this.results = [];
                },

                removeUser(id) {
                    this.selectedUsers = this.selectedUsers.filter(user => user.id !== id);
                }
            }
        }
    </script>

@endsection
