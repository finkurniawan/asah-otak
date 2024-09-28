<?php

declare(strict_types=1);
global $db_config;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Database.php';

use AsahOtak\Database;

session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database($db_config);
    $playerName = $_POST['player_name'] ?? '';
    $score = $_SESSION['game_state']['score'] ?? 0;

    if (empty($playerName)) {
        echo json_encode(['success' => false, 'message' => 'Nama tidak boleh kosong.']);
        exit;
    }

    if ($db->nameExists($playerName)) {
        echo json_encode(['success' => false, 'message' => 'Nama sudah digunakan. Silakan pilih nama lain.']);
        exit;
    }

    $db->saveScore($playerName, $score);

    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Skor berhasil disimpan.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Metode tidak valid.']);
exit;
