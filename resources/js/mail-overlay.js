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

Vous pouvez [ajouter ici un lien vers un formulaire de procuration par exemple].

Je reste à votre disposition pour préparer ce moment important dans la vie de l’association.`;

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

    const fileInput = document.getElementById('pieces_jointes_input');
    const fileListDisplay = document.getElementById('file-list-display');

    const dataTransfer = new DataTransfer();

    if (fileInput) {
        fileInput.addEventListener('change', function() {
            Array.from(this.files).forEach(file => {
                if (dataTransfer.items.length < 5) {
                    let alreadyExists = false;
                    for (let i = 0; i < dataTransfer.files.length; i++) {
                        if (dataTransfer.files[i].name === file.name && dataTransfer.files[i].size === file.size) {
                            alreadyExists = true;
                            break;
                        }
                    }
                    if (!alreadyExists) {
                        dataTransfer.items.add(file);
                    }
                } else {
                    alert("Vous avez atteint la limite de 5 fichiers.");
                }
            });

            this.files = dataTransfer.files;

            updateFileDisplay();
        });
    }

    function updateFileDisplay() {
        fileListDisplay.innerHTML = '';

        Array.from(fileInput.files).forEach((file, index) => {
            const li = document.createElement('li');
            li.className = 'text-xs text-gray-600 flex items-center gap-3 bg-gray-50 p-2.5 rounded-xl border border-gray-100 shadow-sm';

            let sizeStr = file.size > 1024 * 1024 ? (file.size / (1024 * 1024)).toFixed(2) + ' Mo' : Math.round(file.size / 1024) + ' Ko';
            let sizeClass = file.size > 5 * 1024 * 1024 ? 'text-rose-500 font-bold' : 'text-gray-400';

            li.innerHTML = `
                <svg class="w-4 h-4 text-[#16A37A] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                </svg>
                <span class="font-medium truncate flex-1">${file.name}</span>
                <span class="shrink-0 ${sizeClass}">${sizeStr}</span>
                <button type="button" class="ml-2 text-gray-400 hover:text-rose-500 transition-colors" onclick="removeFile(${index})">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            `;
            fileListDisplay.appendChild(li);
        });
    }

    window.removeFile = function(index) {
        dataTransfer.items.remove(index);
        fileInput.files = dataTransfer.files;
        updateFileDisplay();
    };
});
