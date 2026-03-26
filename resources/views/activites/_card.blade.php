<div class="bg-white rounded-2xl border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgba(0,0,0,0.08)] transition-all duration-200 group relative overflow-hidden flex flex-col">

    <div class="absolute top-0 right-0 flex items-center">
        <form action="{{ route('activites.toggleArchive', $activite) }}" method="POST" class="opacity-0 group-hover:opacity-100 transition-opacity">
            @csrf
            <button type="submit" class="p-2 text-gray-300 hover:text-rose-500 transition-colors" title="Archiver cette activité">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                </svg>
            </button>
        </form>

        <span class="text-[10px] font-black uppercase px-3.5 py-1.5 rounded-bl-xl
            {{ $activite->est_stage ? 'bg-amber-100 text-amber-600' : 'bg-[#222A60]/8 text-[#222A60]' }}">
            {{ $activite->est_stage ? 'Stage' : 'Activité' }}
        </span>
    </div>

    <div class="p-5 flex flex-col flex-1">

        <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-4 transition-colors
            {{ $activite->est_stage ? 'bg-amber-50 group-hover:bg-amber-100' : 'bg-[#222A60]/5 group-hover:bg-[#16987C]/10' }}">
            @if($activite->est_stage)
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            @else
                <svg class="w-5 h-5 text-[#222A60]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            @endif
        </div>

        <h3 class="font-grotesk font-black text-base text-[#0F143A] leading-tight mb-1 pr-12">
            {{ $activite->nom }}
        </h3>
        <p class="text-xs text-gray-400 font-medium mb-1">
            @if($activite->adresse) {{ $activite->adresse }} @endif
            @if($activite->adresse && $activite->ville) · @endif
            @if($activite->ville) {{ $activite->ville }} @endif
        </p>

        @if(!empty($activite->horaires_list))
            <div class="flex flex-wrap gap-1 mb-4">
                @foreach($activite->horaires_list as $h)
                    <span class="inline-flex items-center px-2 py-0.5 bg-gray-100 text-gray-500 rounded-md text-[10px] font-semibold">
                        {{ $h }}
                    </span>
                @endforeach
            </div>
        @else
            <div class="mb-4"></div>
        @endif

        <div class="mt-auto flex items-end justify-between pt-4 border-t border-gray-50">
            <div>
                <div class="flex items-baseline gap-1">
                    <p class="font-grotesk text-3xl font-black text-[#222A60] leading-none">
                        {{ $activite->nb_inscrits }}
                    </p>
                    <p class="text-xs font-bold text-gray-400 uppercase">inscrits</p>
                </div>
                <p class="text-sm font-black text-[#0F143A] mt-1">{{ $activite->tarif_format }}</p>
            </div>

            <a href="{{ route('activites.show', $activite) }}"
               class="w-9 h-9 rounded-xl flex items-center justify-center transition-all
               bg-gray-50 text-gray-400 group-hover:bg-[#222A60] group-hover:text-white">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        </div>
    </div>
</div>
