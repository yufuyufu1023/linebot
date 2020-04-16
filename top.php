<?php
session_start();
$u_name = $_SESSION['name'];
$u_cate = $_SESSION['cate'];
if (empty($u_name)) {
    header('location:index.php');
}
$con = mysqli_connect('mysql619.db.sakura.ne.jp', 'tatara', 'password', 'tatara_yufuyufu');
if (!$con) {
    exit;
}
$sql = "SELECT content,regi_date FROM report ORDER BY regi_date DESC";
$result = mysqli_query($con, $sql);
if (!$result) {
    exit;
}

//$rmes[] = "";
//while ($row = mysqli_fetch_assoc($result)) {
//    $rmes[] = $row;
//}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="css/bootstrap.css">
    <!--<link rel="stylesheet" type="text/css" href="css/top.css">-->
    <title></title>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="top.php">TOP</a>
        <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="ナビゲーションの切替">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarText">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="top.php">通報 <span class="sr-only">(現位置)</span></a>
                </li>
                <?php if ($u_cate == "先生") { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="consul.php">相談</a>
                    </li>
                <?php } ?>
            </ul>
            <span class="navbar-text">
                <a href="index.php">ログアウト</a>
            </span>
        </div>
    </nav>
    <div id="content" class="w-100 p-3">
        <table class="mx-auto table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th scope="col">通報日</th>
                    <th scope="col">通報内容</th>
                </tr>
            </thead>
            <?php
            $i = 1;
            while ($row = mysqli_fetch_assoc($result)) {
            ?>
                <tr>
                    <th scope="row"><?php echo $i++; ?></th>
                    <td><?php echo $row['regi_date']; ?></td>
                    <td><?php echo $row['content']; ?></td>

                </tr>
            <?php } ?>
        </table>
    </div>
    <script type="text/javascript" src="js/jquery-3.4.1.js"></script>
    <script type="text/javascript" src="js/bootstrap.bundle.js"></script>
</body>

</html>