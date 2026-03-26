@extends('layouts.app')

@section('title', $activite->nom)

@section('content')

    <div class="flex items-center justify-between mb-0 px-8 py-4 border-b border-gray-100 -mt-8 -mx-8">
        <div class="flex items-center gap-2 text-xs text-gray-400 font-semibold">
            <a href="{{ route('activites.index') }}"
                class="font-bold text-[#222A60] hover:text-[#16987C] transition-colors">Activités</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-600">{{ $activite->nom }}</span>
        </div>
    </div>

    <div class="relative overflow-hidden -mx-8"
        style="background: linear-gradient(140deg, #062b1e 0%, #083325 45%, #121a3d 100%);">

        <div class="absolute -top-20 -right-10 w-96 h-96 rounded-full pointer-events-none"
            style="background: radial-gradient(circle, rgba(22,163,122,.1) 0%, transparent 65%);"></div>
        <div class="absolute -bottom-16 left-1/3 w-72 h-72 rounded-full pointer-events-none"
            style="background: radial-gradient(circle, rgba(34,42,96,.25) 0%, transparent 65%);"></div>

        <div class="absolute inset-0 pointer-events-none opacity-[.06]"
            style="background-image: radial-gradient(circle, rgba(255,255,255,.8) 1px, transparent 1px); background-size: 24px 24px;">
        </div>

        <div class="relative z-10 px-8 pt-10 pb-0">

            <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6 mb-8">

                <div class="flex items-start gap-5">
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center shrink-0 border"
                        style="background: rgba(255,255,255,.07); border-color: rgba(255,255,255,.12);">
                        <svg class="w-7 h-7 text-[#16A37A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if ($activite->est_stage)
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            @endif
                        </svg>
                    </div>

                    <div>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest mb-3 border
                        {{ $activite->est_stage ? 'border-amber-400/30 text-amber-300' : 'border-[#16A37A]/30 text-emerald-300' }}"
                            style="{{ $activite->est_stage ? 'background: rgba(251,191,36,.15);' : 'background: rgba(22,163,122,.18);' }}">
                            <span
                                class="w-1.5 h-1.5 rounded-full {{ $activite->est_stage ? 'bg-amber-300' : 'bg-emerald-400 animate-pulse' }}"></span>
                            {{ $activite->est_stage ? 'Stage' : 'En cours' }}
                        </div>

                        <h1 class="font-grotesk text-3xl sm:text-4xl font-black text-white tracking-tight mb-3"
                            style="letter-spacing: -.03em;">
                            {{ $activite->nom }}
                        </h1>

                        <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                            @if (!empty($activite->horaires_list))
                                <div class="flex items-center gap-1.5 text-sm font-semibold"
                                    style="color: rgba(255,255,255,.5);">
                                    <svg class="w-3.5 h-3.5" style="color: rgba(255,255,255,.3);" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ implode(' · ', $activite->horaires_list) }}
                                </div>
                            @endif
                            @if ($activite->adresse || $activite->ville)
                                <div class="flex items-center gap-1.5 text-sm font-semibold"
                                    style="color: rgba(255,255,255,.5);">
                                    <svg class="w-3.5 h-3.5" style="color: rgba(255,255,255,.3);" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ $activite->adresse }} {{ $activite->ville }}
                                </div>
                            @endif
                        </div>

                        @if(!empty($activite->classes_list))
                            <div class="flex flex-wrap gap-1.5 mt-3">
                                @foreach($activite->classes_list as $classe)
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-bold"
                                        style="background: rgba(99,102,241,.25); color: rgba(199,210,254,.9);">
                                        {{ $classe }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-5 shrink-0">
                    <div class="hidden sm:block text-right">
                        <p class="text-[10px] font-bold uppercase tracking-widest mb-1"
                            style="color: rgba(255,255,255,.32);">Responsable</p>
                        <p class="text-sm font-black text-white">
                            {{ $activite->gestionnaires->first()?->name ?? 'Non assigné' }}</p>
                    </div>
                    <a href="{{ route('activites.edit', $activite) }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white transition-all border"
                        style="background: rgba(255,255,255,.09); border-color: rgba(255,255,255,.14);"
                        onmouseover="this.style.background='rgba(255,255,255,.16)'"
                        onmouseout="this.style.background='rgba(255,255,255,.09)'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        Modifier
                    </a>
                </div>
            </div>

            <div class="flex gap-1">
                <button onclick="switchTab('presences')" id="tab-presences"
                    class="tab-hero-btn active px-6 py-3 rounded-t-xl text-sm font-bold border-none cursor-pointer transition-all">
                    Faire l'appel
                </button>
                <button onclick="switchTab('adherents')" id="tab-adherents"
                    class="tab-hero-btn px-6 py-3 rounded-t-xl text-sm font-bold border-none cursor-pointer transition-all">
                    Adhérents
                </button>
                <button onclick="switchTab('statistiques')" id="tab-statistiques"
                    class="tab-hero-btn px-6 py-3 rounded-t-xl text-sm font-bold border-none cursor-pointer transition-all">
                    Statistiques
                </button>
            </div>
        </div>
    </div>

    <div class="mt-7"></div>

    <div id="content-presences" class="space-y-3 animate-fade-in">

        <div class="section-label">Historique des séances</div>

        @forelse($seances as $seance)
            @php
                $total = $adherentsStats->count();
                $nbAbsents = $seance->presences->count();
                $nbPresents = max(0, $total - $nbAbsents);
                $pct = $total > 0 ? round(($nbPresents / $total) * 100) : 0;
            @endphp
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-[0_1px_4px_rgba(0,0,0,.04)] transition-shadow hover:shadow-[0_4px_16px_rgba(0,0,0,.07)]">

                <button onclick="toggleSeance({{ $seance->id_seance }})"
                    class="w-full px-6 py-[18px] flex items-center justify-between hover:bg-gray-50/80 transition-colors group">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-[52px] h-[52px] rounded-[14px] bg-gray-50 border border-gray-100 flex flex-col items-center justify-center shrink-0">
                            <span
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-wide leading-none">{{ $seance->date->isoFormat('MMM') }}</span>
                            <span
                                class="text-[22px] font-black text-[#0F143A] leading-tight">{{ $seance->date->isoFormat('DD') }}</span>
                        </div>
                        <div>
                            <p class="text-base font-black text-[#0F143A] capitalize">
                                {{ $seance->date->isoFormat('dddd') }}</p>
                            <div class="mt-1.5 w-22 h-[5px] bg-gray-100 rounded-full overflow-hidden" style="width:88px;">
                                <div class="h-full rounded-full transition-all duration-500 {{ $pct >= 75 ? 'bg-[#16987C]' : ($pct >= 50 ? 'bg-amber-400' : 'bg-rose-400') }}"
                                    style="width: {{ $pct }}%;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-5">
                        <div class="flex items-baseline gap-1 font-mono">
                            <span
                                class="text-[22px] font-bold {{ $pct >= 75 ? 'text-[#16987C]' : ($pct >= 50 ? 'text-amber-500' : 'text-rose-500') }}">
                                {{ $nbPresents }}
                            </span>
                            <span class="text-xs font-semibold text-gray-400">/ {{ $total }} présents</span>
                        </div>
                        <div id="chev-{{ $seance->id_seance }}"
                            class="w-8 h-8 rounded-full bg-gray-50 border border-gray-100 flex items-center justify-center transition-all duration-300 group-hover:bg-gray-100">
                            <svg class="w-[15px] h-[15px] text-gray-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </button>

                <div id="content-seance-{{ $seance->id_seance }}" class="hidden border-t border-gray-100">
                    <form
                        action="{{ route('activites.presences.store', ['activite' => $activite->id, 'seance' => $seance->id_seance]) }}"
                        method="POST">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 p-4">
                            @foreach ($adherentsStats as $adherent)
                                @php
                                    $presence = $seance->presences->where('id_adherent', $adherent->id)->first();
                                    $estAbsent = $presence !== null;
                                @endphp

                                <div class="group flex items-center gap-3.5 p-3 rounded-2xl hover:bg-gray-50/80 border border-transparent hover:border-gray-100 transition-all"
                                    x-data="{ isAbsent: {{ $estAbsent ? 'true' : 'false' }} }">

                                    <div class="w-10 h-10 rounded-[14px] flex items-center justify-center text-white text-sm font-black shrink-0 shadow-sm"
                                        style="background-color: {{ $adherent->couleur_avatar }}">
                                        {{ $adherent->initiales }}
                                    </div>

                                    <div class="flex flex-col min-w-0">
                                        <span class="text-sm font-bold text-[#0F143A] truncate leading-tight">
                                            {{ $adherent->nom_complet }}
                                        </span>
                                        <span
                                            class="text-[10px] font-black uppercase tracking-widest mt-0.5 transition-colors"
                                            :class="isAbsent ? 'text-rose-500' : 'text-[#16987C]'"
                                            x-text="isAbsent ? 'Absent' : 'Présent'">
                                        </span>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center py-16 bg-white rounded-2xl border border-gray-100">
                <div class="w-14 h-14 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                    <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <p class="text-sm font-bold text-gray-400">Aucune séance passée pour le moment.</p>
            </div>
        @endforelse
    </div>

    <div id="content-adherents" class="hidden animate-fade-in">

        <div class="section-label">{{ $adherentsStats->count() }} adhérents actifs</div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
            @foreach ($adherentsStats as $adherent)
                @php
                    $rateClass = match (true) {
                        $adherent->taux_presence >= 75 => 'bg-emerald-50 text-emerald-700',
                        $adherent->taux_presence >= 50 => 'bg-amber-50 text-amber-700',
                        default => 'bg-rose-50 text-rose-600',
                    };
                @endphp
                <div
                    class="group relative bg-white rounded-2xl border border-gray-200 shadow-[0_1px_4px_rgba(0,0,0,.04)] overflow-hidden transition-all hover:shadow-[0_6px_20px_rgba(0,0,0,.07)] hover:-translate-y-0.5">
                    <div class="p-5 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-[14px] flex items-center justify-center text-white text-sm font-black shrink-0"
                            style="background-color: {{ $adherent->couleur_avatar }}">
                            {{ $adherent->initiales }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('adherents.show', $adherent) }}"
                                class="block font-black text-base text-[#0F143A] hover:text-[#16987C] transition-colors truncate mb-1.5">
                                {{ $adherent->nom_complet }}
                            </a>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-black {{ $rateClass }}">
                                {{ $adherent->taux_presence }}% d'assiduité
                            </span>
                        </div>

                        {{-- Bouton d'abandon : visible par défaut sur mobile/tablette, caché sur PC sauf au survol --}}
                        <button type="button" onclick="toggleAbandonForm({{ $adherent->id }})"
                            class="opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity p-2 rounded-xl text-gray-400 lg:text-gray-300 hover:text-rose-500 hover:bg-rose-50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6" />
                            </svg>
                        </button>
                    </div>

                    <div id="abandon-form-{{ $adherent->id }}" class="hidden bg-rose-50/50 border-t border-rose-100 p-4">
                        <form
                            action="{{ route('activites.abandonner', ['activite' => $activite->id, 'adherent' => $adherent->id]) }}"
                            method="POST" class="flex flex-col gap-3">
                            @csrf
                            <div
                                class="flex items-center gap-2 text-rose-500 text-[10px] font-black uppercase tracking-widest">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                Déclarer un abandon
                            </div>
                            <input type="text" name="motif_sortie" required
                                placeholder="Motif (santé, déménagement...)"
                                class="text-sm px-3 py-2 border border-rose-200 rounded-lg bg-white text-gray-700 focus:outline-none focus:border-rose-400 focus:ring-1 focus:ring-rose-300 shadow-sm">
                            <div class="flex justify-end gap-2">
                                <button type="button" onclick="toggleAbandonForm({{ $adherent->id }})"
                                    class="px-3 py-1.5 text-xs font-bold text-gray-500 hover:text-gray-700">Annuler</button>
                                <button type="submit"
                                    class="px-4 py-1.5 bg-rose-500 hover:bg-rose-600 text-white text-xs font-bold rounded-lg transition-colors shadow-sm">
                                    Confirmer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div id="content-statistiques" class="hidden space-y-5 animate-fade-in">

        <div class="section-label">Vue d'ensemble</div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative rounded-[18px] p-6 overflow-hidden border"
                style="background: linear-gradient(135deg,#f0fdf4,#dcfce7); border-color:#bbf7d0;">
                <div class="absolute top-4 right-4 w-11 h-11 rounded-[13px] flex items-center justify-center"
                    style="background: rgba(5,150,105,.1);">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                </div>
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-500 mb-2">Présence moyenne</p>
                <p class="font-mono text-5xl font-bold text-emerald-900 mb-1">{{ $tauxMoyen }}%</p>
                <p class="text-xs font-semibold text-gray-500">calculé sur {{ $nbSeancesPassees }} séances</p>
            </div>

            <div class="relative rounded-[18px] p-6 overflow-hidden border"
                style="background: linear-gradient(135deg,#eff6ff,#dbeafe); border-color:#bfdbfe;">
                <div class="absolute top-4 right-4 w-11 h-11 rounded-[13px] flex items-center justify-center"
                    style="background: rgba(59,130,246,.1);">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </div>
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-500 mb-2">Fidélisation</p>
                <p class="font-mono text-5xl font-bold text-blue-900 mb-1">{{ $tauxReconduction }}%</p>
                <p class="text-xs font-semibold text-gray-500">
                    @if ($saisonPrecedente)
                        {{ $nbReconduits }} réinscrit(s) depuis {{ $saisonPrecedente }}
                    @else
                        1ère saison (pas d'historique)
                    @endif
                </p>
            </div>

            <div class="relative rounded-[18px] p-6 overflow-hidden border"
                style="background: linear-gradient(135deg,#fff1f2,#ffe4e6); border-color:#fecdd3;">
                <div class="absolute top-4 right-4 w-11 h-11 rounded-[13px] flex items-center justify-center"
                    style="background: rgba(244,63,94,.1);">
                    <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                    </svg>
                </div>
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-500 mb-2">Taux d'abandon</p>
                <p class="font-mono text-5xl font-bold text-rose-900 mb-1">{{ $tauxAbandon }}%</p>
                <p class="text-xs font-semibold text-gray-500">{{ $nbAbandons }} abandon(s) cette saison</p>
            </div>
        </div>

        <div class="bg-white rounded-[18px] border border-gray-200 p-7 shadow-[0_1px_4px_rgba(0,0,0,.04)]">
            <p class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-7">Évolution des présences</p>

            @if ($graphiqueSeances->count() > 0)
                <div class="flex items-end gap-2 h-44 pt-6">
                    @foreach ($graphiqueSeances as $stat)
                        <div
                            class="flex-1 flex flex-col justify-end items-center gap-2 group relative cursor-pointer h-full">
                            <div
                                class="opacity-0 group-hover:opacity-100 transition-opacity absolute -top-10 bg-[#0F143A] text-white text-[10px] py-1.5 px-3 rounded-lg font-bold whitespace-nowrap z-10 pointer-events-none shadow-lg">
                                {{ $stat['presents'] }} / {{ $stat['total'] }}
                            </div>
                            @if ($stat['pourcentage'] > 0)
                                <span class="text-[11px] font-black text-[#0F143A]">{{ $stat['presents'] }}</span>
                                <div class="w-full max-w-[36px] rounded-t-lg transition-all duration-500 group-hover:opacity-75 shadow-[0_4px_12px_rgba(22,152,124,.18)]"
                                    style="height: {{ $stat['pourcentage'] }}%;
                                       background: linear-gradient(180deg, {{ $stat['pourcentage'] < 60 ? '#f59e0b, #d97706' : '#16987C, #0d7a63' }});">
                                </div>
                            @else
                                <span class="text-[11px] font-bold text-gray-300">0</span>
                                <div class="w-full max-w-[36px] bg-gray-100 rounded-t-lg" style="height:4px;"></div>
                            @endif
                            <span class="text-[10px] font-bold text-gray-400">{{ $stat['date'] }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="h-44 flex items-center justify-center text-sm font-semibold text-gray-400">
                    Pas assez de données pour afficher le graphique.
                </div>
            @endif
        </div>
    </div>
    <script>
        function switchTab(tabName) {
            ['presences', 'adherents', 'statistiques'].forEach(name => {
                document.getElementById('content-' + name).classList.add('hidden');
                const btn = document.getElementById('tab-' + name);
                btn.classList.remove('active');
            });
            document.getElementById('content-' + tabName).classList.remove('hidden');
            document.getElementById('tab-' + tabName).classList.add('active');
        }

        function toggleSeance(id) {
            const content = document.getElementById('content-seance-' + id);
            const chev = document.getElementById('chev-' + id);
            const isOpen = !content.classList.contains('hidden');
            content.classList.toggle('hidden');
            chev.style.transform = isOpen ? '' : 'rotate(180deg)';
        }

        function toggleRaison(checkbox, raisonId) {
            const raisonInput = document.getElementById(raisonId);
            const label = document.getElementById(raisonId.replace('raison-', 'label-'));
            if (checkbox.checked) {
                raisonInput.classList.add('hidden');
                raisonInput.value = '';
                label.textContent = 'Présent';
                label.className = 'ml-3 text-xs font-bold text-[#16987C] w-14 text-center';
            } else {
                raisonInput.classList.remove('hidden');
                raisonInput.focus();
                label.textContent = 'Absent';
                label.className = 'ml-3 text-xs font-bold text-rose-500 w-14 text-center';
            }
        }

        function toggleAbandonForm(adherentId) {
            document.getElementById('abandon-form-' + adherentId).classList.toggle('hidden');
        }
    </script>

@endsection
