@extends('layouts.app')

@section('title', 'Recherches Participatives')

@section('content')

    <div class="flex items-center justify-between mb-6 pl-2">
        <div>
            <p class="text-sm text-gray-400 mt-1 font-medium">
                <span class="font-bold text-gray-600">{{ $recherches->count() }}</span> projets en cours
            </p>
        </div>
        @can('gerer-recherche')
            <a href="{{ route('recherches.create') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#16A37A] hover:bg-[#128a67] text-white text-sm font-bold rounded-xl transition-all shadow-sm hover:shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                Ajouter un projet
            </a>
        @endcan
    </div>

    <form method="GET" action="{{ route('recherches.index') }}" class="mb-6 pl-2">
        <div class="flex gap-2 max-w-sm">
            <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Rechercher un projet…"
                class="flex-1 px-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#16A37A]/30 focus:border-[#16A37A]">
            <button type="submit"
                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-bold rounded-xl transition-all">
                Chercher
            </button>
            @if (!empty($search))
                <a href="{{ route('recherches.index') }}"
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

    @if ($recherches->isNotEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_24px_rgba(0,0,0,0.04)] overflow-hidden mb-8">
            <div class="h-1 bg-gradient-to-r from-[#16A37A] to-[#16A37A]/30"></div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/60">
                            <th class="px-5 py-3.5 text-left text-xs font-black text-gray-400 uppercase tracking-widest">Nom
                                du projet</th>
                            <th class="px-5 py-3.5 text-left text-xs font-black text-gray-400 uppercase tracking-widest">
                                Description</th>
                            <th class="px-5 py-3.5 text-left text-xs font-black text-gray-400 uppercase tracking-widest">
                                Gestionnaire</th>
                            <th class="px-5 py-3.5 text-right text-xs font-black text-gray-400 uppercase tracking-widest">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($recherches as $item)
                            <tr class="hover:bg-gray-50/60 transition-colors group">
                                <td class="px-5 py-4">
                                    <a href="{{ route('recherches.show', $item) }}"
                                        class="font-bold text-[#0F143A] hover:text-[#16A37A] transition-colors">
                                        {{ $item->nom }}
                                    </a>
                                </td>
                                <td class="px-5 py-4 max-w-xs">
                                    <span class="text-gray-500 line-clamp-2">{{ $item->description ?? '—' }}</span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-bold">
                                        @if ($item->gestionnaires->isNotEmpty())
                                            👤 {{ $item->gestionnaires->first()->firstname }}
                                            {{ $item->gestionnaires->first()->name }}
                                        @else
                                            👤 Non assigné
                                        @endif
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('recherches.show', $item) }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 text-xs font-bold rounded-lg transition-all">
                                            Voir adhérents
                                        </a>
                                        @can('gerer-recherche')
                                            <a href="{{ route('recherches.edit', $item) }}"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold rounded-lg transition-all">
                                                Modifier
                                            </a>
                                            <form action="{{ route('recherches.toggleArchive', $item) }}" method="POST"
                                                class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-600 text-xs font-bold rounded-lg transition-all">
                                                    Archiver
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
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
                <span class="text-2xl">🔬</span>
            </div>
            <p class="text-gray-400 text-sm font-medium">Aucun projet de recherche trouvé.</p>
        </div>
    @endif

    @if (isset($archives) && $archives->isNotEmpty())
        <div x-data="{ open: false }">
            <button @click="open = !open"
                class="flex items-center gap-2 text-sm font-bold text-gray-400 hover:text-gray-600 transition-colors mb-4 pl-2">
                <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-90' : ''" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg>
                Archives ({{ $archives->count() }})
            </button>

            <div x-show="open" style="display: none;">
                <div
                    class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_24px_rgba(0,0,0,0.04)] overflow-hidden opacity-70">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($archives as $item)
                                    <tr class="hover:bg-gray-50/60 transition-colors">
                                        <td class="px-5 py-4"><span
                                                class="font-bold text-gray-400 line-through">{{ $item->nom }}</span>
                                        </td>
                                        <td class="px-5 py-4 max-w-xs"><span
                                                class="text-gray-400 line-clamp-2">{{ $item->description ?? '—' }}</span>
                                        </td>
                                        <td class="px-5 py-4">
                                            @can('gerer-recherche')
                                                <div class="flex items-center justify-end gap-2">
                                                    <a href="{{ route('recherches.edit', $item) }}"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold rounded-lg transition-all">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                        Modifier
                                                    </a>
                                                    <form action="{{ route('recherches.toggleArchive', $item) }}"
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
