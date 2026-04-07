@extends('layouts.app')

@section('title', 'Adhérents')

@section('content')

    <div x-data="adherentOverlay()" @keydown.escape.window="close()">

        @include('adherents._modal')

        <div class="pl-2 mb-0">
            <div class="flex items-center gap-1 bg-gray-100/80 p-1 rounded-xl w-fit">
                <a href="{{ route('adherents.index', ['tab' => 'payes'] + request()->except('tab', 'page')) }}"
                    class="flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-bold transition-all duration-200 {{ $tab === 'payes' ? 'bg-white text-[#222A60] shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    Adhérents
                    <span
                        class="px-2 py-0.5 rounded-full text-xs font-black {{ $tab === 'payes' ? 'bg-[#16987C]/10 text-[#16987C]' : 'bg-gray-200 text-gray-500' }}">{{ $countPayes }}</span>
                </a>
                @if (!empty($canVoirTousStatuts))
                    <a href="{{ route('adherents.index', ['tab' => 'partiel'] + request()->except('tab', 'page')) }}"
                        class="flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-bold transition-all duration-200 {{ $tab === 'partiel' ? 'bg-white text-[#222A60] shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                        Partiel
                        <span
                            class="px-2 py-0.5 rounded-full text-xs font-black {{ $tab === 'partiel' ? 'bg-amber-100 text-amber-600' : 'bg-gray-200 text-gray-500' }}">{{ $countPartiel }}</span>
                    </a>
                    <a href="{{ route('adherents.index', ['tab' => 'attente'] + request()->except('tab', 'page')) }}"
                        class="flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-bold transition-all duration-200 {{ $tab === 'attente' ? 'bg-white text-[#222A60] shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                        En attente
                        <span
                            class="px-2 py-0.5 rounded-full text-xs font-black {{ $tab === 'attente' ? 'bg-rose-100 text-rose-500' : 'bg-gray-200 text-gray-500' }}">{{ $countAttente }}</span>
                    </a>
                @endif
            </div>
        </div>

        @include('adherents._tables')

    </div>

@endsection
