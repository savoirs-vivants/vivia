@extends('layouts.app')

@section('title', 'Modifications enregistrées')

@section('content')

    <div class="min-h-screen bg-gray-50 py-16 px-4 font-grotesk">
        <div class="max-w-sm mx-auto text-center">
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-8">
                <div class="w-16 h-16 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-3xl mx-auto mb-4">✅</div>
                <h1 class="text-xl font-bold text-gray-900">Fiche mise à jour</h1>
                <p class="text-sm text-gray-400 mt-2">
                    Vos informations ont bien été enregistrées. Merci !
                </p>
            </div>
        </div>
    </div>

@endsection
