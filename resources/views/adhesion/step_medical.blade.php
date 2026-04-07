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
