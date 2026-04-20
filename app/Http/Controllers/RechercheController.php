<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Models\Saison;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RechercheController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('q');

        $query = Activite::with('gestionnaires')
            ->withCount(['adherentsActifs as nb_inscrits' => function ($q) {
                $q->where('activites_adherents.saison', Saison::current());
            }])
            ->where('type', 'recherche');
        if ($search) {
            $query->where('nom', 'like', "%{$search}%");
        }

        $recherches = (clone $query)->where('is_archived', false)->get();
        $archives   = (clone $query)->where('is_archived', true)->get();

        return view('recherches.index', compact('recherches', 'archives', 'search'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get();
        $selectedUsers = collect();
        return view('recherches.create', compact('users', 'selectedUsers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'gestionnaires' => 'nullable|array',
            'gestionnaires.*' => 'exists:users,id'
        ]);

        $activite = Activite::create([
            'type' => 'recherche',
            'nom' => $validated['nom'],
            'description' => $validated['description'],
            'tarif' => 0,
            'is_archived' => false,
        ]);

        if (!empty($validated['gestionnaires'])) {
            $activite->gestionnaires()->sync($validated['gestionnaires']);
        }

        return redirect()->route('recherches.index')->with('success', 'Le projet de recherche a été créé avec succès.');
    }

    public function show(Activite $recherche)
    {
        $saison = Saison::current();

        $adherents = $recherche->adherents()
            ->wherePivot('saison', $saison)
            ->wherePivot('est_un_abandon', 0)
            ->orderBy('nom')
            ->get();

        return view('recherches.show', compact('recherche', 'adherents'));
    }

    public function edit(Activite $recherche)
    {
        $recherche->load('gestionnaires');
        $users = User::orderBy('name')->get();
        $selectedUsers = $recherche->gestionnaires;

        return view('recherches.edit', compact('recherche', 'users', 'selectedUsers'));
    }

    public function update(Request $request, Activite $recherche)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'gestionnaires' => 'nullable|array',
            'gestionnaires.*' => 'exists:users,id'
        ]);

        $recherche->update([
            'nom' => $validated['nom'],
            'description' => $validated['description'],
        ]);

        $recherche->gestionnaires()->sync($validated['gestionnaires'] ?? []);

        return redirect()->route('recherches.index')->with('success', 'Le projet a été mis à jour.');
    }

    public function toggleArchive(Activite $recherche)
    {
        $recherche->update([
            'is_archived' => !$recherche->is_archived,
        ]);

        $message = $recherche->is_archived
            ? 'La recherche a été archivée.'
            : 'La recherche a été restaurée.';
        return redirect()->back()->with('success', $message);
    }
}
