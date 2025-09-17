<?php
$games_dir = __DIR__ . '/games';
$games_url = './games';

if (is_dir($games_dir)) {
    $folders = scandir($games_dir);
    foreach ($folders as $folder) {
        if ($folder === '.' || $folder === '..') continue;
            $game_path = $games_dir . '/' . $folder . '/index.html';
        if (is_dir($games_dir . '/' . $folder) && file_exists($game_path)) {
            echo "<li><a href='$games_url/$folder/index.html' target='_blank'>" . htmlspecialchars($folder) . "</a></li>";
        }
    }
} else {
    echo "<li>ゲームフォルダが見つかりません。</li>";
}
