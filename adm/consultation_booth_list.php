<?php
$sub_menu = '900400';
require_once('../common.php');
require_once('./admin.lib.php');
require_once(G5_BBS_PATH . '/consultation_config.php');
if (!$is_admin) { alert('최고관리자만 접근 가능합니다.'); exit; }

$f_type  = isset($_GET['ct_type']) ? trim($_GET['ct_type']) : '';
$f_ct_id = isset($_GET['ct_id'])   ? (int)$_GET['ct_id']   : 0;
$all     = cons_get_times('', false);

if ($f_ct_id)        { $display=array(); foreach($all as $t){if((int)$t['ct_id']===$f_ct_id){$display[]=$t;break;}} }
elseif ($f_type)     { $display=array(); foreach($all as $t){if($t['ct_type']===$f_type)$display[]=$t;} }
else                 { $display=$all; }

$data = array();
foreach ($display as $t) {
    $cid=(int)$t['ct_id'];
    $res=sql_query("SELECT ca_id,ca_name,ca_school,ca_booth FROM ".CONS_APP_TABLE." WHERE ct_id=$cid AND ca_status IN ('confirmed','completed') AND ca_booth IS NOT NULL ORDER BY ca_booth");
    $asgn=array(); while($r=sql_fetch_array($res)) $asgn[$r['ca_booth']]=$r;
    $data[$cid]=array('time'=>$t,'asgn'=>$asgn,'c'=>cons_count_by_status($cid,'confirmed'),'w'=>cons_count_by_status($cid,'waiting'));
}

$g5['title']='부스 배정 현황';
include_once('./admin.head.php');
?>
<style>
.fb{display:flex;gap:8px;align-items:center;margin-bottom:14px;flex-wrap:wrap}
.fb select{padding:6px 8px;border:1px solid #ccc;border-radius:4px;font-size:13px}
.bs{margin-bottom:26px}.bs h3{font-size:13px;font-weight:700;color:#333;background:#f0f4ff;border-left:4px solid #4a6fd4;padding:7px 13px;margin:0 0 6px}
.bs .sm{font-size:12px;color:#666;margin-bottom:6px}
.bg{display:grid;grid-template-columns:repeat(10,1fr);gap:4px}
.bc{border:1px solid #e0e0e0;border-radius:4px;padding:5px 3px;text-align:center;font-size:10px;min-height:48px;background:#fafafa}
.bc.a{background:#e8f5e9;border-color:#81c784}
.bc .bn{font-weight:700;font-size:12px;display:block;color:#333}.bc.a .bn{color:#2e7d32}
.bc .nm{display:block;margin-top:2px;font-size:10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#555}
.bc a{color:inherit;text-decoration:none}.bc a:hover{text-decoration:underline}
@media(max-width:800px){.bg{grid-template-columns:repeat(5,1fr)}}
</style>
<div class="content-head">
  <h2 class="h_2">부스 배정 현황</h2>
  <div style="float:right"><a href="./consultation_auto_assign.php" class="btn_01">자동 부스 배정</a></div>
</div>
<form method="get">
<div class="fb">
  <select name="ct_type">
    <option value="">전체 구분</option>
    <option value="middle" <?php if($f_type==='middle')echo'selected'?>>중등</option>
    <option value="high"   <?php if($f_type==='high')echo'selected'?>>고등</option>
  </select>
  <select name="ct_id">
    <option value="">전체 시간대</option>
    <?php foreach($all as $t): ?>
    <option value="<?php echo $t['ct_id'] ?>" <?php if($f_ct_id===(int)$t['ct_id'])echo'selected'?>>
      <?php echo cons_type_label($t['ct_type']).' '.$t['ct_round'].'차 ('.cons_format_time($t['ct_start_time']).'~'.cons_format_time($t['ct_end_time']).')' ?>
    </option>
    <?php endforeach ?>
  </select>
  <button type="submit" class="btn_01">조회</button>
  <a href="?" class="btn_02">초기화</a>
</div>
</form>
<?php if(empty($display)): ?>
<p style="color:#888;padding:16px 0">표시할 시간대가 없습니다.</p>
<?php else: ?>
<?php foreach($data as $cid=>$d):
    $t=$d['time']; $max=(int)$t['ct_max_applicants']; $booths=cons_booth_list($t['ct_type'],$max); ?>
<div class="bs">
  <h3><?php echo cons_type_label($t['ct_type']).' '.$t['ct_round'].'차 ('.cons_format_time($t['ct_start_time']).'~'.cons_format_time($t['ct_end_time']).')' ?></h3>
  <p class="sm">확정 <strong><?php echo $d['c'] ?></strong>명 | 대기 <strong><?php echo $d['w'] ?></strong>명 | 배정 <strong><?php echo count($d['asgn']) ?></strong>/<?php echo $max ?></p>
  <div class="bg">
  <?php foreach($booths as $bn): $app=isset($d['asgn'][$bn])?$d['asgn'][$bn]:null; ?>
    <div class="bc <?php echo $app?'a':'' ?>">
      <span class="bn"><?php echo htmlspecialchars($bn) ?></span>
      <?php if($app): ?>
      <span class="nm"><a href="./consultation_detail.php?ca_id=<?php echo $app['ca_id'] ?>"><?php echo htmlspecialchars(mb_substr($app['ca_name'],0,4,'UTF-8')) ?></a></span>
      <span class="nm" style="color:#888"><?php echo htmlspecialchars(mb_substr($app['ca_school'],0,5,'UTF-8')) ?></span>
      <?php else: ?><span class="nm" style="color:#ccc">-</span><?php endif ?>
    </div>
  <?php endforeach ?>
  </div>
</div>
<?php endforeach ?>
<?php endif ?>
<?php include_once('./admin.tail.php') ?>
