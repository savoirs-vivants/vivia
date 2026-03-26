<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DossierActivite extends Model
{
    protected $table = 'dossiers_activite';

    protected $fillable = ['nom'];

    public function activites(): HasMany
    {
        return $this->hasMany(Activite::class, 'id_dossier');
    }

    public function activitesActives(): HasMany
    {
        return $this->hasMany(Activite::class, 'id_dossier')->where('is_archived', false);
    }
}
