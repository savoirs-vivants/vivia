@extends('layouts.app')

@section('title', 'Modifier – ' . $ressourcerie->nom)

@section('content')

    <div class="max-w-2xl mx-auto">
        <div class="flex items-center gap-2 text-xs text-gray-400 mb-6 pl-1">
            <a href="{{ route('ressourcerie.index') }}"
                class="hover:text-[#16A37A] transition-colors font-medium">Ressourcerie</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-600 font-semibold truncate max-w-[200px]">{{ $ressourcerie->nom }}</span>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_24px_rgba(0,0,0,0.04)] overflow-hidden">
            <div class="h-1.5 bg-gradient-to-r from-[#16A37A] via-[#16A37A]/60 to-[#16A37A]/20"></div>

            <div class="p-8">
                <div class="flex items-start justify-between mb-8">
                    <div>
                        <h1 class="text-2xl font-black text-[#0F143A] tracking-tight">Modifier la ressource</h1>
                        <p class="text-sm text-gray-400 mt-1">Modifiez les informations ci-dessous puis enregistrez.</p>
                    </div>
                    @if ($ressourcerie->is_archived)
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 text-amber-600 text-xs font-bold rounded-full border border-amber-200">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12a2 2 0 002 2h8a2 2 0 002-2L19 8m-9 4v6m4-6v6" />
                            </svg>
                            Archivée
                        </span>
                    @endif
                </div>

                @if ($errors->any())
                    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-xl">
                        <ul class="space-y-1">
                            @foreach ($errors->all() as $error)
                                <li class="text-sm text-rose-600 font-medium flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span>
                                    {{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('ressourcerie.update', $ressourcerie) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">
                            Nom <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nom" value="{{ old('nom', $ressourcerie->nom) }}" required
                            placeholder="Ex : Salle polyvalente, Sono complète…"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#16A37A]/30 focus:border-[#16A37A] transition-colors @error('nom') border-rose-400 @enderror">
                    </div>

                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">
                            Description
                        </label>
                        <textarea name="description" rows="4" placeholder="Décrivez la ressource, ses caractéristiques, son usage…"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#16A37A]/30 focus:border-[#16A37A] transition-colors resize-none @error('description') border-rose-400 @enderror">{{ old('description', $ressourcerie->description) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">
                            Conditions de location
                        </label>
                        <textarea name="condition_location" rows="3" placeholder="Caution, durée maximale, règles d'utilisation…"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#16A37A]/30 focus:border-[#16A37A] transition-colors resize-none @error('condition_location') border-rose-400 @enderror">{{ old('condition_location', $ressourcerie->condition_location) }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">
                                Prix (€)
                            </label>
                            <div class="relative">
                                <input type="number" name="prix" value="{{ old('prix', $ressourcerie->prix) }}"
                                    min="0" step="0.01" placeholder="0.00"
                                    class="w-full pl-4 pr-10 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#16A37A]/30 focus:border-[#16A37A] transition-colors @error('prix') border-rose-400 @enderror">
                                <span
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-bold">€</span>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Laisser à 0 pour gratuit</p>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">
                                Type de tarif <span class="text-rose-500">*</span>
                            </label>
                            <select name="type_tarif" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#16A37A]/30 focus:border-[#16A37A] transition-colors bg-white @error('type_tarif') border-rose-400 @enderror">
                                <option value="">Choisir un type…</option>
                                @foreach ($typesTarif as $key => $label)
                                    <option value="{{ $key }}"
                                        {{ old('type_tarif', $ressourcerie->type_tarif) === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                        <form action="{{ route('ressourcerie.toggleArchive', $ressourcerie) }}" method="POST"
                            class="inline">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold rounded-xl transition-all
                                {{ $ressourcerie->is_archived
                                    ? 'bg-emerald-50 hover:bg-emerald-100 text-emerald-600'
                                    : 'bg-amber-50 hover:bg-amber-100 text-amber-600' }}">
                                @if ($ressourcerie->is_archived)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Restaurer
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12a2 2 0 002 2h8a2 2 0 002-2L19 8m-9 4v6m4-6v6" />
                                    </svg>
                                    Archiver
                                @endif
                            </button>
                        </form>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('ressourcerie.index') }}"
                                class="px-5 py-2.5 text-sm font-bold text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition-all">
                                Annuler
                            </a>
                            <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#16A37A] hover:bg-[#128a67] text-white text-sm font-bold rounded-xl transition-all shadow-sm hover:shadow-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Enregistrer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
