<?php
require 'func.php';
$mail_error;
$pass_error;

session_start();

if (!empty($_POST['send'])) {

    //メールアドレスの入力判定
    if (!empty($_POST['mail'])) {

        $mail = $_POST['mail'];
        //＠マークの検出
        if (mb_substr_count($mail, '@') != 1) {
            $mail_error = '@マークの数がおかしいです、必ず一つ入力してください';
        }
    } else {
        $mail_error = '未入力';
    }
    if (!empty($_POST['pass'])) {
        $pass = $_POST['pass'];
    } else {
        $pass_error = '未入力';
    }
    if (empty($pass_error) && empty($mail_error)) {

        $con = mysqli_connect('mysql619.db.sakura.ne.jp', 'tatara', 'god0419sinobi', 'tatara_yufuyufu');
        if (!$con) {
            exit;
        }
        $sql = "SELECT name,category FROM grown_user WHERE mail='" . $mail . "' AND pass='" . $pass . "';";
        $result = mysqli_query($con, $sql);
        if (!$result) {
            exit;
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $_SESSION['name'] = $row['name'];
            $_SESSION['cate'] = $row['category'];
            header('location:top.php');
        }
        $error = "※メールアドレス又はパスワードが間違っています。\nもう一度入力しなおしてください";
    }
} else {
    // セッション変数を全て解除する
    $_SESSION = array();
    // セッションを切断するにはセッションクッキーも削除する。
    // Note: セッション情報だけでなくセッションを破壊する。
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 42000 * 60, '/');
    }
    // 最終的に、セッションを破壊する
    session_destroy();
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>サインインページ</title>
    <link rel="stylesheet" type="text/css" href="css/top.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
</head>

<body class="text-center">
    <b class="text-justify text-center"><?php echo $error; ?></b>
    <form class="form-signin" method="post">
        <h1 class="h3 mb-3 font-weight-normal">login</h1>
        <label for="inputEmail" class="sr-only">Emailアドレス</label>
        <input type="email" id="inputEmail" name="mail" class="form-control" placeholder="Emailアドレス" required autofocus>
        <label for="inputPassword" class="sr-only">パスワード</label>
        <input type="password" id="inputPassword" name="pass" class="form-control" placeholder="パスワード" required>
        <button class="btn btn-lg btn-secondary btn-block" type="submit" name="send" value="1" formaction="index.php">サインイン</button>
    </form>



    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js" integrity="sha384-6khuMg9gaYr5AxOqhkVIODVIvm9ynTT5J4V1cfthmT+emCG6yVmEZsRHdxlotUnm" crossorigin="anonymous"></script>
</body>

</html>