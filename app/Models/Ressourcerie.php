<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ressourcerie extends Model
{
    protected $table = 'ressourcerie';

    protected $fillable = [
        'nom',
        'description',
        'condition_location',
        'prix',
        'type_tarif',
        'is_archived',
    ];

    protected $casts = [
        'prix'        => 'decimal:2',
        'is_archived' => 'boolean',
    ];

    const TYPES_TARIF = [
        'tarif_particulier' => 'Tarif particulier',
        'tarif_structure'   => 'Tarif structure',
        'tarif_scolaire'    => 'Tarif scolaire',
    ];

    public function getPrixFormatAttribute(): string
    {
        if ((float) $this->prix === 0.0) {
            return 'Gratuit';
        }

        return number_format((float) $this->prix, 2, ',', ' ') . ' €';
    }

    public function scopeActifs($query)
    {
        return $query->where('is_archived', false);
    }
}
