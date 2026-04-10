<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use App\Models\Saison;
use App\Traits\AdherentPresenter;

class Adherent extends Model
{

    use AdherentPresenter;

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

    /* ==============================================================================
     * RELATIONS
     * ============================================================================== */

    public function tousLesTuteurs(): BelongsToMany
    {
        return $this->belongsToMany(Tuteur::class, 'adherent_tuteurs', 'id_adherent', 'id_tuteur');
    }

    public function inscriptions(): HasMany
    {
        return $this->hasMany(Inscription::class, 'id_adherent');
    }

    public function inscription(): HasOne
    {
        return $this->hasOne(Inscription::class, 'id_adherent')->latestOfMany();
    }

    public function activites(): BelongsToMany
    {
        return $this->belongsToMany(Activite::class, 'activites_adherents', 'id_adherent', 'id_activite')
            ->withPivot(['saison', 'date_entree', 'date_sortie', 'motif_sortie', 'est_un_abandon'])
            ->withTimestamps();
    }

    public function activitesActives(): BelongsToMany
    {
        return $this->activites()->wherePivotNull('date_sortie');
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class, 'id_adherent');
    }

    /* ==============================================================================
     * ACCESSEURS (ATTRIBUTS VIRTUELS)
     * ============================================================================== */

    public function getNomCompletAttribute(): string
    {
        return trim("{$this->prenom} {$this->nom}");
    }

    public function getInitialesAttribute(): string
    {
        return strtoupper(substr($this->prenom, 0, 1) . substr($this->nom, 0, 1));
    }

    public function getAgeCourantAttribute(): ?int
    {
        return $this->date_naiss?->age;
    }

    public function getTrancheAgeAttribute(): ?string
    {
        $age = $this->age_courant;
        if ($age === null) return null;

        return match (true) {
            $age < 12  => 'Enfant',
            $age < 18  => 'Adolescent',
            default    => 'Adulte',
        };
    }

    public function getEstPayeAttribute(): bool
    {
        return $this->inscription?->a_paye === 'Payé';
    }

    public function getMontantTotalAttribute(): float
    {
        if ($this->relationLoaded('paiements')) {
            return (float) $this->paiements->sum('montant');
        }
        return (float) $this->paiements()->sum('montant');
    }

    public function getSourceLabelAttribute(): string
    {
        if ($this->relationLoaded('paiements')) {
            return $this->paiements->first()?->source ?: 'Interne';
        }
        return $this->paiements()->value('source') ?: 'Interne';
    }

    public function getIsReinscriptionAttribute(): bool
    {
        $saison = Saison::current();

        if ($this->relationLoaded('inscriptions')) {
            return $this->inscriptions->where('saison', $saison)->where('a_paye', Inscription::PAYE)->isNotEmpty();
        }
        return $this->inscriptions()->where('saison', $saison)->where('a_paye', Inscription::PAYE)->exists();
    }

    /* ==============================================================================
     * SCOPES DE RECHERCHE
     * ============================================================================== */

    public function scopePayes($query, string $saison = null)
    {
        return $query->whereHas('inscriptions', function ($q) use ($saison) {
            $q->where('a_paye', 'Payé');
            if ($saison) $q->where('saison', $saison);
        });
    }

    public function scopeEnAttente($query, string $saison = null)
    {
        return $query->whereHas('inscriptions', function ($q) use ($saison) {
            $q->where('a_paye', 'En attente');
            if ($saison) $q->where('saison', $saison);
        });
    }

    public function scopeRecherche($query, ?string $terme)
    {
        if (blank($terme)) return $query;

        return $query->where(function ($q) use ($terme) {
            $q->where('nom', 'like', "%{$terme}%")
                ->orWhere('prenom', 'like', "%{$terme}%")
                ->orWhere('mail', 'like', "%{$terme}%");
        });
    }
}
