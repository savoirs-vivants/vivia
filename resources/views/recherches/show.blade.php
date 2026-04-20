@extends('layouts.app')

@section('title', 'Adhérents - ' . $recherche->nom)

@section('content')

    <div class="flex items-center gap-2 text-xs text-gray-400 mb-6 pl-1">
        <a href="{{ route('recherches.index') }}" class="hover:text-[#16A37A] transition-colors font-medium">Recherches</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-600 font-semibold truncate">{{ $recherche->nom }}</span>
    </div>

    <div
        class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_24px_rgba(0,0,0,0.04)] p-8 mb-8 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-1.5 h-full bg-[#16A37A]"></div>
        <h1 class="text-3xl font-black text-[#0F143A] tracking-tight mb-2">{{ $recherche->nom }}</h1>
        <div class="flex items-center gap-4 text-sm mb-4">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-bold">
                @if ($recherche->gestionnaires->isNotEmpty())
                    👤 {{ $recherche->gestionnaires->first()->firstname }}
                    {{ $recherche->gestionnaires->first()->name }}
                @else
                    👤 Non assigné
                @endif
            </span>
            <span class="text-gray-500 font-medium">
                📊 {{ $adherents->count() }} participant(s)
            </span>
        </div>
        <p class="text-gray-600 leading-relaxed max-w-3xl">{{ $recherche->description }}</p>
    </div>

    <h2 class="text-lg font-bold text-[#0F143A] mb-4 pl-2">Liste des participants</h2>

    @if ($adherents->isNotEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_24px_rgba(0,0,0,0.04)] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/60">
                            <th class="px-5 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-widest">N°
                                Adhérent</th>
                            <th class="px-5 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-widest">
                                Identité</th>
                            <th class="px-5 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-widest">
                                Contact</th>
                            <th class="px-5 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-widest">Date
                                d'inscription</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($adherents as $adherent)
                            <tr class="hover:bg-gray-50/60 transition-colors">
                                <td class="px-5 py-4 whitespace-nowrap font-medium text-gray-500">
                                    #{{ $adherent->numero_adherent }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="font-bold text-[#0F143A]">{{ $adherent->prenom }} {{ $adherent->nom }}</div>
                                    @if ($adherent->age)
                                        <div class="text-xs text-gray-400 mt-0.5">{{ $adherent->age }} ans</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="text-gray-700">{{ $adherent->mail ?? '—' }}</div>
                                    <div class="text-gray-500 text-xs mt-0.5">{{ $adherent->tel ?? '—' }}</div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-gray-500">
                                    {{ $adherent->pivot->date_entree ? \Carbon\Carbon::parse($adherent->pivot->date_entree)->format('d/m/Y') : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-gray-100 p-10 text-center">
            <span class="text-3xl mb-3 block">📭</span>
            <p class="text-gray-500 font-medium">Aucun adhérent n'est encore inscrit à ce projet.</p>
        </div>
    @endif

@endsection
