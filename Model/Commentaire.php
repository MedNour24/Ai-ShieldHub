<?php
class Commentaire {
    private ?int $id_commentaire;
    private ?int $id_publication;
    private ?int $id_utilisateur;
    private ?string $contenu;
    private ?DateTime $date_commentaire;

    // Constructor
    public function __construct(?int $id_commentaire, ?int $id_publication, ?int $id_utilisateur, ?string $contenu, ?DateTime $date_commentaire) {
        $this->id_commentaire = $id_commentaire;
        $this->id_publication = $id_publication;
        $this->id_utilisateur = $id_utilisateur;
        $this->contenu = $contenu;
        $this->date_commentaire = $date_commentaire;
    }

    public function show() {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID Commentaire</th><th>ID Publication</th><th>ID Utilisateur</th><th>Contenu</th><th>Date Commentaire</th></tr>";
        echo "<tr>";
        echo "<td>{$this->id_commentaire}</td>";
        echo "<td>{$this->id_publication}</td>";
        echo "<td>{$this->id_utilisateur}</td>";
        echo "<td>{$this->contenu}</td>";
        echo "<td>" . ($this->date_commentaire ? $this->date_commentaire->format('Y-m-d H:i:s') : '') . "</td>";
        echo "</tr>";
        echo "</table>";
    }

    // Getters & Setters
    public function getId(): ?int { return $this->id_commentaire; }
    public function setId(?int $id_commentaire): void { $this->id_commentaire = $id_commentaire; }

    public function getIdPublication(): ?int { return $this->id_publication; }
    public function setIdPublication(?int $id_publication): void { $this->id_publication = $id_publication; }

    public function getIdUser(): ?int { return $this->id_utilisateur; }
    public function setIdUser(?int $id_utilisateur): void { $this->id_utilisateur = $id_utilisateur; }

    public function getContenu(): ?string { return $this->contenu; }
    public function setContenu(?string $contenu): void { $this->contenu = $contenu; }

    public function getDateCommentaire(): ?DateTime { return $this->date_commentaire; }
    public function setDateCommentaire(?DateTime $date_commentaire): void { $this->date_commentaire = $date_commentaire; }
}
?>