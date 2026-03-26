@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
    <div class="flex flex-col gap-5 h-full">

        <div class="grid grid-cols-12 gap-4">

            <div
                class="col-span-4 bg-gradient-to-br from-[#083325] to-[#111536] rounded-2xl p-6 relative overflow-hidden shadow-lg shadow-[#083325]/10 border border-white/10">
                <div class="absolute -right-8 -bottom-8 w-40 h-40 bg-[#16A37A]/20 rounded-full blur-3xl pointer-events-none">
                </div>
                <svg class="absolute top-0 right-0 w-48 h-48 text-white/5 translate-x-1/3 -translate-y-1/4 pointer-events-none"
                    viewBox="0 0 100 100" fill="currentColor">
                    <circle cx="50" cy="50" r="40" />
                    <circle cx="50" cy="50" r="20" fill="none" stroke="currentColor" stroke-width="2" />
                </svg>
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <div
                                class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center border border-white/5">
                                <svg class="w-5 h-5 text-[#16A37A]" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <span
                                class="font-grotesk font-bold text-white/60 text-xs uppercase tracking-widest">Adhérents</span>
                        </div>
                        <p class="font-grotesk font-black text-6xl text-white tracking-tighter leading-none">
                            {{ $totalAdherents }}</p>
                        @if ($newThisMonth > 0)
                            <div
                                class="inline-flex items-center gap-1.5 mt-3 bg-white/10 border border-white/10 text-[#16A37A] px-3 py-1.5 rounded-lg text-xs font-bold">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                </svg>
                                +{{ $newThisMonth }} ce mois
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-span-4 bg-white rounded-2xl p-6 border border-gray-200 shadow-sm relative overflow-hidden">
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
                    <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center border border-blue-100/50">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-1 relative z-10">
                    <p class="font-grotesk font-black text-4xl text-gray-900 tracking-tight leading-none">
                        {{ number_format($totalCotisations, 0, ',', ' ') }}</p>
                    <span class="text-2xl text-gray-200 font-bold">€</span>
                </div>
                @if ($totalEnAttente > 0)
                    <div
                        class="mt-3 inline-flex items-center gap-2 bg-amber-50 px-3 py-1.5 rounded-xl border border-amber-100 relative z-10">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse shrink-0"></span>
                        <p class="text-xs font-bold text-amber-700">Attente : <span
                                class="text-amber-900">{{ number_format($totalEnAttente, 0, ',', ' ') }} €</span></p>
                    </div>
                @else
                    <div
                        class="mt-3 inline-flex items-center gap-1.5 text-xs font-bold text-[#16A37A] bg-[#16A37A]/10 px-3 py-1.5 rounded-lg relative z-10">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Tout est à jour
                    </div>
                @endif
            </div>

            <div class="col-span-4 bg-white rounded-2xl p-6 border border-gray-200 shadow-sm flex flex-col justify-center">
                <div class="mb-4">
                    <p class="font-grotesk font-bold text-gray-400 uppercase text-[10px] tracking-[0.2em] mb-0.5">Suivi</p>
                    <h3 class="font-grotesk font-bold text-gray-700 text-sm">État des dossiers</h3>
                </div>

                <div class="space-y-5">
                    @php $total = max(1, $statutPaye + $statutPartiel + $statutAttente); @endphp
                    <div>
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
                    <div>
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
                    <div>
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

        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden flex flex-col flex-1 min-h-0">

            <div class="px-8 py-6 border-b border-gray-100 bg-white flex items-center justify-between shrink-0">
                <div>
                    <h3 class="font-grotesk font-black text-xl text-gray-900 tracking-tight flex items-center gap-3">
                        @if ($isGestionnaire)
                            <span
                                class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center text-base">📋</span>
                            Carnet de bord
                        @elseif (Auth::user()->role === 'admin')
                            <span
                                class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-base">📊</span>
                            Analyse des effectifs
                        @else
                            <span
                                class="w-8 h-8 rounded-lg bg-gray-50 text-gray-400 flex items-center justify-center text-base">☕</span>
                            Espace personnel
                        @endif
                    </h3>
                    <p class="text-sm text-gray-500 font-medium mt-1 pl-11">
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
                    <div class="flex-1 p-8">

                        @if ($prochaineSeance)
                            <div class="max-w-4xl mx-auto">
                                <div class="flex items-center gap-3 mb-6">
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

                                        <div class="md:col-span-3 p-8 flex items-start gap-6">
                                            <div
                                                class="w-24 h-28 shrink-0 bg-white border-2 border-gray-100 rounded-2xl flex flex-col overflow-hidden text-center shadow-sm">
                                                <div
                                                    class="bg-[#222A60] text-white text-[11px] font-bold uppercase py-2 tracking-widest">
                                                    {{ \Carbon\Carbon::parse($prochaineSeance->date)->translatedFormat('M') }}
                                                </div>
                                                <div class="flex-1 flex flex-col items-center justify-center">
                                                    <span
                                                        class="font-grotesk font-black text-4xl text-gray-900 leading-none mb-1">
                                                        {{ \Carbon\Carbon::parse($prochaineSeance->date)->format('d') }}
                                                    </span>
                                                    <span class="text-[10px] font-bold text-gray-400 uppercase">
                                                        {{ \Carbon\Carbon::parse($prochaineSeance->date)->translatedFormat('D') }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="flex flex-col justify-center py-1">
                                                <div
                                                    class="text-[#16A37A] font-black text-sm mb-2 flex items-center gap-2 bg-teal-50 w-fit px-3 py-1 rounded-lg">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2.5"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ \Carbon\Carbon::parse($prochaineSeance->date)->format('H:i') }}
                                                </div>

                                                <h3
                                                    class="font-grotesk font-black text-2xl text-[#0F143A] leading-tight mb-3">
                                                    {{ $prochaineSeance->activite_nom }}
                                                </h3>

                                                <p
                                                    class="inline-flex items-center gap-2 text-sm font-medium text-gray-500">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                    {{ $prochaineSeance->adresse ?: 'Lieu non défini' }}
                                                </p>
                                            </div>
                                        </div>

                                        <div
                                            class="md:col-span-2 bg-gray-50 border-t md:border-t-0 md:border-l border-gray-100 p-8 flex flex-col justify-between">
                                            <button
                                                class="mt-6 w-full bg-[#083325] hover:bg-[#16A37A] text-white font-grotesk font-bold py-3.5 rounded-xl transition-all duration-300 shadow-md hover:shadow-lg flex items-center justify-center gap-2 group text-sm">
                                                Procéder à l'appel
                                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                </svg>
                                            </button>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="h-full flex flex-col items-center justify-center text-gray-400 py-12">
                                <div
                                    class="w-20 h-20 rounded-3xl bg-white flex items-center justify-center mb-5 border border-gray-100 shadow-sm shadow-gray-100/50">
                                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-black text-gray-900 mb-1">Aucune séance à venir</h3>
                                <p class="text-sm font-medium text-gray-500">Vous n'avez pas de séance planifiée
                                    prochainement.</p>
                            </div>
                        @endif
                    </div>
                @elseif (Auth::user()->role === 'admin')
                    <div class="flex-1 p-8 border-b lg:border-b-0 lg:border-r border-gray-200 bg-white">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-8 text-center">Types des
                            adhésions</p>
                        <div class="relative w-full aspect-square max-h-[300px] mx-auto flex items-center justify-center">
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

                    <div class="flex-[1.5] p-8 bg-white">
                        <div class="flex items-center justify-between mb-8">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Top 3 des activités</p>
                        </div>
                        <div class="relative w-full h-[300px]">
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
                            class="w-16 h-16 rounded-full bg-white flex items-center justify-center mb-4 border border-gray-200 shadow-sm">
                            <span class="text-3xl opacity-80">☕</span>
                        </div>
                        <p class="text-base font-bold text-gray-600">Vous n'êtes assigné·e à la gestion d'aucune activité.
                        </p>
                    </div>
                @endif

            </div>
        </div>
    </div>
    </div>
@endsection

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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

        // ==========================================
        // 1. DOUGHNUT : RÉPARTITION PAR TYPE (ADMIN)
        // ==========================================
        const ctxTypes = document.getElementById('chartTypes');
        if (ctxTypes) {
            const labels = {!! $typeLabels ?? '[]' !!};
            const data = {!! $typeData ?? '[]' !!};

            new Chart(ctxTypes, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: [SV_GREEN, SV_DARK, SV_LIGHT, SV_MUTED],
                        borderWidth: 4,
                        borderColor: '#ffffff',
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    layout: {
                        padding: {
                            top: 10,
                            bottom: 10
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    family: 'Space Grotesk, sans-serif',
                                    size: 12,
                                    weight: 'bold'
                                },
                                color: '#4B5563'
                            }
                        },
                        tooltip: modernTooltip
                    }
                }
            });
        }

        // ==========================================
        // 2. BARRES HORIZONTALES : PALMARÈS ACTIVITÉS
        // ==========================================
        const ctxAct = document.getElementById('chartActivites');
        if (ctxAct) {
            const labels = {!! $actLabels ?? '[]' !!};
            const data = {!! $actData ?? '[]' !!};
            const horaires = {!! $actHoraires ?? '[]' !!};

            new Chart(ctxAct, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Inscrits',
                        data: data,
                        backgroundColor: SV_GREEN,
                        borderRadius: 6,
                        barThickness: 28,
                        borderSkipped: false
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            right: 40
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
                                    ` ${horaires[ctx.dataIndex]}` : ''
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
                                    size: 13,
                                    weight: 'bold'
                                },
                                padding: 10,
                                callback: function(val, i) {
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
                            ctx.font = 'bold 13px Space Grotesk, sans-serif';
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
