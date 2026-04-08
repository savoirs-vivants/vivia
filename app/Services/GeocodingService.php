<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    /**
     * Transforme une adresse postale française en coordonnées GPS.
     *
     * @param string|null 
     * @param string|null
     * @param string|null
     * @return array|null
     */
    public function getCoordinates(?string $adresse, ?string $codePostal, ?string $ville): ?array
    {
        $query = trim(sprintf('%s %s %s', $adresse ?? '', $codePostal ?? '', $ville ?? ''));

        if (empty(trim($query))) {
            return null;
        }

        try {
            $response = Http::timeout(3)->get('https://api-adresse.data.gouv.fr/search/', [
                'q' => $query,
                'limit' => 1,
                'autocomplete' => 0
            ]);

            if ($response->successful() && !empty($response->json('features'))) {
                $coordinates = $response->json('features')[0]['geometry']['coordinates'];

                return [
                    'lat' => $coordinates[1],
                    'lng' => $coordinates[0]
                ];
            }
        } catch (\Exception $e) {
            Log::error("Erreur GeocodingService pour '{$query}' : " . $e->getMessage());
        }

        return null;
    }
}
