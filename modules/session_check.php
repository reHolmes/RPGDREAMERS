<!-- 共通部品 ログインが必要なphpファイルの一番上でrequire_once -->

<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    // JavaScriptでアラート → 前のページに戻る
    echo '<script>
        alert("ログインしてください");
        history.back();
    </script>';
    exit;
}
?>