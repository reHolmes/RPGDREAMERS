<?php
// CORSとログ
header("Access-Control-Allow-Origin: *");
file_put_contents(__DIR__ . "/webhook_log.txt", date("Y-m-d H:i:s") . " 受信: " . file_get_contents("php://input") . "\n", FILE_APPEND);

// PayPalの認証情報
$isSandbox = true; // ← trueでサンドボックス、falseで本番

$test_clientId = 'AanzC557T0DCZKWUohSBplJsiShyL42vGb6Ax1lvVnQNnaimWZhZsMlb5t4XBszyNlm6Mu4pp5LrOBTb';
$test_secret   = 'EMCElilrb4KSI2xfsQV4De8H9X76Iy5qR4mI6zZp5iFuiUe2YW50t9ioX0zehg8cKQsl0HgAGi1vxlp9';

// 本番用認証情報
$main_clientId = 'AWTtA3DvU8YW2t0TnXSVMLe9Dxj9SJv9TKJYwbIiRdT_ecqFAtxmVGTAYTK_4plBEMsY8ddxLS8A3Zd2';
$main_secret   = 'EFxjIJ2KWlMlFKxOCbp-fSO4KI4pKMFDx13oaOLHdx9pdAHA6xmPdFC396esjyQq0amuOeYLbWkbHw-0';

$clientId = $isSandbox ? $test_clientId : $main_clientId;
$secret   = $isSandbox ? $test_secret   : $main_secret;

$tokenUrl = $isSandbox 
    ? "https://api-m.sandbox.paypal.com/v1/oauth2/token" 
    : "https://api-m.paypal.com/v1/oauth2/token";

$captureUrlBase = $isSandbox 
    ? "https://api-m.sandbox.paypal.com/v2/checkout/orders/" 
    : "https://api-m.paypal.com/v2/checkout/orders/";

// webhookから注文ID取得
$body = json_decode(file_get_contents("php://input"), true);
$orderId = $body["resource"]["id"] ?? null;

if (!$orderId) {
    file_put_contents(__DIR__ . "/webhook_log.txt", "注文IDが取得できません\n", FILE_APPEND);
    exit;
}

// アクセストークン取得
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $tokenUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, $clientId . ":" . $secret);
curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Accept: application/json",
    "Accept-Language: en_US"
]);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
$accessToken = $result["access_token"] ?? null;

if (!$accessToken) {
    file_put_contents(__DIR__ . "/webhook_log.txt", "アクセストークン取得失敗\n", FILE_APPEND);
    exit;
}

// キャプチャ（支払い完了確認）
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $captureUrlBase . $orderId);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . $accessToken
]);
$orderInfo = curl_exec($ch);
curl_close($ch);

file_put_contents(__DIR__ . "/webhook_log.txt", "注文情報: " . $orderInfo . "\n", FILE_APPEND);
