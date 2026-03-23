<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Presence extends Model
{
    protected $table = 'presence';

    protected $fillable = [
        'id_adherent',
        'id_seance',
        'statut',
        'raison',
    ];

    /**
     * Valeurs possibles de statut.
     */
    const PRESENT  = 'present';
    const ABSENT   = 'absent';
    const EXCUSE   = 'excuse';

    public function adherent(): BelongsTo
    {
        return $this->belongsTo(Adherent::class, 'id_adherent');
    }

    public function seance(): BelongsTo
    {
        return $this->belongsTo(Seance::class, 'id_seance');
    }

    public function scopePresents($query)
    {
        return $query->where('statut', self::PRESENT);
    }

    public function scopeAbsents($query)
    {
        return $query->where('statut', self::ABSENT);
    }

    public function scopeExcuses($query)
    {
        return $query->where('statut', self::EXCUSE);
    }

    public function getEstPresentAttribute(): bool
    {
        return $this->statut === self::PRESENT;
    }

    /**
     * Badge CSS Tailwind selon le statut.
     */
    public function getBadgeClassAttribute(): string
    {
        return match ($this->statut) {
            self::PRESENT => 'bg-emerald-50 text-emerald-600',
            self::ABSENT  => 'bg-rose-50 text-rose-500',
            self::EXCUSE  => 'bg-amber-50 text-amber-600',
            default       => 'bg-gray-100 text-gray-400',
        };
    }

    public function getBadgeDotClassAttribute(): string
    {
        return match ($this->statut) {
            self::PRESENT => 'bg-emerald-500',
            self::ABSENT  => 'bg-rose-400',
            self::EXCUSE  => 'bg-amber-400',
            default       => 'bg-gray-300',
        };
    }

    /**
     * Libellé lisible du statut.
     */
    public function getLibelleStatutAttribute(): string
    {
        return match ($this->statut) {
            self::PRESENT => 'Présent',
            self::ABSENT  => 'Absent',
            self::EXCUSE  => 'Excusé',
            default       => '—',
        };
    }
}
