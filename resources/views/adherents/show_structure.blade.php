@extends('layouts.app')

@section('title', 'Structure — ' . $structure->nom)

@section('content')

    @if (session('success'))
        <div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-xl text-sm font-semibold text-emerald-600 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-8">

        <div class="flex items-center gap-2 text-xs text-gray-400 mb-5 pl-1">
            <a href="{{ route('adherents.index', ['type' => 'structure']) }}" class="hover:text-[#222A60] transition-colors font-medium">Adhérents</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-600 font-semibold">{{ $structure->nom }}</span>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_24px_rgba(0,0,0,0.04)] overflow-hidden">
            <div class="h-1.5 bg-gradient-to-r from-[#222A60] via-indigo-400 to-indigo-400/40"></div>

            <div class="p-6">
                <div class="flex flex-col lg:flex-row lg:items-start gap-6">

                    <div class="flex items-start gap-5 flex-1">
                        <div class="relative shrink-0">
                            <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-white text-xl font-black shadow-lg bg-[#222A60]">
                                🏛️
                            </div>
                            @if ($structure->inscription?->a_paye === 'Payé')
                                <span class="absolute -bottom-1 -right-1 w-5 h-5 bg-[#16987C] rounded-full border-2 border-white flex items-center justify-center">
                                    <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </span>
                            @endif
                        </div>

                        <div class="min-w-0">
                            <h1 class="font-grotesk text-2xl font-black text-[#0F143A] tracking-tight">{{ $structure->nom }}</h1>
                            @if ($structure->sigle)
                                <p class="text-sm text-gray-400 font-medium mt-0.5">{{ $structure->sigle }}</p>
                            @endif

                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1 text-sm text-gray-500">
                                @if ($structure->ville)
                                    <span>{{ $structure->ville }}{{ $structure->code_postal ? ' · ' . $structure->code_postal : '' }}</span>
                                    <span class="text-gray-200">|</span>
                                @endif
                                @if ($structure->mail)
                                    <span>{{ $structure->mail }}</span>
                                @endif
                            </div>

                            <div class="flex flex-wrap gap-2 mt-3">
                                @php
                                    $sjLabel = match($structure->statut_juridique) {
                                        'tpe_asso' => ['label' => 'TPE / Association', 'class' => 'bg-indigo-50 text-indigo-600 border-indigo-100'],
                                        'esr_pme'  => ['label' => 'ESR / PME',         'class' => 'bg-purple-50 text-purple-600 border-purple-100'],
                                        default    => ['label' => $structure->statut_juridique ?? 'Structure', 'class' => 'bg-gray-50 text-gray-500 border-gray-100'],
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border {{ $sjLabel['class'] }}">{{ $sjLabel['label'] }}</span>

                                @if ($structure->statut)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-gray-50 text-gray-500 border border-gray-100">{{ ucfirst($structure->statut) }}</span>
                                @endif

                                @if ($structure->inscription)
                                    @php
                                        $aPaye = $structure->inscription->a_paye;
                                        $badgeClass = match($aPaye) {
                                            'Payé'       => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                            'En attente' => 'bg-amber-50 text-amber-600 border-amber-100',
                                            default      => 'bg-gray-50 text-gray-500 border-gray-100',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold border {{ $badgeClass }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $aPaye === 'Payé' ? 'bg-emerald-500' : 'bg-amber-400' }}"></span>
                                        {{ $aPaye }}
                                    </span>
                                @endif

                                @if ($structure->inscription)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-gray-50 text-gray-400 border border-gray-100">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        Inscrit le {{ $structure->inscription->date_inscription->isoFormat('D MMM YYYY') }}
                                    </span>
                                @endif

                                @if ($structure->numero_adherent)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-[#222A60]/5 text-[#222A60] border border-[#222A60]/10">
                                        N° {{ $structure->numero_adherent }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('structures.pdf', $structure) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 text-sm font-bold rounded-xl transition-all shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V3"/></svg>
                            <span>Télécharger la fiche adhérent</span>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Colonne principale --}}
        <div class="xl:col-span-2 space-y-6">

            {{-- Infos structure --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.03)] overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#222A60]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest">Informations structure</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-0">
                        @php
                            $infos = [
                                ['label' => 'Nom complet',      'value' => $structure->nom],
                                ['label' => 'Sigle',            'value' => $structure->sigle],
                                ['label' => 'Date de création', 'value' => $structure->date_creation?->isoFormat('D MMMM YYYY')],
                                ['label' => 'Adresse',          'value' => $structure->adresse],
                                ['label' => 'Ville / CP',       'value' => $structure->ville ? $structure->ville . ($structure->code_postal ? ' · ' . $structure->code_postal : '') : null],
                                ['label' => 'Téléphone',        'value' => $structure->tel],
                                ['label' => 'Tél. portable',    'value' => $structure->tel_portable],
                                ['label' => 'Email',            'value' => $structure->mail],
                                ['label' => 'Site web',         'value' => $structure->site_web],
                            ];
                        @endphp
                        @foreach ($infos as $info)
                            <div class="flex items-baseline justify-between py-3 border-b border-gray-50 last:border-0">
                                <span class="text-xs font-semibold text-gray-400 shrink-0 mr-4">{{ $info['label'] }}</span>
                                @if ($info['label'] === 'Site web' && $info['value'])
                                    <a href="{{ $info['value'] }}" target="_blank" class="text-sm font-semibold text-teal-600 hover:underline text-right truncate">{{ $info['value'] }}</a>
                                @else
                                    <span class="text-sm font-semibold text-[#0F143A] text-right">{{ $info['value'] ?? '—' }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Correspondant --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.03)] overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#222A60]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest">Correspondant</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-0">
                        @php
                            $corresp = [
                                ['label' => 'Nom',      'value' => $structure->nom_correspondant],
                                ['label' => 'Téléphone','value' => $structure->tel_correspondant],
                            ];
                        @endphp
                        @foreach ($corresp as $info)
                            <div class="flex items-baseline justify-between py-3 border-b border-gray-50 last:border-0">
                                <span class="text-xs font-semibold text-gray-400 shrink-0 mr-4">{{ $info['label'] }}</span>
                                <span class="text-sm font-semibold text-[#0F143A] text-right">{{ $info['value'] ?? '—' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>

        {{-- Colonne droite --}}
        <div class="space-y-6">

            {{-- Paiements --}}
            @if ($structure->paiements->isNotEmpty())
                <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.03)] overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#222A60]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest">Paiements</h2>
                    </div>
                    <div class="divide-y divide-gray-50">
                        @foreach ($structure->paiements->sortByDesc('date_paiement') as $paiement)
                            <div class="flex items-center justify-between px-6 py-3">
                                <div>
                                    <p class="text-sm font-semibold text-[#0F143A]">{{ $paiement->commentaire ?? 'Paiement' }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $paiement->date_paiement?->isoFormat('D MMM YYYY') }} · {{ $paiement->source ?? '—' }}</p>
                                </div>
                                <span class="font-black text-sm text-[#16987C]">{{ number_format($paiement->montant, 2, ',', ' ') }} €</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mx-5 my-4 p-4 bg-[#16987C]/8 rounded-xl border border-[#16987C]/15 flex items-center justify-between">
                        <span class="text-sm font-bold text-[#16987C]">Total encaissé</span>
                        <span class="font-grotesk text-lg font-black text-[#16987C]">{{ number_format($totalPaye, 2, ',', ' ') }} €</span>
                    </div>
                </div>
            @endif

            {{-- Engagements --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.03)] overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-2">
                    <span class="text-teal-500 text-sm">📜</span>
                    <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest">Engagements & Autorisations</h2>
                </div>
                <div class="p-6 space-y-3">
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 flex items-center justify-center rounded-full shrink-0 text-sm {{ $structure->autorisation_photo ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-500' }}">
                            {{ $structure->autorisation_photo ? '✓' : '✕' }}
                        </span>
                        <span class="text-sm text-[#0F143A] font-medium">Droit à l'image accordé</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 flex items-center justify-center rounded-full shrink-0 text-sm {{ $structure->bulletin ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-400' }}">
                            {{ $structure->bulletin ? '✓' : '—' }}
                        </span>
                        <span class="text-sm text-[#0F143A] font-medium">Abonné au bulletin d'information</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
