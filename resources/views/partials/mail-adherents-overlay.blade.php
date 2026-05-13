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
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="type_mail" value="info" class="peer sr-only" checked>
                            <div
                                class="p-4 rounded-xl border-2 border-gray-100 bg-gray-50 peer-checked:border-[#16A37A] peer-checked:bg-teal-50 transition-all text-center h-full flex flex-col justify-center">
                                <span class="block text-sm font-bold text-gray-900 mb-1">Information</span>
                                <span class="block text-xs text-gray-500">Générale</span>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="type_mail" value="ag" class="peer sr-only">
                            <div
                                class="p-4 rounded-xl border-2 border-gray-100 bg-gray-50 peer-checked:border-[#222A60] peer-checked:bg-[#222A60]/5 transition-all text-center h-full flex flex-col justify-center">
                                <span class="block text-sm font-bold text-gray-900 mb-1">Convocation AG</span>
                                <span class="block text-xs text-gray-500">Assemblée Générale</span>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="type_mail" value="bulletin" class="peer sr-only">
                            <div
                                class="p-4 rounded-xl border-2 border-gray-100 bg-gray-50 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 transition-all text-center h-full flex flex-col justify-center">
                                <span class="block text-sm font-bold text-gray-900 mb-1">Bulletin</span>
                                <span class="block text-xs text-gray-500">Ciblé par thème</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div id="bloc-cible-bulletin"
                    class="hidden bg-indigo-50/50 border border-indigo-100 rounded-xl p-4 mt-4">
                    <label for="cible_bulletin"
                        class="block text-xs font-black text-indigo-800 uppercase tracking-widest mb-2">
                        🎯 Quel thème envoyer ? <span class="text-rose-500">*</span>
                    </label>
                    <select name="cible_bulletin" id="cible_bulletin"
                        class="w-full bg-white border border-indigo-200 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-3 outline-none">
                        <option value="general">Association (Infos générales)</option>
                        <option value="creabot">Créabot</option>
                        <option value="schlouk_sciences">Schlouk de sciences</option>
                    </select>
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

                    <input type="file" id="pieces_jointes_input" name="pieces_jointes[]" multiple
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-[#16A37A]/10 file:text-[#16A37A] hover:file:bg-[#16A37A]/20 transition-all">
                    <p class="mt-1.5 text-xs text-gray-400">Max 5 fichiers (5 Mo/fichier). Formats conseillés: PDF, JPG,
                        PNG.</p>

                    <ul id="file-list-display" class="mt-3 space-y-2"></ul>
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

<script src="{{ asset('js/mail-overlay.js') }}?v={{ time() }}"></script>
