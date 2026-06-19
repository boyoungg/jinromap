<?php
require_once('../common.php');
require_once('./admin.lib.php');
require_once(G5_BBS_PATH . '/consultation_config.php');
if (!$is_admin) { alert('최고관리자만 접근 가능합니다.'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { alert('올바르지 않은 접근입니다.', './consultation_times_list.php'); exit; }
if (function_exists('check_token') && !check_token()) { alert('토큰 오류입니다.', './consultation_times_list.php'); exit; }

$mode  = isset($_POST['mode'])  ? $_POST['mode']  : '';
$ct_id = isset($_POST['ct_id']) ? (int)$_POST['ct_id'] : 0;
$ct_type           = isset($_POST['ct_type'])           ? trim($_POST['ct_type'])           : '';
$ct_round          = isset($_POST['ct_round'])          ? (int)$_POST['ct_round']           : 0;
$ct_start_time     = isset($_POST['ct_start_time'])     ? trim($_POST['ct_start_time'])     : '';
$ct_end_time       = isset($_POST['ct_end_time'])       ? trim($_POST['ct_end_time'])       : '';
$ct_max_applicants = isset($_POST['ct_max_applicants']) ? (int)$_POST['ct_max_applicants'] : 20;
$ct_max_waiting    = isset($_POST['ct_max_waiting'])    ? (int)$_POST['ct_max_waiting']    : 3;
$ct_is_active      = isset($_POST['ct_is_active'])      ? (int)$_POST['ct_is_active']      : 1;

$back = ($mode==='edit'&&$ct_id) ? './consultation_times_form.php?ct_id='.$ct_id : './consultation_times_form.php';

if (!in_array($ct_type, array('middle','high')))          { alert('구분을 선택해주세요.', $back); exit; }
if ($ct_round < 1 || $ct_round > 8)                      { alert('회차는 1~8이어야 합니다.', $back); exit; }
if (!preg_match('/^\d{2}:\d{2}$/', $ct_start_time) ||
    !preg_match('/^\d{2}:\d{2}$/', $ct_end_time))        { alert('시간 형식이 올바르지 않습니다.(HH:MM)', $back); exit; }
if ($ct_start_time >= $ct_end_time)                       { alert('종료 시간은 시작 시간보다 늦어야 합니다.', $back); exit; }
if ($ct_max_applicants < 1 || $ct_max_applicants > 100)  { alert('최대 신청 인원은 1~100이어야 합니다.', $back); exit; }
if ($ct_max_waiting < 0 || $ct_max_waiting > 50)         { alert('최대 대기 인원은 0~50이어야 합니다.', $back); exit; }
if ($mode==='edit' && !$ct_id)                            { alert('수정할 ID가 없습니다.', './consultation_times_list.php'); exit; }

$et = cons_esc($ct_type);
$es = cons_esc($ct_start_time.':00');
$ee = cons_esc($ct_end_time.':00');

$dw = "ct_type='$et' AND ct_round=$ct_round";
if ($mode==='edit') $dw .= " AND ct_id!=$ct_id";
$dup = sql_fetch("SELECT ct_id FROM ".CONS_TIME_TABLE." WHERE $dw LIMIT 1");
if (!empty($dup['ct_id'])) {
    alert(cons_type_label($ct_type).' '.$ct_round.'차 시간대가 이미 존재합니다.', $back); exit;
}

if ($mode === 'add') {
    $r = sql_query("INSERT INTO ".CONS_TIME_TABLE." (ct_round,ct_start_time,ct_end_time,ct_type,ct_max_applicants,ct_max_waiting,ct_is_active) VALUES ($ct_round,'$es','$ee','$et',$ct_max_applicants,$ct_max_waiting,$ct_is_active)", false);
    if (!$r) { alert('추가 중 오류가 발생했습니다.', $back); exit; }
    alert('시간대가 추가되었습니다.', './consultation_times_list.php');
} elseif ($mode === 'edit') {
    if (!cons_get_time($ct_id)) { alert('해당 시간대를 찾을 수 없습니다.', './consultation_times_list.php'); exit; }
    $r = sql_query("UPDATE ".CONS_TIME_TABLE." SET ct_round=$ct_round,ct_start_time='$es',ct_end_time='$ee',ct_type='$et',ct_max_applicants=$ct_max_applicants,ct_max_waiting=$ct_max_waiting,ct_is_active=$ct_is_active,ct_updated_at=NOW() WHERE ct_id=$ct_id", false);
    if (!$r) { alert('수정 중 오류가 발생했습니다.', $back); exit; }
    alert('시간대가 수정되었습니다.', './consultation_times_list.php');
} else {
    alert('올바르지 않은 모드입니다.', './consultation_times_list.php');
}
exit;
