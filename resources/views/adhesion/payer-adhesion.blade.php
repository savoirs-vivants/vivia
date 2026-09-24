@extends('layouts.app')

@section('title', "Payer l'adhésion")

@section('content')

    <div class="min-h-screen bg-gray-50 py-16 px-4 font-grotesk">
        <div class="max-w-sm mx-auto text-center">
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-8" x-data="cotisationPaiement()" x-init="init()">
                <div class="w-14 h-14 rounded-2xl bg-teal-50 border border-teal-100 flex items-center justify-center text-3xl mx-auto mb-4">💳</div>
                <h1 class="text-xl font-bold text-gray-900">Payer l'adhésion annuelle</h1>
                <p class="text-sm text-gray-400 mt-2 mb-1">
                    Cotisation annuelle : <strong class="text-gray-700">{{ number_format($montantCotisation, 2, ',', ' ') }} €</strong>
                </p>
                <p class="text-xs text-gray-400 mb-6">
                    Vous allez être redirigé·e vers HelloAsso pour régler la cotisation, puis reviendrez ici confirmer le paiement.
                </p>

                @if ($errors->any())
                    <div class="mb-5 p-3 bg-rose-50 border border-rose-200 rounded-xl text-sm text-rose-700 font-medium">
                        {{ $errors->first() }}
                    </div>
                @endif

                <button @click="ouvrirHelloAsso()" :disabled="loading" x-show="!dejaClique"
                    class="w-full inline-flex items-center justify-center gap-2 bg-teal-600 text-white font-semibold px-6 py-3 rounded-lg hover:bg-teal-700 transition text-sm shadow-sm disabled:opacity-60">
                    <span x-show="!loading">Payer la cotisation sur HelloAsso →</span>
                    <span x-show="loading">Chargement…</span>
                </button>

                <div x-show="dejaClique" x-transition class="space-y-3">
                    <div class="p-4 bg-teal-50 border border-teal-200 rounded-xl text-sm text-teal-800 text-left">
                        <p class="font-semibold mb-1">La page HelloAsso s'est ouverte dans un nouvel onglet.</p>
                        <p class="text-xs text-teal-700">Une fois le paiement finalisé, revenez ici et cliquez sur le bouton ci-dessous.</p>
                    </div>

                    <form action="{{ route('adhesion.payer-adhesion.confirmer', $token) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 bg-emerald-600 text-white font-semibold px-6 py-3 rounded-lg hover:bg-emerald-700 transition text-sm">
                            ✅ J'ai payé — vérifier et continuer
                        </button>
                    </form>

                    <button @click="dejaClique = false" type="button"
                        class="w-full text-xs text-gray-400 hover:text-gray-600 underline py-1">
                        ← Rouvrir la page HelloAsso
                    </button>
                </div>

                <a href="{{ route('adhesion.choix', ['token' => $token]) }}" class="block mt-5 text-xs font-medium text-gray-400 hover:text-gray-600">Annuler et revenir en arrière</a>
            </div>
        </div>
    </div>

    <script>
        window.AdhesionConfig = {
            checkoutRoute: '{{ route('adhesion.helloasso2.checkout', $token) }}',
            csrfToken: '{{ csrf_token() }}',
            dejaClique: false,
        };
    </script>
    <script src="{{ asset('js/adhesion.js') }}"></script>

@endsection
