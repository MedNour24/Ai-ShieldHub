/* ---------- AFFICHAGE DES MENUS ---------- */

document.getElementById("btnMesPublications").onclick = function () {
    document.getElementById("mes-publications-section").style.display = "block";
    document.getElementById("feed-section").style.display = "none";
};

document.getElementById("btnFeed").onclick = function () {
    document.getElementById("mes-publications-section").style.display = "none";
    document.getElementById("feed-section").style.display = "block";
};


/* ---------------- VARIABLES ---------------- */

let myPublications = [];
let feedPublications = [];


/* ---------- AJOUT D’UNE PUBLICATION ---------- */

document.getElementById("publishBtn").onclick = function () {

    const text = document.getElementById("postText").value;
    const file = document.getElementById("fileInput").files[0];

    if (!text && !file) {
        alert("Veuillez écrire quelque chose ou choisir un fichier.");
        return;
    }

    const newPub = {
        id: Date.now(),
        text: text,
        file: file ? file.name : "Aucun",
        likes: 0,
        dislikes: 0,
        userLiked: false,
        userDisliked: false,
        comments: []
    };

    myPublications.push(newPub);
    feedPublications.push(newPub);

    afficherTableauMesPublications();
    afficherFeed();

    document.getElementById("postText").value = "";
    document.getElementById("fileInput").value = "";
};


/* ---------------- TABLEAU MES PUBLICATIONS ---------------- */

function afficherTableauMesPublications() {
    const tbody = document.getElementById("myPostsBody");
    tbody.innerHTML = "";

    myPublications.forEach(pub => {
        const tr = document.createElement("tr");

        tr.innerHTML = `
            <td>${pub.text}</td>
            <td>${pub.file}</td>
            <td>
                <button onclick="modifierPub(${pub.id})">Modifier</button>
                <button onclick="supprimerPub(${pub.id})">Supprimer</button>
            </td>
        `;

        tbody.appendChild(tr);
    });
}


/* ---------- SUPPRESSION PUBLICATION ---------- */

function supprimerPub(id) {
    myPublications = myPublications.filter(pub => pub.id !== id);
    feedPublications = feedPublications.filter(pub => pub.id !== id);
    afficherTableauMesPublications();
    afficherFeed();
}


/* ---------- MODIFIER PUBLICATION AVEC POPUP ---------- */

function modifierPub(id) {
    const pub = myPublications.find(p => p.id === id);

    // Création du popup
    const popup = document.createElement("div");
    popup.style.position = "fixed";
    popup.style.top = "0";
    popup.style.left = "0";
    popup.style.width = "100%";
    popup.style.height = "100%";
    popup.style.background = "rgba(0,0,0,0.6)";
    popup.style.display = "flex";
    popup.style.alignItems = "center";
    popup.style.justifyContent = "center";
    popup.style.zIndex = "9999";

    popup.innerHTML = `
        <div style="
            background:white; 
            padding:20px; 
            width:350px;
            border-radius:10px;
            text-align:center;
        ">
            <h3>Modifier la publication</h3>

            <label>Nouveau texte :</label><br>
            <textarea id="modifTexte" style="width:90%; height:80px;">${pub.text}</textarea><br><br>

            <label>Changer le fichier :</label><br>
            <input type="file" id="modifFichier"><br><br>

            <button id="btnSave" style="margin-right:10px;">Enregistrer</button>
            <button id="btnCancel">Annuler</button>
        </div>
    `;

    document.body.appendChild(popup);

    /* ---- BOUTON ANNULER ---- */
    document.getElementById("btnCancel").onclick = function () {
        popup.remove();
    };

    /* ---- BOUTON ENREGISTRER ---- */
    document.getElementById("btnSave").onclick = function () {

        // sauvegarde du texte
        pub.text = document.getElementById("modifTexte").value;

        // sauvegarde du fichier si un nouveau est choisi
        const newFile = document.getElementById("modifFichier").files[0];
        if (newFile) {
            pub.file = newFile.name;
        }

        // mise à jour du feed
        const fpub = feedPublications.find(p => p.id === id);
        if (fpub) {
            fpub.text = pub.text;
            if (newFile) {
                fpub.file = pub.file;
            }
        }

        afficherTableauMesPublications();
        afficherFeed();
        popup.remove();
    };
}


/* ---------------- FEED ---------------- */

function afficherFeed() {
    const container = document.getElementById("feed-container");
    container.innerHTML = "";

    feedPublications.forEach(pub => {
        const div = document.createElement("div");
        div.className = "feed-post";

        div.innerHTML = `
            <p><strong>Texte :</strong> ${pub.text}</p>
            <p><strong>Fichier :</strong> ${pub.file}</p>

            <!-- Like / Unlike -->
            <button onclick="liker(${pub.id})">👍 Like</button>
            <button onclick="unlike(${pub.id})">❌ Supprimer Like</button>
            <span id="like-${pub.id}">${pub.likes}</span> Likes

            <br><br>

            <!-- Dislike / RemoveDislike -->
            <button onclick="disliker(${pub.id})">👎 Dislike</button>
            <button onclick="removeDislike(${pub.id})">❌ Supprimer Dislike</button>
            <span id="dislike-${pub.id}">${pub.dislikes}</span> Dislikes

            <br><br>

            <div>
                <input type="text" id="comment-${pub.id}" placeholder="Votre commentaire">
                <button onclick="commenter(${pub.id})">Commenter</button>
            </div>

            <div id="comments-${pub.id}" class="comments"></div>
        `;

        container.appendChild(div);
        afficherCommentaires(pub.id);
    });
}


/* ---------------- LIKE / DISLIKE AVEC ANNULATION ---------------- */

function liker(id) {
    let pub = feedPublications.find(p => p.id === id);

    if (pub.userLiked) return; // déjà liké

    pub.likes++;
    pub.userLiked = true;

    if (pub.userDisliked) {
        pub.dislikes--;
        pub.userDisliked = false;
    }

    document.getElementById("like-" + id).innerText = pub.likes;
    document.getElementById("dislike-" + id).innerText = pub.dislikes;
}

function unlike(id) {
    let pub = feedPublications.find(p => p.id === id);
    if (!pub.userLiked) return;

    pub.likes--;
    pub.userLiked = false;

    document.getElementById("like-" + id).innerText = pub.likes;
}

function disliker(id) {
    let pub = feedPublications.find(p => p.id === id);

    if (pub.userDisliked) return;

    pub.dislikes++;
    pub.userDisliked = true;

    if (pub.userLiked) {
        pub.likes--;
        pub.userLiked = false;
    }

    document.getElementById("like-" + id).innerText = pub.likes;
    document.getElementById("dislike-" + id).innerText = pub.dislikes;
}

function removeDislike(id) {
    let pub = feedPublications.find(p => p.id === id);
    if (!pub.userDisliked) return;

    pub.dislikes--;
    pub.userDisliked = false;

    document.getElementById("dislike-" + id).innerText = pub.dislikes;
}


/* ---------------- COMMENTAIRES (ajout, modif, suppression) ---------------- */

function commenter(id) {
    let pub = feedPublications.find(p => p.id === id);
    let comment = document.getElementById("comment-" + id).value;

    if (!comment) return;

    pub.comments.push({ id: Date.now(), text: comment });

    afficherCommentaires(id);
    document.getElementById("comment-" + id).value = "";
}

function afficherCommentaires(id) {
    let pub = feedPublications.find(p => p.id === id);
    let section = document.getElementById("comments-" + id);

    section.innerHTML = "";

    pub.comments.forEach(c => {
        let div = document.createElement("div");

        div.innerHTML = `
            <p>${c.text}</p>
            <button onclick="modifierCommentaire(${id}, ${c.id})">Modifier</button>
            <button onclick="supprimerCommentaire(${id}, ${c.id})">Supprimer</button>
            <hr>
        `;

        section.appendChild(div);
    });
}

function supprimerCommentaire(pubId, commentId) {
    let pub = feedPublications.find(p => p.id === pubId);
    pub.comments = pub.comments.filter(c => c.id !== commentId);
    afficherCommentaires(pubId);
}

function modifierCommentaire(pubId, commentId) {
    let pub = feedPublications.find(p => p.id === pubId);
    let comment = pub.comments.find(c => c.id === commentId);

    let newText = prompt("Modifier votre commentaire :", comment.text);
    if (newText === null) return;

    comment.text = newText;
    afficherCommentaires(pubId);
}
