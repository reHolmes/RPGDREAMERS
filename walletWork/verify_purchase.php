<?php
// CORS 対応（必要に応じて制限可）
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// ユーザーID取得（セッションなどから）
session_start();
$userId = $_SESSION["user_id"] ?? 0;
if (!$userId) {
    http_response_code(401);
    echo json_encode(["error" => "未ログイン"]);
    exit;
}

// webhook_log.txt から対象行を抽出
$logPath = __DIR__ . '/webhook_log.txt';
if (!file_exists($logPath)) {
    echo json_encode([]);
    exit;
}

$lines = file($logPath, FILE_IGNORE_NEW_LINES);
$purchases = [];

foreach ($lines as $line) {
    if (strpos($line, 'reference_id') !== false && strpos($line, "\"$userId-") !== false) {
        if (preg_match('/"reference_id"\s*:\s*"([^"]+)"/', $line, $matches)) {
            $parts = explode("-", $matches[1]);
            if (count($parts) === 4) {
                [$uid, $type, $id, $count] = $parts;
                if ($uid == $userId) {
                    $purchases[] = [
                        "type" => $type,
                        "id" => (int)$id,
                        "count" => (int)$count
                    ];
                }
            }
        }
    }
}

echo json_encode($purchases);
