<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    protected $fillable = ['source', 'status', 'payments_imported', 'errors'];

    protected $casts = [
        'errors' => 'array', 
    ];
}
