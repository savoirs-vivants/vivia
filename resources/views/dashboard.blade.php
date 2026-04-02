@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
    <div class="flex flex-col gap-4 sm:gap-5 h-full">

        @if(empty($isRoleRestreint))
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

            <div
                class="bg-gradient-to-br from-[#083325] to-[#111536] rounded-2xl p-5 sm:p-6
                        relative overflow-hidden shadow-lg shadow-[#083325]/10 border border-white/10">
                <div class="absolute -right-8 -bottom-8 w-40 h-40 bg-[#16A37A]/20 rounded-full blur-3xl pointer-events-none">
                </div>
                <svg class="absolute top-0 right-0 w-48 h-48 text-white/5 translate-x-1/3 -translate-y-1/4 pointer-events-none"
                    viewBox="0 0 100 100" fill="currentColor">
                    <circle cx="50" cy="50" r="40" />
                    <circle cx="50" cy="50" r="20" fill="none" stroke="currentColor" stroke-width="2" />
                </svg>
                <div class="relative z-10">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center border border-white/5">
                            <svg class="w-5 h-5 text-[#16A37A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <span
                            class="font-grotesk font-bold text-white/60 text-xs uppercase tracking-widest">Adhérents</span>
                    </div>
                    <p class="font-grotesk font-black text-5xl sm:text-6xl text-white tracking-tighter leading-none">
                        {{ $totalAdherents }}
                    </p>
                    @if ($newThisMonth > 0)
                        <div
                            class="inline-flex items-center gap-1.5 mt-3 bg-white/10 border border-white/10
                                    text-[#16A37A] px-3 py-1.5 rounded-lg text-xs font-bold">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                            +{{ $newThisMonth }} ce mois
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 sm:p-6 border border-gray-200 shadow-sm relative overflow-hidden">
                <svg class="absolute -right-6 -bottom-6 w-32 h-32 text-gray-50 pointer-events-none" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="relative z-10 flex items-start justify-between mb-4">
                    <div>
                        <p class="font-grotesk font-bold text-gray-400 uppercase text-[10px] tracking-[0.2em] mb-0.5">
                            Finances</p>
                        <h3 class="font-grotesk font-bold text-gray-700 text-sm">Trésorerie encaissée</h3>
                    </div>
                    <div
                        class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center border border-blue-100/50 shrink-0">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-1 relative z-10">
                    <p class="font-grotesk font-black text-3xl sm:text-4xl text-gray-900 tracking-tight leading-none">
                        {{ number_format($totalCotisations, 0, ',', ' ') }}
                    </p>
                    <span class="text-xl sm:text-2xl text-gray-200 font-bold">€</span>
                </div>
                @if ($totalEnAttente > 0)
                    <div
                        class="mt-3 inline-flex items-center gap-2 bg-amber-50 px-3 py-1.5
                                rounded-xl border border-amber-100 relative z-10">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse shrink-0"></span>
                        <p class="text-xs font-bold text-amber-700">
                            Attente : <span class="text-amber-900">{{ number_format($totalEnAttente, 0, ',', ' ') }}
                                €</span>
                        </p>
                    </div>
                @else
                    <div
                        class="mt-3 inline-flex items-center gap-1.5 text-xs font-bold text-[#16A37A]
                                bg-[#16A37A]/10 px-3 py-1.5 rounded-lg relative z-10">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Tout est à jour
                    </div>
                @endif
            </div>

            <div
                class="sm:col-span-2 lg:col-span-1 bg-white rounded-2xl p-5 sm:p-6
                        border border-gray-200 shadow-sm flex flex-col justify-center">
                <div class="mb-4">
                    <p class="font-grotesk font-bold text-gray-400 uppercase text-[10px] tracking-[0.2em] mb-0.5">Suivi</p>
                    <h3 class="font-grotesk font-bold text-gray-700 text-sm">État des dossiers</h3>
                </div>

                <div class="space-y-4 sm:space-y-0 sm:grid sm:grid-cols-3 sm:gap-4 lg:block lg:space-y-5">
                    @php $total = max(1, $statutPaye + $statutPartiel + $statutAttente); @endphp

                    <div class="sm:flex sm:flex-col sm:justify-end lg:block">
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <div class="flex items-center gap-2 font-bold text-gray-700">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#16A37A] shadow-sm"></span> Payés
                            </div>
                            <span class="font-black text-gray-900 text-sm">{{ $statutPaye }}</span>
                        </div>
                        <div class="h-2 w-full bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-[#16A37A] rounded-full"
                                style="width: {{ round(($statutPaye / $total) * 100) }}%"></div>
                        </div>
                    </div>
                    <div class="sm:flex sm:flex-col sm:justify-end lg:block">
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <div class="flex items-center gap-2 font-bold text-gray-700">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-400 shadow-sm"></span> Partiel
                            </div>
                            <span class="font-black text-gray-900 text-sm">{{ $statutPartiel }}</span>
                        </div>
                        <div class="h-2 w-full bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-amber-400 rounded-full"
                                style="width: {{ round(($statutPartiel / $total) * 100) }}%"></div>
                        </div>
                    </div>
                    <div class="sm:flex sm:flex-col sm:justify-end lg:block">
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <div class="flex items-center gap-2 font-bold text-gray-700">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-400 shadow-sm"></span> En attente
                            </div>
                            <span class="font-black text-gray-900 text-sm">{{ $statutAttente }}</span>
                        </div>
                        <div class="h-2 w-full bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-rose-400 rounded-full"
                                style="width: {{ round(($statutAttente / $total) * 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        @endif

        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden flex flex-col flex-1 min-h-0">

            <div
                class="px-5 sm:px-8 py-5 sm:py-6 border-b border-gray-100 bg-white flex items-center justify-between shrink-0">
                <div>
                    <h3
                        class="font-grotesk font-black text-lg sm:text-xl text-gray-900 tracking-tight flex items-center gap-2 sm:gap-3">
                        @if ($isGestionnaire)
                            <span
                                class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center text-sm sm:text-base">📋</span>
                            Carnet de bord
                        @elseif (Auth::user()->role === 'admin')
                            <span
                                class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm sm:text-base">📊</span>
                            Analyse des effectifs
                        @else
                            <span
                                class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-gray-50 text-gray-400 flex items-center justify-center text-sm sm:text-base">☕</span>
                            Espace personnel
                        @endif
                    </h3>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium mt-1 pl-9 sm:pl-11">
                        @if ($isGestionnaire)
                            Gérez votre prochaine intervention sur le terrain
                        @elseif (Auth::user()->role === 'admin')
                            Répartition de la structure et palmarès des activités
                        @else
                            Aucune activité assignée pour le moment
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row flex-1 bg-gray-50/30">

                @if ($isGestionnaire)
                    <div class="flex-1 p-5 sm:p-8">

                        @if ($prochaineSeance)
                            <div class="max-w-4xl mx-auto">
                                <div class="flex items-center gap-3 mb-5 sm:mb-6">
                                    <span class="relative flex h-2.5 w-2.5">
                                        <span
                                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-teal-500"></span>
                                    </span>
                                    <p class="text-xs font-bold text-teal-600 uppercase tracking-widest">Votre prochaine
                                        séance</p>
                                </div>

                                <div
                                    class="bg-white rounded-3xl border border-gray-200 shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
                                    <div class="grid grid-cols-1 md:grid-cols-5">

                                        <div class="md:col-span-3 p-5 sm:p-8 flex items-start gap-4 sm:gap-6">
                                            <div
                                                class="w-20 h-24 sm:w-24 sm:h-28 shrink-0 bg-white border-2 border-gray-100
                                                        rounded-2xl flex flex-col overflow-hidden text-center shadow-sm">
                                                <div
                                                    class="bg-[#222A60] text-white text-[11px] font-bold uppercase py-2 tracking-widest">
                                                    {{ \Carbon\Carbon::parse($prochaineSeance->date)->translatedFormat('M') }}
                                                </div>
                                                <div class="flex-1 flex flex-col items-center justify-center">
                                                    <span
                                                        class="font-grotesk font-black text-3xl sm:text-4xl text-gray-900 leading-none mb-1">
                                                        {{ \Carbon\Carbon::parse($prochaineSeance->date)->format('d') }}
                                                    </span>
                                                    <span class="text-[10px] font-bold text-gray-400 uppercase">
                                                        {{ \Carbon\Carbon::parse($prochaineSeance->date)->translatedFormat('D') }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="flex flex-col justify-center py-1 min-w-0">
                                                <div
                                                    class="text-[#16A37A] font-black text-sm mb-2 flex items-center gap-2
                                                            bg-teal-50 w-fit px-3 py-1 rounded-lg">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2.5"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ \Carbon\Carbon::parse($prochaineSeance->date)->format('H:i') }}
                                                </div>
                                                <h3
                                                    class="font-grotesk font-black text-xl sm:text-2xl text-[#0F143A] leading-tight mb-3 truncate">
                                                    {{ $prochaineSeance->activite_nom }}
                                                </h3>
                                                <p
                                                    class="inline-flex items-center gap-2 text-sm font-medium text-gray-500">
                                                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                    <span
                                                        class="truncate">{{ $prochaineSeance->adresse ?: 'Lieu non défini' }}</span>
                                                </p>
                                            </div>
                                        </div>

                                        <div
                                            class="md:col-span-2 bg-gray-50 border-t md:border-t-0 md:border-l border-gray-100
                                                    p-5 sm:p-8 flex flex-col justify-end gap-3">

                                            @if ($prochaineSeance->statut === 'terminee')
                                                <button disabled
                                                    class="w-full bg-gray-100 text-gray-400 font-grotesk font-bold
                                                               py-3.5 rounded-xl cursor-not-allowed
                                                               flex items-center justify-center gap-2 text-sm border border-gray-200">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Séance clôturée
                                                </button>

                                            @elseif ($prochaineSeance->statut === 'appel_fait')
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="w-2 h-2 rounded-full bg-teal-500 animate-pulse inline-block"></span>
                                                    <span class="text-xs font-bold text-teal-600">Appel enregistré</span>
                                                </div>
                                                <button onclick="openFinOverlay()"
                                                    class="w-full bg-[#222A60] hover:bg-[#2d3a8c] text-white font-grotesk font-bold
                                                               py-3.5 rounded-xl transition-all duration-300 shadow-md hover:shadow-lg
                                                               flex items-center justify-center gap-2 group text-sm">
                                                    Fin de l'activité
                                                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform"
                                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                    </svg>
                                                </button>

                                            @else
                                                <button onclick="openAppelOverlay()"
                                                    class="w-full bg-[#083325] hover:bg-[#16A37A] text-white font-grotesk font-bold
                                                               py-3.5 rounded-xl transition-all duration-300 shadow-md hover:shadow-lg
                                                               flex items-center justify-center gap-2 group text-sm">
                                                    Procéder à l'appel
                                                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform"
                                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                    </svg>
                                                </button>
                                            @endif

                                        </div>

                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="h-full flex flex-col items-center justify-center text-gray-400 py-12">
                                <div
                                    class="w-20 h-20 rounded-3xl bg-white flex items-center justify-center mb-5
                                            border border-gray-100 shadow-sm shadow-gray-100/50">
                                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-black text-gray-900 mb-1">Aucune séance à venir</h3>
                                <p class="text-sm font-medium text-gray-500 text-center">
                                    Vous n'avez pas de séance planifiée prochainement.
                                </p>
                            </div>
                        @endif

                    </div>
                @elseif (Auth::user()->role === 'admin')
                    <div class="flex-1 p-5 sm:p-8 border-b lg:border-b-0 lg:border-r border-gray-200 bg-white">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-6 sm:mb-8 text-center">
                            Types des adhésions
                        </p>
                        <div class="relative w-full mx-auto flex items-center justify-center"
                            style="max-height: 280px; height: min(280px, 55vw);">
                            <canvas id="chartTypes" class="absolute inset-0 w-full h-full z-10"></canvas>
                        </div>
                        @php
                            $typeLabels = $repartitionTypes
                                ->pluck('type_adhesion')
                                ->map(fn($l) => ucfirst($l))
                                ->toJson();
                            $typeData = $repartitionTypes->pluck('total')->toJson();
                        @endphp
                    </div>

                    <div class="flex-[1.5] p-5 sm:p-8 bg-white">
                        <div class="flex items-center justify-between mb-6 sm:mb-8">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Top 3 des activités</p>
                        </div>
                        <div class="relative w-full" style="height: min(280px, 60vw);">
                            @php
                                $actLabels = $activitesStats->pluck('nom')->toJson();
                                $actData = $activitesStats->pluck('total_inscrits')->toJson();
                                $actHoraires = $activitesStats
                                    ->map(function ($a) {
                                        $h = json_decode($a->horaires, true);
                                        return !empty($h) ? array_key_first($h) . ' ' . reset($h) : '';
                                    })
                                    ->toJson();
                            @endphp
                            <canvas id="chartActivites" class="absolute inset-0 w-full h-full"></canvas>
                        </div>
                    </div>
                @else
                    <div class="flex-1 flex flex-col items-center justify-center text-gray-400 py-16 w-full">
                        <div
                            class="w-16 h-16 rounded-full bg-white flex items-center justify-center mb-4
                                    border border-gray-200 shadow-sm">
                            <span class="text-3xl opacity-80">☕</span>
                        </div>
                        <p class="text-sm sm:text-base font-bold text-gray-600 text-center px-6">
                            Vous n'êtes assigné·e à la gestion d'aucune activité.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if ($isGestionnaire && $prochaineSeance && $prochaineSeance->statut !== 'terminee')

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- OVERLAY 1 : APPEL                                              --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <div id="overlay-appel"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4"
         style="display:none!important">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg flex flex-col"
             style="max-height:90vh">

            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between shrink-0">
                <div>
                    <h2 class="font-grotesk font-black text-lg text-gray-900">Appel des adhérents</h2>
                    <p class="text-xs text-gray-500 font-medium mt-0.5">{{ $prochaineSeance->activite_nom }}</p>
                </div>
                <button onclick="closeAppelOverlay()"
                        class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div id="appel-list" class="flex-1 overflow-y-auto px-6 py-4 space-y-2"></div>

            <div class="px-6 py-5 border-t border-gray-100 shrink-0">
                <button onclick="validerAppel()" id="btn-valider-appel"
                        class="w-full bg-[#083325] hover:bg-[#16A37A] text-white font-grotesk font-bold
                               py-3.5 rounded-xl transition-all duration-300 shadow-md hover:shadow-lg text-sm
                               flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    Valider l'appel
                </button>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- OVERLAY 2 : FIN DE L'ACTIVITÉ (liste des enfants)             --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <div id="overlay-fin"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4"
         style="display:none!important">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg flex flex-col"
             style="max-height:90vh">

            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between shrink-0">
                <div>
                    <h2 class="font-grotesk font-black text-lg text-gray-900">Fin de l'activité</h2>
                    <p class="text-xs text-gray-500 font-medium mt-0.5">Confirmez la récupération des enfants</p>
                </div>
                <button onclick="closeFinOverlay()"
                        class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div id="fin-list" class="flex-1 overflow-y-auto px-6 py-4 space-y-2"></div>

            <div class="px-6 py-5 border-t border-gray-100 shrink-0">
                <button id="btn-valider-fin" onclick="validerFin()" disabled
                        class="w-full bg-gray-100 text-gray-400 font-grotesk font-bold
                               py-3.5 rounded-xl cursor-not-allowed text-sm
                               flex items-center justify-center gap-2 transition-all duration-300">
                    Terminer & clôturer la séance
                </button>
            </div>
        </div>
    </div>

    <div id="overlay-enfant"
         class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4 sm:p-6"
         style="display:none!important">

        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl flex flex-col" style="max-height: 95vh;">

            <div class="px-6 py-5 sm:px-8 sm:py-6 border-b border-gray-100 flex items-center justify-between shrink-0">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Récupération</p>
                    <h2 class="font-grotesk font-black text-xl sm:text-2xl text-gray-900" id="enfant-nom-titre">—</h2>
                </div>
                <button onclick="closeEnfantOverlay()"
                        class="w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="px-6 py-5 sm:px-8 sm:py-6 space-y-6 overflow-y-auto flex-1">

                {{-- LISTE DES TUTEURS --}}
                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">
                        👥 Personnes autorisées
                    </label>
                    <div id="enfant-tuteurs-list" class="space-y-3 max-h-60 overflow-y-auto pr-2">
                        </div>
                </div>

                <div class="border-t border-gray-100 pt-6">
                    <label class="flex items-center gap-4 cursor-pointer group bg-gray-50 p-4 sm:p-5 rounded-2xl border border-gray-200 hover:border-gray-300 transition-colors">
                        <div class="relative shrink-0">
                            <input type="checkbox" id="cb-recup" onchange="onEnfantFormChange()"
                                   class="peer w-6 h-6 rounded border-2 border-gray-300 accent-[#083325] cursor-pointer">
                        </div>
                        <span class="text-base sm:text-lg font-semibold text-gray-700 leading-snug group-hover:text-gray-900 transition-colors">
                            Je certifie avoir récupéré mon enfant
                        </span>
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">
                        ✍️ Signature du responsable <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative border-2 border-dashed border-gray-300 rounded-2xl p-2 bg-gray-50 overflow-hidden"
                         style="height:200px">
                        <canvas id="canvas-fin"
                                class="w-full h-full touch-none bg-white rounded-xl cursor-crosshair block border border-gray-100"></canvas>
                        <button type="button" onclick="clearSigFin()"
                                class="absolute top-4 right-4 bg-white border border-gray-200 text-xs font-bold
                                       text-gray-500 hover:text-rose-500 hover:border-rose-200 px-3 py-1.5 rounded-lg
                                       shadow-sm transition-colors">
                            Effacer
                        </button>
                    </div>
                    <p id="sig-warning" class="text-sm text-rose-500 font-medium mt-2 hidden">
                        La signature est requise pour valider.
                    </p>
                </div>
            </div>

            <div class="px-6 py-5 sm:px-8 sm:py-6 border-t border-gray-100 shrink-0">
                <button id="btn-valider-enfant" onclick="validerEnfant()"
                        class="w-full bg-gray-100 text-gray-400 font-grotesk font-bold
                               py-4 rounded-xl cursor-not-allowed text-base transition-all duration-300
                               flex items-center justify-center gap-2" disabled>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    Valider la récupération
                </button>
            </div>
        </div>
    </div>
    @endif

@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const SV_DARK = '#083325';
        const SV_GREEN = '#16A37A';
        const SV_LIGHT = '#34D399';
        const SV_MUTED = '#A7F3D0';

        const modernTooltip = {
            backgroundColor: '#ffffff',
            titleColor: '#111827',
            bodyColor: '#6B7280',
            borderColor: '#F3F4F6',
            borderWidth: 1,
            padding: 12,
            boxPadding: 6,
            usePointStyle: true,
            titleFont: {
                family: 'Space Grotesk, sans-serif',
                size: 13,
                weight: 'bold'
            },
            bodyFont: {
                family: 'sans-serif',
                size: 12,
                weight: '500'
            },
            boxWidth: 8,
            boxHeight: 8,
            cornerRadius: 12,
        };

        const ctxTypes = document.getElementById('chartTypes');
        if (ctxTypes) {
            new Chart(ctxTypes, {
                type: 'doughnut',
                data: {
                    labels: {!! $typeLabels ?? '[]' !!},
                    datasets: [{
                        data: {!! $typeData ?? '[]' !!},
                        backgroundColor: [SV_GREEN, SV_DARK, SV_LIGHT, SV_MUTED],
                        borderWidth: 4,
                        borderColor: '#ffffff',
                        hoverOffset: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    layout: {
                        padding: {
                            top: 8,
                            bottom: 8
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 16,
                                font: {
                                    family: 'Space Grotesk, sans-serif',
                                    size: 11,
                                    weight: 'bold'
                                },
                                color: '#4B5563',
                            }
                        },
                        tooltip: modernTooltip,
                    }
                }
            });
        }

        const ctxAct = document.getElementById('chartActivites');
        if (ctxAct) {
            const labels = {!! $actLabels ?? '[]' !!};
            const data = {!! $actData ?? '[]' !!};
            const horaires = {!! $actHoraires ?? '[]' !!};

            new Chart(ctxAct, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Inscrits',
                        data,
                        backgroundColor: SV_GREEN,
                        borderRadius: 6,
                        barThickness: 24,
                        borderSkipped: false,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            right: 36
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            ...modernTooltip,
                            callbacks: {
                                label: (ctx) => ` ${ctx.parsed.x} inscrits`,
                                afterLabel: (ctx) => horaires[ctx.dataIndex] ?
                                    ` ${horaires[ctx.dataIndex]}` : '',
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: false,
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                color: '#111827',
                                font: {
                                    family: 'Space Grotesk, sans-serif',
                                    size: 12,
                                    weight: 'bold'
                                },
                                padding: 8,
                                callback: function(val) {
                                    const label = this.getLabelForValue(val);
                                    return label.length > 18 ? label.slice(0, 16) + '…' : label;
                                }
                            }
                        }
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeOutQuart'
                    }
                },
                plugins: [{
                    id: 'valuesOnBar',
                    afterDatasetsDraw(chart) {
                        const {
                            ctx,
                            data
                        } = chart;
                        chart.getDatasetMeta(0).data.forEach((bar, i) => {
                            const val = data.datasets[0].data[i];
                            if (!val) return;
                            ctx.save();
                            ctx.fillStyle = '#6B7280';
                            ctx.font = 'bold 12px Space Grotesk, sans-serif';
                            ctx.textAlign = 'left';
                            ctx.textBaseline = 'middle';
                            ctx.fillText(val, bar.x + 8, bar.y);
                            ctx.restore();
                        });
                    }
                }]
            });
        }
    });
</script>

@if ($isGestionnaire && $prochaineSeance && $prochaineSeance->statut !== 'terminee')
<script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/4.1.7/signature_pad.umd.min.js"></script>
<script>
(function () {
    const SEANCE_ID    = {{ $prochaineSeance->id_seance }};
    const CSRF_TOKEN   = '{{ csrf_token() }}';
    const ADHERENTS    = @json($adherentsSeance);
    const ABSENTS_IDS  = @json($absentsSeanceIds ?? []);
    const PRESENTS     = ADHERENTS.filter(a => !ABSENTS_IDS.includes(a.id));

    // ── État ─────────────────────────────────────────────────────────────────
    let presenceState  = {};
    let enfantsState   = {};
    let currentEnfantId = null;
    let sigPad         = null;
    let sigPadInited   = false;

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────
    function showOverlay(id) {
        const el = document.getElementById(id);
        el.style.removeProperty('display');
        el.classList.remove('hidden');
        el.classList.add('flex');
    }
    function hideOverlay(id) {
        const el = document.getElementById(id);
        el.classList.remove('flex');
        el.classList.add('hidden');
        el.style.display = 'none';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // OVERLAY 1 : APPEL
    // ─────────────────────────────────────────────────────────────────────────
    window.openAppelOverlay = function () {
        presenceState = {};
        ADHERENTS.forEach(a => {
            presenceState[a.id] = { statut: 'present', motif: '' };
        });
        renderAppelList();
        showOverlay('overlay-appel');
    };

    window.closeAppelOverlay = function () {
        hideOverlay('overlay-appel');
    };

    function renderAppelList() {
        const container = document.getElementById('appel-list');
        if (!container) return;

        if (ADHERENTS.length === 0) {
            container.innerHTML = `
                <p class="text-center text-gray-400 text-sm py-8 font-medium">
                    Aucun adhérent inscrit à cette activité.
                </p>`;
            return;
        }

        container.innerHTML = ADHERENTS.map(a => {
            const s = presenceState[a.id];
            const isAbsent = s.statut === 'absent';
            return `
            <div class="rounded-xl border ${isAbsent ? 'border-rose-200 bg-rose-50' : 'border-gray-100 bg-white'} p-3 transition-all duration-200">
                <div class="flex items-center justify-between">
                    <span class="font-semibold text-sm text-gray-800">${a.prenom} ${a.nom}</span>
                    <button onclick="togglePresence(${a.id})"
                            class="text-xs font-bold px-3 py-1.5 rounded-lg transition-all duration-200 ${
                                isAbsent
                                ? 'bg-rose-100 text-rose-600 hover:bg-rose-200'
                                : 'bg-teal-50 text-teal-600 hover:bg-teal-100'
                            }">
                        ${isAbsent ? 'Absent' : 'Présent'}
                    </button>
                </div>
                ${isAbsent ? `
                <div class="mt-2">
                    <input type="text"
                           placeholder="Motif d'absence (facultatif)…"
                           value="${escHtml(s.motif)}"
                           oninput="setMotif(${a.id}, this.value)"
                           class="w-full text-xs border border-rose-200 rounded-lg px-3 py-2 bg-white
                                  focus:outline-none focus:ring-2 focus:ring-rose-300 text-gray-700 placeholder-gray-400">
                </div>` : ''}
            </div>`;
        }).join('');
    }

    window.togglePresence = function (id) {
        presenceState[id].statut = presenceState[id].statut === 'present' ? 'absent' : 'present';
        if (presenceState[id].statut === 'present') presenceState[id].motif = '';
        renderAppelList();
    };

    window.setMotif = function (id, value) {
        presenceState[id].motif = value;
    };

    window.validerAppel = async function () {
        const btn = document.getElementById('btn-valider-appel');
        btn.disabled = true;
        btn.textContent = 'Enregistrement…';

        const absents = Object.entries(presenceState)
            .filter(([, s]) => s.statut === 'absent')
            .map(([id, s]) => ({ id_adherent: parseInt(id), motif: s.motif || null }));

        try {
            const res = await fetch(`/seances/${SEANCE_ID}/appel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                },
                body: JSON.stringify({ absents }),
            });
            if (!res.ok) throw new Error('Erreur serveur');
            window.location.reload();
        } catch (e) {
            btn.disabled = false;
            btn.textContent = 'Valider l\'appel';
            alert('Une erreur est survenue, veuillez réessayer.');
        }
    };

    // ─────────────────────────────────────────────────────────────────────────
    // OVERLAY 2 : FIN DE L'ACTIVITÉ
    // ─────────────────────────────────────────────────────────────────────────
    window.openFinOverlay = function () {
        enfantsState = {};
        PRESENTS.forEach(e => { enfantsState[e.id] = { valide: false }; });
        renderFinList();
        showOverlay('overlay-fin');
    };

    window.closeFinOverlay = function () {
        hideOverlay('overlay-fin');
    };

    function renderFinList() {
        const container = document.getElementById('fin-list');
        if (!container) return;

        if (PRESENTS.length === 0) {
            container.innerHTML = `
                <p class="text-center text-gray-400 text-sm py-8 font-medium">
                    Aucun enfant avec tuteur dans cette activité.
                </p>`;
        } else {
            container.innerHTML = PRESENTS.map(e => {
                const done = enfantsState[e.id]?.valide;
                return `
                <div onclick="${done ? '' : `ouvrirEnfantOverlay(${e.id})`}"
                     class="flex items-center justify-between p-4 rounded-xl border transition-all duration-200 ${
                         done
                         ? 'bg-teal-50 border-teal-200 cursor-default'
                         : 'bg-white border-gray-100 hover:border-gray-300 hover:shadow-sm cursor-pointer'
                     }">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-black ${
                            done ? 'bg-teal-100 text-teal-600' : 'bg-gray-100 text-gray-500'
                        }">
                            ${e.prenom.charAt(0)}${e.nom.charAt(0)}
                        </div>
                        <span class="font-semibold text-sm text-gray-800">${e.prenom} ${e.nom}</span>
                    </div>
                    ${done
                        ? `<span class="w-6 h-6 rounded-full bg-teal-500 flex items-center justify-center">
                               <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                               </svg>
                           </span>`
                        : `<svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                           </svg>`
                    }
                </div>`;
            }).join('');
        }

        const allDone = PRESENTS.length === 0 || PRESENTS.every(e => enfantsState[e.id]?.valide);
        const btn = document.getElementById('btn-valider-fin');
        if (allDone) {
            btn.disabled = false;
            btn.className = 'w-full bg-[#222A60] hover:bg-[#2d3a8c] text-white font-grotesk font-bold py-3.5 rounded-xl transition-all duration-300 shadow-md hover:shadow-lg text-sm flex items-center justify-center gap-2';
            btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg> Terminer & clôturer la séance`;
        } else {
            btn.disabled = true;
            btn.className = 'w-full bg-gray-100 text-gray-400 font-grotesk font-bold py-3.5 rounded-xl cursor-not-allowed text-sm flex items-center justify-center gap-2 transition-all duration-300';
            const remaining = PRESENTS.filter(e => !enfantsState[e.id]?.valide).length;
            btn.textContent = `${remaining} enfant${remaining > 1 ? 's' : ''} restant${remaining > 1 ? 's' : ''}`;
        }
    }

    window.validerFin = async function () {
        const btn = document.getElementById('btn-valider-fin');
        btn.disabled = true;
        btn.textContent = 'Clôture en cours…';

        try {
            const res = await fetch(`/seances/${SEANCE_ID}/terminer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                },
                body: JSON.stringify({}),
            });
            if (!res.ok) throw new Error('Erreur serveur');
            window.location.reload();
        } catch (e) {
            btn.disabled = false;
            btn.textContent = 'Terminer & clôturer la séance';
            alert('Une erreur est survenue, veuillez réessayer.');
        }
    };

    // ─────────────────────────────────────────────────────────────────────────
    // OVERLAY 3 : CONFIRMATION ENFANT
    // ─────────────────────────────────────────────────────────────────────────
    window.ouvrirEnfantOverlay = function (id) {
        currentEnfantId = id;
        const enfant = PRESENTS.find(e => e.id === id);
        document.getElementById('enfant-nom-titre').textContent = `${enfant.prenom} ${enfant.nom}`;

        // -- AFFICHAGE DES TUTEURS --
        const tuteursContainer = document.getElementById('enfant-tuteurs-list');
        if(enfant.tous_les_tuteurs && enfant.tous_les_tuteurs.length > 0) {
            tuteursContainer.innerHTML = enfant.tous_les_tuteurs.map(t => {
                let badge = '';
                let bgClass = '';
                let icon = '';

                if(t.type === 'parent_tuteur') {
                    badge = 'Parent / Tuteur';
                    bgClass = 'bg-slate-50 border-slate-200 text-slate-700';
                    icon = '👨‍👩‍👧';
                } else if(t.type === 'autre_autorise') {
                    badge = 'Autorisé(e)';
                    bgClass = 'bg-teal-50 border-teal-200 text-teal-800';
                    icon = '✅';
                } else {
                    badge = 'Non autorisé(e)';
                    bgClass = 'bg-rose-50 border-rose-200 text-rose-800';
                    icon = '🚫';
                }

                const nomComplet = t.nom_complet || `${t.prenom} ${t.nom}`;

                return `
                <div class="flex items-center justify-between p-2.5 rounded-xl border ${bgClass}">
                    <div class="flex items-center gap-3">
                        <span class="text-xl bg-white/60 w-8 h-8 rounded-lg flex items-center justify-center shadow-sm border border-black/5">${icon}</span>
                        <div>
                            <p class="text-sm font-bold leading-tight">${nomComplet}</p>
                        </div>
                    </div>
                    <span class="text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded border border-black/5 bg-white/60 shadow-sm">${badge}</span>
                </div>
                `;
            }).join('');
        } else {
            tuteursContainer.innerHTML = '<div class="p-3 bg-gray-50 rounded-xl border border-gray-100 text-xs text-gray-500 font-medium text-center">Aucun responsable légal renseigné pour cet enfant.</div>';
        }

        document.getElementById('cb-recup').checked = false;
        document.getElementById('sig-warning').classList.add('hidden');
        resetBtnEnfant();

        showOverlay('overlay-enfant');

        requestAnimationFrame(() => initSigPad());
    };

    window.closeEnfantOverlay = function () {
        hideOverlay('overlay-enfant');
        currentEnfantId = null;
    };

    function initSigPad() {
        const canvas = document.getElementById('canvas-fin');
        if (!canvas) return;

        if (!sigPadInited) {
            sigPad = new SignaturePad(canvas, {
                penColor: '#0f172a',
                backgroundColor: 'rgba(255,255,255,1)',
            });
            sigPad.addEventListener('endStroke', onEnfantFormChange);
            sigPadInited = true;
        }

        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const data = sigPad.toData();
        canvas.width  = canvas.offsetWidth  * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        sigPad.clear();
        if (data.length) sigPad.fromData(data);
        sigPad.clear();
    }

    window.clearSigFin = function () {
        if (sigPad) sigPad.clear();
        onEnfantFormChange();
    };

    window.onEnfantFormChange = function () {
        const checked = document.getElementById('cb-recup').checked;
        const signed  = sigPad && !sigPad.isEmpty();
        const btn = document.getElementById('btn-valider-enfant');

        if (checked && signed) {
            btn.disabled = false;
            btn.className = 'w-full bg-[#083325] hover:bg-[#16A37A] text-white font-grotesk font-bold py-3.5 rounded-xl transition-all duration-300 shadow-md hover:shadow-lg text-sm flex items-center justify-center gap-2';
            btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg> Valider`;
        } else {
            resetBtnEnfant();
        }
    };

    function resetBtnEnfant() {
        const btn = document.getElementById('btn-valider-enfant');
        btn.disabled = true;
        btn.className = 'w-full bg-gray-100 text-gray-400 font-grotesk font-bold py-3.5 rounded-xl cursor-not-allowed text-sm flex items-center justify-center gap-2 transition-all duration-300';
        btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
        </svg> Valider`;
    }

    window.validerEnfant = function () {
        const checked = document.getElementById('cb-recup').checked;
        const signed  = sigPad && !sigPad.isEmpty();

        if (!signed) {
            document.getElementById('sig-warning').classList.remove('hidden');
            return;
        }
        if (!checked) return;

        enfantsState[currentEnfantId].valide = true;
        hideOverlay('overlay-enfant');
        currentEnfantId = null;
        renderFinList();
    };

    // ─────────────────────────────────────────────────────────────────────────
    // UTILITAIRE
    // ─────────────────────────────────────────────────────────────────────────
    function escHtml(str) {
        return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    ['overlay-appel','overlay-fin','overlay-enfant'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('click', function (e) {
            if (e.target === el) {
                if (id === 'overlay-enfant') closeEnfantOverlay();
                else if (id === 'overlay-appel') closeAppelOverlay();
                else closeFinOverlay();
            }
        });
    });
})();
</script>
@endif
