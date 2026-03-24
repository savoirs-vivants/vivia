@extends('layouts.app')

@section('title', 'Journal de synchronisation')

@section('content')
<div class="max-w-5xl mx-auto">

    <div class="flex items-center justify-between mb-6 pl-1">
        <div class="flex items-center gap-2 text-xs text-gray-400">
            <a href="{{ url()->previous() }}" class="font-bold text-[#0F143A] hover:text-[#16987C] transition-colors">Mon compte</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
            <span class="text-gray-600 font-semibold">Journal des erreurs</span>
        </div>
    </div>

    <div class="bg-white rounded-[2rem] border border-gray-100 shadow-[0_4px_24px_rgba(0,0,0,0.04)] overflow-hidden min-h-[500px] flex flex-col">
        <div class="p-6 sm:p-8 border-b border-gray-50 bg-gray-50/30">
            <h1 class="font-grotesk text-2xl font-black text-[#0F143A] tracking-tight">Journal de synchronisation</h1>
            <p class="text-sm text-gray-400 mt-1">Historique des erreurs de communication d'API et Webhooks.</p>
        </div>

        <div class="flex-1 flex flex-col items-center justify-center p-10">
            <div class="w-20 h-20 bg-gray-50 rounded-[1.5rem] flex items-center justify-center mb-6 shadow-inner">
                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-black text-[#0F143A] mb-2">Aucune erreur détectée</h3>
            <p class="text-sm text-gray-400 text-center max-w-sm">Tout fonctionne parfaitement. Les services de synchronisation n'ont remonté aucune anomalie récente.</p>
        </div>
    </div>

</div>
@endsection
