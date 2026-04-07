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
                                                <label class="{{ $label }}">📞 Téléphone *</label>
                                                <input type="tel" :name="'tuteurs[' + i + '][tel]'"
                                                    x-model="tuteur.tel" placeholder="06 00 00 00 00"
                                                    class="{{ $field }}">
                                            </div>
                                            <div>
                                                <label class="{{ $label }}">📧 Email *</label>
                                                <input type="email" :name="'tuteurs[' + i + '][mail]'"
                                                    x-model="tuteur.mail" placeholder="email@exemple.com"
                                                    class="{{ $field }}" required>
                                            </div>
                                        </div>

                                        <template x-if="tuteur.type === 'parent_tuteur'">
                                            <div>
                                                <div class="mb-5">
                                                    <label class="{{ $label }}">💼 Profession</label>
                                                    <input type="text" :name="'tuteurs[' + i + '][profession]'" x-model="tuteur.profession" placeholder="Ex : Enseignant, Indépendant..." class="{{ $field }}">
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
