<?php
$sub_menu = '900300';
require_once('../common.php');
require_once('./admin.lib.php');
require_once(G5_BBS_PATH . '/consultation_config.php');
if (!$is_admin) { alert('최고관리자만 접근 가능합니다.'); exit; }

$f_status = isset($_GET['ca_status']) ? trim($_GET['ca_status']) : '';
$f_type   = isset($_GET['ca_type'])   ? trim($_GET['ca_type'])   : '';
$f_ct_id  = isset($_GET['ct_id'])     ? (int)$_GET['ct_id']     : 0;
$f_kw     = isset($_GET['kw'])        ? trim($_GET['kw'])        : '';
$page     = isset($_GET['page'])      ? max(1,(int)$_GET['page']): 1;
$per      = 20;

$w = array('1=1');
if ($f_status && in_array($f_status,array('confirmed','waiting','cancelled','completed')))
    $w[] = "a.ca_status='".cons_esc($f_status)."'";
if ($f_type && in_array($f_type,array('middle','high')))
    $w[] = "a.ca_type='".cons_esc($f_type)."'";
if ($f_ct_id) $w[] = "a.ct_id=$f_ct_id";
if ($f_kw) {
    $ek = cons_esc($f_kw);
    $w[] = "(a.ca_name LIKE '%$ek%' OR a.ca_school LIKE '%$ek%' OR a.mb_id LIKE '%$ek%' OR a.ca_phone LIKE '%$ek%')";
}
$wsql = implode(' AND ',$w);

$total = (int)sql_fetch("SELECT COUNT(*) AS cnt FROM ".CONS_APP_TABLE." a WHERE $wsql")['cnt'];
$pages = max(1,(int)ceil($total/$per));
$off   = ($page-1)*$per;

$res = sql_query("SELECT a.*,t.ct_round,t.ct_start_time,t.ct_end_time FROM ".CONS_APP_TABLE." a LEFT JOIN ".CONS_TIME_TABLE." t ON a.ct_id=t.ct_id WHERE $wsql ORDER BY a.ca_applied_at DESC LIMIT $off,$per");
$rows = array(); while ($r=sql_fetch_array($res)) $rows[]=$r;

$all_times = cons_get_times('',false);
$g5['title'] = '신청자 목록';
include_once('./admin.head.php');
?>
<style>
.cs-tbl{width:100%;border-collapse:collapse;font-size:13px}
.cs-tbl th{background:#f5f6fa;padding:8px 10px;border:1px solid #dde1e9;text-align:center;font-weight:700;color:#555}
.cs-tbl td{padding:7px 10px;border:1px solid #eef0f5;vertical-align:middle}
.cs-tbl tr:hover td{background:#f9faff}
.fb{display:flex;gap:6px;flex-wrap:wrap;align-items:center;margin-bottom:12px}
.fb select,.fb input[type=text]{padding:6px 8px;border:1px solid #ccc;border-radius:4px;font-size:13px}
.fb input[type=text]{width:140px}
.sb{display:inline-block;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:700}
.s-confirmed{background:#e8f5e9;color:#2e7d32}.s-waiting{background:#fff3e0;color:#e65100}
.s-cancelled{background:#fce4ec;color:#c62828}.s-completed{background:#e3f2fd;color:#1565c0}
.pg{margin-top:12px;text-align:center}
.pg a,.pg strong{display:inline-block;padding:4px 9px;margin:0 2px;border:1px solid #ddd;border-radius:4px;font-size:13px;color:#555;text-decoration:none}
.pg strong{background:#4a6fd4;color:#fff;border-color:#4a6fd4}.pg a:hover{background:#f0f4ff}
</style>
<div class="content-head">
  <h2 class="h_2">신청자 목록 <small style="font-size:13px;color:#888">(총 <?php echo $total ?>명)</small></h2>
  <div style="float:right"><a href="./consultation_main.php" class="btn_02">대시보드</a></div>
</div>
<form method="get">
<div class="fb">
  <select name="ca_type">
    <option value="">전체 구분</option>
    <option value="middle" <?php if($f_type==='middle')echo'selected'?>>중등</option>
    <option value="high"   <?php if($f_type==='high')echo'selected'?>>고등</option>
  </select>
  <select name="ct_id">
    <option value="">전체 시간대</option>
    <?php foreach($all_times as $t): ?>
    <option value="<?php echo $t['ct_id'] ?>" <?php if($f_ct_id===(int)$t['ct_id'])echo'selected'?>>
      <?php echo cons_type_label($t['ct_type']).' '.$t['ct_round'].'차' ?>
    </option>
    <?php endforeach ?>
  </select>
  <select name="ca_status">
    <option value="">전체 상태</option>
    <option value="confirmed" <?php if($f_status==='confirmed')echo'selected'?>>신청 확정</option>
    <option value="waiting"   <?php if($f_status==='waiting')echo'selected'?>>대기 중</option>
    <option value="completed" <?php if($f_status==='completed')echo'selected'?>>참여 완료</option>
    <option value="cancelled" <?php if($f_status==='cancelled')echo'selected'?>>취소</option>
  </select>
  <input type="text" name="kw" value="<?php echo htmlspecialchars($f_kw) ?>" placeholder="이름/학교/아이디/전화">
  <button type="submit" class="btn_01">검색</button>
  <a href="?" class="btn_02">초기화</a>
</div>
</form>
<?php if (empty($rows)): ?>
<p style="color:#aaa;padding:14px 0">검색 결과가 없습니다.</p>
<?php else: ?>
<table class="cs-tbl">
<thead><tr><th>ID</th><th>구분</th><th>회차</th><th>이름</th><th>학교</th><th>학년</th><th>연락처</th><th>부스</th><th>상태</th><th>신청일시</th><th>관리</th></tr></thead>
<tbody>
<?php foreach ($rows as $r): ?>
<tr>
  <td><?php echo $r['ca_id'] ?></td>
  <td><?php echo cons_type_label($r['ca_type']) ?></td>
  <td><?php echo isset($r['ct_round'])?$r['ct_round'].'차':'-' ?></td>
  <td><?php echo htmlspecialchars($r['ca_name']) ?></td>
  <td><?php echo htmlspecialchars($r['ca_school']) ?></td>
  <td><?php echo $r['ca_grade'] ?>학년</td>
  <td><?php echo htmlspecialchars($r['ca_phone']) ?></td>
  <td><?php echo $r['ca_booth']?'<strong>'.htmlspecialchars($r['ca_booth']).'</strong>':'<span style="color:#ccc">미배정</span>' ?></td>
  <td><span class="sb s-<?php echo $r['ca_status'] ?>"><?php echo cons_status_label($r['ca_status']) ?></span></td>
  <td style="font-size:11px;color:#888;white-space:nowrap"><?php echo isset($r['ca_applied_at'])?substr($r['ca_applied_at'],0,16):'-' ?></td>
  <td><a href="./consultation_detail.php?ca_id=<?php echo $r['ca_id'] ?>" class="btn_03">상세</a></td>
</tr>
<?php endforeach ?>
</tbody>
</table>
<?php if ($pages>1):
    $q='ca_status='.urlencode($f_status).'&ca_type='.urlencode($f_type).'&ct_id='.$f_ct_id.'&kw='.urlencode($f_kw); ?>
<div class="pg">
  <?php if($page>1): ?><a href="?<?php echo $q ?>&page=1">&laquo;</a><a href="?<?php echo $q ?>&page=<?php echo $page-1 ?>">&lt;</a><?php endif ?>
  <?php for($p=max(1,$page-4);$p<=min($pages,$page+4);$p++): ?>
    <?php if($p===$page): ?><strong><?php echo $p ?></strong><?php else: ?><a href="?<?php echo $q ?>&page=<?php echo $p ?>"><?php echo $p ?></a><?php endif ?>
  <?php endfor ?>
  <?php if($page<$pages): ?><a href="?<?php echo $q ?>&page=<?php echo $page+1 ?>">&gt;</a><a href="?<?php echo $q ?>&page=<?php echo $pages ?>">&raquo;</a><?php endif ?>
</div>
<?php endif ?>
<?php endif ?>
<?php include_once('./admin.tail.php') ?>
