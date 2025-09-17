<?php
require_once 'db.php';

$baseDir = "games/";

if (!is_dir($baseDir)) {
    mkdir($baseDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['gameFiles']['name'][0])) {
    $fileCount = count($_FILES['gameFiles']['name']);

    for ($i = 0; $i < $fileCount; $i++) {
        // オリジナルのファイルパス（例：game1/index.html）
        $relativePath = $_FILES['gameFiles']['name'][$i];

        // 最上位のフォルダ名を取得（game1）
        $folderName = explode("/", $relativePath)[0];

        // アップロード先にサブディレクトリを再現
        $targetPath = $baseDir . $relativePath;
        $targetDir = dirname($targetPath);

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // ファイル保存
        if (move_uploaded_file($_FILES['gameFiles']['tmp_name'][$i], $targetPath)) {
            // データベースに保存
            $stmt = $conn->prepare("INSERT INTO files (foldername, filename, filepath, upload_date) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("sss", $folderName, $relativePath, $targetPath);
            $stmt->execute();
            $stmt->close();
        } else {
            echo "アップロード失敗: " . $relativePath . "<br>";
        }
    }

    echo "フォルダ構造を維持してアップロード完了しました。";
} else {
    echo "ファイルが選択されていません。";
}

$conn->close();
?>