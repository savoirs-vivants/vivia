@extends('layouts.app')

@section('title', 'Que souhaitez-vous faire ?')

@section('content')

    <div class="max-w-xl mx-auto py-10 px-4">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Bonjour {{ $adherent->prenom }} 👋</h1>
            <p class="text-gray-400 mt-2 text-sm">Que souhaitez-vous faire aujourd'hui ?</p>
        </div>

        @if ($errors->any())
            <div class="mb-5 p-4 bg-rose-50 border border-rose-200 rounded-xl text-sm text-rose-700 font-medium">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('adhesion.choisir', $token) }}" method="POST" class="space-y-4">
            @csrf

            <button type="submit" name="choix" value="inscription"
                class="w-full text-left p-5 bg-white border-2 border-gray-100 rounded-2xl shadow-sm hover:border-teal-500 hover:bg-teal-50/40 transition-all group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-teal-600/10 text-teal-700 flex items-center justify-center text-2xl shrink-0">🎯</div>
                    <div>
                        <p class="font-bold text-gray-900">Reprendre une inscription</p>
                        <p class="text-sm text-gray-400 mt-0.5">S'inscrire à une activité, un stage ou finaliser un paiement.</p>
                    </div>
                </div>
            </button>

            <button type="submit" name="choix" value="modifier"
                class="w-full text-left p-5 bg-white border-2 border-gray-100 rounded-2xl shadow-sm hover:border-indigo-500 hover:bg-indigo-50/40 transition-all group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-600/10 text-indigo-700 flex items-center justify-center text-2xl shrink-0">✏️</div>
                    <div>
                        <p class="font-bold text-gray-900">Modifier mes informations</p>
                        <p class="text-sm text-gray-400 mt-0.5">Mettre à jour mes coordonnées, informations médicales, tuteurs…</p>
                    </div>
                </div>
            </button>
        </form>
    </div>

@endsection
