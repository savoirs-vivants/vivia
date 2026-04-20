<div class="p-5 md:p-6 animate-[fadeIn_0.3s_ease-out]">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900">Projets de recherche 🔬</h2>
        <p class="text-gray-500 mt-1.5 text-sm leading-relaxed">
            Sélectionnez les programmes de recherche participative auxquels vous souhaitez contribuer. Vous pouvez en
            choisir plusieurs.
        </p>
    </div>

    <form action="{{ route('adhesion.next', $token) }}" method="POST">
        @csrf
        <input type="hidden" name="current_step" value="17">

        @if ($recherchesDispos->isEmpty())
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 text-center mb-6">
                <span class="text-3xl mb-3 block">📭</span>
                <p class="text-gray-500 font-medium text-sm">Vous êtes déjà inscrit(e) à tous les projets de recherche
                    disponibles, ou aucun n'est ouvert pour le moment.</p>
            </div>
        @else
            <div class="space-y-3 mb-8">
                @foreach ($recherchesDispos as $rech)
                    @php
                        $isChecked = in_array($rech->id, $formData['recherches_selectionnees'] ?? []);
                    @endphp
                    <label
                        class="relative flex items-start p-4 cursor-pointer rounded-xl border-2 transition-all duration-200 hover:bg-gray-50 {{ $isChecked ? 'border-teal-500 bg-teal-50/30' : 'border-gray-200' }}">
                        <div class="flex items-center h-5 mt-1">
                            <input type="checkbox" name="recherches_selectionnees[]" value="{{ $rech->id }}"
                                class="w-5 h-5 text-teal-600 bg-white border-gray-300 rounded focus:ring-teal-500 focus:ring-2"
                                {{ $isChecked ? 'checked' : '' }}
                                onchange="this.closest('label').classList.toggle('border-teal-500', this.checked); this.closest('label').classList.toggle('bg-teal-50/30', this.checked); this.closest('label').classList.toggle('border-gray-200', !this.checked);">
                        </div>

                        <div class="ml-4 flex-1">
                            <h3 class="text-base font-bold text-gray-900">{{ $rech->nom }}</h3>
                            <p class="text-sm text-gray-500 mt-2 leading-relaxed">{{ $rech->description }}</p>
                        </div>
                    </label>
                @endforeach
            </div>
        @endif
        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
            @if ($hasPrev)
                <a href="{{ route('adhesion.show', ['token' => $token, 'step' => $prevStep]) }}"
                    class="{{ $btnBack }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                    Précédent
                </a>
            @else
                <div></div>
            @endif
            <button type="submit" class="{{ $btn }}">
                Continuer
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
    </form>
</div>
