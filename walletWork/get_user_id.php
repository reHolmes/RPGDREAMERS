<?php

session_save_path("/home/users/2/pigboat.jp-doujin-ruis/web/session");
session_set_cookie_params(['path' => '/']);
session_start();

// CORS対応（必要に応じてドメインを制限可能）
header("Access-Control-Allow-Origin: https://doujin-ruis.pigboat.jp"); // ← *ではなく明示
header("Access-Control-Allow-Credentials: true"); // ← 追加

// ログイン状態チェック
if (isset($_SESSION["user_id"])) {
    echo $_SESSION["user_id"];
} else {
    http_response_code(401); // 未認証
    echo "0"; // 未ログイン時は「0」
}
