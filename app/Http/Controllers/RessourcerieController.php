<?php

namespace App\Http\Controllers;

use App\Models\Ressourcerie;
use Illuminate\Http\Request;

class RessourcerieController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('q');
        $type   = $request->get('type_tarif');

        $toutes = Ressourcerie::query()
            ->when($search, fn($q) => $q->where('nom', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%"))
            ->when($type, fn($q) => $q->where('type_tarif', $type))
            ->orderBy('nom')
            ->get();

        $ressourceries = $toutes->where('is_archived', false);
        $archives      = $toutes->where('is_archived', true);

        return view('ressourcerie.index', compact('ressourceries', 'archives', 'search', 'type'));
    }

    public function create()
    {
        $typesTarif = Ressourcerie::TYPES_TARIF;
        return view('ressourcerie.create', compact('typesTarif'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'               => 'required|string|max:255',
            'description'       => 'nullable|string',
            'condition_location' => 'nullable|string',
            'prix'              => 'nullable|numeric|min:0',
            'type_tarif'        => 'required|in:' . implode(',', array_keys(Ressourcerie::TYPES_TARIF)),
        ]);

        $validated['prix']        = $validated['prix'] ?? 0;
        $validated['is_archived'] = false;

        Ressourcerie::create($validated);

        return redirect()->route('ressourcerie.index')
            ->with('success', 'La ressource a été créée avec succès.');
    }

    public function edit(Ressourcerie $ressourcerie)
    {
        $typesTarif = Ressourcerie::TYPES_TARIF;
        return view('ressourcerie.edit', compact('ressourcerie', 'typesTarif'));
    }

    public function update(Request $request, Ressourcerie $ressourcerie)
    {
        $validated = $request->validate([
            'nom'               => 'required|string|max:255',
            'description'       => 'nullable|string',
            'condition_location' => 'nullable|string',
            'prix'              => 'nullable|numeric|min:0',
            'type_tarif'        => 'required|in:' . implode(',', array_keys(Ressourcerie::TYPES_TARIF)),
        ]);

        $validated['prix'] = $validated['prix'] ?? 0;

        $ressourcerie->update($validated);

        return redirect()->route('ressourcerie.index')
            ->with('success', 'La ressource a été modifiée avec succès.');
    }

    public function toggleArchive(Ressourcerie $ressourcerie)
    {
        $ressourcerie->update([
            'is_archived' => !$ressourcerie->is_archived,
        ]);

        $message = $ressourcerie->is_archived
            ? 'La ressource a été archivée.'
            : 'La ressource a été restaurée.';

        return redirect()->back()->with('success', $message);
    }
}
