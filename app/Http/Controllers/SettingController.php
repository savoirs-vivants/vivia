<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        $currentSlug = Setting::where('key', 'helloasso_membership_form_slug')->value('value')
                       ?? config('services.helloasso.membership_form_slug');

        return view('settings.index', compact('currentSlug'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'helloasso_membership_form_slug' => 'required|string|max:255',
        ], [
            'helloasso_membership_form_slug.required' => 'Le lien de la campagne est obligatoire.'
        ]);

        Setting::updateOrCreate(
            ['key' => 'helloasso_membership_form_slug'],
            ['value' => trim($request->input('helloasso_membership_form_slug'))]
        );

        return redirect()->route('settings.index')->with('success', 'La campagne HelloAsso a été mise à jour avec succès !');
    }
}
