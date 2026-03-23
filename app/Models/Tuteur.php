<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tuteur extends Model
{
    protected $table = 'tuteur';

    protected $fillable = [
        'nom',
        'prenom',
        'mail',
        'tel',
        'adhere',
        'rentre_fin',
        'rentre_annul',
        'profession',
    ];

    protected $casts = [
        'adhere'       => 'boolean',
        'rentre_fin'   => 'boolean',
        'rentre_annul' => 'boolean', 
    ];

    /**
     * Enfants / adhérents rattachés à ce tuteur.
     */
    public function adherents(): HasMany
    {
        return $this->hasMany(Adherent::class, 'id_tuteur');
    }

    public function getNomCompletAttribute(): string
    {
        return trim("{$this->prenom} {$this->nom}");
    }

    public function getInitialesAttribute(): string
    {
        return strtoupper(
            substr($this->prenom, 0, 1) . substr($this->nom, 0, 1)
        );
    }
}
