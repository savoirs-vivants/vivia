<?php

namespace App\Http\Controllers;

use App\Mail\CodeModificationMail;
use App\Models\Adherent;
use App\Models\Tuteur;
use App\Http\Requests\UpdateFicheAdherentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AdherentAutoModificationController extends Controller
{
    private function adherentDeSession(Request $request, string $token): ?Adherent
    {
        $formData = $request->session()->get("adhesion_{$token}", []);

        if (empty($formData['_adherent_id'])) {
            return null;
        }

        return Adherent::find($formData['_adherent_id']);
    }

    public function choix(Request $request, string $token)
    {
        $adherent = $this->adherentDeSession($request, $token);
        abort_if(!$adherent, 403, 'Lien invalide ou expiré.');

        return view('adhesion.choix', compact('token', 'adherent'));
    }

    public function choisir(Request $request, string $token)
    {
        $adherent = $this->adherentDeSession($request, $token);
        abort_if(!$adherent, 403, 'Lien invalide ou expiré.');

        $choix = $request->input('choix');

        if ($choix === 'modifier') {
            $code = (string) random_int(100000, 999999);
            Cache::put("modif_adherent_code_{$token}", $code, now()->addMinutes(15));

            try {
                Mail::to($adherent->mail)->send(new CodeModificationMail($adherent, $code));
            } catch (\Exception $e) {
                return back()->withErrors(['choix' => 'Impossible d\'envoyer l\'email de vérification pour le moment. Réessayez plus tard.']);
            }

            return redirect()->route('adhesion.modifier.verifier', ['token' => $token]);
        }

        return redirect()->route('adhesion.show', ['token' => $token, 'step' => 2]);
    }

    public function verifierForm(Request $request, string $token)
    {
        $adherent = $this->adherentDeSession($request, $token);
        abort_if(!$adherent, 403, 'Lien invalide ou expiré.');

        return view('adhesion.verifier-code', compact('token', 'adherent'));
    }

    public function verifier(Request $request, string $token)
    {
        $adherent = $this->adherentDeSession($request, $token);
        abort_if(!$adherent, 403, 'Lien invalide ou expiré.');

        $request->validate(['code' => 'required|string']);

        $codeAttendu = Cache::get("modif_adherent_code_{$token}");

        if (!$codeAttendu || trim($request->input('code')) !== $codeAttendu) {
            return back()->withErrors(['code' => 'Code invalide ou expiré. Vous pouvez en redemander un.']);
        }

        Cache::forget("modif_adherent_code_{$token}");
        $request->session()->put("modif_adherent_verifie_{$token}", true);

        return redirect()->route('adhesion.modifier.edit', ['token' => $token]);
    }

    public function renvoyerCode(Request $request, string $token)
    {
        $adherent = $this->adherentDeSession($request, $token);
        abort_if(!$adherent, 403, 'Lien invalide ou expiré.');

        $code = (string) random_int(100000, 999999);
        Cache::put("modif_adherent_code_{$token}", $code, now()->addMinutes(15));

        try {
            Mail::to($adherent->mail)->send(new CodeModificationMail($adherent, $code));
        } catch (\Exception $e) {
            return back()->withErrors(['code' => 'Impossible d\'envoyer l\'email pour le moment. Réessayez plus tard.']);
        }

        return back()->with('success', 'Un nouveau code vient d\'être envoyé.');
    }

    public function edit(Request $request, string $token)
    {
        $adherent = $this->adherentDeSession($request, $token);
        abort_if(!$adherent, 403, 'Lien invalide ou expiré.');
        abort_unless($request->session()->get("modif_adherent_verifie_{$token}"), 403, 'Vérification requise.');

        $adherent->load('tousLesTuteurs');
        $isMineur = $adherent->tranche_age !== 'Adulte';

        return view('adhesion.modifier', compact('token', 'adherent', 'isMineur'));
    }

    public function update(UpdateFicheAdherentRequest $request, string $token)
    {
        $adherent = $this->adherentDeSession($request, $token);
        abort_if(!$adherent, 403, 'Lien invalide ou expiré.');
        abort_unless($request->session()->get("modif_adherent_verifie_{$token}"), 403, 'Vérification requise.');

        DB::transaction(function () use ($request, $adherent) {
            $validated = $request->validated();
            $validated['communication'] = $request->boolean('communication');
            $validated['bulletin']      = $request->input('bulletin', []);
            $validated['manif']         = $request->boolean('manif');

            if ($request->hasFile('carnet_sante')) {
                $validated['carnet'] = $request->file('carnet_sante')->store('carnets', 'local');
            }

            $adherent->update($validated);

            $tuteursIds = [];
            foreach ((array) $request->input('tuteurs', []) as $t) {
                if (empty($t['nom']) && empty($t['prenom'])) {
                    continue;
                }

                $tuteur = Tuteur::updateOrCreate(
                    ['id' => $t['id'] ?? null],
                    [
                        'type'         => $t['type'] ?? 'parent_tuteur',
                        'nom'          => $t['nom'] ?? '',
                        'prenom'       => $t['prenom'] ?? '',
                        'tel'          => $t['tel'] ?? null,
                        'mail'         => $t['mail'] ?? null,
                        'profession'   => $t['profession'] ?? null,
                        'rentre_fin'   => !empty($t['rentre_fin']),
                        'rentre_annul' => !empty($t['rentre_annul']),
                    ]
                );

                $tuteursIds[] = $tuteur->id;
            }

            $adherent->tousLesTuteurs()->sync($tuteursIds);
        });

        $request->session()->forget("modif_adherent_verifie_{$token}");

        return view('adhesion.modification-confirmee', compact('token'));
    }
}
