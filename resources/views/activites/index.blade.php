@extends('layouts.app')

@section('title', 'Activités')

@section('content')

{{-- En-tête --}}
<div class="flex items-center justify-between mb-6 pl-2">
    <div>
        <p class="text-sm text-gray-400 mt-1 font-medium">
            <span class="font-bold text-gray-600">{{ $activites->count() }}</span> activités cette saison
        </p>
    </div>
    <a href="{{ route('activites.create') }}"
       class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#222A60] hover:bg-[#1a2050] text-white text-sm font-bold rounded-xl transition-all shadow-sm hover:shadow-md">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
        </svg>
        Ajouter une activité
    </a>
</div>

{{-- Filtres --}}
<div class="flex flex-wrap items-center gap-2 mb-6 pl-2">
    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mr-1">Filtrer</span>

     {{-- Type --}}
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

{{-- Grille --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-5">

    @forelse ($activites as $activite)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgba(0,0,0,0.08)] transition-all duration-200 group relative overflow-hidden flex flex-col">

            {{-- Badge type --}}
            <div class="absolute top-0 right-0">
                <span class="text-[10px] font-black uppercase px-3.5 py-1.5 rounded-bl-xl
                    {{ $activite->est_stage ? 'bg-amber-100 text-amber-600' : 'bg-[#222A60]/8 text-[#222A60]' }}">
                    {{ $activite->est_stage ? 'Stage' : 'Activité' }}
                </span>
            </div>

            <div class="p-5 flex flex-col flex-1">

                {{-- Icône --}}
                <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-4 transition-colors
                    {{ $activite->est_stage ? 'bg-amber-50 group-hover:bg-amber-100' : 'bg-[#222A60]/5 group-hover:bg-[#16987C]/10' }}">
                    @if($activite->est_stage)
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-[#222A60]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    @endif
                </div>

                {{-- Nom + lieu --}}
                <h3 class="font-grotesk font-black text-base text-[#0F143A] leading-tight mb-1 pr-12">
                    {{ $activite->nom }}
                </h3>
                <p class="text-xs text-gray-400 font-medium mb-1">
                    @if($activite->adresse) {{ $activite->adresse }} @endif
                    @if($activite->adresse && $activite->ville) · @endif
                    @if($activite->ville) {{ $activite->ville }} @endif
                </p>

                {{-- Horaires --}}
                @if(!empty($activite->horaires_list))
                    <div class="flex flex-wrap gap-1 mb-4">
                        @foreach($activite->horaires_list as $h)
                            <span class="inline-flex items-center px-2 py-0.5 bg-gray-100 text-gray-500 rounded-md text-[10px] font-semibold">
                                {{ $h }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <div class="mb-4"></div>
                @endif

                {{-- Stats + CTA --}}
                <div class="mt-auto flex items-end justify-between pt-4 border-t border-gray-50">
                    <div>
                        <div class="flex items-baseline gap-1">
                            <p class="font-grotesk text-3xl font-black text-[#222A60] leading-none">
                                {{ $activite->nb_inscrits }}
                            </p>
                            <p class="text-xs font-bold text-gray-400 uppercase">inscrits</p>
                        </div>
                        <p class="text-sm font-black text-[#0F143A] mt-1">{{ $activite->tarif_format }}</p>
                    </div>

                    <a href="{{ route('activites.show', $activite) }}"
                       class="w-9 h-9 rounded-xl flex items-center justify-center transition-all
                       bg-gray-50 text-gray-400 group-hover:bg-[#222A60] group-hover:text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full py-20 flex flex-col items-center gap-3 text-gray-300">
            <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <p class="font-bold text-gray-400">Aucune activité trouvée</p>
            @if($search)
                <p class="text-sm">pour « {{ $search }} »</p>
            @endif
        </div>
    @endforelse

    {{-- Carte Ajouter --}}
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

</div>

@endsection
