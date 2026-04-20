// Fonction pour Alpine.js (Paiement HelloAsso)
window.cotisationPaiement = function() {
    return {
        loading: false,
        dejaClique: window.AdhesionConfig.dejaClique,
        init() {},
        async ouvrirHelloAsso() {
            this.loading = true;
            try {
                const response = await fetch(window.AdhesionConfig.checkoutRoute, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': window.AdhesionConfig.csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const data = await response.json();
                if (data.url) {
                    window.open(data.url, '_blank');
                    this.dejaClique = true;
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        }
    }
}

// Fonction pour Alpine.js (Gestion des tuteurs)
window.tuteurManager = function() {
    const existing = window.AdhesionConfig.existingTuteurs;

    return {
        tuteurs: [],
        sigPads: {},

        init() {
            if (existing && Array.isArray(existing) && existing.length > 0) {
                this.tuteurs = existing;
            } else {
                this.tuteurs = [this.emptyTuteur('parent_tuteur')];
            }
            this.$nextTick(() => {
                this.tuteurs.forEach((t, i) => {
                    if (t.type === 'parent_tuteur') this.initSigPad(i);
                });
            });
        },

        emptyTuteur(type) {
            const base = {
                type: type,
                nom: '',
                prenom: '',
                tel: '',
                mail: '',
                profession: '',
            };
            if (type === 'parent_tuteur') {
                Object.assign(base, {
                    nom_enfant: window.AdhesionConfig.nomEnfant,
                    adhere: false,
                    rentre_fin: false,
                    rentre_annul: false,
                    date_signature: new Date().toISOString().split('T')[0],
                    signature: ''
                });
            }
            return base;
        },

        addTuteur(type) {
            this.tuteurs.push(this.emptyTuteur(type));
            const newIdx = this.tuteurs.length - 1;
            if (type === 'parent_tuteur') {
                this.$nextTick(() => this.initSigPad(newIdx));
            }
        },

        removeTuteur(i) {
            if (this.sigPads[i]) {
                delete this.sigPads[i];
            }
            this.tuteurs.splice(i, 1);
        },

        initSigPad(i) {
            const canvas = document.getElementById('canvas-tuteur-' + i);
            if (!canvas || this.sigPads[i]) return;

            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext('2d').scale(ratio, ratio);

            const sp = new SignaturePad(canvas, {
                penColor: '#0f172a',
                backgroundColor: 'rgba(255,255,255,1)'
            });
            this.sigPads[i] = sp;

            if (this.tuteurs[i]?.signature) {
                sp.fromDataURL(this.tuteurs[i].signature);
            }

            sp.addEventListener('endStroke', () => {
                document.getElementById('sig-data-tuteur-' + i).value = sp.toDataURL();
            });
        },

        clearCanvas(i) {
            if (this.sigPads[i]) {
                this.sigPads[i].clear();
                this.tuteurs[i].signature = '';
                document.getElementById('sig-data-tuteur-' + i).value = '';
            }
        },
    };
}

// Initialisation globale (Signature de l'adhérent)
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('canvas-adherent');
    if (!canvas) return;

    const sigPad = new SignaturePad(canvas, {
        penColor: '#0f172a',
        backgroundColor: 'rgba(255,255,255,1)'
    });

    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const data = sigPad.toData();
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        sigPad.clear();
        sigPad.fromData(data);
    }

    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

    const existingData = document.getElementById('sig-data-adherent')?.value;
    if (existingData && existingData.startsWith('data:')) {
        sigPad.fromDataURL(existingData);
    }

    document.getElementById('form-signature')?.addEventListener('submit', function() {
        if (!sigPad.isEmpty()) {
            document.getElementById('sig-data-adherent').value = sigPad.toDataURL();
        }
    });

    document.getElementById('clear-sig-adherent')?.addEventListener('click', function() {
        sigPad.clear();
        document.getElementById('sig-data-adherent').value = '';
    });
});
