<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inscription extends Model
{
    protected $table = 'inscriptions';

    protected $fillable = [
        'id_adherent',
        'saison',
        'date_inscription',
        'type_adhesion',
        'a_paye',
        'montant',
        'renouvellement',
    ];

    protected $casts = [
        'date_inscription' => 'date',
        'renouvellement'   => 'boolean',
        'montant'          => 'float',
    ];

    /**
     * Valeurs possibles de a_paye.
     */
    const PAYE       = 'Payé';
    const EN_ATTENTE = 'En attente';
    const PARTIEL    = 'Partiel';

    public function adherent(): BelongsTo
    {
        return $this->belongsTo(Adherent::class, 'id_adherent');
    }

    public function scopeSaison($query, string $saison)
    {
        return $query->where('saison', $saison);
    }

    public function scopePayees($query)
    {
        return $query->where('a_paye', self::PAYE);
    }

    public function scopeEnAttente($query)
    {
        return $query->where('a_paye', self::EN_ATTENTE);
    }

    public function scopeParType($query, string $type)
    {
        return $query->where('type_adhesion', $type);
    }

    /**
     * Indique si la cotisation est réglée.
     */
    public function getEstPayeeAttribute(): bool
    {
        return $this->a_paye === self::PAYE;
    }

    /**
     * Classe CSS Tailwind pour le badge statut.
     */
    public function getBadgeClassAttribute(): string
    {
        return match ($this->a_paye) {
            self::PAYE       => 'bg-emerald-50 text-emerald-600',
            self::EN_ATTENTE => 'bg-amber-50 text-amber-600',
            default          => 'bg-gray-100 text-gray-400',
        };
    }
    
    /**
     * Montant restant à payer = montant attendu - somme des paiements reçus.
     */
    public function getResteduAttribute(): float
    {
        $verse = (float) $this->adherent?->paiements()->sum('montant');
        return max(0, (float) $this->montant - $verse);
    }

    /**
     * Couleur du point dans le badge.
     */
    public function getBadgeDotClassAttribute(): string
    {
        return match ($this->a_paye) {
            self::PAYE       => 'bg-emerald-500',
            self::EN_ATTENTE => 'bg-amber-400',
            default          => 'bg-gray-300',
        };
    }
}
