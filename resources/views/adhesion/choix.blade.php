@extends('layouts.app')

@section('title', 'Que souhaitez-vous faire ?')

@section('content')

    <div class="min-h-screen bg-gray-50 py-10 px-4 font-grotesk">
        <div class="max-w-xl mx-auto">

            <div class="text-center mb-8">
                <div class="w-16 h-16 rounded-2xl bg-teal-50 border border-teal-100 flex items-center justify-center text-3xl mx-auto mb-4">
                    👋
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Bonjour {{ $adherent->prenom }} !</h1>
                <p class="text-gray-500 mt-1.5 text-sm">Que souhaitez-vous faire aujourd'hui ?</p>
            </div>

            @if ($errors->any())
                <div class="mb-5 p-4 bg-rose-50 border border-rose-200 rounded-xl text-sm text-rose-700 font-medium text-center">
                    {{ $errors->first() }}
                </div>
            @endif

            @if (session('cotisation_payee'))
                <div class="mb-5 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-center">
                    <p class="text-sm font-bold text-emerald-700">✅ Votre cotisation a bien été enregistrée comme payée !</p>
                    <p class="text-xs text-emerald-600 mt-1">Vous pouvez quitter cette page si vous le souhaitez.</p>
                </div>
            @endif

            <form action="{{ route('adhesion.choisir', $token) }}" method="POST" class="space-y-4">
                @csrf

                <button type="submit" name="choix" value="inscription"
                    class="w-full group text-left bg-white border-2 border-gray-100 hover:border-teal-500 hover:bg-teal-50/50 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all duration-200">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-teal-50 group-hover:bg-teal-500 text-teal-600 group-hover:text-white flex items-center justify-center text-2xl shrink-0 transition-colors duration-200">
                            🎯
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-gray-900 text-base">Reprendre une inscription</h3>
                            <p class="text-gray-400 text-sm mt-0.5">S'inscrire à une activité, un stage, ou finaliser un paiement en cours.</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-300 group-hover:text-teal-500 group-hover:translate-x-1 transition-all shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </button>

                <button type="submit" name="choix" value="modifier"
                    class="w-full group text-left bg-white border-2 border-gray-100 hover:border-[#222A60] hover:bg-[#222A60]/[0.03] rounded-2xl p-5 shadow-sm hover:shadow-md transition-all duration-200">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-[#222A60]/5 group-hover:bg-[#222A60] text-[#222A60] group-hover:text-white flex items-center justify-center text-2xl shrink-0 transition-colors duration-200">
                            ✏️
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-gray-900 text-base">Modifier mes informations</h3>
                            <p class="text-gray-400 text-sm mt-0.5">Mettre à jour mes coordonnées, informations médicales, tuteurs…</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-300 group-hover:text-[#222A60] group-hover:translate-x-1 transition-all shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </button>

                @if (!$cotisationPayee && !session('cotisation_payee'))
                    <button type="submit" name="choix" value="payer_adhesion"
                        class="w-full group text-left bg-white border-2 border-amber-200 hover:border-amber-500 hover:bg-amber-50/50 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all duration-200">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-amber-50 group-hover:bg-amber-500 text-amber-600 group-hover:text-white flex items-center justify-center text-2xl shrink-0 transition-colors duration-200">
                                💳
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-gray-900 text-base">Payer l'adhésion</h3>
                                <p class="text-gray-400 text-sm mt-0.5">Votre cotisation annuelle n'a pas encore été réglée — la payer maintenant.</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-300 group-hover:text-amber-500 group-hover:translate-x-1 transition-all shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </button>
                @endif
            </form>
        </div>
    </div>

@endsection
