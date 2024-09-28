<?php
namespace AsahOtak;

use PDO;

class Database {
    private PDO $pdo;

    public function __construct(array $config) {
        $dsn = "pgsql:host={$config['host']};dbname={$config['dbname']}";
        $this->pdo = new PDO($dsn, $config['user'], $config['password']);
    }

    public function getRandomWord(): array {
        $stmt = $this->pdo->query("SELECT * FROM master_kata ORDER BY RANDOM() LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function saveScore(string $name, int $score): void {
        $stmt = $this->pdo->prepare("INSERT INTO point_game (nama_user, total_point) VALUES (?, ?)");
        $stmt->execute([$name, $score]);
    }

    public function nameExists(string $name): bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM point_game WHERE nama_user = ?");
        $stmt->execute([$name]);
        return (bool) $stmt->fetchColumn();
    }
}