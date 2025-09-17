<?php
require_once 'db.php';

$sql = "SELECT DISTINCT foldername, MAX(upload_date) AS latest_upload FROM files GROUP BY foldername ORDER BY latest_upload DESC";
$result = $conn->query($sql);

$folders = [];
while ($row = $result->fetch_assoc()) {
    $folders[] = [
        'foldername' => $row['foldername'],
        'upload_date' => $row['latest_upload']
    ];
}

echo json_encode($folders);
$conn->close();
?>