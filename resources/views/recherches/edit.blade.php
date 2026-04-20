@extends('layouts.app')

@section('title', 'Modifier – ' . $recherche->nom)

@section('content')

    <div class="max-w-4xl mx-auto px-4">
        <div class="flex items-center gap-2 text-xs text-gray-400 mb-8 pl-1">
            <a href="{{ route('recherches.index') }}"
                class="hover:text-[#16A37A] transition-colors font-medium">Recherches</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-600 font-semibold truncate max-w-[200px]">{{ $recherche->nom }}</span>
        </div>
        <div class="bg-white rounded-3xl border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
            <div class="p-6 md:p-12">
                <div class="flex items-start justify-between mb-10">
                    <div>
                        <h1 class="text-3xl font-black text-[#0F143A] tracking-tight">📝 Modifier le projet</h1>
                        <p class="text-base text-gray-400 mt-2">Mettez à jour les détails et l'équipe du programme.</p>
                    </div>
                    @if ($recherche->is_archived)
                        <span
                            class="inline-flex items-center gap-2 px-4 py-2 bg-amber-50 text-amber-600 text-xs font-black rounded-full border border-amber-200 uppercase tracking-widest">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12a2 2 0 002 2h8a2 2 0 002-2L19 8m-9 4v6m4-6v6" />
                            </svg>
                            Projet Archivé
                        </span>
                    @endif
                </div>
                @if ($errors->any())
                    <div class="mb-8 p-5 bg-rose-50 border border-rose-100 rounded-2xl">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li class="text-sm text-rose-600 font-medium">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form action="{{ route('recherches.update', $recherche) }}" method="POST" class="space-y-10">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-xs font-black text-[#0F143A]/40 uppercase tracking-[0.2em] mb-3">Nom du
                                projet de recherche <span class="text-rose-500">*</span></label>
                            <input type="text" name="nom" value="{{ old('nom', $recherche->nom) }}" required
                                class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl text-lg font-bold text-[#0F143A] focus:outline-none focus:ring-4 focus:ring-[#16A37A]/10 focus:border-[#16A37A] focus:bg-white transition-all placeholder:text-gray-300">
                        </div>
                        <div class="md:col-span-2" x-data="gestionnaireSearch({{ json_encode($selectedUsers) }})">
                            <label
                                class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Gestionnaire(s)
                                associé(s) <span class="text-rose-500">*</span></label>
                            <div class="flex flex-wrap gap-2 mb-3">
                                <template x-for="user in selectedUsers" :key="user.id">
                                    <div
                                        class="inline-flex items-center gap-2 px-3 py-1.5 bg-[#222A60] text-white text-xs font-bold rounded-lg shadow-sm">
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
                                    @keydown.escape="results = []" placeholder="Ajouter un gestionnaire (tapez un nom...)"
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
                            <label class="block text-xs font-black text-[#0F143A]/40 uppercase tracking-[0.2em] mb-3 mt-3">Description
                                et enjeux du projet</label>
                            <textarea name="description" rows="8"
                                class="w-full px-6 py-5 bg-gray-50 border border-gray-100 rounded-2xl text-sm leading-relaxed focus:outline-none focus:ring-4 focus:ring-[#16A37A]/10 focus:border-[#16A37A] focus:bg-white transition-all resize-none shadow-inner">{{ old('description', $recherche->description) }}</textarea>
                        </div>
                        <div class="flex items-center justify-between pt-10 border-t border-gray-100">
                            <a href="{{ route('recherches.index') }}"
                                class="px-8 py-4 text-sm font-bold text-gray-400 hover:text-[#0F143A] hover:bg-gray-50 rounded-2xl transition-all">
                                Annuler
                            </a>
                            <button type="submit"
                                class="inline-flex items-center gap-3 px-10 py-4 bg-[#16A37A] hover:bg-[#128a67] text-white text-base font-black rounded-2xl transition-all shadow-[0_10px_20px_-5px_rgba(22,163,122,0.3)] hover:shadow-[0_15px_25px_-5px_rgba(22,163,122,0.4)] active:scale-95">
                                Modifier le projet
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
