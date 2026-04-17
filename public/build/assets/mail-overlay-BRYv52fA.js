document.addEventListener(`DOMContentLoaded`,function(){let e=document.querySelectorAll(`input[name="type_mail"]`),t=document.getElementById(`objet`),n=document.getElementById(`message`);e.forEach(e=>{e.addEventListener(`change`,function(){this.value===`ag`?(t.value=`Invitation à l'Assemblée Générale de l’Association Savoirs Vivants`,n.value=`L’association Savoirs Vivants organise son Assemblée Générale qui se tiendra le [DATE ET LIEU].

Votre présence est nécessaire pour permettre le bon fonctionnement démocratique de l’association mais aussi pour participer à définir les orientations futures de l’association.

Voici l’ordre du jour qui sera proposé lors de notre Assemblée :
- Validation du compte rendu de l’Assemblée Générale précédente
- Vote du rapport moral
- Vote du rapport du trésorier
- Présentation du rapport d’activités
- Verre de l’amitié

Vous pouvez [ajouter ici un lien vers un formulaire de procuration par exemple].

Je reste à votre disposition pour préparer ce moment important dans la vie de l’association.`):this.value===`info`&&(t.value=``,n.value=``)})});let r=document.getElementById(`pieces_jointes_input`),i=document.getElementById(`file-list-display`),a=new DataTransfer;r&&r.addEventListener(`change`,function(){Array.from(this.files).forEach(e=>{if(a.items.length<5){let t=!1;for(let n=0;n<a.files.length;n++)if(a.files[n].name===e.name&&a.files[n].size===e.size){t=!0;break}t||a.items.add(e)}else alert(`Vous avez atteint la limite de 5 fichiers.`)}),this.files=a.files,o()});function o(){i.innerHTML=``,Array.from(r.files).forEach((e,t)=>{let n=document.createElement(`li`);n.className=`text-xs text-gray-600 flex items-center gap-3 bg-gray-50 p-2.5 rounded-xl border border-gray-100 shadow-sm`;let r=e.size>1024*1024?(e.size/(1024*1024)).toFixed(2)+` Mo`:Math.round(e.size/1024)+` Ko`,a=e.size>5*1024*1024?`text-rose-500 font-bold`:`text-gray-400`;n.innerHTML=`
                <svg class="w-4 h-4 text-[#16A37A] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                </svg>
                <span class="font-medium truncate flex-1">${e.name}</span>
                <span class="shrink-0 ${a}">${r}</span>
                <button type="button" class="ml-2 text-gray-400 hover:text-rose-500 transition-colors" onclick="removeFile(${t})">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            `,i.appendChild(n)})}window.removeFile=function(e){a.items.remove(e),r.files=a.files,o()}});