<?php
require_once('../common.php');
require_once('./admin.lib.php');
require_once(G5_BBS_PATH . '/consultation_config.php');
if (!$is_admin) { alert('최고관리자만 접근 가능합니다.'); exit; }
$ct_id = isset($_GET['ct_id']) ? (int)$_GET['ct_id'] : 0;
if (!$ct_id) { alert('올바르지 않은 요청입니다.', './consultation_times_list.php'); exit; }
if (function_exists('check_token') && !check_token()) { alert('토큰 오류입니다.', './consultation_times_list.php'); exit; }
if (!cons_get_time($ct_id)) { alert('해당 시간대를 찾을 수 없습니다.', './consultation_times_list.php'); exit; }
if (cons_count_by_status($ct_id,'confirmed') > 0 || cons_count_by_status($ct_id,'waiting') > 0) {
    alert('확정/대기 신청자가 있어 삭제할 수 없습니다.', './consultation_times_list.php'); exit;
}
sql_query("DELETE FROM ".CONS_BOOTH_TABLE." WHERE ct_id=$ct_id", false);
sql_query("DELETE FROM ".CONS_APP_TABLE."   WHERE ct_id=$ct_id", false);
$r = sql_query("DELETE FROM ".CONS_TIME_TABLE." WHERE ct_id=$ct_id", false);
if ($r) alert('삭제되었습니다.', './consultation_times_list.php');
else    alert('삭제 중 오류가 발생했습니다.', './consultation_times_list.php');
exit;
