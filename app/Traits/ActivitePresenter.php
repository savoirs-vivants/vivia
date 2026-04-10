<?php

namespace App\Traits;

use Carbon\Carbon;

trait ActivitePresenter
{
    /**
     * Résumé des horaires sous forme lisible.
     * Utilisé pour l'affichage public (ex: ["Lundi 14:00-16:00"] ou ["Du 01/01 au 05/01 (10:00 - 12:00)"]).
     */
    public function getHorairesListAttribute(): array
    {
        $horaires = is_string($this->horaires) ? json_decode($this->horaires, true) : $this->horaires;

        if (empty($horaires) || !is_array($horaires)) {
            return [];
        }

        if ($this->est_stage || isset($horaires['stage'])) {
            $data = $horaires['stage'] ?? [];
            if (empty($data['date_debut']) || empty($data['date_fin'])) {
                return [];
            }

            $dateDebut = Carbon::parse($data['date_debut'])->format('d/m/Y');
            $dateFin   = Carbon::parse($data['date_fin'])->format('d/m/Y');
            $hDebut    = $data['heure_debut'] ?? '';
            $hFin      = $data['heure_fin'] ?? '';

            return ["Du {$dateDebut} au {$dateFin} ({$hDebut} - {$hFin})"];
        }

        return array_map(
            fn($jour, $plage) => "{$jour} {$plage}",
            array_keys($horaires),
            $horaires
        );
    }

    /**
     * Tarif formaté pour l'affichage (ex: "50,00 €" ou "Gratuit").
     */
    public function getTarifFormatAttribute(): string
    {
        if ((float) $this->tarif === 0.0) {
            return 'Gratuit';
        }
        return number_format((float) $this->tarif, 2, ',', ' ') . ' €';
    }

    /**
     * Badge CSS selon le type de l'activité (utilisé dans les vues Blade).
     */
    public function getBadgeTypeClassAttribute(): string
    {
        return $this->est_stage
            ? 'bg-violet-50 text-violet-600'
            : 'bg-sky-50 text-sky-600';
    }
}
