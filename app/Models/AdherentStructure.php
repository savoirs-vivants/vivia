<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Paiement;

class AdherentStructure extends Model
{
    protected $table = 'adherents_structure';

    protected $fillable = [
        'numero_adherent',
        'nom',
        'sigle',
        'adresse',
        'code_postal',
        'ville',
        'date_creation',
        'tel',
        'tel_portable',
        'mail',
        'site_web',
        'nom_correspondant',
        'tel_correspondant',
        'bulletin',
        'communication',
        'autorisation_photo',
        'signature',
        'statut',
        'statut_juridique',
    ];

    protected $casts = [
        'date_creation'     => 'date',
        'bulletin'          => 'boolean',
        'communication'     => 'boolean',
        'autorisation_photo'=> 'boolean',
    ];

    public static function genererNumeroUnique(): string
    {
        do {
            $numero = 'STR-' . date('y') . '-' . strtoupper(Str::random(4));
        } while (self::where('numero_adherent', $numero)->exists());

        return $numero;
    }

    public function inscriptions()
    {
        return $this->hasMany(Inscription::class, 'id_structure');
    }

    public function inscription()
    {
        return $this->hasOne(Inscription::class, 'id_structure')->latestOfMany('date_inscription');
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'id_structure');
    }

    public function getMontantAdhesionAttribute(): float
    {
        if ($this->relationLoaded('paiements')) {
            $total = (float) $this->paiements->sum('montant');
            return $total > 0 ? $total : (float) ($this->inscription?->montant ?? ($this->statut_juridique === 'esr_pme' ? 200 : 50));
        }
        return (float) ($this->inscription?->montant ?? ($this->statut_juridique === 'esr_pme' ? 200 : 50));
    }
}
