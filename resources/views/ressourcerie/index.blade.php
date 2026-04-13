@extends('layouts.app')

@section('title', 'Ressourcerie')

@section('content')

    <div class="flex items-center justify-between mb-6 pl-2">
        <div>
            <p class="text-sm text-gray-400 mt-1 font-medium">
                <span class="font-bold text-gray-600">{{ $ressourceries->count() }}</span> ressources disponibles
            </p>
        </div>
        @can('gerer-ressourcerie')
        <a href="{{ route('ressourcerie.create') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#16A37A] hover:bg-[#128a67] text-white text-sm font-bold rounded-xl transition-all shadow-sm hover:shadow-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
            Ajouter une ressource
        </a>
        @endcan
    </div>

    <div class="flex flex-wrap items-center gap-2 mb-4 pl-2">
        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mr-1">Tarif</span>
        <a href="{{ route('ressourcerie.index', array_merge(request()->except('type_tarif', 'page'), [])) }}"
            class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all
       {{ !$type ? 'bg-[#16A37A] text-white shadow-sm' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
            Tous
        </a>
        @foreach (\App\Models\Ressourcerie::TYPES_TARIF as $key => $label)
            <a href="{{ route('ressourcerie.index', array_merge(request()->except('type_tarif', 'page'), ['type_tarif' => $key])) }}"
                class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all
           {{ $type === $key ? 'bg-[#16A37A] text-white shadow-sm' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Recherche --}}
    <form method="GET" action="{{ route('ressourcerie.index') }}" class="mb-6 pl-2">
        @if ($type)
            <input type="hidden" name="type_tarif" value="{{ $type }}">
        @endif
        <div class="flex gap-2 max-w-sm">
            <input type="text" name="q" value="{{ $search }}" placeholder="Rechercher une ressource…"
                class="flex-1 px-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#16A37A]/30 focus:border-[#16A37A]">
            <button type="submit"
                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-bold rounded-xl transition-all">
                Chercher
            </button>
            @if ($search)
                <a href="{{ route('ressourcerie.index', $type ? ['type_tarif' => $type] : []) }}"
                    class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-500 text-sm font-bold rounded-xl transition-all">
                    Effacer
                </a>
            @endif
        </div>
    </form>

    @if (session('success'))
        <div class="mb-6 pl-2">
            <div
                class="flex items-center gap-3 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-medium rounded-xl">
                <svg class="w-4 h-4 shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    {{-- Table des ressources actives --}}
    @if ($ressourceries->isNotEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_24px_rgba(0,0,0,0.04)] overflow-hidden mb-8">
            <div class="h-1 bg-gradient-to-r from-[#16A37A] to-[#16A37A]/30"></div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/60">
                            <th class="px-5 py-3.5 text-left text-xs font-black text-gray-400 uppercase tracking-widest">Nom
                            </th>
                            <th class="px-5 py-3.5 text-left text-xs font-black text-gray-400 uppercase tracking-widest">
                                Description</th>
                            <th class="px-5 py-3.5 text-left text-xs font-black text-gray-400 uppercase tracking-widest">
                                Conditions de location</th>
                            <th class="px-5 py-3.5 text-left text-xs font-black text-gray-400 uppercase tracking-widest">
                                Prix</th>
                            <th class="px-5 py-3.5 text-left text-xs font-black text-gray-400 uppercase tracking-widest">
                                Type de tarif</th>
                            @can('gerer-ressourcerie')
                            <th class="px-5 py-3.5 text-right text-xs font-black text-gray-400 uppercase tracking-widest">
                                Actions</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($ressourceries as $item)
                            <tr class="hover:bg-gray-50/60 transition-colors group">
                                <td class="px-5 py-4">
                                    <span class="font-bold text-[#0F143A]">{{ $item->nom }}</span>
                                </td>
                                <td class="px-5 py-4 max-w-xs">
                                    @if ($item->description)
                                        <span class="text-gray-500 line-clamp-2">{{ $item->description }}</span>
                                    @else
                                        <span class="text-gray-300 italic text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 max-w-xs">
                                    @if ($item->condition_location)
                                        <span class="text-gray-500 line-clamp-2">{{ $item->condition_location }}</span>
                                    @else
                                        <span class="text-gray-300 italic text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span
                                        class="font-semibold {{ (float) $item->prix === 0.0 ? 'text-emerald-600' : 'text-gray-700' }}">
                                        {{ $item->prix_format }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    @php
                                        $colors = [
                                            'tarif_particulier' => 'bg-blue-50 text-blue-700',
                                            'tarif_structure' => 'bg-violet-50 text-violet-700',
                                            'tarif_scolaire' => 'bg-amber-50 text-amber-700',
                                        ];
                                    @endphp
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $colors[$item->type_tarif] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ \App\Models\Ressourcerie::TYPES_TARIF[$item->type_tarif] ?? $item->type_tarif }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    @can('gerer-ressourcerie')
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('ressourcerie.edit', $item) }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold rounded-lg transition-all">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Modifier
                                        </a>
                                        <form action="{{ route('ressourcerie.toggleArchive', $item) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-600 text-xs font-bold rounded-lg transition-all">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12a2 2 0 002 2h8a2 2 0 002-2L19 8m-9 4v6m4-6v6" />
                                                </svg>
                                                Archiver
                                            </button>
                                        </form>
                                    </div>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <p class="text-gray-400 text-sm font-medium">Aucune ressource trouvée.</p>
            @can('gerer-ressourcerie')
            <a href="{{ route('ressourcerie.create') }}"
                class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-[#16A37A] text-white text-sm font-bold rounded-xl hover:bg-[#128a67] transition-all">
                Ajouter la première ressource
            </a>
            @endcan
        </div>
    @endif

    @if ($archives->isNotEmpty())
        <div x-data="{ open: false }">
            <button @click="open = !open"
                class="flex items-center gap-2 text-sm font-bold text-gray-400 hover:text-gray-600 transition-colors mb-4 pl-2">
                <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-90' : ''" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg>
                Archives ({{ $archives->count() }})
            </button>

            <div x-show="open" x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div
                    class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_24px_rgba(0,0,0,0.04)] overflow-hidden opacity-70">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50/60">
                                    <th
                                        class="px-5 py-3.5 text-left text-xs font-black text-gray-400 uppercase tracking-widest">
                                        Nom</th>
                                    <th
                                        class="px-5 py-3.5 text-left text-xs font-black text-gray-400 uppercase tracking-widest">
                                        Description</th>
                                    <th
                                        class="px-5 py-3.5 text-left text-xs font-black text-gray-400 uppercase tracking-widest">
                                        Conditions de location</th>
                                    <th
                                        class="px-5 py-3.5 text-left text-xs font-black text-gray-400 uppercase tracking-widest">
                                        Prix</th>
                                    <th
                                        class="px-5 py-3.5 text-left text-xs font-black text-gray-400 uppercase tracking-widest">
                                        Type de tarif</th>
                                    @can('gerer-ressourcerie')
                                    <th
                                        class="px-5 py-3.5 text-right text-xs font-black text-gray-400 uppercase tracking-widest">
                                        Actions</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($archives as $item)
                                    <tr class="hover:bg-gray-50/60 transition-colors">
                                        <td class="px-5 py-4">
                                            <span class="font-bold text-gray-400 line-through">{{ $item->nom }}</span>
                                        </td>
                                        <td class="px-5 py-4 max-w-xs">
                                            <span
                                                class="text-gray-400 line-clamp-2">{{ $item->description ?? '—' }}</span>
                                        </td>
                                        <td class="px-5 py-4 max-w-xs">
                                            <span
                                                class="text-gray-400 line-clamp-2">{{ $item->condition_location ?? '—' }}</span>
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <span class="text-gray-400">{{ $item->prix_format }}</span>
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-400">
                                                {{ \App\Models\Ressourcerie::TYPES_TARIF[$item->type_tarif] ?? $item->type_tarif }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4">
                                            @can('gerer-ressourcerie')
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('ressourcerie.edit', $item) }}"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold rounded-lg transition-all">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    Modifier
                                                </a>
                                                <form action="{{ route('ressourcerie.toggleArchive', $item) }}"
                                                    method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 text-xs font-bold rounded-lg transition-all">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                        </svg>
                                                        Restaurer
                                                    </button>
                                                </form>
                                            </div>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection
