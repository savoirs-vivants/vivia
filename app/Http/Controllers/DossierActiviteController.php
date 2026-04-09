<?php

namespace App\Http\Controllers;

use App\Models\DossierActivite;
use Illuminate\Http\Request;
use App\Http\Requests\StoreDossierRequest;

class DossierActiviteController extends Controller
{
    public function store(StoreDossierRequest $request)
    {

        DossierActivite::create(['nom' => $request->nom]);

        return redirect()->back()->with('success', 'Le dossier "' . $request->nom . '" a été créé.');
    }

    public function destroy(DossierActivite $dossier)
    {
        $nom = $dossier->nom;
        $dossier->delete();

        return redirect()->back()->with('success', 'Le dossier "' . $nom . '" a été supprimé. Les activités ont été déplacées hors du dossier.');
    }
}
