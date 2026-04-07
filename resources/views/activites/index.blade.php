@extends('layouts.app')

@section('title', 'Activités')

@section('content')

<div class="flex items-center justify-between mb-6 pl-2">
    <div>
        <p class="text-sm text-gray-400 mt-1 font-medium">
            <span class="font-bold text-gray-600">{{ $activites->count() }}</span> activités cette saison
        </p>
    </div>
    @can('gerer-activites')
    <div class="flex items-center gap-2">
        <button onclick="toggleModalDossier()"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-bold rounded-xl transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
            </svg>
            Gérer les dossiers
        </button>
        <a href="{{ route('activites.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#222A60] hover:bg-[#1a2050] text-white text-sm font-bold rounded-xl transition-all shadow-sm hover:shadow-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            Ajouter une activité
        </a>
    </div>
    @endcan
</div>

<div class="flex flex-wrap items-center gap-2 mb-6 pl-2">
    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mr-1">Filtrer</span>

    <a href="{{ route('activites.index', array_merge(request()->except('type', 'page'), [])) }}"
       class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all
       {{ !$type ? 'bg-[#222A60] text-white shadow-sm' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
        Toutes
    </a>
    <a href="{{ route('activites.index', array_merge(request()->except('type', 'page'), ['type' => 'activite'])) }}"
       class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all
       {{ $type === 'activite' ? 'bg-[#222A60] text-white shadow-sm' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
        Activités
    </a>
    <a href="{{ route('activites.index', array_merge(request()->except('type', 'page'), ['type' => 'stage'])) }}"
       class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all
       {{ $type === 'stage' ? 'bg-amber-500 text-white shadow-sm' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
        Stages
    </a>
</div>

@php
    $activitesParDossier = $activites->groupBy(fn($a) => $a->id_dossier ?? 0);
    $activitesSansDossier = $activitesParDossier->get(0, collect());
    $dossiersAvecActivites = $dossiers->filter(fn($d) => $activitesParDossier->has($d->id));
    $dossiersSansActivites = $dossiers->reject(fn($d) => $activitesParDossier->has($d->id));
@endphp

@foreach($dossiersAvecActivites as $dossier)
    @php $activitesDuDossier = $activitesParDossier->get($dossier->id, collect()); @endphp
    <div class="mb-8" x-data="{ open: false }">
        <div class="flex items-center gap-3 mb-4 pl-1 cursor-pointer" @click="open = !open">
            <div class="w-8 h-8 rounded-lg bg-[#222A60]/10 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-[#222A60]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                </svg>
            </div>
            <div class="flex items-center gap-2 flex-1 min-w-0">
                <h2 class="font-grotesk font-black text-sm text-[#0F143A] truncate">{{ $dossier->nom }}</h2>
                <span class="text-[10px] font-bold bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full flex-shrink-0">{{ $activitesDuDossier->count() }}</span>
            </div>
            <svg class="w-4 h-4 text-gray-400 transition-transform flex-shrink-0" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
            </svg>
        </div>

        <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-5">
            @foreach($activitesDuDossier as $activite)
                @include('activites._card', ['activite' => $activite])
            @endforeach
        </div>
    </div>
@endforeach

@if($activitesSansDossier->isNotEmpty() || $activites->isEmpty())
    @if($dossiersAvecActivites->isNotEmpty())
        <div class="flex items-center gap-3 mb-4 pl-1">
            <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <h2 class="font-grotesk font-black text-sm text-gray-400">Sans dossier</h2>
            <span class="text-[10px] font-bold bg-gray-100 text-gray-400 px-2 py-0.5 rounded-full">{{ $activitesSansDossier->count() }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-5">

        @forelse ($activitesSansDossier as $activite)
            @include('activites._card', ['activite' => $activite])
        @empty
            @if($activites->isEmpty())
                <div class="col-span-full py-20 flex flex-col items-center gap-3 text-gray-300">
                    <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <p class="font-bold text-gray-400">Aucune activité trouvée</p>
                </div>
            @endif
        @endforelse

        @can('gerer-activites')
        <a href="{{ route('activites.create') }}"
           class="border-2 border-dashed border-gray-200 rounded-2xl p-5 flex flex-col items-center justify-center gap-3
           text-gray-400 hover:border-[#16987C] hover:text-[#16987C] transition-all group min-h-[200px]">
            <div class="w-11 h-11 rounded-full border-2 border-dashed border-current flex items-center justify-center group-hover:scale-110 transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <span class="font-grotesk font-black text-xs uppercase tracking-widest">Nouvelle activité</span>
        </a>
        @endcan

        @if($archives->count() > 0)
            <button onclick="toggleArchives()"
               class="bg-gray-50 border-2 border-gray-100 rounded-2xl p-5 flex flex-col items-center justify-center gap-3
               text-gray-400 hover:border-gray-300 hover:text-gray-600 transition-all group min-h-[200px]">
                <div class="relative">
                    <div class="w-14 h-14 rounded-2xl bg-gray-200 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                    </div>
                    <div class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-rose-500 text-white text-[10px] font-black flex items-center justify-center border-2 border-white shadow-sm">
                        {{ $archives->count() }}
                    </div>
                </div>
                <span class="font-grotesk font-black text-sm uppercase tracking-widest text-gray-500">Archives</span>
            </button>
        @endif
    </div>
@endif

<div id="archives-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" onclick="toggleArchives()"></div>

    <div class="absolute inset-y-0 right-0 max-w-md w-full bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col" id="archives-panel">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gray-200 flex items-center justify-center text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg>
                </div>
                <h2 class="font-grotesk text-lg font-black text-[#0F143A]">Activités archivées</h2>
            </div>
            <button onclick="toggleArchives()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6 space-y-4">
            @foreach($archives as $archive)
                <div class="p-4 rounded-xl border border-gray-100 bg-white hover:border-gray-200 transition-colors flex items-center justify-between group shadow-sm">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $archive->est_stage ? 'bg-amber-50 text-amber-600' : 'bg-blue-50 text-blue-600' }}">
                                {{ $archive->est_stage ? 'Stage' : 'Activité' }}
                            </span>
                        </div>
                        <h3 class="font-bold text-sm text-[#0F143A]">{{ $archive->nom }}</h3>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $archive->nb_inscrits }} inscrits</p>
                    </div>

                    <form action="{{ route('activites.toggleArchive', $archive) }}" method="POST">
                        @csrf
                        <button type="submit" class="flex items-center gap-2 px-3 py-1.5 bg-gray-50 hover:bg-[#16987C] hover:text-white text-gray-500 text-xs font-bold rounded-lg transition-colors border border-gray-100 hover:border-[#16987C]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                            </svg>
                            Restaurer
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div id="dossiers-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" onclick="toggleModalDossier()"></div>

    <div class="absolute inset-y-0 right-0 max-w-sm w-full bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col" id="dossiers-panel">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-[#222A60]/10 flex items-center justify-center text-[#222A60]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                    </svg>
                </div>
                <h2 class="font-grotesk text-lg font-black text-[#0F143A]">Gérer les dossiers</h2>
            </div>
            <button onclick="toggleModalDossier()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6 space-y-4">

            <form action="{{ route('dossiers.store') }}" method="POST" class="flex gap-2">
                @csrf
                <input type="text" name="nom" placeholder="Nom du nouveau dossier..."
                    class="flex-1 px-3 py-2 bg-gray-50 border border-gray-100 rounded-xl text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-[#16987C]/30 focus:border-[#16987C]/40">
                <button type="submit"
                    class="px-4 py-2 bg-[#222A60] text-white text-sm font-bold rounded-xl hover:bg-[#1a2050] transition-colors">
                    Créer
                </button>
            </form>

            @if($dossiers->isEmpty())
                <p class="text-sm text-gray-400 text-center py-6">Aucun dossier créé</p>
            @else
                <div class="space-y-2">
                    @foreach($dossiers as $dossier)
                        <div class="flex items-center justify-between p-3 rounded-xl border border-gray-100 bg-white hover:border-gray-200 transition-colors group">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-lg bg-[#222A60]/8 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5 text-[#222A60]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-sm text-[#0F143A]">{{ $dossier->nom }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $dossier->nb_activites }} activité(s)</p>
                                </div>
                            </div>
                            <form action="{{ route('dossiers.destroy', $dossier) }}" method="POST"
                                onsubmit="return confirm('Supprimer ce dossier ? Les activités seront déplacées hors du dossier.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-gray-300 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function toggleArchives() {
        const modal = document.getElementById('archives-modal');
        const panel = document.getElementById('archives-panel');

        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
            setTimeout(() => panel.classList.remove('translate-x-full'), 10);
        } else {
            panel.classList.add('translate-x-full');
            setTimeout(() => modal.classList.add('hidden'), 300);
        }
    }

    function toggleModalDossier() {
        const modal = document.getElementById('dossiers-modal');
        const panel = document.getElementById('dossiers-panel');

        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
            setTimeout(() => panel.classList.remove('translate-x-full'), 10);
        } else {
            panel.classList.add('translate-x-full');
            setTimeout(() => modal.classList.add('hidden'), 300);
        }
    }
</script>

@endsection
