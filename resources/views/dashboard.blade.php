@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
<div class="flex flex-col gap-4 h-full">

    {{-- ── LIGNE 1 : KPIs ──── --}}
    <div class="grid grid-cols-12 gap-4">

        {{-- HÉRO --}}
        <div class="col-span-4 bg-gradient-to-br from-sv-blue to-[#111536] rounded-2xl p-6 relative overflow-hidden shadow-lg shadow-sv-blue/10 border border-white/10">
            <div class="absolute -right-8 -bottom-8 w-40 h-40 bg-sv-green/20 rounded-full blur-3xl pointer-events-none"></div>
            <svg class="absolute top-0 right-0 w-48 h-48 text-white/5 translate-x-1/3 -translate-y-1/4 pointer-events-none" viewBox="0 0 100 100" fill="currentColor">
                <circle cx="50" cy="50" r="40"/><circle cx="50" cy="50" r="20" fill="none" stroke="currentColor" stroke-width="2"/>
            </svg>
            <div class="relative z-10 flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center border border-white/5">
                            <svg class="w-5 h-5 text-sv-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <span class="font-grotesk font-bold text-white/60 text-xs uppercase tracking-widest">Adhérents</span>
                    </div>
                    <p class="font-grotesk font-black text-6xl text-white tracking-tighter leading-none">{{ $totalAdherents }}</p>
                    @if ($newThisMonth > 0)
                        <div class="inline-flex items-center gap-1.5 mt-3 bg-white/10 border border-white/10 text-sv-green px-3 py-1.5 rounded-lg text-xs font-bold">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                            +{{ $newThisMonth }} ce mois
                        </div>
                    @endif
                </div>
                <span class="flex h-2.5 w-2.5 relative mt-1">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sv-green opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-sv-green"></span>
                </span>
            </div>
        </div>

        {{-- FINANCES --}}
        <div class="col-span-4 bg-white rounded-2xl p-6 border border-gray-100 shadow-sm relative overflow-hidden">
            <svg class="absolute -right-6 -bottom-6 w-32 h-32 text-gray-50 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div class="relative z-10 flex items-start justify-between mb-4">
                <div>
                    <p class="font-grotesk font-bold text-gray-400 uppercase text-[10px] tracking-[0.2em] mb-0.5">Finances</p>
                    <h3 class="font-grotesk font-bold text-gray-700 text-sm">Trésorerie encaissée</h3>
                </div>
                <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center border border-blue-100/50">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="flex items-baseline gap-1 relative z-10">
                <p class="font-grotesk font-black text-4xl text-gray-900 tracking-tight leading-none">{{ number_format($totalCotisations, 0, ',', ' ') }}</p>
                <span class="text-2xl text-gray-200 font-bold">€</span>
            </div>
            @if ($totalEnAttente > 0)
                <div class="mt-3 inline-flex items-center gap-2 bg-amber-50 px-3 py-1.5 rounded-xl border border-amber-100 relative z-10">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse shrink-0"></span>
                    <p class="text-xs font-bold text-amber-700">Attente : <span class="text-amber-900">{{ number_format($totalEnAttente, 0, ',', ' ') }} €</span></p>
                </div>
            @else
                <div class="mt-3 inline-flex items-center gap-1.5 text-xs font-bold text-sv-green bg-sv-green/5 px-3 py-1.5 rounded-lg relative z-10">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Tout est à jour
                </div>
            @endif
        </div>

        {{-- STATUTS DONUT --}}
        <div class="col-span-4 bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
            <p class="font-grotesk font-bold text-gray-400 uppercase text-[10px] tracking-[0.2em] mb-0.5">Suivi</p>
            <h3 class="font-grotesk font-bold text-gray-700 text-sm mb-4">État des dossiers</h3>
            <div class="flex items-center gap-5">
                @php
                    $total = $statutPaye + $statutAttente ?: 1;
                    $r = 40; $cx = 52; $cy = 52; $circ = 2 * M_PI * $r;
                    $pPaye = ($statutPaye / $total) * $circ;
                    $pAtt  = ($statutAttente / $total) * $circ;
                @endphp
                <div class="relative w-24 h-24 shrink-0">
                    <svg viewBox="0 0 104 104" class="w-full h-full -rotate-90">
                        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="none" stroke="#f1f5f9" stroke-width="13"/>
                        @if ($statutPaye > 0)
                            <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="none" stroke="#16A37A" stroke-width="13"
                                stroke-dasharray="{{ $pPaye }} {{ $circ - $pPaye }}" stroke-dashoffset="{{ $circ }}" stroke-linecap="round"/>
                        @endif
                        @if ($statutAttente > 0)
                            <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="none" stroke="#FBBF24" stroke-width="13"
                                stroke-dasharray="{{ $pAtt }} {{ $circ - $pAtt }}" stroke-dashoffset="{{ $circ - $pPaye }}" stroke-linecap="round"/>
                        @endif
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center bg-white rounded-full m-3.5 shadow-inner">
                        <span class="font-grotesk font-black text-xl text-sv-blue leading-none">{{ $totalAdherents }}</span>
                    </div>
                </div>
                <div class="space-y-2.5 flex-1">
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <div class="flex items-center gap-1.5 font-bold text-gray-600"><span class="w-2 h-2 rounded-full bg-sv-green"></span> Payés</div>
                            <span class="font-black text-gray-900">{{ $statutPaye }}</span>
                        </div>
                        <div class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-sv-green rounded-full" style="width: {{ $total > 0 ? ($statutPaye / $total) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <div class="flex items-center gap-1.5 font-bold text-gray-600"><span class="w-2 h-2 rounded-full bg-amber-400"></span> Attente</div>
                            <span class="font-black text-gray-900">{{ $statutAttente }}</span>
                        </div>
                        <div class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-amber-400 rounded-full" style="width: {{ $total > 0 ? ($statutAttente / $total) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── LIGNE 2 ──── --}}
    <div class="grid grid-cols-12 gap-4 flex-1 min-h-0">

        {{-- GAUCHE : Analyse / Séance --}}
        <div class="col-span-5 bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col overflow-hidden">

            @if (Auth::user()->role === 'admin')
                <div class="px-6 py-4 border-b border-gray-50 bg-gray-50/50 shrink-0">
                    <p class="font-grotesk font-bold text-gray-400 uppercase text-[10px] tracking-[0.2em]">Analyse structurelle</p>
                    <h3 class="font-grotesk font-bold text-gray-800 mt-0.5">Répartition par type</h3>
                </div>
                <div class="flex-1 px-5 pt-2 pb-5 min-h-0">
                    @php
                        $typeLabels = $repartitionTypes->pluck('type_adhesion')->map(fn($l) => ucfirst($l))->toJson();
                        $typeData   = $repartitionTypes->pluck('total')->toJson();
                    @endphp
                    <canvas id="chartTypes" class="w-full h-full"></canvas>
                </div>

            @else
                <div class="px-6 py-4 border-b border-gray-50 bg-gray-50/50 flex items-center justify-between shrink-0">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-sv-green animate-pulse"></span>
                            <p class="font-grotesk font-bold text-gray-400 uppercase text-[10px] tracking-[0.2em]">Sur le terrain</p>
                        </div>
                        <h3 class="font-grotesk font-bold text-gray-800 mt-0.5">Prochaine intervention</h3>
                    </div>
                    @if ($prochaineSeance)
                        <div class="bg-white border border-gray-200 shadow-sm text-sv-blue font-black text-sm px-3 py-1.5 rounded-xl flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ \Carbon\Carbon::parse($prochaineSeance->date)->format('H:i') }}
                        </div>
                    @endif
                </div>

                <div class="flex-1 p-6 flex flex-col">
                    @if ($prochaineSeance)
                        <div class="flex gap-4 mb-5">
                            <div class="w-14 h-14 shrink-0 bg-white border border-gray-200 rounded-2xl shadow-sm flex flex-col overflow-hidden text-center">
                                <div class="bg-red-500 text-white text-[9px] font-bold uppercase py-0.5 leading-none">
                                    {{ \Carbon\Carbon::parse($prochaineSeance->date)->translatedFormat('M') }}
                                </div>
                                <div class="flex-1 flex items-center justify-center font-grotesk font-black text-xl text-gray-900">
                                    {{ \Carbon\Carbon::parse($prochaineSeance->date)->format('d') }}
                                </div>
                            </div>
                            <div>
                                <h3 class="font-grotesk font-black text-xl text-sv-blue leading-tight mb-1.5">{{ $prochaineSeance->activite_nom }}</h3>
                                <p class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 bg-gray-50 px-2.5 py-1 rounded-lg border border-gray-100">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ $prochaineSeance->adresse ?: 'Lieu non défini' }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-auto bg-gray-50 rounded-xl p-4 border border-gray-100">
                            @php $presencePct = $prochaineSeance->nb_inscrits > 0 ? ($nbPresencesEnregistrees / $prochaineSeance->nb_inscrits) * 100 : 0; @endphp
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Appel</p>
                                <p class="text-sm font-black text-gray-900"><span class="text-sv-green">{{ $nbPresencesEnregistrees }}</span> / {{ $prochaineSeance->nb_inscrits }}</p>
                            </div>
                            <div class="h-1.5 w-full bg-gray-200 rounded-full overflow-hidden mb-3">
                                <div class="h-full bg-sv-green rounded-full" style="width: {{ $presencePct }}%"></div>
                            </div>
                            <button class="w-full bg-sv-blue hover:bg-[#111536] text-white font-grotesk font-bold py-3 rounded-xl transition-all shadow-md shadow-sv-blue/20 flex items-center justify-center gap-2 group text-sm">
                                Procéder à l'appel
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </div>
                    @else
                        <div class="flex-1 flex flex-col items-center justify-center text-gray-400">
                            <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center mb-3 border border-gray-100">
                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <p class="text-sm font-bold text-gray-500">Aucune séance planifiée</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- DROITE : Top activités --}}
        <div class="col-span-7 bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col">
            <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between shrink-0">
                <div>
                    <h3 class="font-grotesk font-black text-lg text-gray-900">Palmarès des activités</h3>
                    <p class="text-xs text-gray-400 font-medium mt-0.5">Classement par volume d'inscrits</p>
                </div>
                <div class="flex items-center gap-1.5 bg-amber-50 text-amber-600 px-2.5 py-1 rounded-lg border border-amber-100">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <span class="font-bold text-xs uppercase tracking-wider">Top 3</span>
                </div>
            </div>

            @php
                $actLabels  = $activitesStats->pluck('nom')->toJson();
                $actData    = $activitesStats->pluck('total_inscrits')->toJson();
                $actHoraires = $activitesStats->map(function($a) {
                    $h = json_decode($a->horaires, true);
                    return !empty($h) ? array_key_first($h) . ' ' . reset($h) : '';
                })->toJson();
            @endphp
            <div class="flex-1 px-5 pt-2 pb-5 min-h-0">
                <canvas id="chartActivites" class="w-full h-full"></canvas>
            </div>
        </div>

    </div>
</div>
@endsection

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const SV_BLUE  = '#0F143A';
    const SV_GREEN = '#16A37A';
    const PALETTE  = ['#0F143A', '#16A37A', '#6366F1', '#F59E0B', '#EC4899'];

    // Tooltip partagé, style propre
    const sharedTooltip = {
        backgroundColor: '#fff',
        titleColor: SV_BLUE,
        bodyColor: '#6B7280',
        borderColor: '#E5E7EB',
        borderWidth: 1,
        padding: 12,
        cornerRadius: 10,
        titleFont: { weight: '700', size: 12 },
        bodyFont: { size: 12 },
    };

    // Plugin : affiche la valeur au-dessus de chaque barre
    const topValuePlugin = {
        id: 'topValue',
        afterDatasetsDraw(chart) {
            const { ctx, data } = chart;
            chart.getDatasetMeta(0).data.forEach((bar, i) => {
                const val = data.datasets[0].data[i];
                if (val == null) return;
                ctx.save();
                ctx.fillStyle = SV_BLUE;
                ctx.font = '700 13px Space Grotesk, ui-sans-serif, sans-serif';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'bottom';
                ctx.fillText(val, bar.x, bar.y - 5);
                ctx.restore();
            });
        }
    };

    // ── 1. Bar verticaux — Répartition par type (admin) ──
    const ctxTypes = document.getElementById('chartTypes');
    if (ctxTypes) {
        const labels = {!! $typeLabels ?? '[]' !!};
        const data   = {!! $typeData   ?? '[]' !!};
        const max    = Math.max(...data);

        // Crée un gradient par barre
        const makeGradient = (ctx, color) => {
            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, color + 'FF');
            gradient.addColorStop(1, color + '22');
            return gradient;
        };

        new Chart(ctxTypes, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: (ctx) => {
                        const chart = ctx.chart;
                        const { chartArea } = chart;
                        if (!chartArea) return PALETTE[ctx.dataIndex % PALETTE.length] + 'CC';
                        const gradient = chart.ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                        const color = PALETTE[ctx.dataIndex % PALETTE.length];
                        gradient.addColorStop(0, color + 'EE');
                        gradient.addColorStop(1, color + '33');
                        return gradient;
                    },
                    borderColor: (ctx) => PALETTE[ctx.dataIndex % PALETTE.length],
                    borderWidth: 0,
                    borderRadius: { topLeft: 6, topRight: 6, bottomLeft: 0, bottomRight: 0 },
                    borderSkipped: false,
                    barPercentage: 0.55,
                    categoryPercentage: 0.7,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 24 } },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...sharedTooltip,
                        callbacks: {
                            label: ctx => ` ${ctx.parsed.y} adhérents`,
                            afterLabel: ctx => {
                                const total = data.reduce((a, b) => a + b, 0);
                                const pct = Math.round((ctx.parsed.y / total) * 100);
                                return ` ${pct}% du total`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: {
                            color: '#374151',
                            font: { size: 12, weight: '600', family: 'Space Grotesk, ui-sans-serif' },
                            maxRotation: 0,
                        }
                    },
                    y: {
                        grid: { color: '#F3F4F6', drawTicks: false },
                        border: { display: false, dash: [4, 4] },
                        ticks: {
                            color: '#9CA3AF',
                            font: { size: 11 },
                            maxTicksLimit: 5,
                            stepSize: 1,
                            padding: 8,
                        },
                        suggestedMax: max + Math.ceil(max * 0.2),
                    }
                },
                animation: { duration: 700, easing: 'easeOutQuart' },
            },
            plugins: [topValuePlugin]
        });
    }

    // ── 2. Bar verticaux — Palmarès activités ──
    const ctxAct = document.getElementById('chartActivites');
    if (ctxAct) {
        const labels   = {!! $actLabels   ?? '[]' !!};
        const data     = {!! $actData     ?? '[]' !!};
        const horaires = {!! $actHoraires ?? '[]' !!};
        const max      = Math.max(...data);

        // Couleurs : 1er bleu foncé, 2e vert, 3e indigo
        const barColors = [SV_BLUE, SV_GREEN, '#6366F1'];

        new Chart(ctxAct, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: (ctx) => {
                        const chart = ctx.chart;
                        const { chartArea } = chart;
                        if (!chartArea) return barColors[ctx.dataIndex] + 'CC';
                        const gradient = chart.ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                        const color = barColors[ctx.dataIndex] ?? SV_BLUE;
                        gradient.addColorStop(0, color + 'F0');
                        gradient.addColorStop(1, color + '30');
                        return gradient;
                    },
                    borderWidth: 0,
                    borderRadius: { topLeft: 8, topRight: 8, bottomLeft: 0, bottomRight: 0 },
                    borderSkipped: false,
                    barPercentage: 0.5,
                    categoryPercentage: 0.65,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 28 } },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...sharedTooltip,
                        callbacks: {
                            title: ctx => ctx[0].label,
                            label: ctx => ` ${ctx.parsed.y} inscrits`,
                            afterLabel: ctx => horaires[ctx.dataIndex] ? ` ${horaires[ctx.dataIndex]}` : '',
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: {
                            color: '#374151',
                            font: { size: 12, weight: '700', family: 'Space Grotesk, ui-sans-serif' },
                            maxRotation: 0,
                            // Coupe les labels trop longs
                            callback: function(val, i) {
                                const label = this.getLabelForValue(val);
                                return label.length > 18 ? label.slice(0, 16) + '…' : label;
                            }
                        }
                    },
                    y: {
                        grid: { color: '#F3F4F6', drawTicks: false },
                        border: { display: false, dash: [4, 4] },
                        ticks: {
                            color: '#9CA3AF',
                            font: { size: 11 },
                            maxTicksLimit: 5,
                            stepSize: 1,
                            padding: 8,
                        },
                        suggestedMax: max + Math.ceil(max * 0.2),
                    }
                },
                animation: {
                    delay: (ctx) => ctx.dataIndex * 120,
                    duration: 600,
                    easing: 'easeOutQuart',
                },
            },
            plugins: [topValuePlugin]
        });
    }
});
</script>
