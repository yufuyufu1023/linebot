<?php

/* $_GETを展開 */
$py = filter_input(INPUT_GET, 'y');
$pm = filter_input(INPUT_GET, 'm');
$u_id = filter_input(INPUT_GET, 'user');
$sqlm = filter_input(INPUT_GET, 'm');
if(strlen($sqlm)==1){
    $sqlm="0".$sqlm;
}
/* 3ヵ月分のタイムスタンプを生成する */
try {
    $dt      = new DateTimeImmutable("$py-$pm-1 00:00:00");
    $dt_prev = $dt->sub(new DateInterval('P1M'));
    $dt_next = $dt->add(new DateInterval('P1M'));
} catch (Exception $e) {
    // 失敗したときは今月を基準にする
    $dt      = new DateTimeImmutable('first day of this month 00:00:00');
    $dt_prev = $dt->sub(new DateInterval('P1M'));
    $dt_next = $dt->add(new DateInterval('P1M'));
}

/* リンク・タイトル・フォーム再表示用 */
$py      = $dt->format('Y'); // これを行わない場合はXSS対策が別途必要
$pm      = $dt->format('n'); // これを行わない場合はXSS対策が別途必要
$current = $dt->format('Y年n月');
$prev    = $dt_prev->format('?\y=Y&\a\mp;\m=n');
$next    = $dt_next->format('?\y=Y&\a\mp;\m=n');
$prev   .= "&user=".$u_id;
$next   .= "&user=".$u_id;

/* カレンダー生成用パラメータ */
$max    = (int) $dt->format('t');           // 合計日数
$before = (int) $dt->format('w');           // 曜日オフセット(前)
$after  = (7 - ($before + $max) % 7) % 7;  // 曜日オフセット(後)
$today  = (int) (new DateTime)->format('d'); // 今日

/* 今日をハイライトするかどうか */
$hl = !$dt->diff(new DateTime('first day of this month 00:00:00'))->days;

/* カレンダー生成ロジック */
$rows = array_chunk(array_merge(
    array_fill(0, $before, ''),
    range(1, $max),
    array_fill(0, $after, '')
), 7);


$con = mysqli_connect('mysql619.db.sakura.ne.jp', 'tatara', 'god0419sinobi', 'tatara_yufuyufu');
if (!$con) {
    exit;
}
//予定取得
$sql = "SELECT title,DATE_FORMAT(start_time,'%k:%i') as start_time,DATE_FORMAT(end_time,'%k:%i') as end_time,DATE_FORMAT(sc_date,'%d') as sc_day FROM schedule WHERE u_id = '" . $u_id . "' AND DATE_FORMAT(sc_date, '%Y%m')=" . $py . $sqlm . ";";
$result = mysqli_query($con, $sql);
if (!$result) {
    exit;
}

$plan_row = array();
while ($pl_row = mysqli_fetch_assoc($result)) {
    $plan_row[] =$pl_row;
    $content_row[$pl_row["sc_day"]] .= $pl_row["title"]."<br>開始時刻".$pl_row["start_time"]."<br>終了時刻".$pl_row["end_time"]."<br><br>";
}
?>
<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="stylesheet" href="css/bootstrap.css">
<link rel="stylesheet" href="css/carender.css">
<meta charset="UTF-8">
<title>カレンダー</title>

<!--http://tatara.sakura.ne.jp/yufuyufu/carender.php?y=2020&m=02&user=Uafd84735877d2f95aabd438cb0a81301-->

<div id="content" class="w-100 p-3">
    <table class="mx-auto table table-bordered">
        <thead class="thead-light">
            <tr>
                <th><a href="<?= $prev ?>">←</a></th>
                <th colspan="5">
                    <h2><?= $current ?></h2>
                </th>
                <th><a href="<?= $next ?>">→</a></th>
            </tr>
        </thead>
        <tr>
            <th class="sun">日</th>
            <th>月</th>
            <th>火</th>
            <th>水</th>
            <th>木</th>
            <th>金</th>
            <th class="sat">土</th>
        </tr>
        <?php foreach ($rows as $row) : ?>
            <tr>
                <?php foreach ($row as $cell) : ?>

                    <?php if ($hl && $cell === $today) : ?>
                        <td <?php 
                                                $cnt=0;
                                                foreach ($plan_row as $p) : 
                                                    if ($cell == $p['sc_day']) {
                                                        echo 'data-toggle="modal" data-target="#myModal"  data-html="true" class="plan" data-content="'.$content_row[$p['sc_day']].'" data-day="'.$py."年".$pm."月".$cell."日".'"';
                                                    break;
                                                    }
                                                endforeach; 
                                                
                                                ?>class="today"><?= $cell ?></td>
                    <?php else : ?>
                        <td <?php 
                                                $cnt=0;
                                                $cntent="";
                                                foreach ($plan_row as $p) : 
                                                    if ($cell == $p['sc_day']) {
                                                        echo 'data-toggle="modal" data-target="#myModal"  data-html="true" class="plan" data-content="'.$content_row[$p['sc_day']].'" data-day="'.$py."年".$pm."月".$cell."日".'"';
                                                        break;
                                                    }
                                                endforeach; 
                                                
                                                ?>><?= $cell ?></td>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<!-- モーダルの設定 -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="閉じる">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
                <div class="modal-body">
                    <div class="form-group">
                        <label for="consultitle">スケジュール：</label>
                        <p id="replyContent"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    
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
        var contentdata = button.data('content');
        var daydata = button.data('day');
        var modal = $(this);
        modal.find('#replyContent').html(contentdata);
        modal.find('#exampleModalCenterTitle').text(daydata);

    })
</script>
<?php /*foreach ($plan_row as $p) : 
            if ($cell == $p['sc_day']) {
                echo 'data-toggle="modal" data-target="#myModal" data-userid="'.$u_id.'"';
            }
        endforeach; */?>