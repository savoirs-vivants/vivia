@extends('layouts.app')

@section('title', $activite->nom)

@section('content')

    <div class="flex items-center gap-2 text-xs text-gray-400 mb-6 pl-1">
        <a href="{{ route('activites.index') }}" class="hover:text-[#16987C] transition-colors font-medium">Activités</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-600 font-semibold truncate">{{ $activite->nom }}</span>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_24px_rgba(0,0,0,0.04)] p-8 mb-8 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-1.5 h-full bg-[#16987C]"></div>

        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <h1 class="text-3xl font-black text-[#0F143A] tracking-tight mb-2">{{ $activite->nom }}</h1>
                <div class="flex flex-wrap items-center gap-3 text-sm mb-4">
                    @if ($activite->gestionnaires->isNotEmpty())
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-bold">
                            👤 {{ $activite->gestionnaires->first()->firstname }} {{ $activite->gestionnaires->first()->name }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-50 text-gray-400 font-bold">
                            👤 Non assigné
                        </span>
                    @endif
                    <span class="text-gray-500 font-medium">📊 {{ $adherents->count() }} participant(s)</span>
                    @if ($activite->tarif !== null)
                        <span class="text-gray-500 font-medium">💰 {{ number_format($activite->tarif, 0, ',', ' ') }} €</span>
                    @endif
                    @if ($activite->ville)
                        <span class="text-gray-500 font-medium">📍 {{ $activite->ville }}</span>
                    @endif
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-teal-50 text-teal-700 font-bold text-xs">
                        🕐 Accès libre — sans horaire fixe
                    </span>
                </div>
                @if ($activite->description)
                    <p class="text-gray-600 leading-relaxed max-w-3xl">{{ $activite->description }}</p>
                @endif
            </div>
            @can('admin')
                <a href="{{ route('activites.edit', $activite) }}"
                    class="shrink-0 inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Modifier
                </a>
            @endcan
        </div>
    </div>

    <h2 class="text-lg font-bold text-[#0F143A] mb-4 pl-2">Liste des participants</h2>

    @if ($adherents->isNotEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_24px_rgba(0,0,0,0.04)] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/60">
                            <th class="px-5 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-widest">N° Adhérent</th>
                            <th class="px-5 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-widest">Identité</th>
                            <th class="px-5 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-widest">Contact</th>
                            <th class="px-5 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-widest">Date d'inscription</th>
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
            <p class="text-gray-500 font-medium">Aucun adhérent n'est encore inscrit à cette activité.</p>
        </div>
    @endif

@endsection
