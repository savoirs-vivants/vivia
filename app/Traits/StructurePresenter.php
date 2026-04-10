<?php

namespace App\Traits;

trait StructurePresenter
{
    /**
     * ==============================================================================
     * MÉTHODES MÉTIER POUR L'AFFICHAGE (FRONT-END / ALPINEJS)
     * ==============================================================================
     */
    public function modalData(): array
    {
        $source = $this->paiements->isNotEmpty()
            ? $this->paiements->first()->source
            : ($this->inscription?->source ?? $this->source ?? 'Interne');

        if (isset($this->inscription->mode_paiement) && strtolower($this->inscription->mode_paiement) === 'helloasso') {
            $source = 'HelloAsso';
        }

        $tarifAdhesion = match ($this->statut_juridique) {
            'tpe_asso' => 50,
            'esr_pme'  => 200,
            default    => 0,
        };

        $activites = [];
        if ($tarifAdhesion > 0) {
            $activites[] = ['nom' => 'Cotisation annuelle', 'tarif' => number_format($tarifAdhesion, 2, ',', ' ') . ' €'];
        }
        if ($this->statut === 'ressourcerie') {
            $activites[] = ['nom' => 'Ressourcerie Codey Rocky', 'tarif' => '50,00 €'];
        }
        if (empty($activites)) {
            $activites[] = ['nom' => 'Adhésion', 'tarif' => number_format((float) $this->montant_adhesion, 2, ',', ' ') . ' €'];
        }

        $sourceClass = match ($source) {
            'HelloAsso'    => 'bg-[#16987C]/10 text-[#16987C]',
            'Virement'     => 'bg-blue-50 text-blue-600',
            'Chèque'       => 'bg-amber-50 text-amber-600',
            'Espèces'      => 'bg-orange-50 text-orange-600',
            'Pass Culture' => 'bg-purple-50 text-purple-600',
            default        => 'bg-gray-100 text-gray-600',
        };

        return [
            'actionUrl'   => "/structures/{$this->id}/valider",
            'isStructure' => true,
            'id'          => $this->id,
            'nom'         => $this->nom,
            'initiales'   => $this->sigle ?: mb_substr($this->nom, 0, 2),
            'couleur'     => '#222A60', 
            'meta'        => 'Structure · Inscrite le ' . ($this->inscription?->date_inscription?->isoFormat('D MMM YYYY') ?? ''),
            'source'      => $source,
            'sourceClass' => $sourceClass,
            'montant'     => number_format((float) $this->montant_adhesion, 2, ',', ' ') . ' €',
            'activites'   => $activites,
        ];
    }

    /**
     * ==============================================================================
     * ACCESSEURS DE STYLE ET LABELS
     * ==============================================================================
     */
    public function getStatutJuridiqueLabelAttribute(): string
    {
        return match ($this->statut_juridique) {
            'tpe_asso' => 'TPE/Asso',
            'esr_pme'  => 'ESR/PME',
            default    => $this->statut_juridique,
        };
    }

    public function getStatutJuridiqueClassAttribute(): string
    {
        return match ($this->statut_juridique) {
            'tpe_asso' => 'bg-indigo-50 text-indigo-600',
            'esr_pme'  => 'bg-purple-50 text-purple-600',
            default    => 'bg-gray-100 text-gray-500',
        };
    }
}
