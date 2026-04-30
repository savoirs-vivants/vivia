document.addEventListener('DOMContentLoaded', function() {
    // Ne s'exécute que si les données du carnet de bord sont présentes sur la page
    if (typeof window.carnetBordData === 'undefined') return;

    const SEANCE_ID  = window.carnetBordData.seanceId;
    const CSRF_TOKEN = window.carnetBordData.csrfToken;
    const ADHERENTS  = window.carnetBordData.adherents;
    const ABSENTS_IDS= window.carnetBordData.absentsIds;
    const PRESENTS   = ADHERENTS.filter(a => !ABSENTS_IDS.includes(a.id));

    // ── État ─────────────────────────────────────────────────────────────────
    let presenceState  = {};
    let enfantsState   = {};
    let currentEnfantId = null;
    let sigPad         = null;
    let sigPadInited   = false;

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────
    function showOverlay(id) {
        const el = document.getElementById(id);
        if(!el) return;
        el.style.removeProperty('display');
        el.classList.remove('hidden');
        el.classList.add('flex');
    }

    function hideOverlay(id) {
        const el = document.getElementById(id);
        if(!el) return;
        el.classList.remove('flex');
        el.classList.add('hidden');
        el.style.display = 'none';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // OVERLAY 1 : APPEL
    // ─────────────────────────────────────────────────────────────────────────
    window.openAppelOverlay = function () {
        presenceState = {};
        ADHERENTS.forEach(a => {
            presenceState[a.id] = { statut: 'present', motif: '' };
        });
        renderAppelList();
        showOverlay('overlay-appel');
    };

    window.closeAppelOverlay = function () {
        hideOverlay('overlay-appel');
    };

    function renderAppelList() {
        const container = document.getElementById('appel-list');
        if (!container) return;

        if (ADHERENTS.length === 0) {
            container.innerHTML = `
                <p class="text-center text-gray-400 text-sm py-8 font-medium">
                    Aucun adhérent inscrit à cette activité.
                </p>`;
            return;
        }

        container.innerHTML = ADHERENTS.map(a => {
            const s = presenceState[a.id];
            const isAbsent = s.statut === 'absent';
            return `
            <div class="rounded-xl border ${isAbsent ? 'border-rose-200 bg-rose-50' : 'border-gray-100 bg-white'} p-3 transition-all duration-200">
                <div class="flex items-center justify-between">
                    <span class="font-semibold text-sm text-gray-800">${a.prenom} ${a.nom}</span>
                    <button onclick="togglePresence(${a.id})"
                            class="text-xs font-bold px-3 py-1.5 rounded-lg transition-all duration-200 ${
                                isAbsent
                                ? 'bg-rose-100 text-rose-600 hover:bg-rose-200'
                                : 'bg-teal-50 text-teal-600 hover:bg-teal-100'
                            }">
                        ${isAbsent ? 'Absent' : 'Présent'}
                    </button>
                </div>
                ${isAbsent ? `
                <div class="mt-2">
                    <input type="text"
                           placeholder="Motif d'absence (facultatif)…"
                           value="${escHtml(s.motif)}"
                           oninput="setMotif(${a.id}, this.value)"
                           class="w-full text-xs border border-rose-200 rounded-lg px-3 py-2 bg-white
                                  focus:outline-none focus:ring-2 focus:ring-rose-300 text-gray-700 placeholder-gray-400">
                </div>` : ''}
            </div>`;
        }).join('');
    }

    window.togglePresence = function (id) {
        presenceState[id].statut = presenceState[id].statut === 'present' ? 'absent' : 'present';
        if (presenceState[id].statut === 'present') presenceState[id].motif = '';
        renderAppelList();
    };

    window.setMotif = function (id, value) {
        presenceState[id].motif = value;
    };

    window.validerAppel = async function () {
        const btn = document.getElementById('btn-valider-appel');
        btn.disabled = true;
        btn.textContent = 'Enregistrement…';

        const absents = Object.entries(presenceState)
            .filter(([, s]) => s.statut === 'absent')
            .map(([id, s]) => ({ id_adherent: parseInt(id), motif: s.motif || null }));

        try {
            const res = await fetch(`/seances/${SEANCE_ID}/appel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                },
                body: JSON.stringify({ absents }),
            });
            if (!res.ok) throw new Error('Erreur serveur');
            window.location.reload();
        } catch (e) {
            btn.disabled = false;
            btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Valider l'appel`;
            alert('Une erreur est survenue, veuillez réessayer.');
        }
    };

    // ─────────────────────────────────────────────────────────────────────────
    // OVERLAY 2 : FIN DE L'ACTIVITÉ
    // ─────────────────────────────────────────────────────────────────────────
    window.openFinOverlay = function () {
        enfantsState = {};
        PRESENTS.forEach(e => { enfantsState[e.id] = { valide: false }; });
        renderFinList();
        showOverlay('overlay-fin');
    };

    window.closeFinOverlay = function () {
        hideOverlay('overlay-fin');
    };

    function renderFinList() {
        const container = document.getElementById('fin-list');
        if (!container) return;

        if (PRESENTS.length === 0) {
            container.innerHTML = `
                <p class="text-center text-gray-400 text-sm py-8 font-medium">
                    Aucun enfant avec tuteur dans cette activité.
                </p>`;
        } else {
            container.innerHTML = PRESENTS.map(e => {
                const done = enfantsState[e.id]?.valide;
                return `
                <div onclick="${done ? '' : `ouvrirEnfantOverlay(${e.id})`}"
                     class="flex items-center justify-between p-4 rounded-xl border transition-all duration-200 ${
                         done
                         ? 'bg-teal-50 border-teal-200 cursor-default'
                         : 'bg-white border-gray-100 hover:border-gray-300 hover:shadow-sm cursor-pointer'
                     }">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-black ${
                            done ? 'bg-teal-100 text-teal-600' : 'bg-gray-100 text-gray-500'
                        }">
                            ${e.prenom.charAt(0)}${e.nom.charAt(0)}
                        </div>
                        <span class="font-semibold text-sm text-gray-800">${e.prenom} ${e.nom}</span>
                    </div>
                    ${done
                        ? `<span class="w-6 h-6 rounded-full bg-teal-500 flex items-center justify-center">
                               <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                               </svg>
                           </span>`
                        : `<svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                           </svg>`
                    }
                </div>`;
            }).join('');
        }

        const allDone = PRESENTS.length === 0 || PRESENTS.every(e => enfantsState[e.id]?.valide);
        const btn = document.getElementById('btn-valider-fin');
        if(!btn) return;

        if (allDone) {
            btn.disabled = false;
            btn.className = 'w-full bg-[#222A60] hover:bg-[#2d3a8c] text-white font-grotesk font-bold py-3.5 rounded-xl transition-all duration-300 shadow-md hover:shadow-lg text-sm flex items-center justify-center gap-2';
            btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg> Terminer & clôturer la séance`;
        } else {
            btn.disabled = true;
            btn.className = 'w-full bg-gray-100 text-gray-400 font-grotesk font-bold py-3.5 rounded-xl cursor-not-allowed text-sm flex items-center justify-center gap-2 transition-all duration-300';
            const remaining = PRESENTS.filter(e => !enfantsState[e.id]?.valide).length;
            btn.textContent = `${remaining} enfant${remaining > 1 ? 's' : ''} restant${remaining > 1 ? 's' : ''}`;
        }
    }

    window.validerFin = async function () {
        const btn = document.getElementById('btn-valider-fin');
        btn.disabled = true;
        btn.textContent = 'Clôture en cours…';

        try {
            const res = await fetch(`/seances/${SEANCE_ID}/terminer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                },
                body: JSON.stringify({}),
            });
            if (!res.ok) throw new Error('Erreur serveur');
            window.location.reload();
        } catch (e) {
            btn.disabled = false;
            btn.textContent = 'Terminer & clôturer la séance';
            alert('Une erreur est survenue, veuillez réessayer.');
        }
    };

    // ─────────────────────────────────────────────────────────────────────────
    // OVERLAY 3 : CONFIRMATION ENFANT
    // ─────────────────────────────────────────────────────────────────────────
    window.ouvrirEnfantOverlay = function (id) {
        currentEnfantId = id;
        const enfant = PRESENTS.find(e => e.id === id);
        document.getElementById('enfant-nom-titre').textContent = `${enfant.prenom} ${enfant.nom}`;

        const canGoHomeAlone = enfant.tous_les_tuteurs && enfant.tous_les_tuteurs.some(t => t.rentre_fin == 1 || t.rentre_fin === true);
        const tuteursContainer = document.getElementById('enfant-tuteurs-list');

        if(enfant.tous_les_tuteurs && enfant.tous_les_tuteurs.length > 0) {
            let html = enfant.tous_les_tuteurs.map(t => {
                let badge = '';
                let bgClass = '';
                let icon = '';

                const nomComplet = t.nom_complet || `${t.prenom} ${t.nom}`;
                const telData = t.tel || t.telephone;
                const mailData = t.mail || t.email;

                const telHtml = telData ? `<p class="flex items-center gap-1.5 text-xs mt-1 text-slate-500 font-medium"><svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>${telData}</p>` : '';

                const emailHtml = mailData ? `<p class="flex items-center gap-1.5 text-xs mt-0.5 text-slate-500 font-medium"><svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>${mailData}</p>` : '';

                let typeTuteur = t.pivot ? (t.pivot.type || t.type) : t.type;

                if(typeTuteur === 'parent_tuteur') {
                    badge = 'Parent / Tuteur';
                    bgClass = 'bg-slate-50 border-slate-200 text-slate-700';
                    icon = '👨‍👩‍👧';
                } else if(typeTuteur === 'autre_autorise') {
                    badge = 'Autorisé(e)';
                    bgClass = 'bg-teal-50 border-teal-200 text-teal-800';
                    icon = '✅';
                } else {
                    badge = 'Non autorisé(e)';
                    bgClass = 'bg-rose-50 border-rose-200 text-rose-800';
                    icon = '🚫';
                }

                return `
                <div class="flex items-start justify-between p-3 rounded-xl border ${bgClass}">
                    <div class="flex items-start gap-3">
                        <span class="text-xl bg-white/60 w-8 h-8 rounded-lg flex items-center justify-center shadow-sm border border-black/5 mt-0.5 shrink-0">${icon}</span>
                        <div class="min-w-0">
                            <p class="text-sm font-bold leading-tight text-gray-800">${nomComplet}</p>
                            ${telHtml}
                            ${emailHtml}
                        </div>
                    </div>
                    <span class="text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded border border-black/5 bg-white/60 shadow-sm self-start shrink-0">${badge}</span>
                </div>`;
            }).join('');

            if (canGoHomeAlone) {
                html += `
                <div class="flex items-center justify-between p-2.5 rounded-xl border bg-indigo-50 border-indigo-200 text-indigo-800 mt-2">
                    <div class="flex items-center gap-3">
                        <span class="text-xl bg-white/60 w-8 h-8 rounded-lg flex items-center justify-center shadow-sm border border-indigo-100">🚶</span>
                        <div><p class="text-sm font-bold leading-tight">Autorisation de sortie autonome</p></div>
                    </div>
                    <span class="text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded border border-indigo-200 bg-white/80 shadow-sm">Autorisé(e)</span>
                </div>`;
            }
            tuteursContainer.innerHTML = html;
        } else {
            tuteursContainer.innerHTML = '<div class="p-3 bg-gray-50 rounded-xl border border-gray-100 text-xs text-gray-500 font-medium text-center">Aucun responsable légal renseigné pour cet enfant.</div>';
        }

        const recupContainer = document.getElementById('recup-options-container');

        const checkboxHtml = `
            <label id="recup-checkbox-wrapper" class="flex items-center gap-4 cursor-pointer group bg-gray-50 p-4 sm:p-5 rounded-2xl border border-gray-200 hover:border-gray-300 transition-colors">
                <div class="relative shrink-0">
                    <input type="checkbox" id="cb-recup" onchange="onEnfantFormChange()" class="peer w-6 h-6 rounded border-2 border-gray-300 accent-[#083325] cursor-pointer">
                </div>
                <span id="cb-recup-label" class="text-base sm:text-lg font-semibold text-gray-700 leading-snug group-hover:text-gray-900 transition-colors">
                    Je certifie avoir récupéré l'enfant
                </span>
            </label>
        `;

        if (canGoHomeAlone) {
            recupContainer.innerHTML = `
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Mode de sortie</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                    <label class="radio-label-sortie flex items-center gap-3 p-3 rounded-xl border-2 border-[#16A37A] bg-teal-50 cursor-pointer transition-all">
                        <input type="radio" name="mode_sortie" value="responsable" checked class="w-4 h-4 text-[#16A37A] focus:ring-[#16A37A]" onchange="updateSortieUI()">
                        <span class="text-sm font-bold text-gray-700">Avec un responsable</span>
                    </label>
                    <label class="radio-label-sortie flex items-center gap-3 p-3 rounded-xl border-2 border-gray-200 cursor-pointer transition-all">
                        <input type="radio" name="mode_sortie" value="seul" class="w-4 h-4 text-[#16A37A] focus:ring-[#16A37A]" onchange="updateSortieUI()">
                        <span class="text-sm font-bold text-gray-700">Rentre seul(e)</span>
                    </label>
                </div>
                ${checkboxHtml}
            `;
        } else {
            recupContainer.innerHTML = checkboxHtml;
        }

        updateSortieUI();
        document.getElementById('sig-warning').classList.add('hidden');
        resetBtnEnfant();
        showOverlay('overlay-enfant');
        requestAnimationFrame(() => initSigPad());
    };

    window.updateSortieUI = function() {
        const modeRadio = document.querySelector('input[name="mode_sortie"]:checked');
        const mode = modeRadio ? modeRadio.value : 'responsable';

        document.querySelectorAll('.radio-label-sortie').forEach(lbl => {
            if(lbl.querySelector('input').checked) {
                lbl.classList.add('border-[#16A37A]', 'bg-teal-50');
                lbl.classList.remove('border-gray-200');
            } else {
                lbl.classList.remove('border-[#16A37A]', 'bg-teal-50');
                lbl.classList.add('border-gray-200');
            }
        });

        const recupWrapper = document.getElementById('recup-checkbox-wrapper');
        const sigLabel = document.getElementById('label-signature');
        const sigCanvas = document.getElementById('canvas-fin');
        const sigContainer = sigCanvas ? (sigCanvas.closest('.border') || sigCanvas.parentElement) : null;
        const sigClearBtn = document.querySelector('button[onclick="clearSigFin()"]');
        const sigWarning = document.getElementById('sig-warning');

        if (mode === 'seul') {
            if (recupWrapper) recupWrapper.classList.add('hidden');
            if (sigContainer) sigContainer.classList.add('hidden');
            if (sigLabel) sigLabel.classList.add('hidden');
            if (sigClearBtn) sigClearBtn.classList.add('hidden');
            if (sigWarning) sigWarning.classList.add('hidden');
        } else {
            if (recupWrapper) recupWrapper.classList.remove('hidden');
            if (sigContainer) sigContainer.classList.remove('hidden');
            if (sigLabel) sigLabel.classList.remove('hidden');
            if (sigClearBtn) sigClearBtn.classList.remove('hidden');
        }

        onEnfantFormChange();
    };

    window.closeEnfantOverlay = function () {
        hideOverlay('overlay-enfant');
        currentEnfantId = null;
    };

    function initSigPad() {
        const canvas = document.getElementById('canvas-fin');
        if (!canvas) return;

        if (typeof SignaturePad === 'undefined') return;

        if (!sigPadInited) {
            sigPad = new SignaturePad(canvas, {
                penColor: '#0f172a',
                backgroundColor: 'rgba(255,255,255,1)',
            });
            sigPad.addEventListener('endStroke', onEnfantFormChange);
            sigPadInited = true;
        }

        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const data = sigPad.toData();
        canvas.width  = canvas.offsetWidth  * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        sigPad.clear();
        if (data.length) sigPad.fromData(data);
        sigPad.clear();
    }

    window.clearSigFin = function () {
        if (sigPad) sigPad.clear();
        onEnfantFormChange();
    };

    window.onEnfantFormChange = function () {
        const modeRadio = document.querySelector('input[name="mode_sortie"]:checked');
        const mode = modeRadio ? modeRadio.value : 'responsable';
        const btn = document.getElementById('btn-valider-enfant');

        let isValid = false;

        if (mode === 'seul') {
            isValid = true;
        } else {
            const cb = document.getElementById('cb-recup');
            const checked = cb ? cb.checked : false;
            const signed  = sigPad && !sigPad.isEmpty();
            isValid = checked && signed;
        }

        if (isValid) {
            btn.disabled = false;
            btn.className = 'w-full bg-[#083325] hover:bg-[#16A37A] text-white font-grotesk font-bold py-3.5 rounded-xl transition-all duration-300 shadow-md hover:shadow-lg text-sm flex items-center justify-center gap-2 mt-4';
            btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg> Valider le départ`;
        } else {
            resetBtnEnfant();
        }
    };

    function resetBtnEnfant() {
        const btn = document.getElementById('btn-valider-enfant');
        if(!btn) return;
        btn.disabled = true;
        btn.className = 'w-full bg-gray-100 text-gray-400 font-grotesk font-bold py-3.5 rounded-xl cursor-not-allowed text-sm flex items-center justify-center gap-2 transition-all duration-300 mt-4';
        btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
        </svg> Valider le départ`;
    }

    window.validerEnfant = function () {
        const modeRadio = document.querySelector('input[name="mode_sortie"]:checked');
        const mode = modeRadio ? modeRadio.value : 'responsable';

        if (mode !== 'seul') {
            const cb = document.getElementById('cb-recup');
            const checked = cb ? cb.checked : false;
            const signed  = sigPad && !sigPad.isEmpty();

            if (!signed) {
                document.getElementById('sig-warning').classList.remove('hidden');
                return;
            }
            if (!checked) return;
        }

        enfantsState[currentEnfantId].valide = true;
        hideOverlay('overlay-enfant');
        currentEnfantId = null;
        renderFinList();
    };

    // ─────────────────────────────────────────────────────────────────────────
    // UTILITAIRE
    // ─────────────────────────────────────────────────────────────────────────
    function escHtml(str) {
        return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    ['overlay-appel','overlay-fin','overlay-enfant'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('click', function (e) {
            if (e.target === el) {
                if (id === 'overlay-enfant') closeEnfantOverlay();
                else if (id === 'overlay-appel') closeAppelOverlay();
                else closeFinOverlay();
            }
        });
    });
});
