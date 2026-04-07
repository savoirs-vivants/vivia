@extends('layouts.app')
@section('title', 'Mot de passe oublié')
@section('content')

<section class="bg-gray-50 min-h-screen flex items-center justify-center px-4 sm:px-6 py-12">

    <div class="w-full max-w-md relative z-10">

        <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">

            <div class="px-8 pt-10 text-center border-b border-gray-50 relative">

                <a href="{{ route('login') }}" class="absolute top-6 left-6 text-gray-400 hover:text-sv-blue transition-colors p-2 bg-gray-50 rounded-full hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>

                <div class="w-16 h-16 bg-sv-blue/10 rounded-2xl flex items-center justify-center mx-auto mb-5 mt-2">
                    <svg class="w-8 h-8 text-sv-blue" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                </div>
                <h1 class="font-sans font-bold text-2xl text-sv-blue tracking-tight">Mot de passe oublié ?</h1>
                <p class="text-gray-500 mt-2 text-sm leading-relaxed">
                    Entrez votre adresse email. Nous vous enverrons un lien pour créer un nouveau mot de passe.
                </p>
            </div>

            <div class="px-8 pb-8 pt-8">

                @if (session('success'))
                    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-100 flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold text-emerald-800 text-sm">Email envoyé avec succès !</p>
                            <p class="text-emerald-600 text-sm mt-1 leading-relaxed">{{ session('success') }} <br>Pensez à vérifier vos spams.</p>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 flex items-center gap-3">
                        <svg class="w-5 h-5 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-red-600 text-sm font-medium">{{ $errors->first() }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.send') }}" class="space-y-6">
                    @csrf
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-gray-700">
                            Adresse e-mail
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <input type="email" name="email" required value="{{ old('email') }}"
                                placeholder="prenom.nom@exemple.fr"
                                class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-base rounded-xl
                                       focus:bg-white focus:ring-4 focus:ring-sv-green/10 focus:border-sv-green outline-none
                                       block pl-11 pr-4 py-3.5 transition-all duration-200 placeholder:text-gray-400">
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-sv-blue hover:bg-[#111827] text-white font-bold text-base rounded-xl
                               px-5 py-4 shadow-lg shadow-sv-blue/20 hover:shadow-xl hover:-translate-y-0.5
                               transition-all duration-200 flex items-center justify-center gap-2 group mt-2">
                        Envoyer le lien
                        <svg class="w-4 h-4 text-white/70 group-hover:text-white transition-colors" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </button>
                </form>
            </div>

            <div class="bg-gray-50 px-8 py-4 border-t border-gray-100 text-center">
                <p class="text-xs text-gray-400 font-medium">
                    Ce lien sera valable pendant 60 minutes.
                </p>
            </div>
        </div>

    </div>
</section>

@endsection
