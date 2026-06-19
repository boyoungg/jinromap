<?php
require_once('../common.php');
require_once(G5_BBS_PATH . '/consultation_config.php');

if (!$is_member) { alert('로그인이 필요합니다.', G5_URL); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { alert('올바르지 않은 접근입니다.', G5_URL); exit; }
if (function_exists('check_token') && !check_token()) { alert('올바르지 않은 요청입니다.', G5_URL); exit; }

$ct_id         = isset($_POST['ct_id'])         ? (int)$_POST['ct_id']                    : 0;
$ca_type       = isset($_POST['ca_type'])       ? trim($_POST['ca_type'])                  : '';
$ca_name       = isset($_POST['ca_name'])       ? trim(strip_tags($_POST['ca_name']))      : '';
$ca_school     = isset($_POST['ca_school'])     ? trim(strip_tags($_POST['ca_school']))    : '';
$ca_grade      = isset($_POST['ca_grade'])      ? (int)$_POST['ca_grade']                 : 0;
$ca_phone      = isset($_POST['ca_phone'])      ? trim(strip_tags($_POST['ca_phone']))     : '';
$ca_parent     = isset($_POST['ca_parent_phone'])? trim(strip_tags($_POST['ca_parent_phone'])): '';
$ca_career     = isset($_POST['ca_career'])     ? trim(strip_tags($_POST['ca_career']))    : '';

if (!$ct_id || !in_array($ca_type,array('middle','high'))) { alert('올바르지 않은 요청입니다.', G5_URL); exit; }
if (!$ca_name||!$ca_school||!$ca_grade||!$ca_phone||!$ca_parent||!$ca_career) { alert('모든 항목을 입력해주세요.', 'javascript:history.back()'); exit; }
if ($ca_grade<1||$ca_grade>3) { alert('학년을 선택해주세요.', 'javascript:history.back()'); exit; }

$slot = cons_get_slot_info($ct_id);
if (!$slot) { alert('해당 시간대를 찾을 수 없습니다.', G5_URL); exit; }
if (!(int)$slot['time']['ct_is_active']) { alert('비활성 시간대입니다.', G5_URL); exit; }
if ($slot['is_full'] && $slot['wait_full']) { alert('신청 및 대기 모두 마감되었습니다.', G5_URL); exit; }

if (cons_user_has_applied($member['mb_id'], $ca_type)) {
    alert(cons_type_label($ca_type).' 컨설팅은 1인 1회만 신청 가능합니다.', G5_URL); exit;
}

$status  = $slot['is_full'] ? 'waiting' : 'confirmed';
$en   = cons_esc($ca_name);   $es  = cons_esc($ca_school); $ep = cons_esc($ca_phone);
$epp  = cons_esc($ca_parent); $ec  = cons_esc($ca_career); $et = cons_esc($ca_type);
$mb   = cons_esc($member['mb_id']); $est = cons_esc($status);

$sql = "INSERT INTO ".CONS_APP_TABLE." (ct_id,mb_id,ca_name,ca_school,ca_grade,ca_phone,ca_parent_phone,ca_career,ca_type,ca_status)"
     . " VALUES ($ct_id,'$mb','$en','$es',$ca_grade,'$ep','$epp','$ec','$et','$est')";
$r = sql_query($sql, false);
if (!$r) { alert('신청 중 오류가 발생했습니다. 잠시 후 다시 시도해주세요.', 'javascript:history.back()'); exit; }

$ca_id = sql_insert_id();

// 확정이면 부스 자동 배정
if ($status === 'confirmed') cons_auto_assign_booth($ca_id, $member['mb_id']);

if ($status === 'confirmed') {
    alert('신청이 완료되었습니다! 부스가 자동 배정되었습니다.\n마이페이지에서 배정 부스를 확인하세요.', G5_BBS_URL.'/consultation_mypage.php');
} else {
    alert('대기 신청이 완료되었습니다.\n확정 자리가 생기면 자동으로 승격됩니다.', G5_BBS_URL.'/consultation_mypage.php');
}
exit;
