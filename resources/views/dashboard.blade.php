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

        @if(!empty($isRoleRestreint) && $isGestionnaire)
        <div class="flex flex-col flex-1 min-h-0">
            <div class="bg-gradient-to-r from-[#083325] to-[#0f4a35] rounded-2xl px-6 sm:px-8 py-5 sm:py-6 mb-4 flex items-center gap-4 shrink-0">
                <div class="w-12 h-12 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-[#16A37A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <h1 class="font-grotesk font-black text-xl sm:text-2xl text-white tracking-tight">Carnet de bord</h1>
                    <p class="text-sm text-white/60 mt-0.5">Gérez votre prochaine intervention sur le terrain</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_24px_rgba(0,0,0,0.04)] overflow-hidden flex flex-col flex-1 min-h-0">
        @else
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden flex flex-col flex-1 min-h-0">

            <div
                class="px-5 sm:px-8 py-5 sm:py-6 border-b border-gray-100 bg-white flex items-center justify-between shrink-0">
                <div>
                    <h3 class="font-grotesk font-black text-lg sm:text-xl text-gray-900 tracking-tight flex items-center gap-2 sm:gap-3">
                        @if ($isGestionnaire)
                            <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center text-sm sm:text-base">📋</span>
                            Carnet de bord
                        @else
                            <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm sm:text-base">📊</span>
                            Analyse des effectifs
                        @endif
                    </h3>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium mt-1 pl-9 sm:pl-11">
                        @if ($isGestionnaire)
                            Gérez votre prochaine intervention sur le terrain
                        @else
                            Répartition des inscriptions et palmarès des activités
                        @endif
                    </p>
                </div>

                @if(Auth::user()->role === 'admin' || Auth::user()->role === 'comptable')
                <button onclick="document.getElementById('overlay-mail-adherents').style.display='flex';"
                        class="bg-[#16A37A] hover:bg-[#0f7a5b] text-white px-4 py-2 sm:px-5 sm:py-2.5 rounded-xl font-bold text-sm transition-all shadow-md hover:shadow-lg flex items-center gap-2 shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span class="hidden sm:inline">Envoyer un mail</span>
                </button>
                @endif
                </div>
        @endif

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
                @elseif (Auth::user()->role === 'admin' || Auth::user()->role === 'comptable')
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
                    <div class="flex-1 p-5 sm:p-8 flex flex-col gap-6">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-5">Répartition des inscriptions</p>
                            @if($repartitionTypes->isNotEmpty())
                                <div class="space-y-3">
                                    @php
                                        $totalInsc = $repartitionTypes->sum('total') ?: 1;
                                        $insc_colors = ['bg-[#16A37A]', 'bg-[#222A60]', 'bg-amber-400', 'bg-rose-400', 'bg-violet-400'];
                                    @endphp
                                    @foreach($repartitionTypes->sortByDesc('total') as $i => $type)
                                        @php $pct = round(($type->total / $totalInsc) * 100); @endphp
                                        <div>
                                            <div class="flex items-center justify-between text-xs mb-1.5">
                                                <span class="font-bold text-gray-700 capitalize">{{ $type->type_adhesion ?? 'Autre' }}</span>
                                                <span class="font-black text-gray-900">{{ $type->total }} <span class="text-gray-400 font-normal">({{ $pct }}%)</span></span>
                                            </div>
                                            <div class="h-2.5 w-full bg-gray-100 rounded-full overflow-hidden">
                                                <div class="h-full rounded-full transition-all duration-500 {{ $insc_colors[$loop->index % count($insc_colors)] }}"
                                                     style="width: {{ $pct }}%"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-400">Aucune donnée disponible pour cette saison.</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @if(!empty($isRoleRestreint) && $isGestionnaire)
            </div>
        </div>
        @else
        </div>
        @endif
    </div>

    @if ($isGestionnaire && $prochaineSeance && $prochaineSeance->statut !== 'terminee')
        @include('partials.carnet-bord-overlays')
    @endif

    @if(Auth::user()->role === 'admin' || Auth::user()->role === 'comptable')
        @include('partials.mail-adherents-overlay')
    @endif

@endsection

@if(empty($isRoleRestreint) || !$isGestionnaire)
<script>
    window.dashboardGraphData = {
        typeLabels: {!! $typeLabels ?? '[]' !!},
        typeData: {!! $typeData ?? '[]' !!},
        actLabels: {!! $actLabels ?? '[]' !!},
        actData: {!! $actData ?? '[]' !!},
        actHoraires: {!! $actHoraires ?? '[]' !!}
    };
</script>
@endif

@if ($isGestionnaire && $prochaineSeance && $prochaineSeance->statut !== 'terminee')
<script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/4.1.7/signature_pad.umd.min.js"></script>
<script>
    window.carnetBordData = {
        seanceId: {{ $prochaineSeance->id_seance }},
        csrfToken: '{{ csrf_token() }}',
        adherents: @json($adherentsSeance),
        absentsIds: @json($absentsSeanceIds ?? [])
    };
</script>
@endif
