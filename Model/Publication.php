<?php
class Publication {
    private ?int $id_publication;
    private ?int $id_utilisateur;
    private ?string $texte;
    private ?string $fichier;
    private ?string $type_fichier;
    private ?DateTime $date_publication;
    private ?int $nb_likes;
    private ?int $nb_dislikes;

    public function __construct(
        ?int $id_publication,
        ?int $id_utilisateur,
        ?string $texte,
        ?string $fichier,
        ?string $type_fichier,
        ?DateTime $date_publication,
        ?int $nb_likes = 0,
        ?int $nb_dislikes = 0
    ) {
        $this->id_publication = $id_publication;
        $this->id_utilisateur = $id_utilisateur;
        $this->texte = $texte;
        $this->fichier = $fichier;
        $this->type_fichier = $type_fichier;
        $this->date_publication = $date_publication;
        $this->nb_likes = $nb_likes;
        $this->nb_dislikes = $nb_dislikes;
    }

    // Getters et setters
    public function getIdPublication(): ?int { return $this->id_publication; }
    public function setIdPublication(?int $id) { $this->id_publication = $id; }

    public function getIdUtilisateur(): ?int { return $this->id_utilisateur; }
    public function setIdUtilisateur(?int $id) { $this->id_utilisateur = $id; }

    public function getTexte(): ?string { return $this->texte; }
    public function setTexte(?string $texte) { $this->texte = $texte; }

    public function getFichier(): ?string { return $this->fichier; }
    public function setFichier(?string $fichier) { $this->fichier = $fichier; }

    public function getTypeFichier(): ?string { return $this->type_fichier; }
    public function setTypeFichier(?string $type) { $this->type_fichier = $type; }

    public function getDatePublication(): ?DateTime { return $this->date_publication; }
    public function setDatePublication(?DateTime $date) { $this->date_publication = $date; }

    public function getNbLikes(): ?int { return $this->nb_likes; }
    public function setNbLikes(?int $n) { $this->nb_likes = $n; }

    public function getNbDislikes(): ?int { return $this->nb_dislikes; }
    public function setNbDislikes(?int $n) { $this->nb_dislikes = $n; }
}
?>
