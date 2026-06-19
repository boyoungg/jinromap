<?php
$sub_menu = '900200';
require_once('../common.php');
require_once('./admin.lib.php');
require_once(G5_BBS_PATH . '/consultation_config.php');
if (!$is_admin) { alert('최고관리자만 접근 가능합니다.'); exit; }

$all = cons_get_times('', false);
$cnts = array();
foreach ($all as $t) {
    $cid = (int)$t['ct_id'];
    $cnts[$cid] = array(
        'c' => cons_count_by_status($cid,'confirmed'),
        'w' => cons_count_by_status($cid,'waiting'),
    );
}

$g5['title'] = '시간대 관리';
include_once('./admin.head.php');
?>
<style>
.cs-tbl{width:100%;border-collapse:collapse;font-size:13px}
.cs-tbl th{background:#f5f6fa;padding:9px 10px;border:1px solid #dde1e9;text-align:center;font-weight:bold;color:#555}
.cs-tbl td{padding:8px 10px;border:1px solid #eef0f5;text-align:center;vertical-align:middle}
.cs-tbl tr:hover td{background:#f9faff}
.bc{background:#e8f5e9;color:#2e7d32;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:700}
.bw{background:#fff3e0;color:#e65100;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:700}
</style>
<div class="content-head">
  <h2 class="h_2">시간대 관리</h2>
  <div style="float:right">
    <a href="./consultation_times_form.php" class="btn_01">+ 시간대 추가</a>
    <a href="./consultation_main.php" class="btn_02">대시보드</a>
  </div>
</div>
<?php if (empty($all)): ?>
<p style="color:#aaa;padding:16px 0">등록된 시간대가 없습니다. <a href="./consultation_times_form.php">추가하세요.</a></p>
<?php else: ?>
<table class="cs-tbl">
<thead><tr><th>ID</th><th>구분</th><th>회차</th><th>시작</th><th>종료</th><th>최대신청</th><th>최대대기</th><th>확정</th><th>대기</th><th>활성</th><th>관리</th></tr></thead>
<tbody>
<?php foreach ($all as $t):
    $cid=(int)$t['ct_id']; $c=$cnts[$cid]['c']; $w=$cnts[$cid]['w']; ?>
<tr>
  <td><?php echo $t['ct_id'] ?></td>
  <td><?php echo cons_type_label($t['ct_type']) ?></td>
  <td><?php echo $t['ct_round'] ?>차</td>
  <td><?php echo cons_format_time($t['ct_start_time']) ?></td>
  <td><?php echo cons_format_time($t['ct_end_time']) ?></td>
  <td><?php echo $t['ct_max_applicants'] ?>명</td>
  <td><?php echo $t['ct_max_waiting'] ?>명</td>
  <td><span class="bc"><?php echo $c ?>명</span></td>
  <td><?php echo $w>0?'<span class="bw">'.$w.'명</span>':'-' ?></td>
  <td><?php echo (int)$t['ct_is_active']?'<span style="color:#2e7d32;font-weight:700">사용</span>':'<span style="color:#aaa">미사용</span>' ?></td>
  <td>
    <a href="./consultation_times_form.php?ct_id=<?php echo $t['ct_id'] ?>" class="btn_03">수정</a>
    &nbsp;
    <a href="./consultation_list.php?ct_id=<?php echo $t['ct_id'] ?>" class="btn_04">신청자</a>
    &nbsp;
    <?php if ($c==0 && $w==0): ?>
    <a href="./consultation_times_delete.php?ct_id=<?php echo $t['ct_id'] ?>&token=<?php echo get_token() ?>"
       class="btn_05" onclick="return confirm('삭제하시겠습니까?')">삭제</a>
    <?php else: ?>
    <span style="color:#ccc;font-size:11px">삭제불가</span>
    <?php endif ?>
  </td>
</tr>
<?php endforeach ?>
</tbody>
</table>
<?php endif ?>
<?php include_once('./admin.tail.php') ?>
