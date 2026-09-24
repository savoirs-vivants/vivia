@extends('layouts.app')

@section('title', 'Vérification')

@section('content')

    <div class="min-h-screen bg-gray-50 py-16 px-4 font-grotesk">
        <div class="max-w-sm mx-auto text-center">
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-8">
                <div class="w-14 h-14 rounded-2xl bg-teal-50 border border-teal-100 flex items-center justify-center text-3xl mx-auto mb-4">🔒</div>
                <h1 class="text-xl font-bold text-gray-900">Vérification de votre identité</h1>
                <p class="text-sm text-gray-400 mt-2 mb-6">
                    Un code à 6 chiffres vient d'être envoyé à l'adresse email associée à votre fiche.
                </p>

                @if ($errors->any())
                    <div class="mb-5 p-3 bg-rose-50 border border-rose-200 rounded-xl text-sm text-rose-700 font-medium">
                        {{ $errors->first() }}
                    </div>
                @endif
                @if (session('success'))
                    <div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700 font-medium">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('adhesion.modifier.verifier.submit', $token) }}" method="POST" class="mb-4">
                    @csrf
                    <input type="text" name="code" maxlength="6" inputmode="numeric" autocomplete="one-time-code" required
                        placeholder="000000"
                        class="w-full text-center tracking-[0.5em] text-2xl font-mono px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500/25 focus:border-teal-500 mb-4">
                    <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 bg-teal-600 text-white font-semibold px-6 py-3 rounded-lg hover:bg-teal-700 active:scale-95 transition text-sm shadow-sm">
                        Vérifier
                    </button>
                </form>

                <form action="{{ route('adhesion.modifier.renvoyer-code', $token) }}" method="POST">
                    @csrf
                    <button type="submit" class="text-xs font-semibold text-gray-400 hover:text-gray-600 underline">
                        Je n'ai pas reçu le code, renvoyer
                    </button>
                </form>
            </div>
        </div>
    </div>

@endsection
