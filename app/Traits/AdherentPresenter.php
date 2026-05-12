<?php

namespace App\Traits;

trait AdherentPresenter
{
    public function modalData(string $tab): array
    {
        $isReinscription = $this->is_reinscription;
        $source          = $this->source_label;
        $totalModal      = (float) ($this->inscription?->montant ?? 0);
        $verseModal      = (float) $this->montant_total;
        $resteModal      = max(0, $totalModal - $verseModal);
        $dateInscr       = $this->inscription?->created_at;

        $activites = ($isReinscription && $dateInscr)
            ? $this->activitesActives->filter(fn($a) => $a->pivot->created_at >= $dateInscr->startOfDay())->values()
            : $this->activitesActives->values();
        $isDrusenheim = $this->activitesActives->contains(function ($a) {
            return stripos($a->nom, 'drusenheim') !== false || stripos($a->ville, 'drusenheim') !== false;
        });

        return [
            'actionUrl'       => "/adherents/{$this->id}/valider",
            'versementUrl'    => "/adherents/{$this->id}/versement",
            'isPartiel'       => $tab === 'partiel',
            'isStructure'     => false,
            'isReinscription' => $isReinscription,
            'id'              => $this->id,
            'nom'             => $this->nom_complet,
            'initiales'       => $this->initiales,
            'couleur'         => $this->couleur_avatar,
            'meta'            => ($this->tranche_age ?? 'Adulte')
                . ($isReinscription ? ' · Ré-inscription' : ' · Inscrit le ')
                . ($isReinscription ? '' : $this->inscription?->date_inscription?->isoFormat('D MMM YYYY') ?? ''),
            'source'          => $source,
            'sourceClass'     => $this->source_class,
            'montant'         => number_format($totalModal, 2, ',', ' ') . ' €',
            'montantBrut'     => $totalModal,
            'dejaVerse'       => number_format($verseModal, 2, ',', ' ') . ' €',
            'dejaVerseBrut'   => $verseModal,
            'resteDu'         => number_format($resteModal, 2, ',', ' ') . ' €',
            'resteDuBrut'     => $resteModal,
            'activites'       => $activites->map(fn($a) => [
                'nom'   => $a->nom,
                'info'  => collect($a->horaires_list)->first() ?? '',
                'tarif' => number_format((float) $a->tarif, 2, ',', ' ') . ' €',
            ])->toArray(),
            'montantAdhesion' => $isDrusenheim ? '17,00 €' : '10,00 €',
            'showCotisation'  => ($this->inscription?->type_adhesion ?? '') !== 'club_maker',
            'type_adhesion_attente' => $this->inscriptions()->where('a_paye', 'En attente')->latest()->value('type_adhesion') ?? '',
        ];
    }

    public function getTrancheAgeClassAttribute(): string
    {
        return match ($this->tranche_age) {
            'Enfant'     => 'bg-sky-50 text-sky-600',
            'Adolescent' => 'bg-violet-50 text-violet-600',
            'Adulte'     => 'bg-emerald-50 text-emerald-600',
            default      => 'bg-gray-100 text-gray-400',
        };
    }

    public function getCouleurAvatarAttribute(): string
    {
        $colors = ['#4F7BE8', '#E8624F', '#4FE8A0', '#E8C44F', '#A04FE8', '#4FD0E8', '#E84FA0', '#7BE84F'];
        $index = crc32($this->nom . $this->prenom) % count($colors);
        return $colors[abs($index)];
    }

    public function getSourceClassAttribute(): string
    {
        return match ($this->source_label) {
            'HelloAsso'    => 'bg-[#16987C]/10 text-[#16987C]',
            default        => 'bg-blue-50 text-blue-600', // Interne
        };
    }
}
