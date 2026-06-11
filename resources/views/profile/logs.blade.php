@extends('layouts.app')

@section('title', 'Journal de synchronisation')

@section('content')
    <div class="max-w-5xl mx-auto pb-12">

        <div class="flex items-center justify-between mb-6 pl-1">
            <div class="flex items-center gap-2 text-xs text-gray-400">
                <a href="{{ url()->previous() }}"
                    class="font-bold text-[#0F143A] hover:text-[#16987C] transition-colors duration-200">
                    Mon compte
                </a>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-gray-600 font-semibold">Journal de synchronisation</span>
            </div>
        </div>

        <div
            class="bg-white rounded-[2rem] border border-gray-100 shadow-[0_4px_24px_rgba(0,0,0,0.04)] overflow-hidden min-h-[500px] flex flex-col">

            <div class="p-6 sm:p-8 border-b border-gray-100 bg-gray-50/50">
                <h1 class="font-grotesk text-2xl font-black text-[#0F143A] tracking-tight">Journal de synchronisation</h1>
                <p class="text-sm text-gray-500 mt-1.5">Historique des communications d'API, Webhooks et imports manuels.
                </p>
            </div>

            <div class="p-6 sm:p-8 bg-white flex-1">
                @if ($logs->isEmpty())
                    <div class="flex flex-col items-center justify-center h-full text-center space-y-3 py-12">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-2">
                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                        </div>
                        <p class="text-gray-500 font-medium">Aucun journal de synchronisation disponible.</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($logs as $log)
                            <div
                                class="group relative border border-gray-100 rounded-2xl p-5 hover:border-gray-200 hover:shadow-sm transition-all duration-200 bg-white">

                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="flex items-center gap-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-semibold text-[#0F143A]">
                                                {{ $log->created_at->translatedFormat('d F Y \à H:i') }}
                                            </span>
                                            <span class="text-xs text-gray-400 mt-0.5 font-medium uppercase tracking-wider">
                                                Source : {{ $log->source ?? 'Non définie' }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <div
                                            class="text-sm text-gray-600 font-medium bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100">
                                            <span class="text-[#16987C] font-bold">{{ $log->payments_imported }}</span>
                                            paiement(s)
                                        </div>

                                        @if ($log->status === 'success')
                                            <span
                                                class="inline-flex items-center gap-1.5 text-green-700 bg-green-50 border border-green-100 text-xs font-bold px-3 py-1.5 rounded-full">
                                                <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div> Succès
                                            </span>
                                        @elseif(in_array($log->status, ['warning', 'partial']))
                                            <span
                                                class="inline-flex items-center gap-1.5 text-amber-700 bg-amber-50 border border-amber-100 text-xs font-bold px-3 py-1.5 rounded-full">
                                                <div class="w-1.5 h-1.5 rounded-full bg-amber-500"></div> Partiel
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1.5 text-red-700 bg-red-50 border border-red-100 text-xs font-bold px-3 py-1.5 rounded-full">
                                                <div class="w-1.5 h-1.5 rounded-full bg-red-500"></div> Échec
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @if (!empty($log->errors) && is_array($log->errors) && count($log->errors) > 0)
                                    <div class="mt-4 p-4 bg-red-50/50 border border-red-100 rounded-xl">
                                        <div class="flex items-start gap-3">
                                            <svg class="w-5 h-5 text-red-500 mt-0.5 shrink-0" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <div class="text-sm text-red-800 flex-1">
                                                <p class="font-semibold mb-1">Détails des erreurs :</p>
                                                <ul class="list-disc pl-4 space-y-1 marker:text-red-300">
                                                    @foreach ($log->errors as $erreur)
                                                        <li>{{ $erreur }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>

                                        <form method="POST" action="{{ route('profile.logs.destroy', $log) }}"
                                              onsubmit="return confirm('Marquer ce journal comme traité et le supprimer ?');"
                                              class="mt-3 flex justify-end">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-white hover:bg-red-50 border border-red-200 text-red-600 rounded-lg text-xs font-bold transition-all duration-150">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                J'ai bien vu l'erreur
                                            </button>
                                        </form>
                                    </div>
                                @endif

                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
@endsection
