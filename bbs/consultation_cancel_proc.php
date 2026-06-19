<?php
require_once('../common.php');
require_once(G5_BBS_PATH . '/consultation_config.php');

if (!$is_member) { alert('로그인이 필요합니다.', G5_URL); exit; }
if (function_exists('check_token') && !check_token()) { alert('올바르지 않은 요청입니다.', G5_URL); exit; }

$ca_id = isset($_GET['ca_id']) ? (int)$_GET['ca_id'] : 0;
if (!$ca_id) { alert('올바르지 않은 요청입니다.', G5_BBS_URL.'/consultation_mypage.php'); exit; }

$app = sql_fetch("SELECT * FROM ".CONS_APP_TABLE." WHERE ca_id=$ca_id");
if (!$app) { alert('신청 정보를 찾을 수 없습니다.', G5_BBS_URL.'/consultation_mypage.php'); exit; }
if ($app['mb_id'] !== $member['mb_id']) { alert('본인 신청만 취소할 수 있습니다.', G5_BBS_URL.'/consultation_mypage.php'); exit; }
if (!in_array($app['ca_status'],array('confirmed','waiting'))) { alert('취소할 수 없는 상태입니다.', G5_BBS_URL.'/consultation_mypage.php'); exit; }

$result = cons_change_status($ca_id, 'cancelled', $member['mb_id'], '사용자 직접 취소');
if ($result['ok']) alert('신청이 취소되었습니다.', G5_BBS_URL.'/consultation_mypage.php');
else               alert('오류: '.$result['msg'], G5_BBS_URL.'/consultation_mypage.php');
exit;
