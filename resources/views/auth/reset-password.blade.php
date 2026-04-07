@extends('layouts.app')
@section('title', 'Nouveau mot de passe')
@section('content')

<section class="bg-gray-50 min-h-screen flex items-center justify-center px-4 sm:px-6 py-12">

    <div class="w-full max-w-md relative z-10">

        <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">

            <div class="px-8 pt-10 text-center border-b border-gray-50">
                <div class="w-16 h-16 bg-sv-green/10 rounded-2xl flex items-center justify-center mx-auto mb-5">
                    <svg class="w-8 h-8 text-sv-green" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                    </svg>
                </div>
                <h1 class="font-sans font-bold text-2xl text-sv-blue tracking-tight">Nouveau mot de passe</h1>
                <p class="text-gray-500 mt-2 text-sm leading-relaxed">
                    Veuillez choisir un mot de passe robuste d'au moins 8 caractères.
                </p>
            </div>

            <div class="px-8 pb-8">

                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 flex items-center gap-3">
                        <svg class="w-5 h-5 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-red-600 text-sm font-medium">{{ $errors->first() }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">

                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-gray-700">
                            Nouveau mot de passe
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input type="password" name="password" id="password" required placeholder="••••••••"
                                class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-base rounded-xl
                                       focus:bg-white focus:ring-4 focus:ring-sv-green/10 focus:border-sv-green outline-none
                                       block pl-11 pr-12 py-3.5 transition-all duration-200 placeholder:text-gray-400">
                            <button type="button" onclick="togglePwd('password')"
                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 transition-colors focus:outline-none">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 eye-icon">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-gray-700">
                            Confirmer le mot de passe
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <input type="password" name="password_confirmation" id="password_confirmation" required placeholder="••••••••"
                                class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-base rounded-xl
                                       focus:bg-white focus:ring-4 focus:ring-sv-green/10 focus:border-sv-green outline-none
                                       block pl-11 pr-12 py-3.5 transition-all duration-200 placeholder:text-gray-400">
                            <button type="button" onclick="togglePwd('password_confirmation')"
                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 transition-colors focus:outline-none">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 eye-icon">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-sv-blue hover:bg-[#111827] text-white font-bold text-base rounded-xl
                               px-5 py-4 shadow-lg shadow-sv-blue/20 hover:shadow-xl hover:-translate-y-0.5
                               transition-all duration-200 mt-2">
                        Enregistrer et me connecter
                    </button>
                </form>
            </div>
        </div>

    </div>
</section>
@endsection
