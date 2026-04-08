<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Adherent extends Model
{
    protected $table = 'adherents';

    protected $fillable = [
        'numero_adherent',
        'nom',
        'prenom',
        'carnet',
        'date_naiss',
        'age',
        'genre',
        'adresse',
        'ville',
        'code_postal',
        'tel',
        'mail',
        'occupation',
        'etablissement',
        'regime_social',
        'actions',
        'commentaire',
        'manif',
        'communication',
        'bulletin',
        'signature',
        'problemes_sante',
        'allergies',
        'conduite_a_tenir',
        'restrictions_alimentaires',
        'idee_metier',
        'decouverte_metier',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'criteres'      => 'array',
        'date_naiss'    => 'date',
        'manif'         => 'boolean',
        'communication' => 'boolean',
        'bulletin'      => 'boolean',
    ];

    public static function genererNumeroUnique(): string
    {
        do {
            $numero = 'ADH-' . date('y') . '-' . strtoupper(Str::random(4));
        } while (self::where('numero_adherent', $numero)->exists());

        return $numero;
    }

    /**
     * Tous les tuteurs (parent principal + personnes autorisées/non autorisées).
     */
    public function tousLesTuteurs(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Tuteur::class, 'adherent_tuteurs', 'id_adherent', 'id_tuteur');
    }

    /**
     * Toutes les inscriptions de l'adhérent (une par saison).
     */
    public function inscriptions(): HasMany
    {
        return $this->hasMany(Inscription::class, 'id_adherent');
    }

    /**
     * Inscription de la saison courante (la plus récente).
     */
    public function inscription(): HasOne
    {
        return $this->hasOne(Inscription::class, 'id_adherent')
                    ->latestOfMany();
    }

    /**
     * Activités auxquelles l'adhérent est / était inscrit.
     * Pivot : activites_adherents (id_adherent, id_activite).
     */
    public function activites(): BelongsToMany
    {
        return $this->belongsToMany(
            Activite::class,
            'activites_adherents',
            'id_adherent',
            'id_activite'
        )->withPivot([
            'saison',
            'date_entree',
            'date_sortie',
            'motif_sortie',
            'est_un_abandon',
        ])->withTimestamps();
    }

    /**
     * Activités actives uniquement (pas de date de sortie).
     */
    public function activitesActives(): BelongsToMany
    {
        return $this->activites()->wherePivotNull('date_sortie');
    }

    /**
     * Paiements de l'adhérent (table `paiement`).
     */
    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class, 'id_adherent');
    }

    /**
     * Nom complet : "Prénom Nom".
     */
    public function getNomCompletAttribute(): string
    {
        return trim("{$this->prenom} {$this->nom}");
    }

    /**
     * Initiales pour l'avatar (ex. "LM" pour Léo Martin).
     */
    public function getInitialesAttribute(): string
    {
        return strtoupper(
            substr($this->prenom, 0, 1) . substr($this->nom, 0, 1)
        );
    }

    /**
     * Âge calculé à partir de date_naiss (ignore la colonne `age`
     * qui peut être déphasée).
     */
    public function getAgeCourantAttribute(): ?int
    {
        return $this->date_naiss?->age;
    }

    /**
     * Tranche d'âge : 'Enfant' | 'Adolescent' | 'Adulte' | null.
     */
    public function getTrancheAgeAttribute(): ?string
    {
        $age = $this->age_courant;

        if ($age === null) {
            return null;
        }

        return match (true) {
            $age < 12  => 'Enfant',
            $age < 18  => 'Adolescent',
            default    => 'Adulte',
        };
    }

    /**
     * Couleur d'avatar déterministe basée sur le nom.
     */
    public function getCouleurAvatarAttribute(): string
    {
        $colors = [
            '#4F7BE8', '#E8624F', '#4FE8A0', '#E8C44F',
            '#A04FE8', '#4FD0E8', '#E84FA0', '#7BE84F',
        ];

        $index = crc32($this->nom . $this->prenom) % count($colors);

        return $colors[abs($index)];
    }

    /**
     * Indique si l'adhérent a payé sa cotisation cette saison.
     * a_paye est un varchar : 'Payé' | 'En attente' | null
     */
    public function getEstPayeAttribute(): bool
    {
        return $this->inscription?->a_paye === 'Payé';
    }

    /**
     * Montant total versé (somme des paiements).
     */
    public function getMontantTotalAttribute(): float
    {

        if ($this->relationLoaded('paiements')) {
            return (float) $this->paiements->sum(fn($p) => (float) $p->montant);
        }

        return (float) $this->paiements()->sum('montant');
    }

    /**
     * Adhérents ayant payé leur cotisation (a_paye = 'Payé').
     */
    public function scopePayes($query, string $saison = null)
    {
        return $query->whereHas('inscriptions', function ($q) use ($saison) {
            $q->where('a_paye', 'Payé');
            if ($saison) {
                $q->where('saison', $saison);
            }
        });
    }

    /**
     * Adhérents en attente de paiement (a_paye = 'En attente').
     */
    public function scopeEnAttente($query, string $saison = null)
    {
        return $query->whereHas('inscriptions', function ($q) use ($saison) {
            $q->where('a_paye', 'En attente');
            if ($saison) {
                $q->where('saison', $saison);
            }
        });
    }

    /**
     * Filtre par recherche textuelle (nom, prénom, mail).
     */
    public function scopeRecherche($query, ?string $terme)
    {
        if (blank($terme)) {
            return $query;
        }

        return $query->where(function ($q) use ($terme) {
            $q->where('nom',    'like', "%{$terme}%")
              ->orWhere('prenom', 'like', "%{$terme}%")
              ->orWhere('mail',   'like', "%{$terme}%");
        });
    }
}
