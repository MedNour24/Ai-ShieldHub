<?php
class Reaction {
    private ?int $id_reaction;
    private ?int $id_publication;
    private ?int $id_utilisateur;
    private ?string $type_reaction;
    private ?string $date_reaction;

    // Constructor
    public function __construct(?int $id_reaction, ?int $id_publication, ?int $id_utilisateur, ?string $type_reaction, ?string $date_reaction = null) {
        $this->id_reaction = $id_reaction;
        $this->id_publication = $id_publication;
        $this->id_utilisateur = $id_utilisateur;
        $this->type_reaction = $type_reaction;
        $this->date_reaction = $date_reaction;
    }

    public function show() {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID Reaction</th><th>ID Publication</th><th>ID Utilisateur</th><th>Type Reaction</th><th>Date Reaction</th></tr>";
        echo "<tr>";
        echo "<td>{$this->id_reaction}</td>";
        echo "<td>{$this->id_publication}</td>";
        echo "<td>{$this->id_utilisateur}</td>";
        echo "<td>{$this->type_reaction}</td>";
        echo "<td>{$this->date_reaction}</td>";
        echo "</tr>";
        echo "</table>";
    }

    // Getters & Setters
    public function getIdReaction(): ?int { return $this->id_reaction; }
    public function setIdReaction(?int $id_reaction): void { $this->id_reaction = $id_reaction; }

    public function getIdPublication(): ?int { return $this->id_publication; }
    public function setIdPublication(?int $id_publication): void { $this->id_publication = $id_publication; }

    public function getIdUser(): ?int { return $this->id_utilisateur; }
    public function setIdUser(?int $id_utilisateur): void { $this->id_utilisateur = $id_utilisateur; }

    public function getTypeReaction(): ?string { return $this->type_reaction; }
    public function setTypeReaction(?string $type_reaction): void { $this->type_reaction = $type_reaction; }

    public function getDateReaction(): ?string { return $this->date_reaction; }
    public function setDateReaction(?string $date_reaction): void { $this->date_reaction = $date_reaction; }
}
?>