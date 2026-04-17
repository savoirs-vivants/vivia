<div id="overlay-mail-adherents"
    class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4 sm:p-6"
    style="display:none;">

    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl flex flex-col" style="max-height: 95vh;">

        <div class="px-6 py-5 sm:px-8 sm:py-6 border-b border-gray-100 flex items-center justify-between shrink-0">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Communication</p>
                <h2 class="font-grotesk font-black text-xl sm:text-2xl text-gray-900">Email aux adhérents</h2>
            </div>
            <button type="button" onclick="document.getElementById('overlay-mail-adherents').style.display='none';"
                class="w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="{{ route('dashboard.send-mail') }}" method="POST" enctype="multipart/form-data"
            class="flex flex-col flex-1 overflow-hidden">
            @csrf

            <div class="px-6 py-5 sm:px-8 sm:py-6 space-y-6 overflow-y-auto flex-1">

                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">
                        🏷️ Type de communication <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="type_mail" value="info" class="peer sr-only" checked>
                            <div
                                class="p-4 rounded-xl border-2 border-gray-100 bg-gray-50 peer-checked:border-[#16A37A] peer-checked:bg-teal-50 transition-all text-center">
                                <span class="block text-sm font-bold text-gray-900 mb-1">Information</span>
                                <span class="block text-xs text-gray-500">Info générale</span>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="type_mail" value="ag" class="peer sr-only">
                            <div
                                class="p-4 rounded-xl border-2 border-gray-100 bg-gray-50 peer-checked:border-[#222A60] peer-checked:bg-[#222A60]/5 transition-all text-center">
                                <span class="block text-sm font-bold text-gray-900 mb-1">Convocation AG</span>
                                <span class="block text-xs text-gray-500">Assemblée Générale</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label for="objet" class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">
                        📌 Objet du mail <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="objet" name="objet" required
                        class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-[#16A37A] focus:border-[#16A37A] block p-3"
                        placeholder="Saisissez l'objet du mail">
                </div>

                <div>
                    <label for="message" class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">
                        📝 Message <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="message" name="message" rows="8" required
                        class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-[#16A37A] focus:border-[#16A37A] block p-3 resize-y"
                        placeholder="Rédigez votre message ici... (Les retours à la ligne seront conservés)"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">
                        📎 Pièces jointes (Optionnel)
                    </label>
                    <input type="file" name="piece_jointe"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-[#16A37A]/10 file:text-[#16A37A] hover:file:bg-[#16A37A]/20 transition-all">
                    <p class="mt-1.5 text-xs text-gray-400">Max 5 fichiers (5 Mo/fichier). Formats conseillés: PDF, JPG,
                        PNG.</p>
                </div>

            </div>

            <div class="px-6 py-5 sm:px-8 sm:py-6 border-t border-gray-100 shrink-0">
                <button type="submit"
                    class="w-full bg-[#083325] hover:bg-[#16A37A] text-white font-grotesk font-bold py-4 rounded-xl text-base transition-all duration-300 flex items-center justify-center gap-2 shadow-md hover:shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    Envoyer à tous les adhérents
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const radios = document.querySelectorAll('input[name="type_mail"]');
        const inputObjet = document.getElementById('objet');
        const textareaMessage = document.getElementById('message');

        const templateAgObjet = "Invitation à l'Assemblée Générale de l’Association Savoirs Vivants";

        const templateAgMessage = `L’association Savoirs Vivants organise son Assemblée Générale qui se tiendra le [DATE ET LIEU].

Votre présence est nécessaire pour permettre le bon fonctionnement démocratique de l’association mais aussi pour participer à définir les orientations futures de l’association.

Voici l’ordre du jour qui sera proposé lors de notre Assemblée :
- Validation du compte rendu de l’Assemblée Générale précédente
- Vote du rapport moral
- Vote du rapport du trésorier
- Présentation du rapport d’activités
- Verre de l’amitié

Je reste à votre disposition pour préparer ce moment important dans la vie de l’association.

Laure Froehlig

Présidente`;

        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'ag') {
                    inputObjet.value = templateAgObjet;
                    textareaMessage.value = templateAgMessage;
                } else if (this.value === 'info') {
                    inputObjet.value = "";
                    textareaMessage.value = "";
                }
            });
        });
    });
</script>
