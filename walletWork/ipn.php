// // PayPal IPN の検証 URL
// $paypalUrl = "https://ipnpb.paypal.com/cgi-bin/webscr";

// // POSTデータをそのまま復元
// $postData = file_get_contents("php://input");

// // IPN検証リクエストを構築
// $req = 'cmd=_notify-validate&' . $postData;

// // 検証リクエストを送信
// $ch = curl_init($paypalUrl);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($ch, CURLOPT_HEADER, false);
// curl_setopt($ch, CURLOPT_POST, true);
// curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
// // curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . "/cert/cacert.pem"); // 証明書が必要な環境用
// $res = curl_exec($ch);
// curl_close($ch);

// // ログファイル名（例：日別ログ）
// $logFile = __DIR__ . "/ipn_log_" . date("Ymd") . ".txt";

// // POSTデータを配列に変換
// parse_str($postData, $postArray);
// $custom = $postArray['custom'] ?? 'unknown';
// $txn_id = $postArray['txn_id'] ?? 'none';
// $payment_status = $postArray['payment_status'] ?? '';

// // ログ保存（すべての通知を記録）
// file_put_contents($logFile, date("Y-m-d H:i:s") . " - IPN Received\n", FILE_APPEND);
// file_put_contents($logFile, print_r($postArray, true), FILE_APPEND);

// if ($res === "VERIFIED" && $payment_status === "Completed") {
//     // カスタムデータからゲームIDと商品IDを分離（例："survival3D_item01"）
//     list($gameId, $itemId) = explode("_", $custom);

//     // 各ゲームごとのログファイル
//     $dataFile = __DIR__ . "/purchase_" . $gameId . ".log";

//     $line = date("Y-m-d H:i:s") . "\tTXN:$txn_id\tITEM:$itemId\tPAYMENT:$payment_status\n";

//     file_put_contents($dataFile, $line, FILE_APPEND);
// }



<?php
$log = __DIR__ . '/ipn_log.txt';
file_put_contents($log, json_encode($_POST, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

if ($_POST['payment_status'] === 'Completed') {
    $customId = $_POST['custom'] ?? 'UNKNOWN';
    $txnId = $_POST['txn_id'] ?? 'NO_TXN';

    $logFile = __DIR__ . '/purchase.log';
    $line = date("Y-m-d H:i:s") . "\t" . "TXN:$txnId\t" . "CUSTOM:$customId\t" . "PAYMENT:" . $_POST['payment_status'] . "\n";
    file_put_contents($logFile, $line, FILE_APPEND);
}
?>
