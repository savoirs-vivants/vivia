<div x-show="open" x-transition.opacity class="fixed inset-0 bg-black/30 backdrop-blur-sm z-40" style="display:none"
    @click="close()">
</div>

<div x-show="open" x-transition:enter="transition duration-200"
    x-transition:enter-start="opacity-0 scale-95 translate-y-2"
    x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition duration-150"
    x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 pointer-events-none" style="display:none">

    <div class="bg-white rounded-2xl shadow-[0_20px_60px_rgba(0,0,0,0.15)] w-full max-w-md pointer-events-auto overflow-hidden"
        @click.stop>

        <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-xs font-black shrink-0"
                    :style="'background-color:' + adherent.couleur">
                    <span x-text="adherent.initiales"></span>
                </div>
                <div>
                    <p class="font-bold text-sm text-[#0F143A]" x-text="adherent.nom"></p>
                    <p class="text-xs text-gray-400" x-text="adherent.meta"></p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-lg text-xs font-bold" :class="adherent.sourceClass"
                    x-text="adherent.source"></span>
                <button @click="close()"
                    class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-300 hover:text-gray-500 hover:bg-gray-100 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="p-5 space-y-4 max-h-[60vh] overflow-y-auto">

            <template x-if="adherent.isPartiel">
                <div class="space-y-4">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Solde en cours
                        </p>
                        <div class="grid grid-cols-3 gap-2 mb-3">
                            <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100 text-center">
                                <p class="text-[10px] font-bold text-emerald-500 uppercase mb-1">Déjà versé</p>
                                <p class="font-black text-sm text-emerald-700" x-text="adherent.dejaVerse"></p>
                            </div>
                            <div class="p-3 bg-amber-50 rounded-xl border border-amber-100 text-center">
                                <p class="text-[10px] font-bold text-amber-500 uppercase mb-1">Reste dû</p>
                                <p class="font-black text-sm text-amber-600" x-text="resteApresVersement"></p>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 text-center">
                                <p class="text-[10px] font-bold text-gray-400 uppercase mb-1">Total</p>
                                <p class="font-black text-sm text-gray-700" x-text="adherent.montant"></p>
                            </div>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="bg-[#16987C] h-1.5 rounded-full transition-all duration-500"
                                :style="'width:' + progressPercent + '%'"></div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Enregistrer un
                            versement</p>
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <label
                                    class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Montant
                                    reçu</label>
                                <div class="relative">
                                    <input type="number" x-model="montantVersement"
                                        @input="calculerResteApresVersement()" step="0.01" min="0.01"
                                        placeholder="0,00"
                                        class="w-full pl-3 pr-8 py-2 bg-white border border-gray-200 rounded-lg text-sm font-semibold text-[#0F143A] focus:outline-none focus:ring-2 focus:ring-[#16987C]/30 focus:border-[#16987C]/40">
                                    <span
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-bold">€</span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <label
                                    class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Date</label>
                                <input type="date" x-model="dateVersement"
                                    class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm font-semibold text-[#0F143A] focus:outline-none focus:ring-2 focus:ring-[#16987C]/30">
                            </div>
                        </div>
                        <div>
                            <label
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Moyen
                                de paiement</label>
                            <select x-model="sourceVersement"
                                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm font-semibold text-[#0F143A] focus:outline-none focus:ring-2 focus:ring-[#16987C]/30 focus:border-[#16987C]/40">
                                <option>Espèces</option>
                                <option>Chèque</option>
                                <option>Virement</option>
                            </select>
                        </div>
                        <p class="text-xs text-gray-400">
                            Si le solde est soldé, le statut passera automatiquement en
                            <span class="font-bold text-emerald-600">Payé</span>.
                        </p>
                    </div>
                </div>
            </template>

            <template x-if="!adherent.isPartiel">
                <div class="space-y-4">

                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Détail
                            Inscription</p>
                        <div class="space-y-2.5">
                            <template x-for="activite in adherent.activites" :key="activite.nom">
                                <div class="flex items-center justify-between"
                                    :class="adherent.isReinscription ? 'bg-amber-50/50 p-2 rounded' : ''">
                                    <div>
                                        <p class="text-sm font-semibold text-[#0F143A]" x-text="activite.nom"></p>
                                        <p class="text-xs text-gray-400" x-text="activite.info"></p>
                                        <template x-if="adherent.isReinscription">
                                            <p class="text-xs font-bold text-amber-600 mt-0.5">NOUVEAU</p>
                                        </template>
                                    </div>
                                    <p class="text-sm font-black text-[#0F143A]" x-text="activite.tarif"></p>
                                </div>
                            </template>
                        </div>
                        <div class="mt-3 pt-2 border-t border-gray-50 flex justify-end"
                            x-show="!adherent.isStructure && !adherent.isReinscription && adherent.showCotisation !== false">
                            <p class="text-xs text-gray-400">+ Adhésion annuelle : <span class="font-semibold"
                                    x-text="adherent.montantAdhesion || '10,00 €'"></span></p>
                        </div>
                    </div>

                    <template x-if="adherent.isPreInscrit">
                        <div x-data="{
                            acompteVerse: adherent.totalVerse ?? 50,
                            montantTotal: parseFloat(adherent.montant.toString().replace(',', '.').replace(' €', ''))
                        }" class="p-4 bg-indigo-50 border border-indigo-100 rounded-xl">

                            <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-3">Validation
                                de la rentrée</p>

                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-semibold text-indigo-900">Total de l'adhésion</span>
                                <span class="text-sm font-bold text-indigo-900" x-text="adherent.montant"></span>
                            </div>

                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-indigo-700">Acompte déjà versé</span>
                                <span class="text-sm text-indigo-700"
                                    x-text="'- ' + acompteVerse.toFixed(2).replace('.', ',') + ' €'"></span>
                            </div>

                            <div class="w-full h-px bg-indigo-200/60 my-2"></div>

                            <div class="flex items-center justify-between">
                                <span class="text-sm font-black text-indigo-900">Reste à régler</span>
                                <span class="text-lg font-black text-indigo-600"
                                    x-text="Math.max(0, montantTotal - acompteVerse).toFixed(2).replace('.', ',') + ' €'">
                                </span>
                            </div>

                        </div>
                    </template>

                    <template x-if="adherent.isReinscription">
                        <div
                            class="px-4 py-3 bg-indigo-50 border border-indigo-100 rounded-xl flex items-start gap-2.5">
                            <span style="font-size:16px">🔄</span>
                            <div>
                                <p class="text-xs font-bold text-indigo-800">Ajout en cours d'année</p>
                                <p class="text-xs text-indigo-600 mt-1 leading-relaxed">
                                    Cet adhérent s'est réinscrit pour ajouter de nouveaux éléments : <br>
                                    <span
                                        class="inline-block mt-1 mb-1 font-bold text-indigo-900 px-2 py-0.5 bg-indigo-100 rounded-md"
                                        x-text="adherent.activites && adherent.activites.length > 0
                                    ? adherent.activites.map(a => a.nom).join(' • ')
                                    : {
                                    'ressourcerie': 'Ressourcerie',
                                    'recherche': 'Recherche participative',
                                    'soutien': 'Adhésion de soutien',
                                    'stage': 'Stage'
                                    }[adherent.type_adhesion_attente] || 'Atelier / Club'">
                                    </span>
                                </p>
                            </div>
                        </div>
                    </template>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 rounded-xl border"
                            x-show="!adherent.isPreInscrit"
                            :class="adherent.source === 'HelloAsso' ?
                                'bg-[#16987C]/8 border-[#16987C]/15' :
                                'bg-amber-50 border-amber-100'">
                            <span class="text-sm font-bold"
                                :class="adherent.source === 'HelloAsso' ? 'text-[#16987C]' : 'text-amber-600'"
                                x-text="adherent.source === 'HelloAsso' ? 'Total réglé sur HelloAsso' : 'Total à régler'">
                            </span>
                            <span class="text-base font-black"
                                :class="adherent.source === 'HelloAsso' ? 'text-[#16987C]' : 'text-amber-600'"
                                x-text="adherent.montant">
                            </span>
                        </div>

                        <template x-if="adherent.reductionFormate">
                            <div class="flex items-center justify-between pt-2 border-t border-amber-200/60">
                                <span class="text-xs font-bold text-emerald-600 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    Pondération appliquée
                                </span>
                                <span class="text-xs font-black text-emerald-600">
                                    - <span x-text="adherent.reductionFormate"></span> €
                                </span>
                            </div>
                        </template>

                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100"
                            x-show="adherent.saison">
                            <span class="text-sm font-semibold text-gray-600">Année scolaire</span>
                            <span class="text-sm font-black text-[#0F143A]" x-text="adherent.saison"></span>
                        </div>

                        <div x-show="!adherent.isPreInscrit"
                            class="flex items-center justify-between p-3 bg-rose-50 rounded-xl border border-rose-100">
                            <span class="text-sm font-semibold text-gray-600">Statut actuel</span>
                            <span class="flex items-center gap-1.5 text-xs font-bold text-rose-500">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                En attente de validation
                            </span>
                        </div>

                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 space-y-3"
                            x-show="!adherent.isStructure && !adherent.isPreInscrit">
                            <button type="button" @click="plusieursVersements = !plusieursVersements"
                                class="w-full flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <span class="text-sm font-bold text-gray-600">Paiement en plusieurs fois</span>
                                </div>
                                <div class="w-9 h-5 rounded-full transition-colors duration-200 relative"
                                    :class="plusieursVersements ? 'bg-[#16987C]' : 'bg-gray-200'">
                                    <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200"
                                        :class="plusieursVersements ? 'translate-x-4' : 'translate-x-0'"></div>
                                </div>
                            </button>

                            <div x-show="plusieursVersements" x-transition:enter="transition duration-150"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="space-y-2 pt-1 border-t border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div class="flex-1">
                                        <label
                                            class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">1er
                                            versement reçu</label>
                                        <div class="relative">
                                            <input type="number" x-model="montantRecu" @input="calculerReste()"
                                                step="0.01" min="0" placeholder="0,00"
                                                class="w-full pl-3 pr-8 py-2 bg-white border border-gray-200 rounded-lg text-sm font-semibold text-[#0F143A] focus:outline-none focus:ring-2 focus:ring-[#16987C]/30 focus:border-[#16987C]/40">
                                            <span
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-bold">€</span>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <label
                                            class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Reste
                                            dû</label>
                                        <div class="relative">
                                            <input type="text" :value="resteFormate" readonly
                                                class="w-full pl-3 pr-8 py-2 bg-amber-50 border border-amber-100 rounded-lg text-sm font-black text-amber-600 cursor-not-allowed">
                                            <span
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-amber-400 font-bold">€</span>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-400">
                                    Le statut passera en <span class="font-bold text-amber-600">Partiel</span> jusqu'au
                                    solde complet.
                                </p>
                            </div>
                        </div>

                    </div>
                </div>
            </template>
        </div>

        <div class="flex items-center justify-between px-5 py-4 border-t border-gray-100 bg-gray-50/50">
            <button @click="close()"
                class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition-all">
                Annuler
            </button>

            <form x-show="adherent.isPartiel" :action="adherent.versementUrl" method="POST">
                @csrf
                <input type="hidden" name="montant_versement" :value="montantVersement">
                <input type="hidden" name="source" :value="sourceVersement">
                <input type="hidden" name="date_paiement" :value="dateVersement">
                <button type="submit" :disabled="!montantVersement || parseFloat(montantVersement) <= 0"
                    class="inline-flex items-center gap-2 px-5 py-2 text-white text-sm font-bold rounded-xl transition-all shadow-sm bg-[#16987C] hover:bg-[#138a6f] disabled:opacity-40 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    Ajouter le versement
                </button>
            </form>

            <form x-show="!adherent.isPartiel && !adherent.isPreInscrit" :action="actionUrl" method="POST">
                @csrf
                <input type="hidden" name="plusieurs_versements" :value="plusieursVersements ? '1' : '0'">
                <input type="hidden" name="montant_recu" :value="montantRecu">
                <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2 text-white text-sm font-bold rounded-xl transition-all shadow-sm"
                    :class="plusieursVersements ? 'bg-amber-500 hover:bg-amber-600' : 'bg-[#16987C] hover:bg-[#138a6f]'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span x-text="plusieursVersements ? 'Valider (partiel)' : 'Valider l\'adhésion'"></span>
                </button>
            </form>
        </div>
    </div>
</div>
