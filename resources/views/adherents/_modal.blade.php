<div x-show="open" x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition duration-150"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    class="fixed inset-0 bg-black/30 backdrop-blur-sm z-40" style="display:none" @click="close()">
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
                        <p class="text-xs text-gray-400">Si le solde est soldé, le statut passera automatiquement en
                            <span class="font-bold text-emerald-600">Payé</span>.</p>
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
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-[#0F143A]" x-text="activite.nom"></p>
                                        <p class="text-xs text-gray-400" x-text="activite.info"></p>
                                    </div>
                                    <p class="text-sm font-black text-[#0F143A]" x-text="activite.tarif"></p>
                                </div>
                            </template>
                        </div>
                        <div class="mt-3 pt-2 border-t border-gray-50 flex justify-end"
                            x-show="!adherent.isStructure && !adherent.activites.some(a => a.nom.toLowerCase().includes('club maker'))">
                            <p class="text-xs text-gray-400">+ Adhésion annuelle : <span class="font-semibold">10,00
                                    €</span></p>
                        </div>
                    </div>

                    <template x-if="adherent.source === 'HelloAsso'">
                        <div class="space-y-3">
                            <div
                                class="flex items-center justify-between p-3 bg-[#16987C]/8 rounded-xl border border-[#16987C]/15">
                                <span class="text-sm font-bold text-[#16987C]">Total réglé sur HelloAsso</span>
                                <span class="font-grotesk text-base font-black text-[#16987C]"
                                    x-text="adherent.montant"></span>
                            </div>
                            <div
                                class="flex items-center justify-between p-3 bg-rose-50 rounded-xl border border-rose-100">
                                <span class="text-sm font-semibold text-gray-600">Statut actuel</span>
                                <span class="flex items-center gap-1.5 text-xs font-bold text-rose-500">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    En attente de validation
                                </span>
                            </div>
                        </div>
                    </template>

                    <template x-if="adherent.source !== 'HelloAsso'">
                        <div class="space-y-3">
                            <div
                                class="flex items-center justify-between p-3 bg-amber-50 rounded-xl border border-amber-100">
                                <span class="text-sm font-bold text-amber-600">Total à régler</span>
                                <span class="font-grotesk text-base font-black text-amber-600"
                                    x-text="adherent.montant"></span>
                            </div>
                            <div
                                class="flex items-center justify-between p-3 bg-rose-50 rounded-xl border border-rose-100">
                                <span class="text-sm font-semibold text-gray-600">Statut actuel</span>
                                <span class="flex items-center gap-1.5 text-xs font-bold text-rose-500">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    En attente de validation
                                </span>
                            </div>

                            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 space-y-3"
                                x-show="!adherent.isStructure">
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
                                    <p class="text-xs text-gray-400">Le statut passera en <span
                                            class="font-bold text-amber-600">Partiel</span> jusqu'au solde complet.</p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        <div class="flex items-center justify-between px-5 py-4 border-t border-gray-100 bg-gray-50/50">
            <button @click="close()"
                class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition-all">Annuler</button>

            <form x-show="adherent.isPartiel" :action="adherent.versementUrl" method="POST">
                @csrf
                <input type="hidden" name="montant_versement" :value="montantVersement">
                <input type="hidden" name="source" :value="sourceVersement">
                <input type="hidden" name="date_paiement" :value="dateVersement">
                <button type="submit" :disabled="!montantVersement || parseFloat(montantVersement) <= 0"
                    class="inline-flex items-center gap-2 px-5 py-2 text-white text-sm font-bold rounded-xl transition-all shadow-sm bg-[#16987C] hover:bg-[#138a6f] disabled:opacity-40 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg> Ajouter le versement
                </button>
            </form>

            <form x-show="!adherent.isPartiel" :action="actionUrl" method="POST">
                @csrf
                <input type="hidden" name="commentaire" :value="commentaire">
                <input type="hidden" name="plusieurs_versements" :value="plusieursVersements ? '1' : '0'">
                <input type="hidden" name="montant_recu" :value="montantRecu">
                <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2 text-white text-sm font-bold rounded-xl transition-all shadow-sm"
                    :class="plusieursVersements ? 'bg-amber-500 hover:bg-amber-600' : 'bg-[#16987C] hover:bg-[#138a6f]'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg> <span x-text="plusieursVersements ? 'Valider (partiel)' : 'Valider l\'adhésion'"></span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function adherentOverlay() {
        return {
            open: false,
            actionUrl: '',
            commentaire: '',
            plusieursVersements: false,
            montantRecu: '',
            resteFormate: '0,00',
            montantVersement: '',
            sourceVersement: 'Espèces',
            dateVersement: new Date().toISOString().split('T')[0],
            resteApresVersement: '—',
            progressPercent: 0,
            adherent: {
                isStructure: false,
                isPartiel: false,
                id: null,
                nom: '',
                initiales: '',
                couleur: '',
                meta: '',
                source: '',
                sourceClass: '',
                montant: '',
                refFacture: '',
                datePaiement: '',
                activites: [],
                commentaire: '',
                versementUrl: '',
                dejaVerse: '',
                resteDu: '',
                resteDuBrut: 0,
                dejaVerseBrut: 0,
                montantBrut: 0,
            },
            ouvrirModal(data) {
                this.adherent = data;
                this.actionUrl = data.actionUrl;
                this.commentaire = data.commentaire || '';
                this.plusieursVersements = false;
                this.montantRecu = '';
                this.resteFormate = '0,00';
                this.montantVersement = '';
                this.sourceVersement = 'Espèces';
                this.dateVersement = new Date().toISOString().split('T')[0];
                this.resteApresVersement = data.resteDu ?? '—';
                this.progressPercent = data.montantBrut > 0 ? Math.min(100, Math.round((data.dejaVerseBrut / data
                    .montantBrut) * 100)) : 0;
                this.open = true;
                document.body.style.overflow = 'hidden';
            },
            calculerReste() {
                const total = parseFloat(this.adherent.montant.replace(/\s/g, '').replace(',', '.').replace('€', '')) ||
                    0;
                const recu = parseFloat(this.montantRecu) || 0;
                this.resteFormate = Math.max(0, total - recu).toFixed(2).replace('.', ',');
            },
            calculerResteApresVersement() {
                const resteBrut = parseFloat(this.adherent.resteDuBrut) || 0;
                const versement = parseFloat(this.montantVersement) || 0;
                this.resteApresVersement = Math.max(0, resteBrut - versement).toFixed(2).replace('.', ',') + ' €';
                if (this.adherent.montantBrut > 0) {
                    const verse = (this.adherent.dejaVerseBrut || 0) + versement;
                    this.progressPercent = Math.min(100, Math.round((verse / this.adherent.montantBrut) * 100));
                }
            },
            close() {
                this.open = false;
                document.body.style.overflow = '';
            }
        }
    }
</script>
