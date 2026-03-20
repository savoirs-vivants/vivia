@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
    <div class="grid grid-cols-12 gap-6 pb-10">

        {{-- ── CARTE 1 : HÉRO (Total Adhérents) ──── --}}
        <div class="col-span-4 relative group">
            {{-- Effet d'ombre colorée au survol pour donner du volume --}}
            <div class="absolute inset-0 bg-sv-blue/20 rounded-3xl blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

            <div class="bg-gradient-to-br from-sv-blue to-[#111536] rounded-3xl p-8 relative overflow-hidden shadow-xl shadow-sv-blue/10 h-full border border-white/10 transition-transform duration-300 group-hover:-translate-y-1">
                {{-- Texture géométrique discrète en fond --}}
                <svg class="absolute top-0 right-0 w-64 h-64 text-white/5 transform translate-x-1/3 -translate-y-1/4 pointer-events-none" viewBox="0 0 100 100" fill="currentColor">
                    <circle cx="50" cy="50" r="40" />
                    <circle cx="50" cy="50" r="20" fill="none" stroke="currentColor" stroke-width="2" />
                </svg>
                <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-sv-green/20 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col h-full justify-between">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 text-white/80">
                            <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center backdrop-blur-md shadow-inner border border-white/5">
                                <svg class="w-6 h-6 text-sv-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <span class="font-grotesk font-bold tracking-wide text-sm">Adhérents Total</span>
                        </div>
                        <span class="flex h-3 w-3 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sv-green opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-sv-green"></span>
                        </span>
                    </div>

                    <div class="mt-10">
                        <div class="flex items-baseline gap-2">
                            <p class="font-grotesk font-black text-7xl text-white tracking-tighter leading-none">{{ $totalAdherents }}</p>
                        </div>
                        @if ($newThisMonth > 0)
                            <div class="inline-flex items-center gap-2 mt-5 bg-white/10 backdrop-blur-md border border-white/10 text-sv-green px-4 py-2 rounded-xl text-sm font-bold shadow-sm">
                                <div class="w-5 h-5 rounded-full bg-sv-green/20 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-sv-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
                                </div>
                                +{{ $newThisMonth }} nouvelles inscriptions
                            </div>
                        @else
                            <div class="h-9 mt-5"></div> {{-- Spacer pour alignement --}}
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ── CARTE 2 : FINANCES ──── --}}
        <div class="col-span-4 relative group">
            <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 flex flex-col justify-between h-full transition-all duration-300 group-hover:border-gray-200 group-hover:shadow-md relative overflow-hidden">
                {{-- Icône géante en filigrane --}}
                <svg class="absolute -right-8 -bottom-8 w-40 h-40 text-gray-50 opacity-50 pointer-events-none transform -rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>

                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <p class="font-grotesk font-bold text-gray-400 uppercase text-[10px] tracking-[0.2em] mb-1">Finances</p>
                        <h3 class="font-grotesk font-bold text-gray-900 text-lg">Trésorerie encaissée</h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center border border-blue-100/50 shadow-inner group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                </div>

                <div class="relative z-10 mt-8">
                    <div class="flex items-baseline gap-1">
                        <p class="font-grotesk font-black text-5xl text-gray-900 tracking-tight leading-none">{{ number_format($totalCotisations, 0, ',', ' ') }}</p>
                        <span class="text-3xl text-gray-300 font-bold">€</span>
                    </div>

                    @if ($totalEnAttente > 0)
                        <div class="mt-6 flex items-center gap-3 bg-amber-50/80 px-4 py-3 rounded-2xl border border-amber-100/50">
                            <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-sm shrink-0">
                                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-amber-600 uppercase tracking-widest">En attente de paiement</p>
                                <p class="text-sm font-black text-amber-900">{{ number_format($totalEnAttente, 0, ',', ' ') }} €</p>
                            </div>
                        </div>
                    @else
                        <div class="mt-6 flex items-center gap-2 text-xs font-bold text-sv-green bg-sv-green/5 px-3 py-2 rounded-lg w-fit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Toutes cotisations à jour
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── CARTE 3 : STATUTS (Donut) ──── --}}
        <div class="col-span-4 bg-white rounded-3xl p-8 shadow-sm border border-gray-100 flex flex-col transition-all duration-300 hover:border-gray-200">
            <div class="mb-6">
                <p class="font-grotesk font-bold text-gray-400 uppercase text-[10px] tracking-[0.2em] mb-1">Suivi</p>
                <h3 class="font-grotesk font-bold text-gray-900 text-lg">État des dossiers</h3>
            </div>

            <div class="flex items-center gap-8 flex-1">
                @php $total = $statutPaye + $statutAttente ?: 1; @endphp
                <div class="relative w-32 h-32 shrink-0 drop-shadow-sm">
                    @php
                        $r = 50; $cx = 64; $cy = 64; $circ = 2 * M_PI * $r;
                        $pPaye = ($statutPaye / $total) * $circ;
                        $pAttente = ($statutAttente / $total) * $circ;
                    @endphp
                    <svg viewBox="0 0 128 128" class="w-full h-full -rotate-90">
                        {{-- Background circle --}}
                        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="none" stroke="#f8fafc" stroke-width="16" />
                        {{-- Payé (Vert) --}}
                        @if ($statutPaye > 0)
                            <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="none"
                                stroke="#16A37A" stroke-width="16" stroke-dasharray="{{ $pPaye }} {{ $circ - $pPaye }}"
                                stroke-dashoffset="{{ $circ }}" stroke-linecap="round" class="transition-all duration-1000 ease-out"/>
                        @endif
                        {{-- En attente (Ambre) --}}
                        @if ($statutAttente > 0)
                            <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="none"
                                stroke="#FBBF24" stroke-width="16"
                                stroke-dasharray="{{ $pAttente }} {{ $circ - $pAttente }}"
                                stroke-dashoffset="{{ $circ - $pPaye }}" stroke-linecap="round" class="transition-all duration-1000 ease-out"/>
                        @endif
                    </svg>
                    {{-- Centre du Donut --}}
                    <div class="absolute inset-0 flex flex-col items-center justify-center bg-white rounded-full m-4 shadow-inner border border-gray-50">
                        <span class="font-grotesk font-black text-2xl text-sv-blue leading-none">{{ $totalAdherents }}</span>
                    </div>
                </div>

                {{-- Légende --}}
                <div class="space-y-4 w-full">
                    <div class="group cursor-default">
                        <div class="flex items-center justify-between text-sm mb-1">
                            <div class="flex items-center gap-2 font-bold text-gray-600">
                                <span class="w-3 h-3 rounded-full bg-sv-green shadow-sm shadow-sv-green/50"></span> Payés
                            </div>
                            <span class="font-black text-gray-900">{{ $statutPaye }}</span>
                        </div>
                        <div class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-sv-green rounded-full transform origin-left transition-transform duration-500 scale-x-100 group-hover:scale-x-105" style="width: {{ $totalAdherents > 0 ? ($statutPaye / $totalAdherents) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    <div class="group cursor-default">
                        <div class="flex items-center justify-between text-sm mb-1">
                            <div class="flex items-center gap-2 font-bold text-gray-600">
                                <span class="w-3 h-3 rounded-full bg-amber-400 shadow-sm shadow-amber-400/50"></span> Attente
                            </div>
                            <span class="font-black text-gray-900">{{ $statutAttente }}</span>
                        </div>
                        <div class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-amber-400 rounded-full transform origin-left transition-transform duration-500 scale-x-100 group-hover:scale-x-105" style="width: {{ $totalAdherents > 0 ? ($statutAttente / $totalAdherents) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── LIGNE 2 : PROCHAINE SÉANCE & TOP ACTIVITÉS ──── --}}

        {{-- Bloc Gauche (Admin ou Gestionnaire) --}}
        <div class="col-span-5 bg-white rounded-3xl border border-gray-100 shadow-sm flex flex-col min-h-[440px] overflow-hidden">

            @if (Auth::user()->role === 'admin')
                <div class="p-8 border-b border-gray-50 bg-gray-50/30 flex justify-between items-center">
                    <div>
                        <h3 class="font-grotesk font-black text-2xl text-gray-900">Analyse Structurelle</h3>
                    </div>
                </div>

                <div class="flex-1 p-8 bg-white">
                    <p class="text-sm font-bold text-gray-500 mb-6">Répartition par type d'adhésion</p>
                    <div class="space-y-6">
                        @forelse ($repartitionTypes as $index => $type)
                            @php
                                $typePct = $totalAdherents > 0 ? ($type->total / $totalAdherents) * 100 : 0;
                                $colors = ['bg-sv-blue', 'bg-sv-green', 'bg-purple-500', 'bg-amber-400'];
                                $colorClass = $colors[$index % count($colors)];
                            @endphp
                            <div class="group">
                                <div class="flex justify-between items-end mb-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center border border-gray-100">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                        </div>
                                        <span class="text-sm font-bold text-gray-700 capitalize">{{ $type->type_adhesion }}</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-lg font-black text-gray-900">{{ $type->total }}</span>
                                        <span class="text-xs text-gray-400 font-medium ml-1">({{ round($typePct) }}%)</span>
                                    </div>
                                </div>
                                <div class="h-2 w-full bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full {{ $colorClass }} rounded-full transition-all duration-1000 ease-out" style="width: {{ $typePct }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="flex items-center justify-center h-32 text-gray-400">
                                <p class="text-sm font-medium">Données insuffisantes</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @else
                <div class="p-8 border-b border-gray-50 bg-gray-50/30 flex justify-between items-center relative overflow-hidden">
                    {{-- Motif fond --}}
                    <svg class="absolute -right-4 -top-4 w-32 h-32 text-gray-100 opacity-50" fill="currentColor" viewBox="0 0 100 100"><rect x="10" y="10" width="80" height="80" rx="20" transform="rotate(45 50 50)"/></svg>

                    <div class="relative z-10">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="w-2 h-2 rounded-full bg-sv-green animate-pulse"></span>
                            <span class="font-grotesk font-bold text-gray-400 uppercase text-[10px] tracking-[0.2em]">Sur le terrain</span>
                        </div>
                        <h3 class="font-grotesk font-black text-2xl text-gray-900">Prochaine intervention</h3>
                    </div>
                    @if ($prochaineSeance)
                        <div class="relative z-10 bg-white border border-gray-200 shadow-sm text-sv-blue font-black text-sm px-4 py-2 rounded-xl flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            {{ \Carbon\Carbon::parse($prochaineSeance->date)->format('H:i') }}
                        </div>
                    @endif
                </div>

                <div class="flex-1 p-8 flex flex-col bg-white">
                    @if ($prochaineSeance)
                        <div class="flex gap-5 mb-8">
                            {{-- Calendrier Style Mac --}}
                            <div class="w-16 h-16 shrink-0 bg-white border border-gray-200 rounded-2xl shadow-sm flex flex-col overflow-hidden text-center">
                                <div class="bg-red-500 text-white text-[10px] font-bold uppercase py-1 leading-none border-b border-red-600">
                                    {{ \Carbon\Carbon::parse($prochaineSeance->date)->translatedFormat('M') }}
                                </div>
                                <div class="flex-1 flex items-center justify-center font-grotesk font-black text-2xl text-gray-900">
                                    {{ \Carbon\Carbon::parse($prochaineSeance->date)->format('d') }}
                                </div>
                            </div>

                            <div>
                                <h3 class="font-grotesk font-black text-2xl text-sv-blue leading-tight mb-2">
                                    {{ $prochaineSeance->activite_nom }}
                                </h3>
                                <p class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ $prochaineSeance->adresse ?: 'Lieu non défini' }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-auto bg-gray-50 rounded-2xl p-5 border border-gray-100">
                            <div class="flex justify-between items-end mb-3">
                                <div>
                                    <p class="font-bold text-xs text-gray-400 uppercase tracking-widest mb-1">État de l'appel</p>
                                    <p class="text-sm font-bold text-gray-900"><span class="text-sv-green text-lg">{{ $nbPresencesEnregistrees }}</span> / {{ $prochaineSeance->nb_inscrits }} présents</p>
                                </div>
                                @php $presencePct = $prochaineSeance->nb_inscrits > 0 ? ($nbPresencesEnregistrees / $prochaineSeance->nb_inscrits) * 100 : 0; @endphp
                                <div class="w-1/2 h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-sv-green rounded-full transition-all duration-1000 ease-out" style="width: {{ $presencePct }}%"></div>
                                </div>
                            </div>

                            <button class="mt-4 w-full bg-sv-blue hover:bg-[#111536] text-white font-grotesk font-bold py-4 rounded-xl transition-all shadow-md shadow-sv-blue/20 flex items-center justify-center gap-2 group">
                                Procéder à l'appel
                                <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </div>
                    @else
                        <div class="flex-1 flex flex-col items-center justify-center text-gray-400">
                            <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mb-4 border border-gray-100">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <p class="font-grotesk font-bold text-lg text-gray-500 mb-1">Carnet de bord vide</p>
                            <p class="text-sm font-medium">Aucune séance planifiée pour le moment.</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- ── BLOC DROITE : TOP 3 ACTIVITÉS ──── --}}
        <div class="col-span-7 bg-white rounded-3xl border border-gray-100 shadow-sm flex flex-col">
            <div class="p-8 border-b border-gray-50 flex items-center justify-between">
                <div>
                    <h3 class="font-grotesk font-black text-2xl text-gray-900">Palmarès des activités</h3>
                    <p class="text-sm text-gray-400 font-medium mt-1">Classement par volume d'inscrits</p>
                </div>
                <div class="flex items-center gap-2 bg-amber-50 text-amber-600 px-3 py-1.5 rounded-lg border border-amber-100">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                    <span class="font-bold text-xs uppercase tracking-widest">Top 3</span>
                </div>
            </div>

            <div class="p-8 flex-1 flex flex-col justify-center">
                <div class="space-y-6">
                    @forelse ($activitesStats as $index => $act)
                        @php
                            $pct = ($act->total_inscrits / $maxInscrits) * 100;
                            $horairesArr = json_decode($act->horaires, true);
                            $premierHoraire = !empty($horairesArr) ? array_key_first($horairesArr) . ' ' . reset($horairesArr) : 'Horaire non défini';
                            $rankColors = ['text-amber-500 bg-amber-50 border-amber-200', 'text-gray-500 bg-gray-100 border-gray-200', 'text-amber-700 bg-amber-100/50 border-amber-200/50'];
                            $rankColor = $rankColors[$index] ?? 'text-gray-400 bg-gray-50 border-gray-100';
                        @endphp

                        <div class="relative group cursor-default">
                            {{-- Ligne de fond au hover --}}
                            <div class="absolute -inset-x-4 -inset-y-3 bg-gray-50 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity z-0"></div>

                            <div class="relative z-10 flex items-start gap-5">
                                {{-- Badge Position --}}
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center font-black text-xl border shadow-sm shrink-0 {{ $rankColor }}">
                                    #{{ $index + 1 }}
                                </div>

                                <div class="flex-1 min-w-0 pt-1">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <h4 class="font-grotesk font-bold text-gray-900 text-lg leading-tight group-hover:text-sv-blue transition-colors">{{ $act->nom }}</h4>
                                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1 flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                {{ $premierHoraire }}
                                            </p>
                                        </div>
                                        <div class="text-right flex items-center gap-2">
                                            <span class="font-grotesk font-black text-3xl text-sv-blue leading-none">{{ $act->total_inscrits }}</span>
                                        </div>
                                    </div>

                                    {{-- Barre de stat élégante --}}
                                    <div class="h-2 w-full bg-gray-100 rounded-full overflow-hidden mt-3">
                                        <div class="h-full bg-gradient-to-r from-sv-blue to-[#4A55A2] rounded-full transform origin-left transition-transform duration-1000 ease-out scale-x-0" style="transform: scaleX({{ $pct / 100 }})"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-10 text-gray-400">
                            <svg class="w-12 h-12 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                            <p class="font-grotesk font-bold text-sm uppercase tracking-widest text-gray-400">Aucune donnée disponible</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
@endsection
