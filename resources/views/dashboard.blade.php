@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
    <div class="grid grid-cols-12 gap-6">

        {{-- ── CARTE 1 : HÉRO ──── --}}
        <div
            class="col-span-4 bg-gradient-to-br from-sv-blue to-[#111536] rounded-3xl p-8 relative overflow-hidden shadow-xl shadow-sv-blue/10">
            <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-sv-green/20 rounded-full blur-3xl"></div>
            <div class="relative z-10 flex flex-col h-full justify-between">
                <div class="flex items-center gap-3 text-white/80">
                    <div class="w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center backdrop-blur-md">
                        <svg class="w-5 h-5 text-sv-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <span class="font-grotesk font-medium tracking-wide">Adhérents</span>
                </div>
                <div class="mt-8">
                    <p class="font-grotesk font-black text-6xl text-white">{{ $totalAdherents }}</p>
                    @if ($newThisMonth > 0)
                        <div
                            class="inline-flex items-center gap-1.5 mt-4 bg-sv-green/20 text-sv-green px-3 py-1.5 rounded-full text-sm font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                            +{{ $newThisMonth }} ce mois-ci
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── CARTE 2 : FINANCES ──── --}}
        <div class="col-span-4 bg-white rounded-3xl p-8 shadow-sm border border-gray-100 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="font-grotesk font-bold text-gray-500 uppercase text-xs tracking-widest">Trésorerie
                    encaissée</span>
                <span class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center border border-gray-100">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div>
                <p class="font-grotesk font-bold text-4xl text-gray-900 mt-6">
                    {{ number_format($totalCotisations, 0, ',', ' ') }} <span class="text-gray-300 font-light">€</span></p>
                @if ($totalEnAttente > 0)
                    <div
                        class="mt-4 flex items-center gap-2 text-sm bg-amber-50 p-2 rounded-xl border border-amber-100 w-fit">
                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                        <span class="text-amber-800 font-medium">Attente :
                            <strong>{{ number_format($totalEnAttente, 0, ',', ' ') }} €</strong></span>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── CARTE 3 : STATUTS (Donut) ──── --}}
        <div class="col-span-4 bg-white rounded-3xl p-8 shadow-sm border border-gray-100 flex items-center gap-6">
            @php $total = $statutPaye + $statutAttente ?: 1; @endphp
            <div class="relative w-28 h-28 shrink-0">
                @php
                    $r = 45;
                    $cx = 56;
                    $cy = 56;
                    $circ = 2 * M_PI * $r;
                    $pPaye = ($statutPaye / $total) * $circ;
                    $pAttente = ($statutAttente / $total) * $circ;
                @endphp
                <svg viewBox="0 0 112 112" class="w-full h-full -rotate-90">
                    <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="none"
                        stroke="#f3f4f6" stroke-width="14" />
                    @if ($statutPaye > 0)
                        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="none"
                            stroke="#16987C" stroke-width="14" stroke-dasharray="{{ $pPaye }} {{ $circ - $pPaye }}"
                            stroke-dashoffset="{{ $circ }}" stroke-linecap="round" />
                    @endif
                    @if ($statutAttente > 0)
                        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="none"
                            stroke="#f59e0b" stroke-width="14"
                            stroke-dasharray="{{ $pAttente }} {{ $circ - $pAttente }}"
                            stroke-dashoffset="{{ $circ - $pPaye }}" stroke-linecap="round" />
                    @endif
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="font-grotesk font-bold text-xl text-sv-blue">{{ $totalAdherents }}</span>
                </div>
            </div>
            <div class="space-y-3 w-full">
                <p class="font-grotesk font-bold text-gray-900 mb-2">Dossiers</p>
                <div
                    class="flex items-center justify-between text-sm bg-gray-50 rounded-xl px-3 py-2 border border-gray-100">
                    <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-sv-green"></span> Payés
                    </div>
                    <span class="font-bold text-gray-900">{{ $statutPaye }}</span>
                </div>
                <div
                    class="flex items-center justify-between text-sm bg-gray-50 rounded-xl px-3 py-2 border border-gray-100">
                    <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Attente
                    </div>
                    <span class="font-bold text-gray-900">{{ $statutAttente }}</span>
                </div>
            </div>
        </div>

        {{-- ── LIGNE 2 : PROCHAINE SÉANCE & TOP ACTIVITÉS ──── --}}

        <div
            class="col-span-5 bg-white rounded-3xl p-8 shadow-sm border border-gray-100 flex flex-col h-full min-h-[400px]">

            @if (Auth::user()->role === 'admin')
                <div class="flex justify-between items-center mb-10">
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-sv-blue"></span>
                        <span class="font-grotesk font-bold text-gray-400 uppercase text-[10px] tracking-[0.15em]">Analyse
                            Structurelle</span>
                    </div>
                </div>

                <div class="flex-1 space-y-6">
                    <h3 class="font-grotesk font-black text-2xl text-sv-blue leading-tight mb-6">Type d'adhésions</h3>

                    @foreach ($repartitionTypes as $type)
                        @php
                            $typePct = $totalAdherents > 0 ? ($type->total / $totalAdherents) * 100 : 0;
                        @endphp
                        <div class="space-y-2">
                            <div class="flex justify-between items-end">
                                <span class="text-sm font-bold text-gray-700 capitalize">{{ $type->type_adhesion }}</span>
                                <span class="text-sm font-black text-sv-blue">{{ $type->total }}</span>
                            </div>
                            <div class="h-2 w-full bg-gray-50 rounded-full overflow-hidden border border-gray-100">
                                <div class="h-full bg-sv-green rounded-full" style="width: {{ $typePct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex justify-between items-center mb-10">
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-sv-green"></span>
                        <span
                            class="font-grotesk font-bold text-gray-400 uppercase text-[10px] tracking-[0.15em]">Prochaine
                            séance</span>
                    </div>
                    @if ($prochaineSeance)
                        <div
                            class="bg-sv-blue/5 text-sv-blue font-grotesk font-black text-xs px-3 py-1.5 rounded-xl border border-sv-blue/10 flex items-center gap-2">
                            {{ \Carbon\Carbon::parse($prochaineSeance->date)->format('H:i') }}
                        </div>
                    @endif
                </div>

                @if ($prochaineSeance)
                    <div class="flex-1">
                        <div
                            class="inline-block px-3 py-1 rounded-lg bg-sv-green/10 text-sv-green text-[10px] font-black uppercase tracking-wider mb-3">
                            {{ \Carbon\Carbon::parse($prochaineSeance->date)->format('d/m') }}
                        </div>
                        <h3 class="font-grotesk font-black text-3xl text-sv-blue leading-[1.1] mb-3 tracking-tight">
                            {{ $prochaineSeance->activite_nom }}
                        </h3>
                        <p
                            class="text-gray-500 text-sm font-medium flex items-center gap-2 bg-gray-50 w-fit px-3 py-1.5 rounded-xl border border-gray-100">
                            {{ $prochaineSeance->adresse ?: 'Lieu non défini' }}
                        </p>
                    </div>

                    <div class="mt-10">
                        <div
                            class="flex justify-between items-end mb-4 font-bold text-xs text-gray-400 uppercase tracking-wider">
                            <span>Appel</span>
                            <span>{{ $nbPresencesEnregistrees }} / {{ $prochaineSeance->nb_inscrits }}</span>
                        </div>
                        <button
                            class="w-full bg-sv-blue hover:bg-[#111536] text-white font-grotesk font-bold py-4 rounded-2xl transition-all shadow-lg shadow-sv-blue/20 flex items-center justify-center gap-3">
                            Faire l'appel
                        </button>
                    </div>
                @else
                    <div class="flex-1 flex flex-col items-center justify-center text-gray-300">
                        <p class="font-medium text-sm italic uppercase tracking-widest">Aucune séance</p>
                    </div>
                @endif
            @endif
        </div>

        {{-- TOP 3 ACTIVITÉS --}}
        <div class="col-span-7 bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-8">
                <h3 class="font-grotesk font-bold text-xl text-gray-900">Activités Populaires</h3>
                <span
                    class="text-[10px] font-black uppercase text-gray-400 tracking-widest bg-gray-50 px-2 py-1 rounded-md">Top
                    3</span>
            </div>
            <div class="space-y-8">
                @forelse ($activitesStats as $act)
                    @php
                        $pct = ($act->total_inscrits / $maxInscrits) * 100;
                        $horairesArr = json_decode($act->horaires, true);
                        $premierHoraire = !empty($horairesArr)
                            ? array_key_first($horairesArr) . ' ' . reset($horairesArr)
                            : 'Horaire non défini';
                    @endphp
                    <div>
                        <div class="flex justify-between items-end mb-2">
                            <div>
                                <h4 class="font-grotesk font-bold text-gray-900 text-base">{{ $act->nom }}</h4>
                                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-tight mt-0.5">
                                    {{ $premierHoraire }}</p>
                            </div>
                            <div class="text-right">
                                <span
                                    class="font-grotesk font-black text-xl text-sv-blue leading-none">{{ $act->total_inscrits }}</span>
                            </div>
                        </div>
                        <div class="h-2.5 w-full bg-gray-50 rounded-full border border-gray-100 p-0.5">
                            <div class="h-full bg-sv-blue rounded-full transition-all duration-1000"
                                style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-400 py-10">Aucune donnée.</p>
                @endforelse
            </div>
        </div>

    </div>
@endsection
