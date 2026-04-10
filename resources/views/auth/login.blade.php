@extends('layouts.app')

@section('title', 'Connexion')

@section('content')

    <section class="bg-gray-50 min-h-screen flex items-center justify-center px-4 sm:px-6 py-12">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
                <div class="px-8 pt-10 text-center border-b border-gray-50">
                    <div class="w-16 h-16 bg-sv-green/10 rounded-2xl flex items-center justify-center mx-auto mb-5">
                        <svg class="w-8 h-8 text-sv-green" fill="none" stroke="currentColor" stroke-width="1.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                    </div>
                    <h1 class="font-sans font-bold text-2xl text-sv-blue tracking-tight">Connexion à votre espace</h1>
                    <p class="text-gray-500 mt-2 text-sm">Veuillez saisir vos identifiants pour continuer.</p>
                </div>

                <div class="px-8 pb-8 pt-8">
                    <form class="space-y-6" action="{{ route('login.submit') }}" method="POST">
                        @csrf

                        @if ($errors->any())
                            <div
                                class="p-4 rounded-xl bg-red-50 border border-red-100 text-red-600 text-sm font-medium flex items-start gap-3">
                                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>{{ $errors->first() }}</span>
                            </div>
                        @endif
                        <div class="space-y-2">
                            <label for="email" class="block text-sm font-bold text-gray-700">
                                Adresse e-mail
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                    </svg>
                                </div>
                                <input type="email" name="email" id="email" required
                                    placeholder="exemple@example.com"
                                    class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-base rounded-xl
                                           focus:bg-white focus:ring-4 focus:ring-sv-green/10 focus:border-sv-green outline-none
                                           block pl-11 pr-4 py-3.5 transition-all duration-200 placeholder:text-gray-400">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label for="password" class="block text-sm font-bold text-gray-700">
                                    Mot de passe
                                </label>
                                <a href="{{ route('password.forgot') }}"
                                    class="text-sm font-semibold text-sv-green hover:text-sv-blue transition-colors">
                                    Oublié ?
                                </a>
                            </div>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <input type="password" name="password" id="password" required placeholder="••••••••"
                                    class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-base rounded-xl
                                           focus:bg-white focus:ring-4 focus:ring-sv-green/10 focus:border-sv-green outline-none
                                           block pl-11 pr-12 py-3.5 transition-all duration-200 placeholder:text-gray-400">
                                <button type="button" onclick="togglePwd('password')"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 transition-colors focus:outline-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-5 h-5" id="eye-icon">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="pt-4">
                            <button type="submit"
                                class="w-full bg-sv-blue hover:bg-[#111827] text-white font-bold text-base rounded-xl
                                       px-5 py-4 shadow-lg shadow-sv-blue/20 hover:shadow-xl hover:-translate-y-0.5
                                       transition-all duration-200 flex items-center justify-center gap-2 group">
                                Se connecter
                                <svg class="w-5 h-5 text-white/70 group-hover:text-white transition-colors" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <p class="text-center text-xs text-gray-400 mt-8 font-medium">
                &copy; {{ date('Y') }} Vivia. Tous droits réservés.
            </p>
        </div>
    </section>
@endsection
