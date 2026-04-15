@extends('layouts.app')

@section('title', 'Paramètres HelloAsso')

@section('content')

    <div class="max-w-3xl mx-auto mt-8">

        {{-- Message de succès après enregistrement --}}
        @if (session('success'))
            <div class="mb-6 p-4 bg-[#16A37A]/10 border border-[#16A37A]/20 rounded-xl flex items-center gap-3">
                <span class="w-2 h-2 rounded-full bg-[#16A37A]"></span>
                <p class="font-bold text-[#16A37A]">{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-white border border-gray-200 shadow-sm rounded-2xl p-6 sm:p-8">
            <h2 class="text-xl font-black font-grotesk text-[#0F143A] mb-2">Campagne HelloAsso (Adhésions)</h2>
            <p class="text-gray-500 text-sm mb-6">
                Modifiez ce lien à chaque nouvelle rentrée scolaire (ex: passez de <span class="font-bold">adhesion-2025-2026</span> à <span class="font-bold">adhesion-2026-2027</span>).
            </p>

            <form action="{{ route('settings.update') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="helloasso_membership_form_slug" class="block text-sm font-bold text-gray-700 mb-2">
                        Identifiant de la campagne (Slug)
                    </label>

                    <div class="flex items-center w-full bg-gray-50 border border-gray-200 rounded-xl overflow-hidden transition-colors focus-within:bg-white focus-within:ring-2 focus-within:ring-[#16A37A]/20 focus-within:border-[#16A37A] @error('helloasso_membership_form_slug') border-red-500 @enderror">

                        <span class="pl-4 pr-1 text-gray-400 text-sm sm:text-base hidden sm:inline-flex whitespace-nowrap select-none">
                            helloasso.com/.../adhesions/
                        </span>

                        <input type="text"
                               name="helloasso_membership_form_slug"
                               id="helloasso_membership_form_slug"
                               value="{{ old('helloasso_membership_form_slug', $currentSlug) }}"
                               class="w-full py-3 sm:pl-1 pl-4 pr-4 bg-transparent border-0 focus:ring-0 text-[#0F143A] font-medium"
                               required>
                    </div>

                    @error('helloasso_membership_form_slug')
                        <p class="mt-2 text-sm text-red-500 font-bold">{{ $message }}</p>
                    @enderror

                    <p class="mt-3 text-xs text-gray-400">
                        Où le trouver ? Allez sur la page de votre campagne HelloAsso. L'identifiant est la toute dernière partie de l'adresse web (l'URL).
                    </p>
                </div>

                <div class="pt-4 border-t border-gray-100 flex justify-end">
                    <button type="submit"
                            class="px-6 py-3 bg-[#0F143A] hover:bg-black text-white font-bold rounded-xl transition-all active:scale-95 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
