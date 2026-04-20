<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Saison extends Model
{
    protected $table = 'saisons';

    protected $fillable = ['nom', 'date_debut', 'date_fin', 'est_active'];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin'   => 'date',
        'est_active' => 'boolean',
    ];

    /**
     * Returns the name (e.g. "2025-2026") of the currently active saison.
     */
    public static function current(): string
    {
        $active = static::where('est_active', true)->first();
        if ($active) {
            return $active->nom;
        }

        $year = now()->month >= 9 ? now()->year : now()->year - 1;
        return $year . '-' . ($year + 1);
    }

    /**
     * Returns the Saison model for the currently active saison.
     */
    public static function currentModel(): ?self
    {
        $active = static::where('est_active', true)->first();
        if ($active) {
            return $active;
        }

        $year = now()->month >= 9 ? now()->year : now()->year - 1;
        $nom = $year . '-' . ($year + 1);
        return static::where('nom', $nom)->first();
    }

    /**
     * Returns all saison names sorted newest first.
     */
    public static function allSorted(): \Illuminate\Support\Collection
    {
        return static::orderByDesc('date_debut')->pluck('nom');
    }

    public static function preinscriptions(): string
    {
        $year = now()->month >= 9 ? now()->year + 1 : now()->year;
        return $year . '-' . ($year + 1);
    }

    public static function syncActive(): void
    {
        $year = now()->month >= 9 ? now()->year : now()->year - 1;
        $nom  = $year . '-' . ($year + 1);

        $saison = static::firstOrCreate(
            ['nom' => $nom],
            [
                'date_debut' => Carbon::create($year, 9, 1)->toDateString(),
                'date_fin'   => Carbon::create($year + 1, 8, 31)->toDateString(),
                'est_active' => true,
            ]
        );

        static::where('id', '!=', $saison->id)->update(['est_active' => false]);
        $saison->update(['est_active' => true]);
    }
}
