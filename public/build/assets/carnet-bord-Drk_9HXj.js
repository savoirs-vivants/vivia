document.addEventListener(`DOMContentLoaded`,function(){if(window.carnetBordData===void 0)return;let e=window.carnetBordData.seanceId,t=window.carnetBordData.csrfToken,n=window.carnetBordData.adherents,r=window.carnetBordData.absentsIds,i=n.filter(e=>!r.includes(e.id)),a={},o={},s=null,c=null,l=!1;function u(e){let t=document.getElementById(e);t&&(t.style.removeProperty(`display`),t.classList.remove(`hidden`),t.classList.add(`flex`))}function d(e){let t=document.getElementById(e);t&&(t.classList.remove(`flex`),t.classList.add(`hidden`),t.style.display=`none`)}window.openAppelOverlay=function(){a={},n.forEach(e=>{a[e.id]={statut:`present`,motif:``}}),f(),u(`overlay-appel`)},window.closeAppelOverlay=function(){d(`overlay-appel`)};function f(){let e=document.getElementById(`appel-list`);if(e){if(n.length===0){e.innerHTML=`
                <p class="text-center text-gray-400 text-sm py-8 font-medium">
                    Aucun adhérent inscrit à cette activité.
                </p>`;return}e.innerHTML=n.map(e=>{let t=a[e.id],n=t.statut===`absent`;return`
            <div class="rounded-xl border ${n?`border-rose-200 bg-rose-50`:`border-gray-100 bg-white`} p-3 transition-all duration-200">
                <div class="flex items-center justify-between">
                    <span class="font-semibold text-sm text-gray-800">${e.prenom} ${e.nom}</span>
                    <button onclick="togglePresence(${e.id})"
                            class="text-xs font-bold px-3 py-1.5 rounded-lg transition-all duration-200 ${n?`bg-rose-100 text-rose-600 hover:bg-rose-200`:`bg-teal-50 text-teal-600 hover:bg-teal-100`}">
                        ${n?`Absent`:`Présent`}
                    </button>
                </div>
                ${n?`
                <div class="mt-2">
                    <input type="text"
                           placeholder="Motif d'absence (facultatif)…"
                           value="${g(t.motif)}"
                           oninput="setMotif(${e.id}, this.value)"
                           class="w-full text-xs border border-rose-200 rounded-lg px-3 py-2 bg-white
                                  focus:outline-none focus:ring-2 focus:ring-rose-300 text-gray-700 placeholder-gray-400">
                </div>`:``}
            </div>`}).join(``)}}window.togglePresence=function(e){a[e].statut=a[e].statut===`present`?`absent`:`present`,a[e].statut===`present`&&(a[e].motif=``),f()},window.setMotif=function(e,t){a[e].motif=t},window.validerAppel=async function(){let n=document.getElementById(`btn-valider-appel`);n.disabled=!0,n.textContent=`Enregistrement…`;let r=Object.entries(a).filter(([,e])=>e.statut===`absent`).map(([e,t])=>({id_adherent:parseInt(e),motif:t.motif||null}));try{if(!(await fetch(`/seances/${e}/appel`,{method:`POST`,headers:{"Content-Type":`application/json`,"X-CSRF-TOKEN":t},body:JSON.stringify({absents:r})})).ok)throw Error(`Erreur serveur`);window.location.reload()}catch{n.disabled=!1,n.innerHTML=`<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Valider l'appel`,alert(`Une erreur est survenue, veuillez réessayer.`)}},window.openFinOverlay=function(){o={},i.forEach(e=>{o[e.id]={valide:!1}}),p(),u(`overlay-fin`)},window.closeFinOverlay=function(){d(`overlay-fin`)};function p(){let e=document.getElementById(`fin-list`);if(!e)return;i.length===0?e.innerHTML=`
                <p class="text-center text-gray-400 text-sm py-8 font-medium">
                    Aucun enfant avec tuteur dans cette activité.
                </p>`:e.innerHTML=i.map(e=>{let t=o[e.id]?.valide;return`
                <div onclick="${t?``:`ouvrirEnfantOverlay(${e.id})`}"
                     class="flex items-center justify-between p-4 rounded-xl border transition-all duration-200 ${t?`bg-teal-50 border-teal-200 cursor-default`:`bg-white border-gray-100 hover:border-gray-300 hover:shadow-sm cursor-pointer`}">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-black ${t?`bg-teal-100 text-teal-600`:`bg-gray-100 text-gray-500`}">
                            ${e.prenom.charAt(0)}${e.nom.charAt(0)}
                        </div>
                        <span class="font-semibold text-sm text-gray-800">${e.prenom} ${e.nom}</span>
                    </div>
                    ${t?`<span class="w-6 h-6 rounded-full bg-teal-500 flex items-center justify-center">
                               <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                               </svg>
                           </span>`:`<svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                           </svg>`}
                </div>`}).join(``);let t=i.length===0||i.every(e=>o[e.id]?.valide),n=document.getElementById(`btn-valider-fin`);if(n)if(t)n.disabled=!1,n.className=`w-full bg-[#222A60] hover:bg-[#2d3a8c] text-white font-grotesk font-bold py-3.5 rounded-xl transition-all duration-300 shadow-md hover:shadow-lg text-sm flex items-center justify-center gap-2`,n.innerHTML=`<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg> Terminer & clôturer la séance`;else{n.disabled=!0,n.className=`w-full bg-gray-100 text-gray-400 font-grotesk font-bold py-3.5 rounded-xl cursor-not-allowed text-sm flex items-center justify-center gap-2 transition-all duration-300`;let e=i.filter(e=>!o[e.id]?.valide).length;n.textContent=`${e} enfant${e>1?`s`:``} restant${e>1?`s`:``}`}}window.validerFin=async function(){let n=document.getElementById(`btn-valider-fin`);n.disabled=!0,n.textContent=`Clôture en cours…`;try{if(!(await fetch(`/seances/${e}/terminer`,{method:`POST`,headers:{"Content-Type":`application/json`,"X-CSRF-TOKEN":t},body:JSON.stringify({})})).ok)throw Error(`Erreur serveur`);window.location.reload()}catch{n.disabled=!1,n.textContent=`Terminer & clôturer la séance`,alert(`Une erreur est survenue, veuillez réessayer.`)}},window.ouvrirEnfantOverlay=function(e){s=e;let t=i.find(t=>t.id===e);document.getElementById(`enfant-nom-titre`).textContent=`${t.prenom} ${t.nom}`;let n=t.tous_les_tuteurs&&t.tous_les_tuteurs.some(e=>e.rentre_fin==1||e.rentre_fin===!0),r=document.getElementById(`enfant-tuteurs-list`);if(t.tous_les_tuteurs&&t.tous_les_tuteurs.length>0){let e=t.tous_les_tuteurs.map(e=>{let t=``,n=``,r=``;e.type===`parent_tuteur`?(t=`Parent / Tuteur`,n=`bg-slate-50 border-slate-200 text-slate-700`,r=`👨‍👩‍👧`):e.type===`autre_autorise`?(t=`Autorisé(e)`,n=`bg-teal-50 border-teal-200 text-teal-800`,r=`✅`):(t=`Non autorisé(e)`,n=`bg-rose-50 border-rose-200 text-rose-800`,r=`🚫`);let i=e.nom_complet||`${e.prenom} ${e.nom}`;return`
                <div class="flex items-center justify-between p-2.5 rounded-xl border ${n}">
                    <div class="flex items-center gap-3">
                        <span class="text-xl bg-white/60 w-8 h-8 rounded-lg flex items-center justify-center shadow-sm border border-black/5">${r}</span>
                        <div><p class="text-sm font-bold leading-tight">${i}</p></div>
                    </div>
                    <span class="text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded border border-black/5 bg-white/60 shadow-sm">${t}</span>
                </div>`}).join(``);n&&(e+=`
                <div class="flex items-center justify-between p-2.5 rounded-xl border bg-indigo-50 border-indigo-200 text-indigo-800 mt-2">
                    <div class="flex items-center gap-3">
                        <span class="text-xl bg-white/60 w-8 h-8 rounded-lg flex items-center justify-center shadow-sm border border-indigo-100">🚶</span>
                        <div><p class="text-sm font-bold leading-tight">Autorisation de sortie autonome</p></div>
                    </div>
                    <span class="text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded border border-indigo-200 bg-white/80 shadow-sm">Autorisé(e)</span>
                </div>`),r.innerHTML=e}else r.innerHTML=`<div class="p-3 bg-gray-50 rounded-xl border border-gray-100 text-xs text-gray-500 font-medium text-center">Aucun responsable légal renseigné pour cet enfant.</div>`;let a=document.getElementById(`recup-options-container`);n?a.innerHTML=`
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
                <label class="flex items-center gap-4 cursor-pointer group bg-gray-50 p-4 sm:p-5 rounded-2xl border border-gray-200 hover:border-gray-300 transition-colors">
                    <div class="relative shrink-0">
                        <input type="checkbox" id="cb-recup" onchange="onEnfantFormChange()" class="peer w-6 h-6 rounded border-2 border-gray-300 accent-[#083325] cursor-pointer">
                    </div>
                    <span id="cb-recup-label" class="text-base sm:text-lg font-semibold text-gray-700 leading-snug group-hover:text-gray-900 transition-colors">
                        Je certifie avoir récupéré l'enfant
                    </span>
                </label>
            `:a.innerHTML=`
                <label class="flex items-center gap-4 cursor-pointer group bg-gray-50 p-4 sm:p-5 rounded-2xl border border-gray-200 hover:border-gray-300 transition-colors">
                    <div class="relative shrink-0">
                        <input type="checkbox" id="cb-recup" onchange="onEnfantFormChange()" class="peer w-6 h-6 rounded border-2 border-gray-300 accent-[#083325] cursor-pointer">
                    </div>
                    <span id="cb-recup-label" class="text-base sm:text-lg font-semibold text-gray-700 leading-snug group-hover:text-gray-900 transition-colors">
                        Je certifie avoir récupéré l'enfant
                    </span>
                </label>
            `,updateSortieUI(),document.getElementById(`sig-warning`).classList.add(`hidden`),h(),u(`overlay-enfant`),requestAnimationFrame(()=>m())},window.updateSortieUI=function(){let e=document.querySelector(`input[name="mode_sortie"]:checked`),t=e?e.value:`responsable`;document.querySelectorAll(`.radio-label-sortie`).forEach(e=>{e.querySelector(`input`).checked?(e.classList.add(`border-[#16A37A]`,`bg-teal-50`),e.classList.remove(`border-gray-200`)):(e.classList.remove(`border-[#16A37A]`,`bg-teal-50`),e.classList.add(`border-gray-200`))});let n=document.getElementById(`cb-recup-label`),r=document.getElementById(`label-signature`);t===`seul`?(n&&(n.textContent=`Je certifie quitter l'activité seul(e)`),r&&(r.innerHTML=`✍️ Signature du jeune <span class="text-rose-500">*</span>`)):(n&&(n.textContent=`Je certifie avoir récupéré l'enfant`),r&&(r.innerHTML=`✍️ Signature du responsable <span class="text-rose-500">*</span>`)),onEnfantFormChange()},window.closeEnfantOverlay=function(){d(`overlay-enfant`),s=null};function m(){let e=document.getElementById(`canvas-fin`);if(!e||typeof SignaturePad>`u`)return;l||=(c=new SignaturePad(e,{penColor:`#0f172a`,backgroundColor:`rgba(255,255,255,1)`}),c.addEventListener(`endStroke`,onEnfantFormChange),!0);let t=Math.max(window.devicePixelRatio||1,1),n=c.toData();e.width=e.offsetWidth*t,e.height=e.offsetHeight*t,e.getContext(`2d`).scale(t,t),c.clear(),n.length&&c.fromData(n),c.clear()}window.clearSigFin=function(){c&&c.clear(),onEnfantFormChange()},window.onEnfantFormChange=function(){let e=document.getElementById(`cb-recup`).checked,t=c&&!c.isEmpty(),n=document.getElementById(`btn-valider-enfant`);e&&t?(n.disabled=!1,n.className=`w-full bg-[#083325] hover:bg-[#16A37A] text-white font-grotesk font-bold py-3.5 rounded-xl transition-all duration-300 shadow-md hover:shadow-lg text-sm flex items-center justify-center gap-2`,n.innerHTML=`<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg> Valider`):h()};function h(){let e=document.getElementById(`btn-valider-enfant`);e&&(e.disabled=!0,e.className=`w-full bg-gray-100 text-gray-400 font-grotesk font-bold py-3.5 rounded-xl cursor-not-allowed text-sm flex items-center justify-center gap-2 transition-all duration-300`,e.innerHTML=`<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
        </svg> Valider`)}window.validerEnfant=function(){let e=document.getElementById(`cb-recup`).checked;if(!(c&&!c.isEmpty())){document.getElementById(`sig-warning`).classList.remove(`hidden`);return}e&&(o[s].valide=!0,d(`overlay-enfant`),s=null,p())};function g(e){return String(e||``).replace(/&/g,`&amp;`).replace(/</g,`&lt;`).replace(/>/g,`&gt;`).replace(/"/g,`&quot;`)}[`overlay-appel`,`overlay-fin`,`overlay-enfant`].forEach(e=>{let t=document.getElementById(e);t&&t.addEventListener(`click`,function(n){n.target===t&&(e===`overlay-enfant`?closeEnfantOverlay():e===`overlay-appel`?closeAppelOverlay():closeFinOverlay())})})});