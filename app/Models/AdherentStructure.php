<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdherentStructure extends Model
{
    protected $table = 'adherents_structure';

    protected $fillable = [
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
        'statut',
        'statut_juridique',
    ];

    protected $casts = [
        'date_creation'     => 'date',
        'bulletin'          => 'boolean',
        'communication'     => 'boolean',
        'autorisation_photo'=> 'boolean',
    ];

    public function inscriptions()
    {
        return $this->hasMany(Inscription::class, 'id_structure');
    }

    public function inscription()
    {
        return $this->hasOne(Inscription::class, 'id_structure')->latestOfMany('date_inscription');
    }

    public function getMontantAdhesionAttribute(): int
    {
        return $this->statut_juridique === 'esr_pme' ? 200 : 50;
    }
}
