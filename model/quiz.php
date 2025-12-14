<?php

class Quiz {
    private ?int $id_quiz = null;
    private string $titre;
    private string $description;
    private string $statut;
    private ?string $date_creation = null;

    // Constructeur
    public function __construct(string $titre, string $description, string $statut = 'actif') {
        $this->titre = $titre;
        $this->description = $description;
        $this->statut = $statut;
    }

    // Getters
    public function getIdQuiz(): ?int {
        return $this->id_quiz;
    }

    public function getTitre(): string {
        return $this->titre;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getStatut(): string {
        return $this->statut;
    }

    public function getDateCreation(): ?string {
        return $this->date_creation;
    }

    // Setters
    public function setIdQuiz(int $id_quiz): void {
        $this->id_quiz = $id_quiz;
    }

    public function setTitre(string $titre): void {
        $this->titre = $titre;
    }

    public function setDescription(string $description): void {
        $this->description = $description;
    }

    public function setStatut(string $statut): void {
        $this->statut = $statut;
    }

    public function setDateCreation(string $date_creation): void {
        $this->date_creation = $date_creation;
    }

    // Méthode pour exporter les données
    public function toArray(): array {
        return [
            'id_quiz' => $this->id_quiz,
            'titre' => $this->titre,
            'description' => $this->description,
            'statut' => $this->statut,
            'date_creation' => $this->date_creation
        ];
    }
}
?>