@extends('layouts.app')

@section('title', 'Modifier le profil')

@section('content')
<div class="max-w-3xl mx-auto">

    <div class="flex items-center gap-2 text-xs text-gray-400 mb-6 pl-1">
        <a href="{{ url()->previous() }}" class="font-bold text-[#0F143A] hover:text-[#16987C] transition-colors">Mon compte</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
        <span class="text-gray-600 font-semibold">Modification</span>
    </div>

    <div class="bg-white rounded-[2rem] border border-gray-100 shadow-[0_4px_24px_rgba(0,0,0,0.04)] overflow-hidden">
        <div class="h-2 bg-gradient-to-r from-[#222A60] via-[#16987C] to-[#16987C]/40"></div>
        <div class="p-8 sm:p-10">
            <h1 class="font-grotesk text-2xl font-black text-[#0F143A] tracking-tight mb-2">Modifier vos informations</h1>
            <p class="text-sm text-gray-400 mb-8">Mettez à jour votre nom, adresse email ou mot de passe.</p>

            <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Prénom</label>
                        <input type="text" name="firstname" value="{{ old('firstname', $user->firstname) }}" required class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm text-[#0F143A] focus:outline-none focus:border-[#16987C] focus:ring-1 focus:ring-[#16987C] transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Nom</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm text-[#0F143A] focus:outline-none focus:border-[#16987C] focus:ring-1 focus:ring-[#16987C] transition-all">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Adresse Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm text-[#0F143A] focus:outline-none focus:border-[#16987C] focus:ring-1 focus:ring-[#16987C] transition-all">
                    </div>
                </div>

                <div class="h-px bg-gray-100 my-8"></div>
                <h3 class="text-sm font-bold text-[#0F143A] mb-4">Sécurité (Optionnel)</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Nouveau mot de passe</label>
                        <input type="password" name="password" placeholder="Laisser vide pour ne pas modifier" class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm text-[#0F143A] focus:outline-none focus:border-[#222A60] focus:ring-1 focus:ring-[#222A60] transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Confirmer mot de passe</label>
                        <input type="password" name="password_confirmation" placeholder="Confirmer le nouveau" class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm text-[#0F143A] focus:outline-none focus:border-[#222A60] focus:ring-1 focus:ring-[#222A60] transition-all">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4 pt-8">
                    <a href="{{ url()->previous() }}" class="text-sm font-bold text-gray-400 hover:text-gray-600 transition-colors">Annuler</a>
                    <button type="submit" class="px-6 py-3 bg-[#222A60] hover:bg-[#1a2050] text-white text-sm font-bold rounded-xl transition-all shadow-sm">
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
