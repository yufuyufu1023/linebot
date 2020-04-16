<?php

$accessToken = 'laLYtVvoFKihrhNWCUbC2cEMAswJXSMfKyw41N5qKBQiqbTnUh0DXndze9QdGdfuIOEdZJ93H3QOKzsfPIR9aSCZLEM4pwDv0pJ0IuZdr/ViSX4jsf5L+h4binsKyabSXXq/DZCEwNqMRXJXaTEuHAdB04t89/1O/w1cDnyilFU=';

//ユーザーからのメッセージ取得
$json_string = file_get_contents('php://input');
$json_object = json_decode($json_string);
//取得データの分解

$event_type = $json_object->{"events"}[0]->{"type"};                //イベントタイプ
$replyToken = $json_object->{"events"}[0]->{"replyToken"};          //返信用トークン
$user_id = $json_object->{"events"}[0]->{"source"}->{"userId"};     //ユーザーID 
$message_text = $json_object->{"events"}[0]->{"message"}->{"text"}; //メッセージ内容

//絵文字
// 0xを抜いた数字の部分
$code = '1000B2';
// 16進エンコードされたバイナリ文字列をデコード
$bin = hex2bin(str_repeat('0', 8 - strlen($code)) . $code);
// UTF8へエンコード
$emoticon =  mb_convert_encoding($bin, 'UTF-8', 'UTF-32BE');


//sqlに接続
$con = mysqli_connect('mysql619.db.sakura.ne.jp', 'tatara', 'password', 'tatara_yufuyufu');
if (!$con) {
    exit;
}
//イベントごとに処理内容を変える
if ($event_type == 'follow') {
    //友達追加、ブロック解除したときuserIdを取得し登録
    $sql = "INSERT INTO user_info(user_id) VALUES ('" . $user_id . "');";
    $result = mysqli_query($con, $sql);
    if (!$result) {
        exit;
    }
    $return_message_text = "友達追加ありがとうございます" . $emoticon . "\n\nモニコンでは、スケジュールや宿題の管理、日常生活での通報案件、些細な相談を受け付けています\n\nどうぞご活用ください\n\n「相談」で相談ができます。\nそのほかの機能は「ヘルプ」で確認できます";
    push_messages($user_id, $return_message_text);
} else if ($event_type == 'unfollow') {
    //ブロックしたときuserIdを取得し削除
    $sql = "DELETE FROM user_info WHERE user_id='" . $user_id . "';";
    $result = mysqli_query($con, $sql);
    if (!$result) {
        exit;
    }
} else if ($event_type == 'message') {
    //何らかのメッセージイベントが働いた場合発動
    //メッセージタイプ認識
    $message_type = $json_object->{"events"}[0]->{"message"}->{"type"};

    //メッセージタイプが「text」以外のときは何も返さず終了
    if ($message_type != "text") exit;

    //ひとつ前のメッセージが何の目的で送られてきた確認する
    $sql = "SELECT * FROM message WHERE u_id='" . $user_id . "' ORDER BY id DESC;";
    $result = mysqli_query($con, $sql);
    if (!$result) {
        exit;
    }
    $status = '';
    $stcnt = '';
    if ($row = mysqli_fetch_assoc($result)) {
        $status = $row['status'];
        $stcnt = $row['stcnt'];
    }

    if ($status == '' || $status == '終了') {

        //返信メッセージ
        if ($message_text == '通報') {
            $return_message_text = "通報内容を教えて";
        } elseif ($message_text == '相談') {
            $return_message_text = "相談内容は何?";
        } elseif ($message_text == 'スケジュール') {
            $return_message_text = "タイトルを教えて";
        } elseif ($message_text == '宿題') {
            $return_message_text = "タイトルを教えて";
        } elseif ($message_text == '宿題完了') {
            //宿題一覧を取得し、表示
            $sql = "SELECT title FROM task WHERE u_id='" . $user_id . "' AND end_flg=0 AND deadline >= DATE(NOW())";
            $result = mysqli_query($con, $sql);
            if (!$result) {
                exit;
            }
            $rmes = "";
            while ($row = mysqli_fetch_assoc($result)) {
                $rmes .= $row['title'];
                $rmes .= "\n";
            }
            if ($rmes == "") {
                $return_message_text = "宿題が登録されていません";
                sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
                exit;
            } else {
                $return_message_text = $rmes . "↑からタイトルを教えて";
            }
        } elseif ($message_text == '宿題削除') {
            //宿題一覧を取得し、表示
            $sql = "SELECT title FROM task WHERE u_id='" . $user_id . "' AND end_flg=0 AND deadline >= DATE(NOW())";
            $result = mysqli_query($con, $sql);
            if (!$result) {
                exit;
            }
            $rmes = "";
            while ($row = mysqli_fetch_assoc($result)) {
                $rmes .= $row['title'];
                $rmes .= "\n";
            }
            if ($rmes == "") {
                $return_message_text = "宿題が登録されていません";
                sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
                exit;
            } else {
                $return_message_text = $rmes . "↑からタイトルを教えて";
            }
        } elseif ($message_text == '宿題一覧') {
            //宿題一覧を取得し、表示
            $sql = "SELECT * FROM task WHERE u_id = '" . $user_id . "' AND end_flg = 0 AND deadline >= DATE(NOW());";
            $result = mysqli_query($con, $sql);
            if (!$result) {
                exit;
            }

            $ta_push = "宿題";
            $ta_cnt = 0;
            while ($ta_row = mysqli_fetch_assoc($result)) {
                $ta_push .= "\n\n*" . $ta_row["title"] . "*\n";
                $ta_push .= $ta_row["content"] . "\n";
                $ta_push .= "期限：" . $ta_row["deadline"] . "";
                $ta_cnt++;
            }
            if ($ta_cnt == 0) {
                $ta_push .= "*なし*";
            }
            $return_message_text = $ta_push;
            sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
            exit;
        } elseif ($message_text == 'スケジュール削除') {
            $return_message_text = "年月を2020/01の形式で教えて";
        } elseif ($message_text == 'スケジュール一覧') {
            $return_message_text = "年月を2020/01の形式で教えて";
        } elseif ($message_text == 'ヘルプ') {
            $return_message_text = "ヘルプ\n" . $emoticon . "コマンド名\nコマンドに対する説明文\nで表示されます\n
" . $emoticon . "通報\n匿名での通報が行えます。\n
" . $emoticon . "相談\n匿名での相談が行えます。\n返答は後日帰ってきます。\n
" . $emoticon . "宿題\n宿題の登録が行えます。\n登録された宿題は期日まで夜７時に通知されます。\n
" . $emoticon . "宿題一覧\n現在登録されている宿題の一覧を表示できます。\n
" . $emoticon . "宿題削除\n宿題の削除が行えます。\n
" . $emoticon . "宿題完了\n宿題の完了報告が行えます・\n
" . $emoticon . "スケジュール\nスケジュールの登録が行えます。\n
" . $emoticon . "スケジュール一覧\nスケジュールの月ごとの一覧が表示できます。\n
" . $emoticon . "スケジュール削除\nスケジュールの削除が行えます。\n
" . $emoticon . "キャンセル\nコマンドの動作を中止できます。";
            sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
            exit;
        } elseif ($message_text == 'カレンダー') {
            $return_message_text = "年月を2020/01の形式で教えて";
        } else {
            exit;
        }

        $sql = "INSERT INTO message(u_id,regi_date,status,stcnt) 
        VALUES ('" . $user_id . "',CURRENT_TIMESTAMP(),'" . $message_text . "',0);";
        $result = mysqli_query($con, $sql);
        if (!$result) {
            exit;
        }

        //返信実行
        sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
    } elseif ($status == '通報') {
        //キャンセル処理
        if ($message_text == "キャンセル") {
            $sql = "INSERT INTO message(u_id,regi_date,status,stcnt) 
            VALUES ('" . $user_id . "',CURRENT_TIMESTAMP(),'終了',0);";
            $result = mysqli_query($con, $sql);
            if (!$result) {
                exit;
            }
            $return_message_text = "キャンセルしたよ";
            sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
            exit;
        }

        $sql = "INSERT INTO report(u_id,regi_date,content) 
        VALUES ('" . $user_id . "',CURRENT_TIMESTAMP(),'" . $message_text . "');";
        $result = mysqli_query($con, $sql);
        if (!$result) {
            exit;
        }

        $sql = "INSERT INTO message(u_id,regi_date,status,stcnt) 
        VALUES ('" . $user_id . "',CURRENT_TIMESTAMP(),'終了',0);";
        $result = mysqli_query($con, $sql);
        if (!$result) {
            exit;
        }

        $return_message_text = "通報できたよ";
        sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
    } elseif ($status == '相談') {
        //キャンセル処理
        if ($message_text == "キャンセル") {
            $sql = "INSERT INTO message(u_id,regi_date,status,stcnt) 
            VALUES ('" . $user_id . "',CURRENT_TIMESTAMP(),'終了',0);";
            $result = mysqli_query($con, $sql);
            if (!$result) {
                exit;
            }
            $return_message_text = "キャンセルしたよ";
            sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
            exit;
        }

        $sql = "INSERT INTO consultation(u_id,regi_date,content,reply) 
        VALUES ('" . $user_id . "',CURRENT_TIMESTAMP(),'" . $message_text . "','');";
        $result = mysqli_query($con, $sql);
        if (!$result) {
            exit;
        }

        $sql = "INSERT INTO message(u_id,regi_date,status,stcnt) 
        VALUES ('" . $user_id . "',CURRENT_TIMESTAMP(),'終了',0);";
        $result = mysqli_query($con, $sql);
        if (!$result) {
            exit;
        }

        $return_message_text = "返答が来るまで待っててね";
        sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
    } elseif ($status == 'スケジュール') {
        //一番上のスケジュール確認
        $sql = "SELECT * FROM schedule WHERE u_id='" . $user_id . "' ORDER BY id DESC;";
        $result = mysqli_query($con, $sql);
        if (!$result) {
            exit;
        }
        $id = '';
        if ($row = mysqli_fetch_assoc($result)) {
            $id = $row['id'];
        }

        //キャンセル処理
        if ($message_text == "キャンセル") {
            $sql = "INSERT INTO message(u_id,regi_date,status,stcnt) 
            VALUES ('" . $user_id . "',CURRENT_TIMESTAMP(),'終了',0);";
            $result = mysqli_query($con, $sql);
            if (!$result) {
                exit;
            }
            //インサートされている時削除
            if ($stcnt > 0) {
                //キャンセルした情報の削除
                $sql = "DELETE FROM schedule WHERE id='" . $id . "';";
                $result = mysqli_query($con, $sql);
                if (!$result) {
                    exit;
                }
            }


            $return_message_text = "キャンセルしたよ";
            sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
            exit;
        }

        if ($stcnt == 0) {
            //タイトル登録
            $sql = "INSERT INTO schedule(u_id,title) 
            VALUES ('" . $user_id . "','" . $message_text . "');";
            $result = mysqli_query($con, $sql);

            if (!$result) {
                exit;
            }
            //flg処理
            $sql = "INSERT INTO message(u_id,regi_date,status,stcnt) 
            VALUES ('" . $user_id . "',CURRENT_TIMESTAMP(),'スケジュール',1);";
            $result = mysqli_query($con, $sql);
            if (!$result) {
                exit;
            }
            $return_message_text = "日付を教えて\n2020/01/01の形式で入力してね";
            sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
        } elseif ($stcnt == 1) {
            //日付
            $sql = "UPDATE schedule SET sc_date = '" . $message_text . "' WHERE id = " . $id . ";";
            $result = mysqli_query($con, $sql);
            if (!$result) {
                exit;
            }
            //flg処理
            $sql = "INSERT INTO message(u_id,regi_date,status,stcnt) 
            VALUES ('" . $user_id . "',CURRENT_TIMESTAMP(),'スケジュール',2);";
            $result = mysqli_query($con, $sql);
            if (!$result) {
                exit;
            }
            $return_message_text = "開始時間を教えて\n16:15の形式で入力してね";
            sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
        } elseif ($stcnt == 2) {
            //開始時刻
            $sql = "UPDATE schedule SET start_time = '" . $message_text . "' WHERE id = " . $id . ";";
            $result = mysqli_query($con, $sql);
            if (!$result) {
                exit;
            }
            //flg処理
            $sql = "INSERT INTO message(u_id,regi_date,status,stcnt) 
            VALUES ('" . $user_id . "',CURRENT_TIMESTAMP(),'スケジュール',3);";
            $result = mysqli_query($con, $sql);
            if (!$result) {
                exit;
            }
            $return_message_text = "終了時間を教えて\n16:15の形式で入力してね";
            sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
        } elseif ($stcnt == 3) {
            //終了時刻
            $sql = "UPDATE schedule SET end_time = '" . $message_text . "' WHERE id = " . $id . ";";
            $result = mysqli_query($con, $sql);
            if (!$result) {
                exit;
            }

            //flg処理
            $sql = "INSERT INTO message(u_id,regi_date,status,stcnt) 
            VALUES ('" . $user_id . "',CURRENT_TIMESTAMP(),'終了',0);";
            $result = mysqli_query($con, $sql);
            if (!$result) {
                exit;
            }
            $return_message_text = "登録できたよ。前日に伝えるね";
            sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
        }
    } elseif ($status == '宿題') {
        //一番上のタスク確認
        $sql = "SELECT * FROM task WHERE u_id='" . $user_id . "' ORDER BY id DESC;";
        $result = mysqli_query($con, $sql);
        if (!$result) {
            exit;
        }
        $id = '';
        if ($row = mysqli_fetch_assoc($result)) {
            $id = $row['id'];
        }

        //キャンセル処理
        if ($message_text == "キャンセル") {
            $sql = "INSERT INTO message(u_id,regi_date,status,stcnt) 
            VALUES ('" . $user_id . "',CURRENT_TIMESTAMP(),'終了',0);";
            $result = mysqli_query($con, $sql);
            if (!$result) {
                exit;
            }
            //インサートされている時削除
            if ($stcnt > 0) {
                //キャンセルした情報の削除
                $sql = "DELETE FROM schedule WHERE id='" . $id . "';";
                $result = mysqli_query($con, $sql);
                if (!$result) {
                    exit;
                }
            }


            $return_message_text = "キャンセルしたよ";
            sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
            exit;
        }

        if ($stcnt == 0) {
            //タイトル登録
            $sql = "INSERT INTO task(u_id,title,end_flg) 
            VALUES ('" . $user_id . "','" . $message_text . "',0);";
            $result = mysqli_query($con, $sql);

            if (!$result) {
                exit;
            }
            //flg処理
            $sql = "INSERT INTO message(u_id,regi_date,status,stcnt) 
            VALUES ('" . $user_id . "',CURRENT_TIMESTAMP(),'宿題',1);";
            $result = mysqli_query($con, $sql);
            if (!$result) {
                exit;
            }
            $return_message_text = "内容を教えて";
            sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
        } elseif ($stcnt == 1) {
            //内容
            $sql = "UPDATE task SET content = '" . $message_text . "' WHERE id = " . $id . ";";
            $result = mysqli_query($con, $sql);

            if (!$result) {
                exit;
            }
            //flg処理
            $sql = "INSERT INTO message(u_id,regi_date,status,stcnt) 
            VALUES ('" . $user_id . "',CURRENT_TIMESTAMP(),'宿題',2);";
            $result = mysqli_query($con, $sql);
            if (!$result) {
                exit;
            }
            $return_message_text = "期限を教えて\n2020/01/01の形式で入力してね";
            sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
        } elseif ($stcnt == 2) {
            //期限
            $sql = "UPDATE task SET deadline = '" . $message_text . "' WHERE id = " . $id . ";";
            $result = mysqli_query($con, $sql);

            if (!$result) {
                exit;
            }
            //flg処理
            $sql = "INSERT INTO message(u_id,regi_date,status,stcnt) 
            VALUES ('" . $user_id . "',CURRENT_TIMESTAMP(),'終了',0);";
            $result = mysqli_query($con, $sql);
            if (!$result) {
                exit;
            }
            $return_message_text = "登録できたよ。\n期限が来るまで毎晩伝えるね";
            sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
        }
    } elseif ($status == '宿題完了') {
        //キャンセル処理
        if ($message_text == "キャンセル") {
            $sql = "INSERT INTO message(u_id,regi_date,status,stcnt) 
            VALUES ('" . $user_id . "',CURRENT_TIMESTAMP(),'終了',0);";
            $result = mysqli_query($con, $sql);
            if (!$result) {
                exit;
            }
            $return_message_text = "キャンセルしたよ";
            sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
            exit;
        }

        $sql = "UPDATE task SET end_flg = 1 WHERE title = '" . $message_text . "' AND u_id='" . $user_id . "';";
        $result = mysqli_query($con, $sql);
        if (!$result) {
            exit;
        }

        $sql = "INSERT INTO message(u_id,regi_date,status,stcnt) 
        VALUES ('" . $user_id . "',CURRENT_TIMESTAMP(),'終了',0);";
        $result = mysqli_query($con, $sql);
        if (!$result) {
            exit;
        }

        $return_message_text = "完了登録できたよ";
        sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
    } elseif ($status == '宿題削除') {
        //キャンセル処理
        if ($message_text == "キャンセル") {
            $sql = "INSERT INTO message(u_id,regi_date,status,stcnt) 
            VALUES ('" . $user_id . "',CURRENT_TIMESTAMP(),'終了',0);";
            $result = mysqli_query($con, $sql);
            if (!$result) {
                exit;
            }
            $return_message_text = "キャンセルしたよ";
            sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
            exit;
        }

        $sql = "DELETE FROM task WHERE u_id='" . $user_id . "' AND title = '" . $message_text . "';";
        $result = mysqli_query($con, $sql);
        if (!$result) {
            exit;
        }

        $sql = "INSERT INTO message(u_id,regi_date,status,stcnt) 
        VALUES ('" . $user_id . "',CURRENT_TIMESTAMP(),'終了',0);";
        $result = mysqli_query($con, $sql);
        if (!$result) {
            exit;
        }

        $return_message_text = "削除できたよ";
        sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
    } elseif ($status == 'スケジュール削除') {
        //キャンセル処理
        if ($message_text == "キャンセル") {
            $sql = "INSERT INTO message(u_id,regi_date,status,stcnt) 
            VALUES ('" . $user_id . "',CURRENT_TIMESTAMP(),'終了',0);";
            $result = mysqli_query($con, $sql);
            if (!$result) {
                exit;
            }
            $return_message_text = "キャンセルしたよ";
            sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
            exit;
        }

        if ($stcnt == 0) {
            //受け取った情報をもとに検索
            $sql = "SELECT sc_date,title FROM schedule WHERE DATE_FORMAT(sc_date, '%Y%m')=" . str_replace('/', '', $message_text) . ";";
            $result = mysqli_query($con, $sql);
            if (!$result) {
                $return_message_text = "もう一度入力しなおしてね";
                sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
                exit;
            }

            $rmes = "";
            while ($row = mysqli_fetch_assoc($result)) {
                $rmes .= $row['sc_date'] . ":";
                $rmes .= $row['title'];
                $rmes .= "\n";
            }
            if ($rmes == "") {
                //flg処理
                $sql = "INSERT INTO message(u_id,regi_date,status,stcnt) 
                    VALUES ('" . $user_id . "',CURRENT_TIMESTAMP(),'終了',0);";
                $result = mysqli_query($con, $sql);
                if (!$result) {
                    exit;
                }
                $return_message_text = "スケジュールが登録されていません";
                sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
                exit;
            } else {
                $return_message_text = $rmes . "↑からタイトルを教えて";
            }

            //flg処理
            $sql = "INSERT INTO message(u_id,regi_date,status,stcnt) 
                    VALUES ('" . $user_id . "',CURRENT_TIMESTAMP(),'スケジュール削除',1);";
            $result = mysqli_query($con, $sql);
            if (!$result) {
                exit;
            }
            sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
        } elseif ($stcnt == 1) {
            //タイトルをもとに削除
            $sql = "DELETE FROM schedule WHERE u_id='" . $user_id . "' AND title = '" . $message_text . "';";
            $result = mysqli_query($con, $sql);
            if (!$result) {
                exit;
            }

            $sql = "INSERT INTO message(u_id,regi_date,status,stcnt) 
                    VALUES ('" . $user_id . "',CURRENT_TIMESTAMP(),'終了',0);";
            $result = mysqli_query($con, $sql);
            if (!$result) {
                exit;
            }

            $return_message_text = "削除できたよ";
            sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
        }
    } elseif ($status == 'スケジュール一覧') {
        //キャンセル処理
        if ($message_text == "キャンセル") {
            $sql = "INSERT INTO message(u_id,regi_date,status,stcnt) 
            VALUES ('" . $user_id . "',CURRENT_TIMESTAMP(),'終了',0);";
            $result = mysqli_query($con, $sql);
            if (!$result) {
                exit;
            }
            $return_message_text = "キャンセルしたよ";
            sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
            exit;
        }

        //受け取った情報をもとに検索
        $sql = "SELECT sc_date,title,DATE_FORMAT(sc_date,'%Y/%m') as sc_month FROM schedule WHERE u_id='" . $user_id . "' AND DATE_FORMAT(sc_date, '%Y%m')=" . str_replace('/', '', $message_text) . ";";
        $result = mysqli_query($con, $sql);
        if (!$result) {
            $return_message_text = "もう一度入力しなおしてね";
            sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
            exit;
        }

        $rmes = "";
        $month = "";
        while ($row = mysqli_fetch_assoc($result)) {
            $rmes .= "\n\n" . $row['sc_date'];
            $rmes .= "\n" . $row['title'];
            $month = $row['sc_month'];
        }
        if ($rmes == "") {
            //flg処理
            $sql = "INSERT INTO message(u_id,regi_date,status,stcnt) 
                    VALUES ('" . $user_id . "',CURRENT_TIMESTAMP(),'終了',0);";
            $result = mysqli_query($con, $sql);
            if (!$result) {
                exit;
            }
            $return_message_text = "スケジュールが登録されていません";
            sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
            exit;
        } else {
            $return_message_text = $month . "の\nスケジュール一覧" . $rmes;
        }

        //flg処理
        $sql = "INSERT INTO message(u_id,regi_date,status,stcnt) 
                    VALUES ('" . $user_id . "',CURRENT_TIMESTAMP(),'終了',0);";
        $result = mysqli_query($con, $sql);
        if (!$result) {
            exit;
        }
        sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
    }elseif ($status == 'カレンダー') {
        //キャンセル処理
        if ($message_text == "キャンセル") {
            $sql = "INSERT INTO message(u_id,regi_date,status,stcnt) 
            VALUES ('" . $user_id . "',CURRENT_TIMESTAMP(),'終了',0);";
            $result = mysqli_query($con, $sql);
            if (!$result) {
                exit;
            }
            $return_message_text = "キャンセルしたよ";
            sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
            exit;
        }

        //受け取った情報をもとに検索
       $ym=explode("/", $message_text);
       $return_message_text="http://tatara.sakura.ne.jp/yufuyufu/carender.php?y=".$ym[0]."&m=".$ym[1]."&user=".$user_id."";

        //flg処理
        $sql = "INSERT INTO message(u_id,regi_date,status,stcnt) 
                    VALUES ('" . $user_id . "',CURRENT_TIMESTAMP(),'終了',0);";
        $result = mysqli_query($con, $sql);
        if (!$result) {
            exit;
        }
        sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
    }
}



?>



<?php
//メッセージの送信
function sending_messages($accessToken, $replyToken, $message_type, $return_message_text)
{
    //レスポンスフォーマット
    $response_format_text = [
        "type" => $message_type,
        "text" => $return_message_text
    ];

    //ポストデータ
    $post_data = [
        "replyToken" => $replyToken,
        "messages" => [$response_format_text]
    ];

    //curl実行
    $ch = curl_init("https://api.line.me/v2/bot/message/reply");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charser=UTF-8',
        'Authorization: Bearer ' . $accessToken
    ));
    $result = curl_exec($ch);
    curl_close($ch);
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

?>