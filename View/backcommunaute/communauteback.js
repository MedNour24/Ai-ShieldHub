// ---------- Contrôle de saisie JS pour le formulaire ----------
document.getElementById("formPublication").addEventListener("submit", function(e){
    const messages = [];
    const texte = document.getElementById("textePublication").value.trim();
    const fileInput = document.getElementById("filePublication");
    const file = fileInput.files[0];

    if(!texte && !file){
        messages.push("Vous devez écrire un texte ou ajouter un fichier.");
    }

    if(texte.length > 200){
        messages.push("Le texte ne doit pas dépasser 200 caractères.");
    }

    if(file){
        const allowedExtensions = /(\.pdf|\.txt|\.docx|\.mp4|\.avi|\.mov)$/i;
        if(!allowedExtensions.exec(file.name)){
            messages.push("Le fichier doit être PDF, TXT, DOCX ou une vidéo.");
        }
    }

    if(messages.length > 0){
        e.preventDefault();
        document.getElementById("messages").innerHTML = messages.join("<br>");
    } else {
        document.getElementById("messages").innerHTML = "";
    }
});
