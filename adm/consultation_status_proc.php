<?php
require_once('../common.php');
require_once('./admin.lib.php');
require_once(G5_BBS_PATH . '/consultation_config.php');
if (!$is_admin) { alert('최고관리자만 접근 가능합니다.'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { alert('올바르지 않은 접근입니다.', './consultation_list.php'); exit; }
if (function_exists('check_token') && !check_token()) { alert('토큰 오류입니다.', './consultation_list.php'); exit; }

$ca_id      = isset($_POST['ca_id'])      ? (int)$_POST['ca_id']                  : 0;
$new_status = isset($_POST['new_status']) ? trim($_POST['new_status'])             : '';
$reason     = isset($_POST['reason'])     ? trim(strip_tags($_POST['reason']))     : '';
$from       = isset($_POST['from'])       ? trim($_POST['from'])                   : 'list';

$back = ($from==='detail'&&$ca_id) ? "./consultation_detail.php?ca_id=$ca_id" : './consultation_list.php';
if (!$ca_id) { alert('올바르지 않은 신청 ID입니다.', './consultation_list.php'); exit; }
if (!in_array($new_status,array('confirmed','waiting','cancelled','completed'))) { alert('유효하지 않은 상태입니다.', $back); exit; }

$res = cons_change_status($ca_id, $new_status, $member['mb_id'], $reason);
if ($res['ok']) alert($res['msg'], $back);
else            alert('오류: '.$res['msg'], $back);
exit;
