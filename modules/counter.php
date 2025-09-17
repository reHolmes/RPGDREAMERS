<?php
// カウンターファイル保存ディレクトリ（必ず書き込み権限を与える）
$counterDir = __DIR__ . '/visitor_count';

// 今日の日付
$date = date("Y-m-d");

// 今日のカウンターファイルパス
$file = $counterDir . "/counter_{$date}.txt";

// ユーザーIP
$ip = $_SERVER['REMOTE_ADDR'];

// 初期値
$count = 0;
$ips = [];

// ディレクトリがなければ作成
if (!is_dir($counterDir)) {
    mkdir($counterDir, 0755, true);
}

// ファイルが存在する場合は読み込み
if (file_exists($file)) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (count($lines) > 0) {
        $count = (int)$lines[0];
        $ips = array_slice($lines, 1);
    }
}

// IPが未記録ならカウントアップ
if (!in_array($ip, $ips)) {
    $count++;
    $ips[] = $ip;

    // 保存
    $data = $count . PHP_EOL . implode(PHP_EOL, $ips);
    file_put_contents($file, $data, LOCK_EX);
}

// 表示
echo "本日 {$count} 人目の来場者です！";
?>
