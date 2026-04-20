<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Adherent;
use App\Models\Paiement;
use App\Models\Ressourcerie;
use App\Models\Setting;
use App\Models\Inscription;
use App\Models\AdherentStructure;
use App\Models\SyncLog;
use Illuminate\Support\Facades\Log;
use App\Traits\AdhesionSharedLogic;


class HelloAssoController extends Controller
{
    use AdhesionSharedLogic;
    public function helloassoReturn(Request $request, $token, $status)
    {
        if ($status === 'cancel' || $status === 'error') {
            return redirect()->route('adhesion.show', ['token' => $token, 'step' => 10])
                ->withErrors(['helloasso' => 'Le paiement a été annulé ou a échoué. Vous pouvez réessayer.']);
        }

        $formData = $request->session()->get("adhesion_{$token}", []);
        $formData['_last_completed'] = max((int)($formData['_last_completed'] ?? 0), 10);
        $request->session()->put("adhesion_{$token}", $formData);

        if (!empty($formData['_is_paying_solde'])) {
            $adherentSolde = Adherent::find($formData['_adherent_id']);
            $preInscSolde  = Inscription::find($formData['_pre_inscription_id']);

            if ($adherentSolde && $preInscSolde) {
                Paiement::create([
                    'id_adherent'   => $adherentSolde->id,
                    'montant'       => $formData['_montant_solde'],
                    'source'        => 'HelloAsso',
                    'date_paiement' => now()->toDateString(),
                    'commentaire'   => 'Restant de l\'acompte payé via HelloAsso',
                ]);
                $preInscSolde->update(['a_paye' => Inscription::EN_ATTENTE]);
            }

            $formData['_helloasso_ok'] = true;
            $formData['_last_completed'] = 11;
            unset($formData['_is_paying_solde']);
            $request->session()->put("adhesion_{$token}", $formData);

            return redirect()->route('adhesion.show', ['token' => $token, 'step' => 11]);
        }

        if ($this->isStructure($formData)) {
            if (($formData['type_activite'] ?? '') === 'ressourcerie' && empty($formData['_ressourcerie_paid'])) {
                $formData['_ressourcerie_paid'] = true;

                if (!empty($formData['_structure_id'])) {
                    $ressourcerieIds     = $formData['ressourcerie_selectionnees'] ?? [];
                    $montantRessourcerie = Ressourcerie::whereIn('id', $ressourcerieIds)->sum('prix');

                    if ($montantRessourcerie > 0) {
                        Paiement::create([
                            'id_structure'  => $formData['_structure_id'],
                            'montant'       => $montantRessourcerie,
                            'source'        => 'HelloAsso',
                            'date_paiement' => now()->toDateString(),
                            'commentaire'   => 'Ressourcerie structure via HelloAsso',
                        ]);
                    }
                }

                if (($formData['is_adherent'] ?? 'non') === 'oui' && !empty($formData['_structure_id'])) {
                    $formData['_paiement2_cree'] = true;
                    $formData['_helloasso_ok']   = true;
                    $formData['_last_completed'] = 11;
                    $request->session()->put("adhesion_{$token}", $formData);
                    return redirect()->route('adhesion.show', ['token' => $token, 'step' => 11]);
                }

                $request->session()->put("adhesion_{$token}", $formData);
                $request->session()->put("paiement1_done_{$token}", true);
                return redirect()->route('adhesion.show', ['token' => $token, 'step' => 10]);
            }

            $formData['_helloasso_ok']   = true;
            $formData['_last_completed'] = 11;
            $request->session()->put("adhesion_{$token}", $formData);
            return redirect()->route('adhesion.show', ['token' => $token, 'step' => 11]);
        }

        $estNouvelAdherent = ($formData['is_adherent'] ?? 'non') === 'non';

        if (!empty($formData['_adherent_id']) && empty($formData['_paiement1_cree'])) {
            $activiteIds       = array_filter((array) ($formData['activites_selectionnees'] ?? []));
            $ressourcerieIds   = array_filter((array) ($formData['ressourcerie_selectionnees'] ?? []));
            $isPreInscription  = $this->isPreInscription($formData);

            $totalActivites    = $isPreInscription ? (count($activiteIds) * 50.0) : $this->calculerMontantActivites($activiteIds);
            $totalRessourcerie = !empty($ressourcerieIds) ? Ressourcerie::whereIn('id', $ressourcerieIds)->sum('prix') : 0;
            $montantActivite   = (float) ($totalActivites + $totalRessourcerie);

            if ($montantActivite > 0) {
                $commentaireDynamique = $isPreInscription
                    ? 'Acompte pré-inscription'
                    : $this->genererCommentairePaiement($activiteIds, $ressourcerieIds);

                Paiement::create([
                    'id_adherent'   => $formData['_adherent_id'],
                    'montant'       => $montantActivite,
                    'source'        => 'HelloAsso',
                    'date_paiement' => now()->toDateString(),
                    'commentaire'   => $commentaireDynamique,
                ]);
                $formData['_paiement1_cree'] = true;
                $request->session()->put("adhesion_{$token}", $formData);
            }
        }

        if ($estNouvelAdherent) {
            $request->session()->put("paiement1_done_{$token}", true);
            return redirect()->route('adhesion.show', ['token' => $token, 'step' => 10]);
        }

        $formData['_helloasso_ok'] = true;
        $formData['_last_completed'] = 11;
        $request->session()->put("adhesion_{$token}", $formData);

        return redirect()->route('adhesion.show', ['token' => $token, 'step' => 11]);
    }

    public function helloassoCheckout2(Request $request, string $token)
    {
        abort_if(!$request->session()->has("adhesion_{$token}"), 403);

        $formData = $request->session()->get("adhesion_{$token}", []);
        $formData['_via_url_checkout'] = true;
        $request->session()->put("adhesion_{$token}", $formData);

        $isSandbox  = config('services.helloasso.sandbox', true);
        $basePublic = $isSandbox ? 'https://www.helloasso-sandbox.com' : 'https://www.helloasso.com';
        $orgSlug    = config('services.helloasso.org_slug');

        $formSlug = Setting::where('key', 'helloasso_membership_form_slug')->value('value')
            ?? config('services.helloasso.membership_form_slug');

        $url = "{$basePublic}/associations/{$orgSlug}/adhesions/{$formSlug}";

        Log::info("HelloAsso checkout2 : URL d'adhésion", ['url' => $url, 'statut_juridique' => $formData['statut_juridique'] ?? 'n/a']);

        if ($request->expectsJson()) {
            return response()->json(['url' => $url]);
        }

        return redirect($url);
    }

    public function helloassoReturn2(Request $request, $token, $status)
    {
        if ($status === 'cancel' || $status === 'error') {
            $request->session()->put("paiement1_done_{$token}", true);
            return redirect()->route('adhesion.show', ['token' => $token, 'step' => 10])
                ->withErrors(['helloasso2' => 'Le paiement de la cotisation a été annulé. Vous pouvez réessayer.']);
        }

        $formData = $request->session()->get("adhesion_{$token}", []);
        $formData['_helloasso_ok']   = true;
        $formData['_last_completed'] = 11;
        $request->session()->put("adhesion_{$token}", $formData);

        if (!empty($formData['_adherent_id']) && empty($formData['_paiement2_cree'])) {
            $typeActivite = $formData['type_activite'] ?? '';
            $cotisation = $this->getMontantCotisation($formData);

            if ($cotisation > 0) {
                Paiement::create([
                    'id_adherent'   => $formData['_adherent_id'],
                    'montant'       => $cotisation,
                    'source'        => 'HelloAsso',
                    'date_paiement' => now()->toDateString(),
                    'commentaire'   => 'Cotisation annuelle via HelloAsso',
                ]);
                $formData['_paiement2_cree'] = true;
                $request->session()->put("adhesion_{$token}", $formData);
            }
        }

        return redirect()->route('adhesion.show', ['token' => $token, 'step' => 11]);
    }

    public function verifierCotisation(Request $request, string $token)
    {
        abort_if(!$request->session()->has("adhesion_{$token}"), 403);

        $formData = $request->session()->get("adhesion_{$token}", []);

        if (!empty($formData['_paiement2_cree'])) {
            return redirect()->route('adhesion.show', ['token' => $token, 'step' => 11]);
        }

        if ($this->isStructure($formData)) {
            if (empty($formData['_structure_id'])) {
                return redirect()->route('adhesion.show', ['token' => $token, 'step' => 10])
                    ->withErrors(['helloasso2' => 'Structure introuvable, veuillez réessayer.']);
            }

            $isDejaAdherentStr = ($formData['is_adherent'] ?? 'non') === 'oui';
            $montantCotisation = $isDejaAdherentStr ? 0 : (($formData['statut_juridique'] ?? '') === 'esr_pme' ? 200 : 50);

            if ($montantCotisation > 0) {
                Paiement::create([
                    'id_structure'  => $formData['_structure_id'],
                    'montant'       => $montantCotisation,
                    'source'        => 'HelloAsso',
                    'date_paiement' => now()->toDateString(),
                    'commentaire'   => 'Cotisation structure via HelloAsso',
                ]);
            }

            $formData['_paiement2_cree'] = true;
            $formData['_helloasso_ok']   = true;
            $formData['_last_completed'] = 11;
            $request->session()->put("adhesion_{$token}", $formData);

            Log::info("Cotisation structure {$formData['_structure_id']} validée (URL directe HelloAsso)");
            return redirect()->route('adhesion.show', ['token' => $token, 'step' => 11]);
        }

        if (empty($formData['_adherent_id'])) {
            return redirect()->route('adhesion.show', ['token' => $token, 'step' => 10]);
        }

        $typeActivite   = $formData['type_activite'] ?? '';
        $isDejaAdherent = ($formData['is_adherent'] ?? 'non') === 'oui';
        $cotisation = $this->getMontantCotisation($formData);

        if ($cotisation > 0) {
            Paiement::create([
                'id_adherent'   => $formData['_adherent_id'],
                'montant'       => $cotisation,
                'source'        => 'HelloAsso',
                'date_paiement' => now()->toDateString(),
                'commentaire'   => 'Cotisation annuelle via HelloAsso',
            ]);
        }

        $formData['_paiement2_cree'] = true;
        $formData['_helloasso_ok']   = true;
        $formData['_last_completed'] = 11;
        $request->session()->put("adhesion_{$token}", $formData);

        Log::info("Cotisation validée pour adhérent {$formData['_adherent_id']} (URL directe HelloAsso)");
        return redirect()->route('adhesion.show', ['token' => $token, 'step' => 11]);
    }

    public function helloassoWebhook(Request $request)
    {
        $payload = $request->all();
        $eventType = $payload['eventType'] ?? null;
        $state     = $payload['data']['state'] ?? null;

        if ($eventType !== 'Payment' || $state !== 'Authorized') {
            return response()->json(['status' => 'ignored'], 200);
        }

        $syncLog = SyncLog::create([
            'source' => 'webhook_helloasso',
            'status' => 'running',
            'payments_imported' => 0,
            'errors' => []
        ]);

        try {
            $email     = $payload['data']['payer']['email']  ?? null;
            $firstName = $payload['data']['payer']['firstName'] ?? null;
            $lastName  = $payload['data']['payer']['lastName']  ?? null;
            $amount    = ($payload['data']['amount'] ?? 0) / 100;
            $formSlug  = $payload['data']['order']['formSlug'] ?? null;

            if (!$email) {
                $syncLog->update([
                    'status' => 'error',
                    'errors' => ["Email manquant dans le payload HelloAsso (Formulaire: {$formSlug})"]
                ]);
                return response()->json(['status' => 'missing_email'], 200);
            }

            $adherent = Adherent::where('mail', $email)->latest()->first();
            $structure = AdherentStructure::where('mail', $email)->latest()->first();

            if ($adherent) {
                $dejaCree = Paiement::where('id_adherent', $adherent->id)
                    ->where('source', 'HelloAsso')
                    ->where('montant', $amount)
                    ->whereDate('date_paiement', today())
                    ->exists();

                if (!$dejaCree) {
                    Paiement::create([
                        'id_adherent'   => $adherent->id,
                        'montant'       => $amount,
                        'source'        => 'HelloAsso',
                        'date_paiement' => now()->toDateString(),
                        'commentaire'   => 'Paiement Webhook HelloAsso',
                    ]);
                }

                $syncLog->update(['status' => 'success', 'payments_imported' => 1]);
                return response()->json(['status' => 'ok'], 200);
            }

            if ($structure) {
                $dejaCreeeStruct = Paiement::where('id_structure', $structure->id)
                    ->where('source', 'HelloAsso')
                    ->where('montant', $amount)
                    ->whereDate('date_paiement', today())
                    ->exists();

                if (!$dejaCreeeStruct) {
                    Paiement::create([
                        'id_structure'  => $structure->id,
                        'montant'       => $amount,
                        'source'        => 'HelloAsso',
                        'date_paiement' => now()->toDateString(),
                    ]);
                }

                $syncLog->update(['status' => 'success', 'payments_imported' => 1]);
                return response()->json(['status' => 'ok'], 200);
            }

            $syncLog->update([
                'status' => 'warning',
                'errors' => ["Paiement de {$amount}€ reçu pour {$firstName} {$lastName}, mais l'email {$email} est introuvable."]
            ]);

            return response()->json(['status' => 'not_found_but_acknowledged'], 200);
        } catch (\Exception $e) {
            Log::error("Erreur critique Webhook HelloAsso : " . $e->getMessage());

            $syncLog->update([
                'status' => 'error',
                'errors' => ["Erreur serveur durant le traitement : " . $e->getMessage()]
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }
}
