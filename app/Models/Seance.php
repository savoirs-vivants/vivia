<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seance extends Model
{
    protected $table      = 'seances';
    protected $primaryKey = 'id_seance';

    protected $fillable = [
        'id_activite',
        'date',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    /**
     * L'activité à laquelle appartient cette séance.
     */
    public function activite(): BelongsTo
    {
        return $this->belongsTo(Activite::class, 'id_activite');
    }

    /**
     * Toutes les présences enregistrées pour cette séance.
     */
    public function presences(): HasMany
    {
        return $this->hasMany(Presence::class, 'id_seance');
    }

    /**
     * Adhérents présents (statut = 'present').
     */
    public function adherentsPresents(): BelongsToMany
    {
        return $this->belongsToMany(
            Adherent::class,
            'presence',
            'id_seance',
            'id_adherent'
        )->withPivot(['statut', 'raison'])
         ->withTimestamps()
         ->wherePivot('statut', 'present');
    }

    /**
     * Séances passées.
     */
    public function scopePassees($query)
    {
        return $query->where('date', '<', now());
    }

    /**
     * Séances à venir.
     */
    public function scopeAVenir($query)
    {
        return $query->where('date', '>=', now());
    }

    /**
     * Séances d'une activité donnée.
     */
    public function scopePourActivite($query, int $idActivite)
    {
        return $query->where('id_activite', $idActivite);
    }

    /**
     * Date formatée : "Mercredi 12 mars 2025 à 14h00"
     */
    public function getDateFormateeAttribute(): string
    {
        return $this->date->isoFormat('dddd D MMMM YYYY [à] HH[h]mm');
    }

    /**
     * Nombre de présents pour cette séance.
     */
    public function getNbPresentsAttribute(): int
    {
        return $this->presences()->where('statut', 'present')->count();
    }

    /**
     * Nombre d'absents pour cette séance.
     */
    public function getNbAbsentsAttribute(): int
    {
        return $this->presences()->where('statut', 'absent')->count();
    }
}
