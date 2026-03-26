{{--
    Partial : sélection des classes
    Variables attendues :
      $selectedClasses (array) – classes déjà cochées
--}}
@php
    use App\Models\Activite;
    $niveaux = Activite::CLASSES_NIVEAUX;
    $selected = $selectedClasses ?? [];
@endphp

<div x-data="classesPicker({{ json_encode($selected) }})">
    <span class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Classes / niveaux</span>
    <div class="p-4 bg-gray-50/50 rounded-xl border border-gray-100 space-y-4">
        @foreach($niveaux as $niveau => $classes)
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">{{ $niveau }}</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($classes as $classe)
                        <button type="button"
                            @click="toggle('{{ $classe }}')"
                            :class="isSelected('{{ $classe }}')
                                ? 'bg-[#222A60] text-white border-[#222A60] shadow-sm'
                                : 'bg-white text-gray-500 border-gray-200 hover:border-gray-300 hover:text-gray-700'"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border-2 text-xs font-bold transition-all duration-150">
                            <svg x-show="isSelected('{{ $classe }}')" class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ $classe }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endforeach

        {{-- Champs cachés pour la soumission du formulaire --}}
        <template x-for="classe in selected" :key="classe">
            <input type="hidden" name="classes[]" :value="classe">
        </template>
    </div>
</div>

<script>
    function classesPicker(initial) {
        return {
            selected: initial || [],
            toggle(classe) {
                const idx = this.selected.indexOf(classe);
                if (idx === -1) {
                    this.selected.push(classe);
                } else {
                    this.selected.splice(idx, 1);
                }
            },
            isSelected(classe) {
                return this.selected.includes(classe);
            }
        }
    }
</script>
