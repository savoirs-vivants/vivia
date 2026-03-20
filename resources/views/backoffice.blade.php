@extends('layouts.app')

@section('title', 'Utilisateurs')

@section('content')

    <div class="flex flex-col h-full gap-5" x-data="selectionTable({{ collect($users->items())->pluck('id')->toJson() }})">

        <div class="flex items-center justify-between">

            <div class="flex items-center gap-3 w-full">

                <div class="flex items-center gap-2 flex-1">
                    <form action="{{ route('backoffice') }}" method="GET" id="search-form" class="flex items-center gap-2">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" name="search" value="{{ $search ?? '' }}"
                                placeholder="Rechercher un membre..."
                                class="w-64 bg-white border border-gray-200 rounded-xl pl-10 pr-8 py-2.5 text-sm font-medium text-gray-700 placeholder-gray-400 outline-none focus:ring-2 focus:ring-sv-blue/10 focus:border-sv-blue/30 transition-all shadow-sm">
                            @if (!empty($search))
                                <a href="{{ route('backoffice') }}"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 bg-gray-100 hover:bg-gray-200 p-1 rounded-full transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </a>
                            @endif
                        </div>

                        <select name="per_page" onchange="this.form.submit()"
                            class="bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-bold text-gray-500 outline-none focus:ring-2 focus:ring-sv-blue/10 cursor-pointer shadow-sm">
                            @foreach ([5, 10, 25, 50] as $val)
                                <option value="{{ $val }}"
                                    {{ isset($perPage) && $perPage == $val ? 'selected' : '' }}>{{ $val }} / page
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <div class="flex items-center gap-3">
                    <div x-show="selected.length > 0" x-cloak x-transition
                        class="flex items-center gap-3 bg-sv-blue rounded-xl px-4 py-2 shadow-lg shadow-sv-blue/20">
                        <div class="w-7 h-7 rounded-lg bg-sv-green flex items-center justify-center shrink-0 shadow-sm">
                            <span class="font-black text-sv-blue text-xs" x-text="selected.length"></span>
                        </div>
                        <span class="text-sm font-bold text-white/90 tracking-wide">sélectionnés</span>
                        <div class="w-px h-5 bg-white/20 mx-1"></div>
                        <button type="button" @click="selected = []"
                            class="text-sm font-bold text-white/60 hover:text-white transition-colors">
                            Annuler
                        </button>
                        <button type="button" @click="confirmBulkDelete = true"
                            class="flex items-center gap-1.5 text-sm font-bold text-sv-blue bg-white hover:bg-red-50 hover:text-red-600 px-3 py-1.5 rounded-lg transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Supprimer
                        </button>
                    </div>

                    <div>
                        @livewire('create-user')
                    </div>
                </div>

            </div>
        </div>

        <div class="flex-1 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">

            @if ($users->isNotEmpty())

                <div class="grid grid-cols-12 gap-4 px-6 py-4 border-b border-gray-100 bg-gray-50/80">
                    <div class="col-span-1 flex items-center">
                        <button @click="toggleAll()"
                            class="w-5 h-5 rounded-md border-2 flex items-center justify-center transition-all"
                            :class="allSelected ? 'bg-sv-blue border-sv-blue' : 'border-gray-300 hover:border-sv-blue/50'">
                            <svg x-show="allSelected" class="w-3 h-3 text-white" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                            <div x-show="someSelected && !allSelected" class="w-2.5 h-0.5 bg-sv-blue rounded-full"></div>
                        </button>
                    </div>
                    <div
                        class="col-span-4 text-[11px] font-black text-gray-400 uppercase tracking-widest flex items-center">
                        Membre</div>
                    <div
                        class="col-span-3 text-[11px] font-black text-gray-400 uppercase tracking-widest flex items-center">
                        Email</div>
                    <div
                        class="col-span-3 text-[11px] font-black text-gray-400 uppercase tracking-widest flex items-center">
                        Rôle</div>
                    <div class="col-span-1"></div>
                </div>

                <div class="divide-y divide-gray-50 flex-1 overflow-y-auto">
                    @foreach ($users as $user)
                        @php
                            $initials = strtoupper(
                                substr($user->firstname ?? '', 0, 1) . substr($user->name ?? '', 0, 1),
                            );
                            $avatarBg = match ($user->role) {
                                'admin' => 'bg-red-100 text-red-600',
                                'gestionnaire' => 'bg-sv-blue/10 text-sv-blue',
                                default => 'bg-sv-green/15 text-sv-green',
                            };
                            $rolePill = match ($user->role) {
                                'admin' => 'bg-red-50 text-red-600 border-red-100',
                                'gestionnaire' => 'bg-sv-blue/5 text-sv-blue border-sv-blue/10',
                                default => 'bg-sv-green/10 text-sv-green border-sv-green/15',
                            };
                            $roleDot = match ($user->role) {
                                'admin' => 'bg-red-500',
                                'gestionnaire' => 'bg-sv-blue',
                                default => 'bg-sv-green',
                            };
                            $roleLabel = match ($user->role) {
                                'admin' => 'Administrateur',
                                'gestionnaire' => 'Gestionnaire',
                                default => 'Lecteur',
                            };
                        @endphp

                        <div class="grid grid-cols-12 gap-4 items-center px-6 py-4 transition-colors group cursor-pointer"
                            :class="selected.includes({{ $user->id }}) ? 'bg-sv-blue/[0.03]' : 'hover:bg-gray-50/70'"
                            x-data="{ confirmDelete: false }">

                            <div class="col-span-1">
                                <button @click.stop="toggle({{ $user->id }})"
                                    class="w-5 h-5 rounded-md border-2 flex items-center justify-center transition-all"
                                    :class="selected.includes({{ $user->id }}) ? 'bg-sv-blue border-sv-blue' :
                                        'border-gray-200 group-hover:border-gray-300'">
                                    <svg x-show="selected.includes({{ $user->id }})" class="w-3 h-3 text-white"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                            </div>

                            <div class="col-span-4 flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-sm shrink-0 {{ $avatarBg }}">
                                    {{ $initials ?: strtoupper(substr($user->email, 0, 2)) }}
                                </div>
                                <div>
                                    <p
                                        class="font-grotesk font-bold text-sm text-gray-900 group-hover:text-sv-blue transition-colors leading-tight">
                                        {{ $user->firstname }} {{ $user->name }}
                                    </p>
                                </div>
                            </div>

                            <div class="col-span-3">
                                <a href="mailto:{{ $user->email }}" @click.stop
                                    class="text-sm font-medium text-gray-500 hover:text-sv-blue transition-colors truncate block max-w-[200px]">
                                    {{ $user->email }}
                                </a>
                            </div>

                            <div class="col-span-3">
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold border {{ $rolePill }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $roleDot }}"></span>
                                    {{ $roleLabel }}
                                </span>
                            </div>

                            <div class="col-span-1 flex items-center justify-end relative">
                                <div x-show="!confirmDelete"
                                    class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-all duration-200">
                                    <a href="{{ route('user.edit', $user) }}" @click.stop
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-sv-blue hover:bg-sv-blue/10 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </a>
                                    <button @click.stop="confirmDelete = true"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>

                                <div x-show="confirmDelete" x-cloak @click.outside="confirmDelete = false"
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    class="absolute right-0 flex items-center gap-2 bg-white rounded-xl shadow-xl shadow-black/5 border border-gray-100 p-2 z-10">
                                    <form action="{{ route('backoffice.destroy', $user) }}" method="POST" @click.stop>
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="text-xs font-black uppercase tracking-wide text-white bg-red-500 hover:bg-red-600 rounded-lg px-3 py-2 transition-colors">
                                            Confirmer
                                        </button>
                                    </form>
                                    <button @click.stop="confirmDelete = false"
                                        class="text-xs font-black uppercase tracking-wide text-gray-500 hover:text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg px-3 py-2 transition-colors">
                                        Annuler
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($users->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                        {{ $users->links('components.pagination-fr') }}
                    </div>
                @endif
            @else
                <div class="flex-1 flex flex-col items-center justify-center p-12 text-center">
                    <div
                        class="w-20 h-20 rounded-3xl bg-gray-50 border border-gray-100 flex items-center justify-center mb-5 shadow-inner">
                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="font-grotesk font-black text-gray-900 text-xl mb-2">Aucun utilisateur trouvé</h3>
                    <p class="text-sm font-medium text-gray-400 max-w-sm mx-auto">
                        @if (!empty($search))
                            Il n'y a aucun résultat pour "<span class="text-gray-700">{{ $search }}</span>". Essayez
                            de modifier vos critères de recherche.
                        @else
                            La base de données est vide. Commencez par ajouter un nouvel utilisateur.
                        @endif
                    </p>

                    @if (!empty($search))
                        <a href="{{ route('backoffice') }}"
                            class="mt-6 inline-flex items-center gap-2 text-sm font-bold text-sv-blue bg-sv-blue/5 hover:bg-sv-blue/10 px-5 py-2.5 rounded-xl transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Effacer la recherche
                        </a>
                    @endif
                </div>
            @endif

        </div>

        <div x-show="confirmBulkDelete" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-sv-blue/30 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100">

            <div class="bg-white rounded-3xl shadow-2xl shadow-black/10 w-full max-w-sm mx-4 p-8 border border-gray-100 relative overflow-hidden"
                @click.outside="confirmBulkDelete = false" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0">

                <div class="w-12 h-12 rounded-2xl bg-red-50 border border-red-100 flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>

                <h3 class="font-grotesk font-black text-xl text-sv-blue mb-2">Supprimer la sélection</h3>
                <p class="text-sm text-gray-500 font-medium leading-relaxed mb-8">
                    Vous allez supprimer définitivement
                    <span class="font-black text-sv-blue px-1.5 py-0.5 bg-sv-blue/5 rounded-md"
                        x-text="selected.length"></span>
                    compte(s). Cette action est irréversible.
                </p>

                <div class="flex gap-3">
                    <button @click="confirmBulkDelete = false"
                        class="flex-1 py-3 rounded-xl font-grotesk font-bold text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">
                        Annuler
                    </button>
                    <button @click="submitBulkDelete('{{ route('backoffice.destroyMultiple') }}')"
                        class="flex-1 py-3 rounded-xl font-grotesk font-bold text-sm text-white bg-red-500 hover:bg-red-600 transition-colors shadow-lg shadow-red-500/20">
                        Supprimer
                    </button>
                </div>

            </div>
        </div>

    </div>

@endsection
