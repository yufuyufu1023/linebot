<?php
session_start();
$u_name = $_SESSION['name'];
$u_cate = $_SESSION['cate'];
if (empty($u_name)) {
    header('location:index.php');
}
if ($u_cate != "先生") {
    header('location:index.php');
}
$con = mysqli_connect('mysql619.db.sakura.ne.jp', 'tatara', 'password', 'tatara_yufuyufu');
if (!$con) {
    exit;
}
$sql = "SELECT content,regi_date,id,u_id FROM consultation WHERE reply='' ORDER BY id DESC";
$result = mysqli_query($con, $sql);
if (!$result) {
    exit;
}

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="css/bootstrap.css">
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
                <li class="nav-item">
                    <a class="nav-link" href="top.php">通報</a>
                </li>
                <li class="nav-item active">
                    <a class="nav-link" href="consul.php">相談 <span class="sr-only">(現位置)</span></a>
                </li>
            </ul>
            <span class="navbar-text">
                <a href="index.php">ログアウト</a>
            </span>
        </div>
    </nav>
    <div id="content" class="w-100 p-3">
        <table class="mx-auto table table-bordered table-hover">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th scope="col">相談日</th>
                    <th scope="col">相談内容</th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <?php
            $i = 1;
            while ($row = mysqli_fetch_assoc($result)) {
            ?>
                <tr>
                    <th scope=" row"><?php echo $i++; ?></th>
                    <td><?php echo $row['regi_date']; ?></td>
                    <td><?php echo $row['content']; ?></td>
                    <td><button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#myModal" data-userid="<?php echo $row['u_id']; ?>" data-content="<?php echo $row['content']; ?>" data-id="<?php echo $row['id']; ?>">返信</button></td>

                </tr>
            <?php } ?>
        </table>
    </div>

    <!-- モーダルの設定 -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">返信内容を入力してください</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="閉じる">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="get">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="consultitle">相談内容:</label>
                            <p id="replyContent"></p>
                            <input type="hidden" name="repcon" id="repcon" value="">
                        </div>
                        <div class="form-group">
                            <textarea class="form-control" rows="3" name="reply" placeholder="返信内容"></textarea>
                        </div>
                        <input type="hidden" name="id" id="replyId" value="">
                        <input type="hidden" name="u_id" id="uid" value="">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-secondary" formaction="reply.php">送信</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="js/jquery-3.4.1.js"></script>
    <script type="text/javascript" src="js/bootstrap.bundle.js"></script>
    <script>
        $('#myModal').on('shown.bs.modal', function() {
            $('#myInput').trigger('focus')
        })
        // モーダルにパラメータ渡し
        $('#myModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var iddata = button.data('id');
            var contentdata = button.data('content');
            var uiddata = button.data('userid');
            var modal = $(this);
            modal.find('#replyId').val(iddata);
            modal.find('#replyContent').text(contentdata);
            modal.find('#repcon').val(contentdata);
            modal.find('#uid').val(uiddata);
        })
    </script>

</body>

</html>