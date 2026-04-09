document.addEventListener('alpine:init', () => {
    Alpine.data('adherentOverlay', () => ({
        open: false,
        actionUrl: '',
        plusieursVersements: false,
        montantRecu: '',
        resteFormate: '0,00',
        montantVersement: '',
        sourceVersement: 'Espèces',
        dateVersement: new Date().toISOString().split('T')[0],
        resteApresVersement: '—',
        progressPercent: 0,
        adherent: {},

        ouvrirModal(data) {
            this.adherent = data;
            this.actionUrl = data.actionUrl;
            this.resteApresVersement = data.resteDu ?? '—';
            this.progressPercent = this._pct(data.dejaVerseBrut, data.montantBrut);

            Object.assign(this, {
                plusieursVersements: false,
                montantRecu: '',
                resteFormate: '0,00',
                montantVersement: '',
                sourceVersement: 'Espèces',
                dateVersement: new Date().toISOString().split('T')[0],
            });

            this.adherent.reductionFormate = this._calcReduction(data);
            this.open = true;
            document.body.style.overflow = 'hidden';
        },

        calculerReste() {
            const total = this._parseMontant(this.adherent.montant);
            const recu = parseFloat(this.montantRecu) || 0;
            this.resteFormate = this._format(Math.max(0, total - recu));
        },

        calculerResteApresVersement() {
            const versement = parseFloat(this.montantVersement) || 0;
            this.resteApresVersement = this._format(Math.max(0, this.adherent.resteDuBrut - versement)) + ' €';
            this.progressPercent = this._pct(
                (this.adherent.dejaVerseBrut || 0) + versement,
                this.adherent.montantBrut
            );
        },

        close() {
            this.open = false;
            document.body.style.overflow = '';
        },

        _pct(verse, total) {
            return total > 0 ? Math.min(100, Math.round((verse / total) * 100)) : 0;
        },

        _format(val) {
            return val.toFixed(2).replace('.', ',');
        },

        _parseMontant(str) {
            return parseFloat((str || '0').replace(/\s/g, '').replace('€', '').replace(',', '.')) || 0;
        },

        _calcReduction(data) {
            if (!data.montantBrut || !data.activites?.length) return null;

            const sumActivites = data.activites.reduce((acc, a) => {
                return acc + (parseFloat((a.tarif || '0').replace(/[^0-9,-]/g, '').replace(',', '.')) || 0);
            }, 0);

            const adhesion = (!data.isStructure && !data.isReinscription &&
                              !data.activites.some(a => a.nom.toLowerCase().includes('club maker'))) ? 10 : 0;

            const theorique = sumActivites + adhesion;
            const reduction = theorique > data.montantBrut + 0.5 ? theorique - data.montantBrut : 0;

            return reduction > 0 ? this._format(reduction) : null;
        }
    }));
});
