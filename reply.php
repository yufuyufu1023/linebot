<?php
$id = $_GET["id"];
$reply = $_GET["reply"];
$u_id = $_GET["u_id"];
$repcon = $_GET["repcon"];
$con = mysqli_connect('mysql619.db.sakura.ne.jp', 'tatara', 'god0419sinobi', 'tatara_yufuyufu');
if (!$con) {
    exit;
}
$sql = "UPDATE consultation SET reply = '" . $reply . "' WHERE id = " . $id . ";";
$result = mysqli_query($con, $sql);
if (!$result) {
    exit;
}
echo 'sql実行後';
$replyCon = "相談内容：\n" . $repcon . "\n\n返信内容：\n" . $reply;
// HTTPヘッダを設定
$channelToken = 'laLYtVvoFKihrhNWCUbC2cEMAswJXSMfKyw41N5qKBQiqbTnUh0DXndze9QdGdfuIOEdZJ93H3QOKzsfPIR9aSCZLEM4pwDv0pJ0IuZdr/ViSX4jsf5L+h4binsKyabSXXq/DZCEwNqMRXJXaTEuHAdB04t89/1O/w1cDnyilFU=';

$headers = [
    'Authorization: Bearer ' . $channelToken,
    'Content-Type: application/json; charset=utf-8',
];

// POSTデータを設定してJSONにエンコード
$post = [
    'to' => $u_id,
    'messages' => [
        [
            'type' => 'text',
            'text' => $replyCon,
        ],
    ],
];
$post = json_encode($post);

// HTTPリクエストを設定
$ch = curl_init('https://api.line.me/v2/bot/message/push');
$options = [
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_BINARYTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLOPT_POSTFIELDS => $post,
];
curl_setopt_array($ch, $options);

// 実行
$result = curl_exec($ch);

// エラーチェック
$errno = curl_errno($ch);
if ($errno) {
    return;
}

// HTTPステータスを取得
$info = curl_getinfo($ch);
$httpStatus = $info['http_code'];

$responseHeaderSize = $info['header_size'];
$body = substr($result, $responseHeaderSize);

// 200 だったら OK
echo $httpStatus . ' ' . $body;

header("location: consul.php");
