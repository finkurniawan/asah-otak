<?php

namespace AsahOtak;

class Game {
    private array $currentWord;
    private int $score;
    private Database $db;

    public function __construct(Database $db) {
        $this->db = $db;
        $this->currentWord = $this->db->getRandomWord();
        $this->score = 0;
    }

    public function getCurrentWord(): array {
        return $this->currentWord;
    }

    public function getScore(): int {
        return $this->score;
    }

    public function processAnswer(array $answer): void {
        $userAnswer = strtoupper(implode('', $answer));
        $correctAnswer = $this->currentWord['kata'];
        $this->score = $this->calculateScore($userAnswer, $correctAnswer);
    }

    private function calculateScore(string $userAnswer, string $correctAnswer): int {
        $score = 0;
        for ($i = 0; $i < strlen($userAnswer); $i++) {
            if ($i == 2 || $i == 6) continue; // Skip 3rd and 7th characters
            if ($userAnswer[$i] == $correctAnswer[$i]) {
                $score += 10;
            } else {
                $score -= 2;
            }
        }
        return $score;
    }

    public function getState(): array {
        return [
            'currentWord' => $this->currentWord,
            'score' => $this->score,
        ];
    }

    public static function fromState(array $state, Database $db): self {
        $game = new self($db);
        $game->currentWord = $state['currentWord'];
        $game->score = $state['score'];
        return $game;
    }
}