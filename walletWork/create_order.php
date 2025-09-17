<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$isSandbox = true;

// サンドボックス用認証情報
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

$orderUrl = $isSandbox
    ? "https://api-m.sandbox.paypal.com/v2/checkout/orders"
    : "https://api-m.paypal.com/v2/checkout/orders";

$customId = $_GET['customId'] ?? '';
$price = $_GET['price'] ?? '';

if (!$customId || !$price) {
    http_response_code(400);
    echo json_encode(["error" => "Missing customId or price"]);
    exit;
}

// ログパス
$logPath = __DIR__ . "/create_order_log.txt";
function log_debug($msg) {
    global $logPath;
    file_put_contents($logPath, date("Y-m-d H:i:s") . " " . $msg . "\n", FILE_APPEND);
}
log_debug("開始: customId=$customId, price=$price");

// アクセストークン取得
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $tokenUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERPWD, $clientId . ":" . $secret);
curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Accept: application/json",
    "Accept-Language: en_US"
]);

$response = curl_exec($ch);
if (!$response) {
    log_debug("トークン取得失敗: " . curl_error($ch));
    http_response_code(500);
    echo json_encode(["error" => "Token error: " . curl_error($ch)]);
    exit;
}
$result = json_decode($response, true);
$accessToken = $result['access_token'] ?? '';
curl_close($ch);

if (!$accessToken) {
    log_debug("access_token 空: " . $response);
    http_response_code(500);
    echo json_encode(["error" => "Failed to get access token"]);
    exit;
}
log_debug("トークン取得成功");

// 注文作成
$orderData = [
    "intent" => "CAPTURE",
    "purchase_units" => [[
        "reference_id" => $customId,
        "amount" => [
            "currency_code" => "JPY",
            "value" => $price
        ]
    ]],
    "application_context" => [
        "return_url" => "https://doujin-ruis.pigboat.jp/walletWork/success.php",
        "cancel_url" => "https://doujin-ruis.pigboat.jp/walletWork/cancel.php"
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $orderUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . $accessToken
]);

$response = curl_exec($ch);
if (!$response) {
    log_debug("注文作成失敗: " . curl_error($ch));
    http_response_code(500);
    echo json_encode(["error" => "Order error: " . curl_error($ch)]);
    exit;
}
$order = json_decode($response, true);
curl_close($ch);

if (!isset($order["links"])) {
    log_debug("注文応答エラー: " . $response);
    http_response_code(500);
    echo json_encode(["error" => "Failed to get approve_url"]);
    exit;
}

foreach ($order["links"] as $link) {
    if ($link["rel"] === "approve") {
        $approveUrl = $link["href"];
        break;
    }
}

log_debug("注文成功: order_id=" . ($order["id"] ?? "不明"));

echo json_encode([
    "approve_url" => $approveUrl ?? '',
    "order_id" => $order["id"] ?? ''
]);




// // ヘッダー設定（CORSとJSON出力）
// header("Access-Control-Allow-Origin: *");
// header("Content-Type: application/json");

// // 環境切り替え設定（true ならサンドボックス、false なら本番）
// $isSandbox = false;

// // サンドボックス用認証情報
// $test_clientId = 'AanzC557T0DCZKWUohSBplJsiShyL42vGb6Ax1lvVnQNnaimWZhZsMlb5t4XBszyNlm6Mu4pp5LrOBTb';
// $test_secret   = 'EMCElilrb4KSI2xfsQV4De8H9X76Iy5qR4mI6zZp5iFuiUe2YW50t9ioX0zehg8cKQsl0HgAGi1vxlp9';

// // 本番用認証情報
// $main_clientId = 'AawnPZ_CXDyvvJLRwVuBiFLhxx7CjoDlmMySyOyP_-yog3MDhGa2h_JYPFsnfXC9TOI0_IO3x8xdtO7f';
// $main_secret   = 'EKY-WFOO70nEJa_78Kl0TRm5HflSEOZixRPaVuCALtS951y_UKmHjgZN4nKGYHfgbON_d8p5SLxHZuty';

// // 認証情報を切り替え
// $clientId = $isSandbox ? $test_clientId : $main_clientId;
// $secret   = $isSandbox ? $test_secret   : $main_secret;

// // APIエンドポイントの切り替え
// $tokenUrl = $isSandbox 
//     ? "https://api-m.sandbox.paypal.com/v1/oauth2/token" 
//     : "https://api-m.paypal.com/v1/oauth2/token";

// $orderUrl = $isSandbox 
//     ? "https://api-m.sandbox.paypal.com/v2/checkout/orders" 
//     : "https://api-m.paypal.com/v2/checkout/orders";


// // パラメータ受信
// $customId = $_GET['customId'] ?? '';
// $price = $_GET['price'] ?? '';

// if (!$customId || !$price) {
//     http_response_code(400);
//     echo json_encode(["error" => "Missing customId or price"]);
//     exit;
// }

// // URLも切り替え
// $tokenUrl = $isSandbox 
//     ? "https://api-m.sandbox.paypal.com/v1/oauth2/token" 
//     : "https://api-m.paypal.com/v1/oauth2/token";

// $orderUrl = $isSandbox 
//     ? "https://api-m.sandbox.paypal.com/v2/checkout/orders" 
//     : "https://api-m.paypal.com/v2/checkout/orders";

// // アクセストークン取得に適用
// $ch = curl_init();
// curl_setopt($ch, CURLOPT_URL, $tokenUrl);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// curl_setopt($ch, CURLOPT_USERPWD, $clientId . ":" . $secret);
// curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
// curl_setopt($ch, CURLOPT_POST, 1);
// curl_setopt($ch, CURLOPT_HTTPHEADER, [
//     "Accept: application/json",
//     "Accept-Language: en_US"
// ]);

// $response = curl_exec($ch);
// if (!$response) {
//     http_response_code(500);
//     echo json_encode(["error" => "Token error: " . curl_error($ch)]);
//     exit;
// }
// $result = json_decode($response, true);
// $accessToken = $result['access_token'] ?? '';
// curl_close($ch);

// if (!$accessToken) {
//     http_response_code(500);
//     echo json_encode(["error" => "Failed to get access token"]);
//     exit;
// }

// // 注文作成
// $orderData = [
//     "intent" => "CAPTURE",
//     "purchase_units" => [[
//         "reference_id" => $customId,
//         "amount" => [
//             "currency_code" => "JPY",
//             "value" => $price
//         ]
//     ]],
//     "application_context" => [
//         "return_url" => "https://doujin-ruis.pigboat.jp/walletWork/success.php",
//         "cancel_url" => "https://doujin-ruis.pigboat.jp/walletWork/cancel.php"
//     ]
// ];

// $ch = curl_init();
// curl_setopt($ch, CURLOPT_URL, $orderUrl);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
// curl_setopt($ch, CURLOPT_POST, 1);
// curl_setopt($ch, CURLOPT_HTTPHEADER, [
//     "Content-Type: application/json",
//     "Authorization: Bearer " . $accessToken
// ]);

// $response = curl_exec($ch);
// if (!$response) {
//     http_response_code(500);
//     echo json_encode(["error" => "Order error: " . curl_error($ch)]);
//     exit;
// }
// $order = json_decode($response, true);
// curl_close($ch);

// // 承認リンク取得
// $approveUrl = "";
// foreach ($order["links"] as $link) {
//     if ($link["rel"] === "approve") {
//         $approveUrl = $link["href"];
//         break;
//     }
// }

// if (!$approveUrl) {
//     http_response_code(500);
//     echo json_encode(["error" => "Failed to get approve_url"]);
//     exit;
// }

// // 既存の認証・注文作成処理の後に...
// $orderId = $order["id"] ?? "";

// echo json_encode([
//     "approve_url" => $approveUrl,
//     "order_id"   => $orderId
// ]);
