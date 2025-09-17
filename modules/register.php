<!-- ユーザー登録 -->
<!-- 項目チェックと登録処理 -->
<?php
require_once 'db.php';

$username = $_POST['registerUsername'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['registerPassword'] ?? '';

// バリデーション
if (!$username || !$email || !$password) {
    die('すべての項目を入力してください');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('無効なメールアドレスです');
}

// パスワードをハッシュに変換
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// DB登録
try {
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $hashedPassword]);
    echo '登録が完了しました。ログインページへ戻ってください。';

} catch (PDOException $e) {
    if ($e->errorInfo[1] === 1062) {
        echo 'ユーザー名またはメールアドレスは既に登録されています。';
    } else {
        echo '登録エラー: ' . $e->getMessage();
    }
}
?>