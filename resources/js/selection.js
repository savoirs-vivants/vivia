/**
 * Permet de gérer les cases à cocher (individuelles et globales) et les actions groupées.
 */
export default (ids = []) => ({
    selected: [],   
    allIds: ids,      
    confirmBulkDelete: false, 

    /**
     * État de la checkbox "Master" (en-tête du tableau)
     * Vérifie si tous les IDs de la page sont présents dans le tableau 'selected'.
     */
    get allSelected() {
        return this.allIds.length > 0 && this.selected.length === this.allIds.length;
    },

    /**
     * État indéterminé (partiel)
     * Vrai si certains éléments sont cochés, mais pas la totalité.
     */
    get someSelected() {
        return this.selected.length > 0 && !this.allSelected;
    },

    /**
     * Action globale : Coche tout ou vide la sélection
     */
    toggleAll() {
        if (this.allSelected) {
            this.selected = [];
        } else {
            this.selected = [...this.allIds];
        }
    },

    /**
     * Action individuelle : Ajoute ou retire un ID de la sélection
     * @param {number|string} id 
     */
    toggle(id) {
        if (this.selected.includes(id)) {
            this.selected = this.selected.filter(i => i !== id);
        } else {
            this.selected.push(id);
        }
    },

    /**
     * SOUMISSION DYNAMIQUE DU FORMULAIRE
     * Cette partie crée un formulaire "fantôme" dans le DOM
     * pour permettre l'envoi d'une requête DELETE avec des IDs multiples sans
     * avoir besoin de déclarer un formulaire lourd dans le HTML.
     * * @param {string} routeUrl - L'URL de destination (ex: {{ route('backoffice.destroyMultiple') }})
     */
    submitBulkDelete(routeUrl) {
        if (this.selected.length === 0) return;

        const form = document.createElement('form');
        form.method = 'POST'; 
        form.action = routeUrl;

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                     || '';
        form.appendChild(csrf);

        // On ajoute ce champ pour 
        // que Laravel comprenne qu'il s'agit d'une requête de type DELETE.
        const method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        method.value = 'DELETE';
        form.appendChild(method);

        // AJOUT DES IDENTIFIANTS
        // L'utilisation de 'ids[]' permet au contrôleur Laravel de recevoir un tableau.
        this.selected.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    }
});