<?php

class Reponse {
    private ?int $id_reponse = null;
    private int $id_quiz;
    private string $question;
    private string $option1;
    private string $option2;
    private string $option3;
    private int $reponse_correcte;

    // Constructeur
    public function __construct(int $id_quiz, string $question, string $option1, string $option2, string $option3, int $reponse_correcte) {
        $this->id_quiz = $id_quiz;
        $this->question = $question;
        $this->option1 = $option1;
        $this->option2 = $option2;
        $this->option3 = $option3;
        $this->reponse_correcte = $reponse_correcte;
    }

    // Getters
    public function getIdReponse(): ?int {
        return $this->id_reponse;
    }

    public function getIdQuiz(): int {
        return $this->id_quiz;
    }

    public function getQuestion(): string {
        return $this->question;
    }

    public function getOption1(): string {
        return $this->option1;
    }

    public function getOption2(): string {
        return $this->option2;
    }

    public function getOption3(): string {
        return $this->option3;
    }

    public function getReponseCorrecte(): int {
        return $this->reponse_correcte;
    }

    // Setters
    public function setIdReponse(int $id_reponse): void {
        $this->id_reponse = $id_reponse;
    }

    public function setIdQuiz(int $id_quiz): void {
        $this->id_quiz = $id_quiz;
    }

    public function setQuestion(string $question): void {
        $this->question = $question;
    }

    public function setOption1(string $option1): void {
        $this->option1 = $option1;
    }

    public function setOption2(string $option2): void {
        $this->option2 = $option2;
    }

    public function setOption3(string $option3): void {
        $this->option3 = $option3;
    }

    public function setReponseCorrecte(int $reponse_correcte): void {
        $this->reponse_correcte = $reponse_correcte;
    }
}
?>