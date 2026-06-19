<?php
require_once('../common.php');
require_once('./admin.lib.php');
require_once(G5_BBS_PATH . '/consultation_config.php');
if (!$is_admin) { alert('최고관리자만 접근 가능합니다.'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { alert('올바르지 않은 접근입니다.', './consultation_list.php'); exit; }
if (function_exists('check_token') && !check_token()) { alert('토큰 오류입니다.', './consultation_list.php'); exit; }

$ca_id     = isset($_POST['ca_id'])     ? (int)$_POST['ca_id']       : 0;
$new_booth = isset($_POST['new_booth']) ? trim($_POST['new_booth'])   : '';
if (!$ca_id)     { alert('올바르지 않은 신청 ID입니다.', './consultation_list.php'); exit; }
if (!$new_booth) { alert('부스를 선택해주세요.', "./consultation_detail.php?ca_id=$ca_id"); exit; }

$back = "./consultation_detail.php?ca_id=$ca_id";
$app  = sql_fetch("SELECT * FROM ".CONS_APP_TABLE." WHERE ca_id=$ca_id");
if (!$app) { alert('신청 정보를 찾을 수 없습니다.', './consultation_list.php'); exit; }
if (!in_array($app['ca_status'],array('confirmed','completed'))) { alert('확정/완료 상태만 부스를 배정할 수 있습니다.', $back); exit; }

$booths = cons_booth_list($app['ca_type']);
if (!in_array($new_booth, $booths)) { alert('유효하지 않은 부스 번호입니다.', $back); exit; }

$ct_id = (int)$app['ct_id'];
$eb    = cons_esc($new_booth); $et = cons_esc($app['ca_type']);
$dup   = sql_fetch("SELECT ca_id FROM ".CONS_APP_TABLE." WHERE ct_id=$ct_id AND ca_type='$et' AND ca_booth='$eb' AND ca_status IN ('confirmed','completed') AND ca_id!=$ca_id");
if (!empty($dup['ca_id'])) { alert('해당 부스는 이미 다른 신청자에게 배정되어 있습니다.', $back); exit; }

$eby = cons_esc($member['mb_id']);
sql_query("DELETE FROM ".CONS_BOOTH_TABLE." WHERE ca_id=$ca_id", false);
sql_query("UPDATE ".CONS_APP_TABLE." SET ca_booth='$eb',ca_booth_assigned_at=NOW() WHERE ca_id=$ca_id", false);
sql_query("INSERT INTO ".CONS_BOOTH_TABLE." (ct_id,ca_id,cba_booth,cba_type,cba_assigned_by) VALUES ($ct_id,$ca_id,'$eb','$et','$eby') ON DUPLICATE KEY UPDATE cba_booth='$eb',cba_assigned_by='$eby',cba_assigned_at=NOW()", false);

alert('부스가 '.$new_booth.'으로 변경되었습니다.', $back);
exit;
