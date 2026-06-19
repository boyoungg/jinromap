<?php
$sub_menu = '900500';
require_once('../common.php');
require_once('./admin.lib.php');
require_once(G5_BBS_PATH . '/consultation_config.php');
if (!$is_admin) { alert('최고관리자만 접근 가능합니다.'); exit; }

$ca_id = isset($_GET['ca_id']) ? (int)$_GET['ca_id'] : 0;
$ct_id = isset($_GET['ct_id']) ? (int)$_GET['ct_id'] : 0;

// 단일 신청자 배정
if ($ca_id) {
    if (function_exists('check_token') && !check_token()) { alert('토큰 오류입니다.', './consultation_list.php'); exit; }
    $res = cons_auto_assign_booth($ca_id, $member['mb_id']);
    $back = "./consultation_detail.php?ca_id=$ca_id";
    if ($res) alert('부스 '.$res.'가 배정되었습니다.', $back);
    else      alert('자동 배정 실패 (이미 배정됐거나 빈 부스 없음)', $back);
    exit;
}

// 특정 시간대 전체 배정
if ($ct_id && isset($_GET['token'])) {
    if (function_exists('check_token') && !check_token()) { alert('토큰 오류입니다.', './consultation_booth_list.php'); exit; }
    $res = sql_query("SELECT ca_id FROM ".CONS_APP_TABLE." WHERE ct_id=$ct_id AND ca_status='confirmed' AND ca_booth IS NULL");
    $done=0; while($r=sql_fetch_array($res)){if(cons_auto_assign_booth((int)$r['ca_id'],$member['mb_id']))$done++;}
    alert($done.'명 자동 배정 완료.', './consultation_booth_list.php?ct_id='.$ct_id);
    exit;
}

// 전체 배정 (POST)
if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (function_exists('check_token') && !check_token()) { alert('토큰 오류입니다.', './consultation_booth_list.php'); exit; }
    $res = sql_query("SELECT ca_id FROM ".CONS_APP_TABLE." WHERE ca_status='confirmed' AND ca_booth IS NULL");
    $done=0; while($r=sql_fetch_array($res)){if(cons_auto_assign_booth((int)$r['ca_id'],$member['mb_id']))$done++;}
    alert('전체 '.$done.'명 부스 자동 배정 완료.', './consultation_booth_list.php');
    exit;
}

// 미배정 목록 표시
$res = sql_query("SELECT a.ca_id,a.ca_name,a.ca_school,a.ca_type,t.ct_round,t.ct_start_time,t.ct_end_time FROM ".CONS_APP_TABLE." a LEFT JOIN ".CONS_TIME_TABLE." t ON a.ct_id=t.ct_id WHERE a.ca_status='confirmed' AND a.ca_booth IS NULL ORDER BY t.ct_type,t.ct_round,a.ca_id");
$unassigned=array(); while($r=sql_fetch_array($res)) $unassigned[]=$r;

$token = get_token();
$g5['title']='자동 부스 배정';
include_once('./admin.head.php');
?>
<style>
.cs-tbl{width:100%;border-collapse:collapse;font-size:13px}
.cs-tbl th{background:#f5f6fa;padding:8px 12px;border:1px solid #dde1e9;text-align:center}
.cs-tbl td{padding:7px 12px;border:1px solid #eef0f5;text-align:center}
.cs-tbl tr:hover td{background:#f9faff}
.wb{background:#fff3e0;border:1px solid #ffe0b2;border-radius:6px;padding:14px 18px;margin-bottom:16px;font-size:13px;color:#e65100}
</style>
<div class="content-head">
  <h2 class="h_2">자동 부스 배정</h2>
  <div style="float:right"><a href="./consultation_booth_list.php" class="btn_02">부스 현황</a></div>
</div>
<?php if(empty($unassigned)): ?>
<div class="wb">부스 미배정 확정 신청자가 없습니다.</div>
<?php else: ?>
<div class="wb">
  확정 상태이지만 부스 미배정 신청자: <strong><?php echo count($unassigned) ?>명</strong><br>
  아래 버튼으로 빈 부스를 순서대로 자동 배정합니다.
</div>
<form method="post">
  <input type="hidden" name="token" value="<?php echo $token ?>">
  <button type="submit" class="btn_01" onclick="return confirm('전체 <?php echo count($unassigned) ?>명에게 부스를 자동 배정하시겠습니까?')">
    전체 자동 배정 실행 (<?php echo count($unassigned) ?>명)
  </button>
</form>
<br>
<table class="cs-tbl">
<thead><tr><th>ID</th><th>구분</th><th>시간대</th><th>이름</th><th>학교</th><th>개별 배정</th></tr></thead>
<tbody>
<?php foreach($unassigned as $u): ?>
<tr>
  <td><?php echo $u['ca_id'] ?></td>
  <td><?php echo cons_type_label($u['ca_type']) ?></td>
  <td><?php echo isset($u['ct_round'])?$u['ct_round'].'차 '.cons_format_time($u['ct_start_time']).'~'.cons_format_time($u['ct_end_time']):'-' ?></td>
  <td><?php echo htmlspecialchars($u['ca_name']) ?></td>
  <td><?php echo htmlspecialchars($u['ca_school']) ?></td>
  <td><a href="?ca_id=<?php echo $u['ca_id'] ?>&token=<?php echo get_token() ?>" class="btn_03" onclick="return confirm('자동 배정하시겠습니까?')">배정</a></td>
</tr>
<?php endforeach ?>
</tbody>
</table>
<?php endif ?>
<?php include_once('./admin.tail.php') ?>
