<?php
$servername = "mysql312.phy.lolipop.lan";
$username = "LAA1654245";
$password = "doujin4ruis";
$dbname = "LAA1654245-rpgdreamers";

// // MySQL接続
// $conn = new mysqli($servername, $username, $password, $dbname);

// // 接続チェック
// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('データベース接続失敗: ' . $e->getMessage());
}

?>