@extends('layouts.app')

@section('title', 'Journal de synchronisation')

@section('content')
    <div class="max-w-5xl mx-auto">

        <div class="flex items-center justify-between mb-6 pl-1">
            <div class="flex items-center gap-2 text-xs text-gray-400">
                <a href="{{ url()->previous() }}" class="font-bold text-[#0F143A] hover:text-[#16987C] transition-colors">Mon
                    compte</a>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-gray-600 font-semibold">Journal des erreurs</span>
            </div>
        </div>

        <div
            class="bg-white rounded-[2rem] border border-gray-100 shadow-[0_4px_24px_rgba(0,0,0,0.04)] overflow-hidden min-h-[500px] flex flex-col">
            <div class="p-6 sm:p-8 border-b border-gray-50 bg-gray-50/30">
                <h1 class="font-grotesk text-2xl font-black text-[#0F143A] tracking-tight">Journal de synchronisation</h1>
                <p class="text-sm text-gray-400 mt-1">Historique des erreurs de communication d'API et Webhooks.</p>
            </div>

            @foreach ($logs as $log)
                <div
                    class="border {{ $log->status === 'error' ? 'border-red-500' : 'border-gray-200' }} p-4 mb-4 rounded-xl">
                    <div class="flex justify-between">
                        <span class="font-bold">Date : {{ $log->created_at->format('d/m/Y H:i') }}</span>

                        @if ($log->status === 'success')
                            <span class="text-green-600 bg-green-50 px-2 py-1 rounded">✅ Succès</span>
                        @elseif($log->status === 'warning')
                            <span class="text-amber-600 bg-amber-50 px-2 py-1 rounded">⚠️ Succès partiel</span>
                        @else
                            <span class="text-red-600 bg-red-50 px-2 py-1 rounded">❌ Échec</span>
                        @endif
                    </div>

                    <p class="mt-2">Paiements importés : <strong>{{ $log->payments_imported }}</strong></p>

                    @if (!empty($log->errors))
                        <div class="mt-3 p-3 bg-red-50 text-red-700 text-sm rounded-lg">
                            <ul class="list-disc pl-5">
                                @foreach ($log->errors as $erreur)
                                    <li>{{ $erreur }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

    </div>
@endsection
