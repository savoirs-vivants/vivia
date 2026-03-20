<section class="flex min-h-screen bg-gray-50/50">
    <div class="flex-1 p-8">
        <div class="max-w-3xl mx-auto">

            {{-- HEADER --}}
            <div class="mb-8">
                <a href="{{ route('backoffice') }}"
                    class="inline-flex items-center gap-2 text-sm font-bold text-gray-400 hover:text-[#16A37A] transition-colors duration-200 mb-6">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5M12 19l-7-7 7-7" />
                    </svg>
                    Retour à l'équipe
                </a>
                <h1 class="font-grotesk font-black text-3xl text-gray-900 tracking-tight">Modifier l'utilisateur</h1>
                <p class="text-sm text-gray-500 font-medium mt-1">
                    Mise à jour des informations de <span class="font-bold text-gray-900">{{ $user->firstname }} {{ $user->name }}</span> ({{ $user->email }})
                </p>
            </div>

            <form wire:submit.prevent="save">

                {{-- CARD PRINCIPALE --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-8 mb-6 shadow-sm shadow-gray-100/50">

                    <div class="flex items-center gap-3 mb-8 pb-5 border-b border-gray-100">
                        <div class="w-10 h-10 rounded-xl bg-[#16A37A]/10 text-[#16A37A] flex items-center justify-center shrink-0 border border-[#16A37A]/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-black text-gray-900 font-grotesk tracking-tight">Informations du compte</h2>
                    </div>

                    <div class="flex flex-col gap-6">

                        {{-- LIGNE NOM / PRENOM --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[11px] font-bold tracking-widest uppercase text-gray-500 mb-2">Nom</label>
                                <input type="text" wire:model="form.name"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-900 font-medium transition-all duration-200 outline-none focus:bg-white focus:border-[#16A37A]/50 focus:ring-4 focus:ring-[#16A37A]/10 placeholder-gray-400">
                                @error('form.name')
                                    <p class="text-red-500 text-xs mt-1.5 font-bold">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold tracking-widest uppercase text-gray-500 mb-2">Prénom</label>
                                <input type="text" wire:model="form.firstname"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-900 font-medium transition-all duration-200 outline-none focus:bg-white focus:border-[#16A37A]/50 focus:ring-4 focus:ring-[#16A37A]/10 placeholder-gray-400">
                                @error('form.firstname')
                                    <p class="text-red-500 text-xs mt-1.5 font-bold">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- LIGNE EMAIL --}}
                        <div>
                            <label class="block text-[11px] font-bold tracking-widest uppercase text-gray-500 mb-2">Email <span class="text-red-500">*</span></label>
                            <input type="email" wire:model="form.email"
                                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-900 font-medium transition-all duration-200 outline-none focus:bg-white focus:border-[#16A37A]/50 focus:ring-4 focus:ring-[#16A37A]/10 placeholder-gray-400">
                            @error('form.email')
                                <p class="text-red-500 text-xs mt-1.5 font-bold">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- LIGNE ROLE --}}
                        <div>
                            <label class="block text-[11px] font-bold tracking-widest uppercase text-gray-500 mb-2">Niveau d'accès <span class="text-red-500">*</span></label>

                            @if (auth()->user()->role === 'admin')
                                <div class="relative">
                                    <select wire:model="form.role"
                                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-900 font-bold transition-all duration-200 outline-none focus:bg-white focus:border-[#16A37A]/50 focus:ring-4 focus:ring-[#16A37A]/10 cursor-pointer appearance-none">
                                        <option value="">-- Sélectionner un rôle --</option>
                                        <option value="lecteur">Lecteur</option>
                                        <option value="gestionnaire">Gestionnaire</option>
                                        <option value="admin">Administrateur</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>
                                @error('form.role')
                                    <p class="text-red-500 text-xs mt-1.5 font-bold">{{ $message }}</p>
                                @enderror
                            @else
                                <div class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 flex justify-between items-center cursor-not-allowed opacity-80">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full {{ $form->role === 'admin' ? 'bg-red-500' : ($form->role === 'gestionnaire' ? 'bg-gray-400' : 'bg-[#16A37A]') }}"></span>
                                        <span class="text-sm font-bold text-gray-700 capitalize">{{ $form->role === 'travailleur' ? 'Travailleur Social' : $form->role }}</span>
                                    </div>
                                    <span class="text-[9px] font-black uppercase tracking-widest bg-white border border-gray-200 px-2 py-1 rounded-md text-gray-400 shadow-sm">Verrouillé</span>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>

                {{-- BOUTONS D'ACTION --}}
                <div class="flex items-center justify-between pt-2">
                    <a href="{{ route('backoffice') }}"
                        class="inline-flex items-center gap-2 text-gray-500 hover:bg-white hover:border-gray-300 border border-transparent px-4 py-2.5 rounded-xl font-bold text-sm transition-all duration-200">
                        Annuler
                    </a>

                    <button type="submit" wire:loading.attr="disabled" wire:target="save"
                        class="inline-flex items-center gap-2 bg-[#16A37A] hover:bg-[#128a65] text-white px-6 py-3 rounded-xl font-bold text-sm transition-all duration-200 shadow-sm shadow-[#16A37A]/30 disabled:opacity-50 disabled:cursor-not-allowed group">

                        <svg wire:loading wire:target="save" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>

                        <svg wire:loading.remove wire:target="save" class="w-4 h-4 group-hover:-translate-y-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>

                        <span wire:loading.remove wire:target="save">Enregistrer</span>
                        <span wire:loading wire:target="save">En cours...</span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</section>
