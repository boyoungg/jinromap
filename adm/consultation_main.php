<?php
$sub_menu = '900100';
require_once('../common.php');
require_once('./admin.lib.php');
require_once(G5_BBS_PATH . '/consultation_config.php');
if (!$is_admin) { alert('최고관리자만 접근 가능합니다.'); exit; }

$all_times = cons_get_times('', false);
$total_times = count($all_times);
$total_confirmed = $total_waiting = $total_cancelled = $total_completed = 0;
$slot_info = array();

foreach ($all_times as $t) {
    $cid = (int)$t['ct_id'];
    $c = cons_count_by_status($cid, 'confirmed');
    $w = cons_count_by_status($cid, 'waiting');
    $total_confirmed += $c;
    $total_waiting   += $w;
    $total_cancelled += cons_count_by_status($cid, 'cancelled');
    $total_completed += cons_count_by_status($cid, 'completed');
    $slot_info[] = array('time'=>$t, 'confirmed'=>$c, 'waiting'=>$w);
}

$g5['title'] = '컨설팅 관리 대시보드';
include_once('./admin.head.php');
?>
<style>
.cs-grid{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:22px}
.cs-box{flex:1;min-width:110px;max-width:160px;background:#fff;border:1px solid #e0e4ed;border-radius:8px;padding:16px 12px;text-align:center}
.cs-box .v{font-size:26px;font-weight:700;color:#4a6fd4}.cs-box .l{font-size:12px;color:#888;margin-top:3px}
.cs-box.g .v{color:#2e7d32}.cs-box.o .v{color:#e65100}.cs-box.gr .v{color:#999}
.cs-tbl{width:100%;border-collapse:collapse;font-size:13px}
.cs-tbl th{background:#f5f6fa;padding:8px 10px;border:1px solid #dde1e9;text-align:center;font-weight:bold;color:#555}
.cs-tbl td{padding:7px 10px;border:1px solid #eef0f5;text-align:center;vertical-align:middle}
.cs-tbl tr:hover td{background:#f9faff}
.bc{background:#e8f5e9;color:#2e7d32;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:700}
.bw{background:#fff3e0;color:#e65100;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:700}
.bf{background:#fce4ec;color:#c62828;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:700}
.ql{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:18px}
.ql a{padding:7px 16px;background:#4a6fd4;color:#fff;border-radius:5px;font-size:13px;text-decoration:none}
.ql a:hover{background:#3a5fc4;color:#fff}.ql a.alt{background:#f5f6fa;color:#555;border:1px solid #dde1e9}.ql a.alt:hover{background:#e8eaf6}
</style>
<div class="content-head"><h2 class="h_2">컨설팅 관리 대시보드</h2></div>
<div class="ql">
  <a href="./consultation_times_list.php">시간대 관리</a>
  <a href="./consultation_list.php">신청자 목록</a>
  <a href="./consultation_booth_list.php">부스 배정 현황</a>
  <a href="./consultation_auto_assign.php">자동 부스 배정</a>
  <a href="./consultation_times_form.php" class="alt">+ 시간대 추가</a>
</div>
<h3 style="font-size:14px;color:#555;margin-bottom:8px">전체 통계</h3>
<div class="cs-grid">
  <div class="cs-box"><div class="v"><?php echo $total_times ?></div><div class="l">등록 시간대</div></div>
  <div class="cs-box g"><div class="v"><?php echo $total_confirmed ?></div><div class="l">신청 확정</div></div>
  <div class="cs-box o"><div class="v"><?php echo $total_waiting ?></div><div class="l">대기 중</div></div>
  <div class="cs-box"><div class="v"><?php echo $total_completed ?></div><div class="l">참여 완료</div></div>
  <div class="cs-box gr"><div class="v"><?php echo $total_cancelled ?></div><div class="l">취소</div></div>
  <div class="cs-box"><div class="v"><?php echo $total_confirmed + $total_waiting ?></div><div class="l">총 신청</div></div>
</div>
<h3 style="font-size:14px;color:#555;margin-bottom:8px">시간대별 현황</h3>
<?php if (empty($slot_info)): ?>
<p style="color:#aaa">등록된 시간대가 없습니다. <a href="./consultation_times_form.php">시간대를 추가</a>하세요.</p>
<?php else: ?>
<table class="cs-tbl">
<thead><tr><th>구분</th><th>회차</th><th>시간</th><th>최대</th><th>확정</th><th>대기</th><th>활성</th><th>관리</th></tr></thead>
<tbody>
<?php foreach ($slot_info as $si):
    $t=$si['time']; $max=(int)$t['ct_max_applicants']; $c=$si['confirmed']; $w=$si['waiting']; ?>
<tr>
  <td><?php echo cons_type_label($t['ct_type']) ?></td>
  <td><?php echo $t['ct_round'] ?>차</td>
  <td><?php echo cons_format_time($t['ct_start_time']).'~'.cons_format_time($t['ct_end_time']) ?></td>
  <td><?php echo $max ?>명</td>
  <td><?php if ($c>=$max): ?><span class="bf"><?php echo $c.'/'.$max ?> 마감</span><?php else: ?><span class="bc"><?php echo $c.'/'.$max ?></span><?php endif ?></td>
  <td><?php echo $w>0?'<span class="bw">'.$w.'명</span>':'<span style="color:#ccc">-</span>' ?></td>
  <td><?php echo (int)$t['ct_is_active']?'<span style="color:#2e7d32">●</span>':'<span style="color:#aaa">●</span>' ?></td>
  <td>
    <a href="./consultation_times_form.php?ct_id=<?php echo $t['ct_id'] ?>" class="btn_03">수정</a>
    <a href="./consultation_list.php?ct_id=<?php echo $t['ct_id'] ?>" class="btn_04">신청자</a>
  </td>
</tr>
<?php endforeach ?>
</tbody>
</table>
<?php endif ?>
<?php include_once('./admin.tail.php') ?>
