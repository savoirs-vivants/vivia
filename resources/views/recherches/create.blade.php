@extends('layouts.app')

@section('title', 'Nouveau projet de recherche')

@section('content')

    <div class="max-w-4xl mx-auto px-4">
        <div class="flex items-center gap-2 text-xs text-gray-400 mb-8 pl-1">
            <a href="{{ route('recherches.index') }}"
                class="hover:text-[#16A37A] transition-colors font-medium">Recherches</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-600 font-semibold">Nouveau projet</span>
        </div>

        <div class="bg-white rounded-3xl border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
            <div class="h-2 bg-gradient-to-r from-[#16A37A] via-[#16A37A]/60 to-[#16A37A]/10"></div>
            <div class="p-6 md:p-12">
                <div class="mb-10">
                    <h1 class="text-3xl font-black text-[#0F143A] tracking-tight">🔬 Créer un projet de recherche</h1>
                    <p class="text-base text-gray-400 mt-2">Définissez les paramètres et l'équipe responsable de ce nouveau
                        programme participatif.</p>
                </div>
                @if ($errors->any())
                    <div class="mb-8 p-5 bg-rose-50 border border-rose-100 rounded-2xl">
                        <h3 class="text-rose-800 font-bold text-sm mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                            Des informations sont manquantes :
                        </h3>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li class="text-sm text-rose-600 font-medium">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form action="{{ route('recherches.store') }}" method="POST" class="space-y-10">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-black text-[#0F143A]/40 uppercase tracking-[0.2em] mb-3">Nom du
                                projet de recherche <span class="text-rose-500">*</span></label>
                            <input type="text" name="nom" value="{{ old('nom') }}" required
                                placeholder="Ex: Oasis de biodiversité en milieu urbain"
                                class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl text-lg font-bold text-[#0F143A] focus:outline-none focus:ring-4 focus:ring-[#16A37A]/10 focus:border-[#16A37A] focus:bg-white transition-all placeholder:text-gray-300">
                            <div class="md:col-span-2 mt-3" x-data="gestionnaireSearch(@json($selectedUsers ?? []))">
                                <label
                                    class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Gestionnaire(s)
                                    associé(s) <span class="text-rose-500">*</span></label>
                                <div class="flex flex-wrap gap-2 mb-3">
                                    <template x-for="user in selectedUsers" :key="user.id">
                                        <div
                                            class="inline-flex items-center gap-2 px-3 py-1.5 bg-[#222A60] text-white text-xs font-bold rounded-lg shadow-sm animate-in fade-in zoom-in duration-200">
                                            <span x-text="user.firstname + ' ' + user.name"></span>
                                            <button type="button" @click="removeUser(user.id)"
                                                class="hover:text-rose-400 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                            <input type="hidden" name="gestionnaires[]" :value="user.id">
                                        </div>
                                    </template>
                                    <template x-if="selectedUsers.length === 0">
                                        <span class="text-xs text-gray-400 italic">Aucun gestionnaire sélectionné</span>
                                    </template>
                                </div>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                    <input type="text" x-model="query" @input.debounce.300ms="search()"
                                        @keydown.escape="results = []"
                                        placeholder="Ajouter un gestionnaire (tapez un nom...)"
                                        class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-[#16987C]/30 focus:border-[#16987C]/40 transition-all">
                                    <div x-show="results.length > 0" @click.away="results = []"
                                        class="absolute z-50 w-full mt-2 bg-white border border-gray-100 rounded-xl shadow-xl overflow-hidden">
                                        <template x-for="user in results" :key="user.id">
                                            <button type="button" @click="addUser(user)"
                                                class="w-full px-4 py-3 text-left text-sm hover:bg-gray-50 flex items-center justify-between group transition-colors">
                                                <div><span class="font-bold text-[#0F143A]"
                                                        x-text="user.firstname + ' ' + user.name"></span></div>
                                                <svg class="w-4 h-4 text-gray-300 group-hover:text-[#16987C] transition-colors"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                        d="M12 4v16m8-8H4" />
                                                </svg>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                                @error('gestionnaires')
                                    <span class="text-xs text-rose-500 mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label
                                    class="block text-xs font-black text-[#0F143A]/40 uppercase tracking-[0.2em] mb-3 mt-3">Description
                                    et enjeux du projet</label>
                                <textarea name="description" rows="8"
                                    placeholder="Décrivez les objectifs scientifiques, les méthodes de collecte de données et le rôle attendu des citoyens participants..."
                                    class="w-full px-6 py-5 bg-gray-50 border border-gray-100 rounded-2xl text-sm leading-relaxed focus:outline-none focus:ring-4 focus:ring-[#16A37A]/10 focus:border-[#16A37A] focus:bg-white transition-all resize-none shadow-inner placeholder:text-gray-300">{{ old('description') }}</textarea>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-10 border-t border-gray-100">
                            <a href="{{ route('recherches.index') }}"
                                class="px-8 py-4 text-sm font-bold text-gray-400 hover:text-[#0F143A] hover:bg-gray-50 rounded-2xl transition-all">
                                Annuler
                            </a>
                            <button type="submit"
                                class="inline-flex items-center gap-3 px-10 py-4 bg-[#16A37A] hover:bg-[#128a67] text-white text-base font-black rounded-2xl transition-all shadow-[0_10px_20px_-5px_rgba(22,163,122,0.3)] hover:shadow-[0_15px_25px_-5px_rgba(22,163,122,0.4)] active:scale-95">
                                Créer le projet
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
