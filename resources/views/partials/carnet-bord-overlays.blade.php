{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- OVERLAY 1 : APPEL                                              --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div id="overlay-appel"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4"
     style="display:none!important">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg flex flex-col"
         style="max-height:90vh">

        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between shrink-0">
            <div>
                <h2 class="font-grotesk font-black text-lg text-gray-900">Appel des adhérents</h2>
                <p class="text-xs text-gray-500 font-medium mt-0.5">{{ $prochaineSeance->activite_nom }}</p>
            </div>
            <button onclick="closeAppelOverlay()"
                    class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div id="appel-list" class="flex-1 overflow-y-auto px-6 py-4 space-y-2"></div>

        <div class="px-6 py-5 border-t border-gray-100 shrink-0">
            <button onclick="validerAppel()" id="btn-valider-appel"
                    class="w-full bg-[#083325] hover:bg-[#16A37A] text-white font-grotesk font-bold
                           py-3.5 rounded-xl transition-all duration-300 shadow-md hover:shadow-lg text-sm
                           flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                Valider l'appel
            </button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- OVERLAY 2 : FIN DE L'ACTIVITÉ (liste des enfants)             --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div id="overlay-fin"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4"
     style="display:none!important">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg flex flex-col"
         style="max-height:90vh">

        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between shrink-0">
            <div>
                <h2 class="font-grotesk font-black text-lg text-gray-900">Fin de l'activité</h2>
                <p class="text-xs text-gray-500 font-medium mt-0.5">Confirmez la récupération des enfants</p>
            </div>
            <button onclick="closeFinOverlay()"
                    class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div id="fin-list" class="flex-1 overflow-y-auto px-6 py-4 space-y-2"></div>

        <div class="px-6 py-5 border-t border-gray-100 shrink-0">
            <button id="btn-valider-fin" onclick="validerFin()" disabled
                    class="w-full bg-gray-100 text-gray-400 font-grotesk font-bold
                           py-3.5 rounded-xl cursor-not-allowed text-sm
                           flex items-center justify-center gap-2 transition-all duration-300">
                Terminer & clôturer la séance
            </button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- OVERLAY 3 : SIGNATURE PARENT (Sortie Enfant)                  --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div id="overlay-enfant"
     class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4 sm:p-6"
     style="display:none!important">

    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl flex flex-col" style="max-height: 95vh;">

        <div class="px-6 py-5 sm:px-8 sm:py-6 border-b border-gray-100 flex items-center justify-between shrink-0">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Sortie</p>
                <h2 class="font-grotesk font-black text-xl sm:text-2xl text-gray-900" id="enfant-nom-titre">—</h2>
            </div>
            <button onclick="closeEnfantOverlay()"
                    class="w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="px-6 py-5 sm:px-8 sm:py-6 space-y-6 overflow-y-auto flex-1">

            <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">
                    👥 Personnes autorisées
                </label>
                <div id="enfant-tuteurs-list" class="space-y-3 max-h-60 overflow-y-auto pr-2">
                    </div>
            </div>

            <div class="border-t border-gray-100 pt-6" id="recup-options-container">
                </div>

            <div class="mt-6">
                <label id="label-signature" class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3 transition-all">
                    ✍️ Signature du responsable <span class="text-rose-500">*</span>
                </label>
                <div class="relative border-2 border-dashed border-gray-300 rounded-2xl p-2 bg-gray-50 overflow-hidden"
                     style="height:200px">
                    <canvas id="canvas-fin"
                            class="w-full h-full touch-none bg-white rounded-xl cursor-crosshair block border border-gray-100"></canvas>
                    <button type="button" onclick="clearSigFin()"
                            class="absolute top-4 right-4 bg-white border border-gray-200 text-xs font-bold
                                   text-gray-500 hover:text-rose-500 hover:border-rose-200 px-3 py-1.5 rounded-lg
                                   shadow-sm transition-colors">
                        Effacer
                    </button>
                </div>
                <p id="sig-warning" class="text-sm text-rose-500 font-medium mt-2 hidden">
                    La signature est requise pour valider.
                </p>
            </div>
        </div>

        <div class="px-6 py-5 sm:px-8 sm:py-6 border-t border-gray-100 shrink-0">
            <button id="btn-valider-enfant" onclick="validerEnfant()"
                    class="w-full bg-gray-100 text-gray-400 font-grotesk font-bold
                           py-4 rounded-xl cursor-not-allowed text-base transition-all duration-300
                           flex items-center justify-center gap-2" disabled>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                Valider la sortie
            </button>
        </div>
    </div>
</div>
