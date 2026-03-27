<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HelloAssoService
{
    private string $baseUrl;
    private string $authUrl;
    private string $clientId;
    private string $clientSecret;
    private string $orgSlug;

    public function __construct()
    {
        $isSandbox = config('services.helloasso.sandbox', true);

        $this->baseUrl       = $isSandbox
            ? 'https://api.helloasso-sandbox.com/v5'
            : 'https://api.helloasso.com/v5';

        $this->authUrl       = $isSandbox
            ? 'https://api.helloasso-sandbox.com/oauth2/token'
            : 'https://api.helloasso.com/oauth2/token';

        $this->clientId      = config('services.helloasso.client_id');
        $this->clientSecret  = config('services.helloasso.client_secret');
        $this->orgSlug       = config('services.helloasso.org_slug');
    }

    private function getAccessToken(): string
    {
        return Cache::remember('helloasso_token', now()->addMinutes(25), function () {
            Log::info('HelloAsso: récupération d\'un nouveau token');

            $response = Http::asForm()->post($this->authUrl, [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

            Log::info('HelloAsso auth response', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            if ($response->failed()) {
                throw new \Exception("Authentification HelloAsso échouée [{$response->status()}] : " . $response->body());
            }

            return $response->json('access_token');
        });
    }

    public function createCheckout(int $amountInCents, array $payer, string $tokenRequete, string $returnRoute = 'adhesion.helloasso.return', string $itemName = 'Adhésion - Savoirs Vivants'): string
    {
        $accessToken = $this->getAccessToken();

        $payload = [
            'totalAmount'      => $amountInCents,
            'initialAmount'    => $amountInCents,
            'itemName'         => $itemName,
            'backUrl'          => route($returnRoute, ['token' => $tokenRequete, 'status' => 'cancel']),
            'errorUrl'         => route($returnRoute, ['token' => $tokenRequete, 'status' => 'error']),
            'returnUrl'        => route($returnRoute, ['token' => $tokenRequete, 'status' => 'success']),
            'containsDonation' => false,
            'payer'            => [
                'firstName' => $payer['prenom'],
                'lastName'  => $payer['nom'],
                'email'     => $payer['mail'],
            ],
        ];

        Log::info('HelloAsso createCheckout payload', $payload);

        $response = Http::withToken($accessToken)
            ->post("{$this->baseUrl}/organizations/{$this->orgSlug}/checkout-intents", $payload);

        Log::info('HelloAsso checkout response', [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);

        if ($response->successful()) {
            $redirectUrl = $response->json('redirectUrl');
            if (!$redirectUrl) {
                throw new \Exception('HelloAsso n\'a pas retourné de redirectUrl. Réponse : ' . $response->body());
            }
            return $redirectUrl;
        }

        throw new \Exception("Erreur checkout HelloAsso [{$response->status()}] : " . $response->body());
    }

    /**
     * Va lire le prix du premier tarif de la campagne d'adhésion
     * Retourne le prix en EUROS.
     */
    public function getBaseMembershipPrice(string $formSlug): float
    {
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)
            ->get("{$this->baseUrl}/organizations/{$this->orgSlug}/forms/Membership/{$formSlug}/public");

        if ($response->successful()) {
            $data = $response->json();

            if (isset($data['tiers']) && count($data['tiers']) > 0) {
                $priceInCents = $data['tiers'][0]['price'];
                return $priceInCents / 100;
            }
        }

        throw new \Exception("Impossible de lire le tarif sur HelloAsso. Vérifiez le nom de la campagne (HELLOASSO_MEMBERSHIP_FORM_SLUG).");
    }
}
