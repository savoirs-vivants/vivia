<?php

namespace App\Models;

use App\Traits\ActivitePresenter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activite extends Model
{
    use ActivitePresenter; // Inclusion du Trait d'affichage

    protected $table = 'activites';

    protected $fillable = [
        'nom',
        'type',
        'adresse',
        'ville',
        'tarif',
        'max_eleves',
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

    const TYPE_ACTIVITE = 'activite';
    const TYPE_STAGE    = 'stage';

    // ==========================================
    // RELATIONS
    // ==========================================

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(DossierActivite::class, 'id_dossier');
    }

    public function adherents(): BelongsToMany
    {
        return $this->belongsToMany(Adherent::class, 'activites_adherents', 'id_activite', 'id_adherent')
            ->withPivot(['saison', 'date_entree', 'date_sortie', 'motif_sortie', 'est_un_abandon'])
            ->withTimestamps();
    }

    public function adherentsActifs(): BelongsToMany
    {
        return $this->adherents()->wherePivotNull('date_sortie');
    }

    public function seances(): HasMany
    {
        return $this->hasMany(Seance::class, 'id_activite');
    }

    public function gestionnaires(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'activites_gestionnaire', 'id_activite', 'id_users')
            ->withTimestamps();
    }

    // ==========================================
    // SCOPES
    // ==========================================

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

    // ==========================================
    // ACCESSEURS DE DONNÉES (LOGIQUE MÉTIER)
    // ==========================================

    public function getEstStageAttribute(): bool
    {
        return $this->type === self::TYPE_STAGE;
    }

    /**
     * Structure de données des horaires pour préremplir les formulaires (Edit).
     */
    public function getHorairesPlatsAttribute(): array
    {
        $plats = [];
        if (is_array($this->horaires) && !$this->est_stage) {
            foreach ($this->horaires as $jour => $plagesStr) {
                if ($jour === 'stage') continue;
                foreach (explode(', ', $plagesStr) as $p) {
                    $parts = explode('-', $p);
                    if (count($parts) === 2) {
                        $plats[] = ['jour' => $jour, 'debut' => trim($parts[0]), 'fin' => trim($parts[1])];
                    }
                }
            }
        }
        return $plats;
    }

    /**
     * Classes triées selon l'ordre canonique défini dans CLASSES_NIVEAUX.
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
}
