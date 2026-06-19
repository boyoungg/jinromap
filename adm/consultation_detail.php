<?php
$sub_menu = '900300';
require_once('../common.php');
require_once('./admin.lib.php');
require_once(G5_BBS_PATH . '/consultation_config.php');
if (!$is_admin) { alert('최고관리자만 접근 가능합니다.'); exit; }

$ca_id = isset($_GET['ca_id']) ? (int)$_GET['ca_id'] : 0;
if (!$ca_id) { alert('올바르지 않은 접근입니다.', './consultation_list.php'); exit; }

$app = sql_fetch("SELECT a.*,t.ct_round,t.ct_start_time,t.ct_end_time,t.ct_max_applicants
                  FROM ".CONS_APP_TABLE." a
                  LEFT JOIN ".CONS_TIME_TABLE." t ON a.ct_id=t.ct_id
                  WHERE a.ca_id=$ca_id");
if (!$app) { alert('신청 정보를 찾을 수 없습니다.', './consultation_list.php'); exit; }

$logs = array();
$lr = sql_query("SELECT * FROM ".CONS_LOG_TABLE." WHERE ca_id=$ca_id ORDER BY csl_id DESC");
while ($l=sql_fetch_array($lr)) $logs[]=$l;

// 부스 목록
$booths = cons_booth_list($app['ca_type'], (int)$app['ct_max_applicants']);
$used_r = sql_query("SELECT ca_booth FROM ".CONS_APP_TABLE." WHERE ct_id=".(int)$app['ct_id']." AND ca_type='".cons_esc($app['ca_type'])."' AND ca_status IN ('confirmed','completed') AND ca_booth IS NOT NULL AND ca_id!=$ca_id");
$used = array(); while ($ur=sql_fetch_array($used_r)) $used[]=$ur['ca_booth'];

$token = get_token();
$g5['title'] = '신청자 상세';
include_once('./admin.head.php');
?>
<style>
.dt{width:100%;max-width:680px;border-collapse:collapse;font-size:13px;margin-bottom:18px}
.dt th{background:#f5f6fa;padding:9px 14px;border:1px solid #dde1e9;text-align:left;font-weight:700;color:#555;width:130px;white-space:nowrap}
.dt td{padding:9px 14px;border:1px solid #eef0f5}
.sb{display:inline-block;padding:3px 10px;border-radius:10px;font-size:12px;font-weight:700}
.s-confirmed{background:#e8f5e9;color:#2e7d32}.s-waiting{background:#fff3e0;color:#e65100}
.s-cancelled{background:#fce4ec;color:#c62828}.s-completed{background:#e3f2fd;color:#1565c0}
.st{font-size:14px;font-weight:700;color:#333;border-left:4px solid #4a6fd4;padding:6px 12px;background:#f0f4ff;margin:18px 0 10px}
.lt{width:100%;max-width:680px;border-collapse:collapse;font-size:12px}
.lt th{background:#f5f6fa;padding:7px 10px;border:1px solid #dde1e9;text-align:center}
.lt td{padding:6px 10px;border:1px solid #eef0f5;text-align:center;color:#555}
.ar{display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end;margin-top:12px}
.ar select,.ar input[type=text]{padding:7px 10px;border:1px solid #ccc;border-radius:4px;font-size:13px}
.ar input[type=text]{width:200px}
</style>
<div class="content-head">
  <h2 class="h_2">신청자 상세</h2>
  <div style="float:right"><a href="./consultation_list.php" class="btn_02">목록으로</a></div>
</div>

<div class="st">신청 정보</div>
<table class="dt">
  <tr><th>신청 ID</th><td><?php echo $app['ca_id'] ?></td></tr>
  <tr><th>구분</th><td><?php echo cons_type_label($app['ca_type']) ?></td></tr>
  <tr><th>시간대</th><td><?php echo isset($app['ct_round'])?$app['ct_round'].'차 ('.cons_format_time($app['ct_start_time']).'~'.cons_format_time($app['ct_end_time']).')':'-' ?></td></tr>
  <tr><th>이름</th><td><?php echo htmlspecialchars($app['ca_name']) ?></td></tr>
  <tr><th>학교</th><td><?php echo htmlspecialchars($app['ca_school']) ?></td></tr>
  <tr><th>학년</th><td><?php echo $app['ca_grade'] ?>학년</td></tr>
  <tr><th>연락처</th><td><?php echo htmlspecialchars($app['ca_phone']) ?></td></tr>
  <tr><th>보호자 연락처</th><td><?php echo htmlspecialchars($app['ca_parent_phone']) ?></td></tr>
  <tr><th>희망 진로</th><td><?php echo nl2br(htmlspecialchars($app['ca_career'])) ?></td></tr>
  <tr><th>상태</th><td><span class="sb s-<?php echo $app['ca_status'] ?>"><?php echo cons_status_label($app['ca_status']) ?></span></td></tr>
  <tr><th>배정 부스</th><td><?php echo $app['ca_booth']?'<strong style="font-size:18px;color:#4a6fd4">'.htmlspecialchars($app['ca_booth']).'</strong>':'<span style="color:#aaa">미배정</span>' ?></td></tr>
  <tr><th>신청일시</th><td><?php echo isset($app['ca_applied_at'])?$app['ca_applied_at']:'-' ?></td></tr>
</table>

<div class="st">상태 변경</div>
<form method="post" action="./consultation_status_proc.php">
  <input type="hidden" name="ca_id"  value="<?php echo $ca_id ?>">
  <input type="hidden" name="from"   value="detail">
  <input type="hidden" name="token"  value="<?php echo $token ?>">
  <div class="ar">
    <select name="new_status">
      <?php foreach(array('confirmed'=>'신청 확정','waiting'=>'대기 중','completed'=>'참여 완료','cancelled'=>'취소') as $v=>$l): ?>
      <option value="<?php echo $v ?>" <?php if($app['ca_status']===$v)echo'selected'?>><?php echo $l ?></option>
      <?php endforeach ?>
    </select>
    <input type="text" name="reason" placeholder="변경 사유 (선택)">
    <button type="submit" class="btn_01">상태 변경</button>
  </div>
</form>

<?php if (in_array($app['ca_status'],array('confirmed','completed'))): ?>
<div class="st">부스 수동 변경</div>
<form method="post" action="./consultation_booth_proc.php">
  <input type="hidden" name="ca_id"  value="<?php echo $ca_id ?>">
  <input type="hidden" name="token"  value="<?php echo get_token() ?>">
  <div class="ar">
    <select name="new_booth">
      <option value="">-- 부스 선택 --</option>
      <?php foreach($booths as $b): ?>
      <option value="<?php echo $b ?>" <?php if($app['ca_booth']===$b)echo'selected';if(in_array($b,$used))echo' disabled' ?>>
        <?php echo $b.($app['ca_booth']===$b?' (현재)':'') ?><?php if(in_array($b,$used))echo' (사용 중)' ?>
      </option>
      <?php endforeach ?>
    </select>
    <button type="submit" class="btn_01">부스 변경</button>
    <?php if (!$app['ca_booth']): ?>
    <a href="./consultation_auto_assign.php?ca_id=<?php echo $ca_id ?>&token=<?php echo get_token() ?>" class="btn_02">자동 배정</a>
    <?php endif ?>
  </div>
</form>
<?php endif ?>

<div class="st">상태 변경 이력</div>
<?php if (empty($logs)): ?>
<p style="color:#aaa;font-size:13px">이력이 없습니다.</p>
<?php else: ?>
<table class="lt">
<thead><tr><th>이전 상태</th><th>변경 상태</th><th>변경자</th><th>사유</th><th>변경일시</th></tr></thead>
<tbody>
<?php foreach($logs as $l): ?>
<tr>
  <td><?php echo $l['csl_prev_status']?cons_status_label($l['csl_prev_status']):'-' ?></td>
  <td><span class="sb s-<?php echo $l['csl_new_status'] ?>"><?php echo cons_status_label($l['csl_new_status']) ?></span></td>
  <td><?php echo htmlspecialchars($l['csl_changed_by']) ?></td>
  <td><?php echo htmlspecialchars($l['csl_reason']) ?></td>
  <td style="white-space:nowrap"><?php echo isset($l['csl_changed_at'])?$l['csl_changed_at']:'-' ?></td>
</tr>
<?php endforeach ?>
</tbody>
</table>
<?php endif ?>
<?php include_once('./admin.tail.php') ?>
