<div class="p-6 md:p-8 text-center">
    <div
        class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4 text-3xl bg-indigo-50 border border-indigo-100">
        ✨
    </div>
    <h2 class="text-2xl font-bold text-gray-900 mb-2">Bonne nouvelle !</h2>
    <p class="text-gray-500 mb-6">Nous avons retrouvé votre pré-inscription effectuée cet été.</p>

    <div class="bg-indigo-50/50 border border-indigo-100 rounded-xl p-5 text-left mb-6 max-w-sm mx-auto">
        <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-3">Détails de votre rentrée</p>

        <div class="mb-3">
            <span class="text-xs text-gray-500 block">Activité(s) réservée(s) :</span>
            <span class="text-sm font-bold text-[#0F143A]">{{ $preInscription->noms_activites }}</span>
        </div>

        <div class="flex justify-between items-center mb-2">
            <span class="text-sm text-gray-600">Total de l'adhésion</span>
            <span class="text-sm font-bold text-gray-900">{{ number_format($preInscription->montant, 2, ',', ' ') }}
                €</span>
        </div>
        <div class="flex justify-between items-center mb-4">
            <span class="text-sm text-emerald-600">Acompte déjà versé</span>
            <span class="text-sm font-bold text-emerald-600">- {{ number_format($totalVersePreInscrit, 2, ',', ' ') }}
                €</span>
        </div>

        <div class="w-full h-px bg-indigo-100 mb-3"></div>

        <div class="flex justify-between items-center">
            <span class="text-sm font-black text-indigo-900">Reste à régler</span>
            <span class="text-xl font-black text-amber-600">{{ number_format($resteAPayer, 2, ',', ' ') }} €</span>
        </div>
    </div>

    <form action="{{ route('adhesion.next', $token) }}" method="POST" class="space-y-3 max-w-sm mx-auto">
        @csrf
        <input type="hidden" name="current_step" value="16">

        <button type="submit" name="action_pre_inscription" value="pay_balance"
            class="w-full flex items-center justify-center gap-2 bg-[#16987C] hover:bg-[#138a6f] text-white text-sm font-bold px-4 py-3.5 rounded-xl transition-colors shadow-sm">
            💳 Régler le solde et valider
        </button>
    </form>
</div>
