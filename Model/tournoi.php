<?php
require_once __DIR__ . '/../config/config.php';

class Tournoi
{
    private ?int $id;
    private string $nom;
    private string $theme;
    private string $niveau;
    private string $date_debut;
    private string $date_fin;

    // Constructor
    public function __construct(?int $id, string $nom, string $theme, string $niveau, string $date_debut, string $date_fin)
    {
        $this->id = $id;
        $this->nom = $nom;
        $this->theme = $theme;
        $this->niveau = $niveau;
        $this->date_debut = $date_debut;
        $this->date_fin = $date_fin;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getTheme(): string { return $this->theme; }
    public function getNiveau(): string { return $this->niveau; }
    public function getDateDebut(): string { return $this->date_debut; }
    public function getDateFin(): string { return $this->date_fin; }

    // Setters
    public function setId(?int $id): void { $this->id = $id; }
    public function setNom(string $nom): void { $this->nom = $nom; }
    public function setTheme(string $theme): void { $this->theme = $theme; }
    public function setNiveau(string $niveau): void { $this->niveau = $niveau; }
    public function setDateDebut(string $date_debut): void { $this->date_debut = $date_debut; }
    public function setDateFin(string $date_fin): void { $this->date_fin = $date_fin; }
}
?>
