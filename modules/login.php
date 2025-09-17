<!-- ログイン -->

<?php
// セッション設定強化
ini_set('session.cookie_secure', '1'); // HTTPSのみ
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'None'); // or 'Lax' if using same domain

session_start();
session_save_path("/home/users/2/pigboat.jp-doujin-ruis/web/session");
session_set_cookie_params(['path' => '/']);

require_once 'db.php';

$username = $_POST['loginUsername'] ?? '';
$password = $_POST['loginPassword'] ?? '';

// DBから該当ユーザーを取得
$stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// セッションにID、ユーザー名を設定
if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $username;
    header('Location: ../'); // リダイレクト
    exit;
} else {
    echo 'ログイン失敗';
}
?>