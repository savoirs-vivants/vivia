<div x-data="{ open: @entangle('isOpen') }">

    <button wire:click="openModal"
        class="inline-flex items-center gap-2 bg-sv-green hover:bg-sv-green/90 text-white font-bold text-sm px-4 py-2 rounded-xl transition-all duration-200 shadow-sm shadow-sv-green/20 hover-scale">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
        </svg>
        Ajouter un utilisateur
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-sv-blue/60 backdrop-blur-md">

        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            @click.outside="open = false; $wire.closeModal()"
            class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden">

            <div class="stagger-1 relative bg-gradient-to-br from-sv-blue via-sv-blue to-sv-green/80 px-8 pt-8 pb-10 overflow-hidden">
                <button wire:click="closeModal"
                    class="absolute top-4 right-4 w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 text-white/70 hover:text-white flex items-center justify-center transition-all duration-150 z-10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <div class="relative z-10 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-sv-green flex items-center justify-center shadow-lg shadow-sv-green/30 shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-white/50 text-xs font-bold uppercase tracking-widest mb-0.5">Administration</p>
                        <h2 class="text-white font-bold text-xl leading-tight">Nouvel utilisateur</h2>
                    </div>
                </div>

                <div class="relative z-10 flex flex-wrap gap-2 mt-5">
                    <span class="inline-flex items-center gap-1.5 bg-white/10 text-white/70 text-xs font-medium px-3 py-1.5 rounded-full">
                        <div class="w-1.5 h-1.5 rounded-full bg-sv-green animate-pulse-gentle"></div>
                        Invitation par email
                    </span>
                    <span class="inline-flex items-center gap-1.5 bg-white/10 text-white/70 text-xs font-medium px-3 py-1.5 rounded-full">
                        <div class="w-1.5 h-1.5 rounded-full bg-sv-green animate-pulse-gentle"></div>
                        Mot de passe libre
                    </span>
                </div>
            </div>

            <div class="-mt-4 relative z-10">
                <svg viewBox="0 0 400 20" class="w-full" preserveAspectRatio="none" style="height:20px">
                    <path d="M0,0 C100,20 300,20 400,0 L400,20 L0,20 Z" fill="white"/>
                </svg>
            </div>

            <form wire:submit.prevent="save" class="px-8 pb-8 -mt-1">
                <div class="space-y-4">

                    <div class="stagger-2 grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Nom</label>
                            <input type="text" wire:model="form.name" placeholder="Nom"
                                class="w-full border-2 border-gray-100 focus:border-sv-green bg-gray-50 focus:bg-white rounded-xl px-3.5 py-2.5 outline-none text-sm font-semibold text-gray-800 transition-all duration-200 placeholder-gray-300">
                            @error('form.name')
                                <p class="text-red-500 text-xs flex items-center gap-1">
                                    <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Prénom</label>
                            <input type="text" wire:model="form.firstname" placeholder="Prénom"
                                class="w-full border-2 border-gray-100 focus:border-sv-green bg-gray-50 focus:bg-white rounded-xl px-3.5 py-2.5 outline-none text-sm font-semibold text-gray-800 transition-all duration-200 placeholder-gray-300">
                            @error('form.firstname')
                                <p class="text-red-500 text-xs flex items-center gap-1">
                                    <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <div class="stagger-3 space-y-1.5">
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Email <span class="text-sv-green normal-case tracking-normal font-semibold">*</span>
                        </label>
                        <div class="relative">
                            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-300 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <input type="email" wire:model="form.email" placeholder="exemple@example.fr"
                                class="w-full border-2 border-gray-100 focus:border-sv-green bg-gray-50 focus:bg-white rounded-xl pl-10 pr-3.5 py-2.5 outline-none text-sm font-semibold text-gray-800 transition-all duration-200 placeholder-gray-300">
                        </div>
                        @error('form.email')
                            <p class="text-red-500 text-xs flex items-center gap-1">
                                <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="stagger-4 grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                                Rôle <span class="text-sv-green normal-case tracking-normal font-semibold">*</span>
                            </label>
                            <select wire:model="form.role"
                                class="w-full border-2 border-gray-100 focus:border-sv-green bg-gray-50 focus:bg-white rounded-xl px-3.5 py-2.5 outline-none text-sm font-semibold text-gray-800 transition-all duration-200 cursor-pointer">
                                <option value="">— Choisir —</option>
                                <option value="lecteur">Lecteur</option>
                                @if (auth()->user()->role === 'admin')
                                    <option value="gestionnaire">Gestionnaire</option>
                                    <option value="admin">Administrateur</option>
                                @endif
                            </select>
                            @error('form.role')
                                <p class="text-red-500 text-xs flex items-center gap-1">
                                    <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                </div>

                <div class="stagger-5 flex items-center justify-between pt-6 mt-6 border-t border-gray-100">
                    <button type="button" wire:click="closeModal" wire:loading.attr="disabled"
                        class="text-sm font-bold text-gray-400 hover:text-gray-600 transition-colors duration-150 disabled:opacity-50">
                        Annuler
                    </button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="save"
                        class="inline-flex items-center gap-2.5 bg-sv-green hover:bg-sv-green/90 active:scale-95 text-white font-bold text-sm px-6 py-3 rounded-2xl transition-all duration-150 shadow-lg shadow-sv-green/25 disabled:opacity-70">
                        <svg wire:loading wire:target="save" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                        </svg>
                        <svg wire:loading.remove wire:target="save" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span wire:loading.remove wire:target="save">Envoyer l'invitation</span>
                        <span wire:loading wire:target="save">Envoi en cours…</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
