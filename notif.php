<?php

$con = mysqli_connect('mysql619.db.sakura.ne.jp', 'tatara', 'god0419sinobi', 'tatara_yufuyufu');
if (!$con) {
    exit;
}

$sql = "SELECT * FROM user_info;";
$result = mysqli_query($con, $sql);
if (!$result) {
    exit;
}

while ($row = mysqli_fetch_assoc($result)) {

    $sql = "SELECT * FROM task WHERE u_id = '" . $row['user_id'] . "' AND end_flg = 0 AND deadline > DATE(NOW());";
    $ta_result = mysqli_query($con, $sql);
    if (!$ta_result) {
        exit;
    }

    $ta_push = "\n\n宿題";
    $ta_cnt = 0;
    while ($ta_row = mysqli_fetch_assoc($ta_result)) {
        $ta_push .= "\n\n*" . $ta_row["title"] . "*\n";
        $ta_push .= $ta_row["content"] . "\n";
        $ta_push .= "期限：" . $ta_row["deadline"] . "";
        $ta_cnt++;
    }
    if ($ta_cnt == 0) {
        $ta_push .= "*なし*\n\n";
    }

    $sql = "SELECT title,DATE_FORMAT(start_time,'%k:%i') as start_time,DATE_FORMAT(end_time,'%k:%i') as end_time FROM schedule WHERE u_id = '" . $row['user_id'] . "' AND sc_date = DATE_ADD(DATE(NOW()), INTERVAL 1 DAY);";
    $sc_result = mysqli_query($con, $sql);
    if (!$sc_result) {
        exit;
    }

    $sc_push = "明日の予定";
    $sc_cnt = 0;
    while ($sc_row = mysqli_fetch_assoc($sc_result)) {
        $sc_push .= "\n\n*" . $sc_row["title"] . "*\n";
        $sc_push .= "開始時刻：" . $sc_row["start_time"] . "\n";
        $sc_push .= "終了時刻：" . $sc_row["end_time"] . "";
        $sc_cnt++;
    }
    if ($sc_cnt == 0) {
        $sc_push .= "\n\n*なし*";
    }

    $replyCon = $sc_push . $ta_push . "\n\n明日も一日頑張りましょう";
    push_messages($row['user_id'], $replyCon);
}

function push_messages($u_id, $replyCon)
{
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
}
