@extends('layouts.app')

@section('title', 'Statistiques')

@section('content')

    <div class="min-h-screen -m-4 sm:-m-8 p-4 sm:p-8 font-sans">

        <div class="max-w-[1600px] mx-auto space-y-6">

            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8">
                <div>
                    <p class="text-xl text-gray-500 font-medium mt-1">
                        {{ $totalAdherents }} adhérents · Saison {{ $saisonCourante }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                    <select id="saisonFilter"
                        class="font-grotesk text-sm font-black bg-white border border-gray-200 rounded-xl px-4 py-2.5 shadow-sm hover:shadow-md focus:ring-2 focus:ring-[#16987C] focus:border-transparent transition-all min-w-[180px]"
                        onchange="if(this.value) window.location.href = '{{ route('statistiques.index') }}?saison=' + this.value">
                        @foreach ($saisons as $s)
                            <option value="{{ $s }}" {{ $s === $saisonCourante ? 'selected' : '' }}>
                                {{ $s }}</option>
                        @endforeach
                    </select>
                    <livewire:export-statistiques :saison="$saisonCourante" />
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">

                <div
                    class="bg-white rounded-2xl p-5 border border-gray-100 shadow-[0_2px_10px_rgba(0,0,0,0.02)] relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-[#16987C]"></div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Total adhérents</p>
                    <p class="font-grotesk text-3xl font-black text-[#0F143A]">{{ $totalAdherents }}</p>
                    <p
                        class="text-xs font-bold {{ $diffTotalAdherents >= 0 ? 'text-emerald-500' : 'text-rose-500' }} mt-2 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if ($diffTotalAdherents >= 0)
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            @endif
                        </svg>
                        {{ $diffTotalAdherents > 0 ? '+' : '' }}{{ $diffTotalAdherents }} vs saison préc.
                    </p>
                </div>

                <div
                    class="bg-white rounded-2xl p-5 border border-gray-100 shadow-[0_2px_10px_rgba(0,0,0,0.02)] relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-blue-500"></div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Âge moyen</p>
                    <p class="font-grotesk text-3xl font-black text-[#0F143A]">{{ $ageMoyen }}</p>
                    <p class="text-xs font-bold text-gray-400 mt-2">ans · médiane {{ $medianeAge }} ans</p>
                </div>

                <div
                    class="bg-white rounded-2xl p-5 border border-gray-100 shadow-[0_2px_10px_rgba(0,0,0,0.02)] relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-indigo-400"></div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Parité F/M</p>
                    <p class="font-grotesk text-3xl font-black text-[#0F143A]">{{ $pctFilles }} / {{ $pctGarcons }}</p>
                    <p class="text-xs font-bold text-gray-400 mt-2">% Filles / Garçons</p>
                </div>

                <div
                    class="bg-white rounded-2xl p-5 border border-gray-100 shadow-[0_2px_10px_rgba(0,0,0,0.02)] relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-amber-500"></div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Taux de fidélisation</p>
                    <p class="font-grotesk text-3xl font-black text-[#0F143A]">{{ $tauxFidelisation }}%</p>
                    <p class="text-xs font-bold text-gray-400 mt-2 flex items-center gap-1">
                        {{ $nbReinscrits }} Réinscrits
                    </p>
                </div>

                <div
                    class="bg-white rounded-2xl p-5 border border-gray-100 shadow-[0_2px_10px_rgba(0,0,0,0.02)] relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-[#0F143A]"></div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Nouveaux inscrits</p>
                    <p class="font-grotesk text-3xl font-black text-[#0F143A]">{{ $nouveauxInscrits }}</p>
                    <p
                        class="text-xs font-bold {{ $diffNouveaux >= 0 ? 'text-emerald-500' : 'text-rose-500' }} mt-2 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if ($diffNouveaux >= 0)
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            @endif
                        </svg>
                        {{ $diffNouveaux > 0 ? '+' : '' }}{{ $diffNouveaux }} vs saison préc.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">

                <div
                    class="xl:col-span-2 bg-white rounded-3xl border border-gray-100 p-6 shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
                    <div class="flex items-start justify-between mb-6">
                        <div>
                            <h2 class="font-bold text-[#0F143A]">Répartition par tranches d'âge</h2>
                            <p class="text-xs text-gray-400 font-medium">Distribution des {{ $totalAdherents }} adhérents
                            </p>
                        </div>
                        <span
                            class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-lg text-[10px] font-black uppercase tracking-widest">Âge</span>
                    </div>
                    <div class="relative h-64 w-full">
                        <canvas id="ageChart"></canvas>
                    </div>
                </div>

                <div
                    class="xl:col-span-1 bg-white rounded-3xl border border-gray-100 p-6 shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
                    <h2 class="font-bold text-[#0F143A]">Répartition par genre</h2>
                    <p class="text-xs text-gray-400 font-medium mb-6">Parité femme / homme</p>

                    <div class="relative h-40 w-full flex justify-center mb-6">
                        <canvas id="genderChart"></canvas>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#16987C]"></span>
                                <span class="font-semibold text-gray-600">Filles / Femmes</span>
                            </div>
                            <span class="font-black text-[#0F143A]">{{ $nbFilles }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                                <span class="font-semibold text-gray-600">Garçons / Hommes</span>
                            </div>
                            <span class="font-black text-[#0F143A]">{{ $nbGarcons }}</span>
                        </div>
                    </div>
                </div>

                <div
                    class="xl:col-span-1 bg-white rounded-3xl border border-gray-100 p-6 shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
                    <h2 class="font-bold text-[#0F143A]">Occupation des parents</h2>
                    <p class="text-xs text-gray-400 font-medium mb-8">Catégorie socio-professionnelle</p>

                    <div class="space-y-4">
                        @php
                            $colors = [
                                'bg-[#16987C]',
                                'bg-blue-500',
                                'bg-indigo-400',
                                'bg-amber-500',
                                'bg-rose-500',
                                'bg-pink-400',
                            ];
                        @endphp

                        @forelse ($cspData as $index => $csp)
                            <div>
                                <div class="flex justify-between items-end mb-1.5">
                                    <span class="text-[11px] font-bold text-gray-600 leading-tight w-2/3 truncate"
                                        title="{{ $csp['label'] }}">{{ $csp['label'] }}</span>
                                    <span class="text-xs font-black text-[#0F143A]">{{ $csp['pct'] }}%</span>
                                </div>
                                <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full {{ $colors[$index % count($colors)] }} rounded-full"
                                        style="width: {{ $csp['pct'] }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div
                                class="flex items-center justify-center p-4 bg-gray-50 rounded-xl border border-gray-100 border-dashed">
                                <p class="text-xs text-gray-400 font-medium text-center">Aucune profession renseignée pour
                                    les adhérents de cette saison.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div
                    class="xl:col-span-2 bg-white rounded-3xl border border-gray-100 p-6 shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
                    <div class="flex items-start justify-between mb-6">
                        <div>
                            <h2 class="font-bold text-[#0F143A]">Évolution des inscriptions</h2>
                            <p class="text-xs text-gray-400 font-medium">Cumul des nouveaux adhérents par mois</p>
                        </div>
                        <span
                            class="px-3 py-1 bg-blue-50 text-blue-600 rounded-lg text-[10px] font-black uppercase tracking-widest">Tendance</span>
                    </div>
                    <div class="relative h-64 w-full">
                        <canvas id="evolutionChart"></canvas>
                    </div>
                </div>

                <div
                    class="xl:col-span-2 bg-white rounded-3xl border border-gray-100 p-6 shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
                    <h2 class="font-bold text-[#0F143A]">Statut des adhérents</h2>
                    <p class="text-xs text-gray-400 font-medium mb-6">Aperçu global</p>

                    <div class="space-y-4">
                        <div
                            class="flex items-center justify-between p-4 rounded-xl border border-gray-100 bg-gray-50/50 hover:bg-white hover:shadow-sm transition-all">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                </div>
                                <span class="text-sm font-bold text-gray-700">Réinscrits (fidèles)</span>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-black text-[#0F143A]">{{ $statutData['reinscrits']['count'] }}</p>
                                <p class="text-xs font-bold text-gray-400">{{ $statutData['reinscrits']['pct'] }}%</p>
                            </div>
                        </div>

                        <div
                            class="flex items-center justify-between p-4 rounded-xl border border-gray-100 bg-gray-50/50 hover:bg-white hover:shadow-sm transition-all">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-lg bg-amber-100 text-amber-500 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                    </svg>
                                </div>
                                <span class="text-sm font-bold text-gray-700">Nouveaux inscrits</span>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-black text-[#0F143A]">{{ $statutData['nouveaux']['count'] }}</p>
                                <p class="text-xs font-bold text-gray-400">{{ $statutData['nouveaux']['pct'] }}%</p>
                            </div>
                        </div>

                        <div
                            class="flex items-center justify-between p-4 rounded-xl border border-gray-100 bg-gray-50/50 hover:bg-white hover:shadow-sm transition-all">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-lg bg-rose-100 text-rose-500 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <span class="text-sm font-bold text-gray-700">Abandons en cours</span>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-black text-rose-500">{{ $statutData['abandons']['count'] }}</p>
                                <p class="text-xs font-bold text-gray-400">{{ $statutData['abandons']['pct'] }}%</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h2 class="font-bold text-[#0F143A]">Carte d'origine des adhérents</h2>
                        <p class="text-xs text-gray-400 font-medium">Répartition géographique</p>
                    </div>
                </div>

                <div class="w-full rounded-2xl overflow-hidden border border-gray-100 shadow-inner">
                    <iframe style="width: 100%; height: 600px; border: 0;" allowfullscreen allow="geolocation"
                        src="//umap.openstreetmap.fr/fr/map/reseau-iut-savoirs-vivants_1132770?scaleControl=false&miniMap=false&scrollWheelZoom=true&zoomControl=true&editMode=disabled&moreControl=false&searchControl=true&tilelayersControl=null&embedControl=null&datalayersControl=true&onLoadPanel=none&captionBar=false&captionMenus=false&captionControl=true&homeControl=false"></iframe>
                </div>

                <div class="text-right mt-3">
                    <a href="//umap.openstreetmap.fr/fr/map/reseau-iut-savoirs-vivants_1132770?scaleControl=false&miniMap=false&scrollWheelZoom=true&zoomControl=true&editMode=disabled&moreControl=false&searchControl=true&tilelayersControl=null&embedControl=null&datalayersControl=true&onLoadPanel=none&captionBar=false&captionMenus=false&captionControl=true&homeControl=false"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 text-xs font-bold text-[#16987C] hover:text-[#138a6f] hover:underline transition-colors">
                        Ouvrir la carte en plein écran
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>

            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {

                Chart.defaults.color = '#9ca3af';

                const ctxAge = document.getElementById('ageChart').getContext('2d');
                new Chart(ctxAge, {
                    type: 'bar',
                    data: {
                        labels: @json($ageData['labels']),
                        datasets: [{
                                label: 'Filles / Femmes',
                                data: @json($ageData['filles']),
                                backgroundColor: '#16987C',
                                borderRadius: 4,
                                barPercentage: 0.6,
                                categoryPercentage: 0.8
                            },
                            {
                                label: 'Garçons / Hommes',
                                data: @json($ageData['garcons']),
                                backgroundColor: '#3b82f6',
                                borderRadius: 4,
                                barPercentage: 0.6,
                                categoryPercentage: 0.8
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 8,
                                    font: {
                                        weight: 'bold',
                                        size: 11
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: '#f3f4f6',
                                    drawBorder: false
                                },
                                border: {
                                    display: false
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                border: {
                                    display: false
                                }
                            }
                        }
                    }
                });

                const ctxGender = document.getElementById('genderChart').getContext('2d');
                new Chart(ctxGender, {
                    type: 'doughnut',
                    data: {
                        labels: ['Filles / Femmes', 'Garçons / Hommes'],
                        datasets: [{
                            data: [{{ $nbFilles }}, {{ $nbGarcons }}],
                            backgroundColor: ['#16987C', '#3b82f6'],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });

                const ctxEvol = document.getElementById('evolutionChart').getContext('2d');

                let gradientSaisonActuelle = ctxEvol.createLinearGradient(0, 0, 0, 300);
                gradientSaisonActuelle.addColorStop(0, 'rgba(22, 152, 124, 0.2)');
                gradientSaisonActuelle.addColorStop(1, 'rgba(22, 152, 124, 0)');

                new Chart(ctxEvol, {
                    type: 'line',
                    data: {
                        labels: @json($evolutionData['labels']),
                        datasets: [{
                                label: 'Saison {{ $saisonCourante }}',
                                data: @json($evolutionData['courante']),
                                borderColor: '#16987C',
                                backgroundColor: gradientSaisonActuelle,
                                borderWidth: 3,
                                pointBackgroundColor: '#16987C',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 5,
                                fill: true,
                                tension: 0.4
                            },
                            {
                                label: 'Saison {{ $saisonPrecedente ?? 'Précédente' }}',
                                data: @json($evolutionData['precedente']),
                                borderColor: '#9ca3af',
                                borderWidth: 2,
                                borderDash: [5, 5],
                                pointBackgroundColor: '#9ca3af',
                                pointRadius: 0,
                                fill: false,
                                tension: 0.4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 8,
                                    font: {
                                        weight: 'bold',
                                        size: 11
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: '#f3f4f6',
                                    drawBorder: false
                                },
                                border: {
                                    display: false
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                border: {
                                    display: false
                                }
                            }
                        }
                    }
                });

            });
        </script>

    @endsection
