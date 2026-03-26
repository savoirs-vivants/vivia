<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activite extends Model
{
    protected $table = 'activites';

    protected $fillable = [
        'nom',
        'type',
        'adresse',
        'ville',
        'tarif',
        'horaires',
        'classes',
        'is_archived',
        'id_dossier',
    ];

    protected $casts = [
        'horaires'    => 'array',
        'classes'     => 'array',
        'tarif'       => 'decimal:2',
        'is_archived' => 'boolean',
    ];

    const CLASSES_NIVEAUX = [
        'Maternelle' => ['PS', 'MS', 'GS'],
        'Primaire'   => ['CP', 'CE1', 'CE2', 'CM1', 'CM2'],
        'Collège'    => ['6ème', '5ème', '4ème', '3ème'],
        'Lycée'      => ['Seconde', 'Première', 'Terminale'],
        'Autre'      => ['Adulte', 'Senior'],
    ];

    /**
     * Types possibles.
     */
    const TYPE_ACTIVITE = 'activite';
    const TYPE_STAGE    = 'stage';

    /**
     * Dossier auquel appartient cette activité.
     */
    public function dossier(): BelongsTo
    {
        return $this->belongsTo(DossierActivite::class, 'id_dossier');
    }

    /**
     * Adhérents inscrits à cette activité.
     * Pivot : activites_adherents.
     */
    public function adherents(): BelongsToMany
    {
        return $this->belongsToMany(
            Adherent::class,
            'activites_adherents',
            'id_activite',
            'id_adherent'
        )->withPivot([
            'saison',
            'date_entree',
            'date_sortie',
            'motif_sortie',
            'est_un_abandon',
        ])->withTimestamps();
    }

    /**
     * Adhérents encore actifs dans cette activité (pas de date_sortie).
     */
    public function adherentsActifs(): BelongsToMany
    {
        return $this->adherents()->wherePivotNull('date_sortie');
    }

    /**
     * Séances planifiées pour cette activité.
     */
    public function seances(): HasMany
    {
        return $this->hasMany(Seance::class, 'id_activite');
    }

    /**
     * Gestionnaires (users) responsables de cette activité.
     */
    public function gestionnaires(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'activites_gestionnaire',
            'id_activite',
            'id_users'
        )->withTimestamps();
    }

    public function scopeActivites($query)
    {
        return $query->where('type', self::TYPE_ACTIVITE);
    }

    public function scopeStages($query)
    {
        return $query->where('type', self::TYPE_STAGE);
    }

    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }


    public function getEstStageAttribute(): bool
    {
        return $this->type === self::TYPE_STAGE;
    }

    public function getTarifFormatAttribute(): string
    {
        if ((float) $this->tarif === 0.0) {
            return 'Gratuit';
        }

        return number_format((float) $this->tarif, 2, ',', ' ') . ' €';
    }

    /**
     * Résumé des horaires sous forme lisible.
     * Ex. : ["Mercredi 14:00-16:00", "Samedi 10:00-11:30"]
     *
     * @return string[]
     */
    public function getHorairesListAttribute(): array
    {
        if (empty($this->horaires)) {
            return [];
        }

        return array_map(
            fn($jour, $plage) => "{$jour} {$plage}",
            array_keys($this->horaires),
            $this->horaires
        );
    }

    /**
     * Classes sous forme lisible, triées selon l'ordre canonique.
     * Ex. : ["CP", "CE1", "CM1"]
     *
     * @return string[]
     */
    public function getClassesListAttribute(): array
    {
        if (empty($this->classes)) {
            return [];
        }

        $ordre = array_merge(...array_values(self::CLASSES_NIVEAUX));
        $classes = $this->classes;
        usort($classes, fn($a, $b) => (array_search($a, $ordre) ?? 99) <=> (array_search($b, $ordre) ?? 99));

        return $classes;
    }

    /**
     * Badge CSS selon le type.
     */
    public function getBadgeTypeClassAttribute(): string
    {
        return $this->est_stage
            ? 'bg-violet-50 text-violet-600'
            : 'bg-sky-50 text-sky-600';
    }
}
