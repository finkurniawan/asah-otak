<?php

declare(strict_types=1);
global $db_config;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Game.php';

use AsahOtak\Game;
use AsahOtak\Database;

session_start();

$db = new Database($db_config);

if (!isset($_SESSION['game_state'])) {
    $game = new Game($db);
    $_SESSION['game_state'] = $game->getState();
} else {
    $game = Game::fromState($_SESSION['game_state'], $db);
}

$message = '';
$gameOver = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'])) {
    $game->processAnswer($_POST['answer']);
    $gameOver = true;
    $message = "Poin yang anda dapat adalah {$game->getScore()}";
    $_SESSION['game_state'] = $game->getState();
}

$currentWord = $game->getCurrentWord();
$score = $game->getScore();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asah Otak Game</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-500 to-purple-600 min-h-screen flex items-center justify-center">
<div class="bg-white p-8 rounded-lg shadow-2xl max-w-md w-full">
    <h1 class="text-3xl font-bold mb-6 text-center text-gray-800">Asah Otak Game</h1>

    <?php if (!$gameOver): ?>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6" role="alert">
            <p class="font-bold">Petunjuk:</p>
            <p><?= htmlspecialchars($currentWord['clue']) ?></p>
        </div>
        <form method="post" id="gameForm" class="space-y-4">
            <div class="flex justify-center space-x-2">
                <?php
                $word = $currentWord['kata'];
                for ($i = 0; $i < strlen($word); $i++) {
                    $readonly = ($i == 2 || $i == 6) ? 'readonly bg-gray-100' : '';
                    $value = ($i == 2 || $i == 6) ? $word[$i] : '';
                    echo "<input type='text' class='w-10 h-10 text-center border-2 border-gray-300 rounded focus:border-blue-500 focus:outline-none uppercase {$readonly}' maxlength='1' name='answer[]' {$readonly} value='{$value}' required>";
                }
                ?>
            </div>
            <button type="submit"
                    class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300">
                Jawab
            </button>
        </form>
    <?php else: ?>
        <div class="text-center">
            <h2 class="text-2xl font-bold mb-4">Permainan Selesai</h2>
            <p class="text-xl mb-6"><?= $message ?></p>
            <button onclick="showSaveScorePopup()"
                    class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300 mr-2">
                Simpan Poin
            </button>
            <a href="index.php"
               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300">
                Ulangi
            </a>
        </div>
    <?php endif; ?>
</div>

<div id="saveScorePopup" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" style="display: none;">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Simpan Skor</h3>
            <div class="mt-2 px-7 py-3">
                <input type="text" id="playerName" placeholder="Masukkan nama Anda" class="w-full px-3 py-2 placeholder-gray-300 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-100 focus:border-indigo-300">
                <p id="nameError" class="text-red-500 text-sm mt-1" style="display: none;"></p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="saveScoreBtn" class="px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-300">
                    Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('gameForm');
        if (form) {
            const inputs = form.querySelectorAll('input[type="text"]:not([readonly])');

            inputs.forEach((input, index) => {
                input.addEventListener('input', function () {
                    this.value = this.value.toUpperCase();
                    if (this.value && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                });

                input.addEventListener('keydown', function (e) {
                    if (e.key === 'Backspace' && !this.value && index > 0) {
                        inputs[index - 1].focus();
                    }
                });
            });
        }

        document.getElementById('saveScoreBtn').addEventListener('click', function() {
            const playerName = document.getElementById('playerName').value;
            const nameError = document.getElementById('nameError');

            if (playerName) {
                fetch('save_score.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'player_name=' + encodeURIComponent(playerName)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = 'index.php';
                        } else {
                            nameError.textContent = data.message;
                            nameError.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        nameError.textContent = 'Terjadi kesalahan. Silakan coba lagi.';
                        nameError.style.display = 'block';
                    });
            } else {
                nameError.textContent = 'Nama tidak boleh kosong.';
                nameError.style.display = 'block';
            }
        });
    });

    function showSaveScorePopup() {
        document.getElementById('saveScorePopup').style.display = 'block';
    }
</script>
</body>
</html>