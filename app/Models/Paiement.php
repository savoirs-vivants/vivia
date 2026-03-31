<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paiement extends Model
{
    protected $table      = 'paiement';
    protected $primaryKey = 'id_paiement';

    protected $fillable = [
        'id_adherent',
        'id_structure',
        'montant',
        'source',
        'ref_facture',
        'date_paiement',
        'commentaire',
    ];

    protected $casts = [
        'montant'       => 'decimal:2',
        'date_paiement' => 'date',
    ];

    public function adherent(): BelongsTo
    {
        return $this->belongsTo(Adherent::class, 'id_adherent');
    }

    /**
     * Filtrer par source : HelloAsso | Virement | Chèque | Espèces
     */
    public function scopeSource($query, ?string $source)
    {
        if (blank($source) || $source === 'Tous') {
            return $query;
        }

        return $query->where('source', $source);
    }

    /**
     * Paiements d'une période donnée.
     */
    public function scopeEntreDates($query, string $debut, string $fin)
    {
        return $query->whereBetween('date_paiement', [$debut, $fin]);
    }


    /**
     * Montant formaté : "150,00 €"
     */
    public function getMontantFormatAttribute(): string
    {
        return number_format((float) $this->montant, 2, ',', ' ') . ' €';
    }
}
