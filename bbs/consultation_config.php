<?php
/**
 * 컨설팅 신청 관리 - 공통 설정 및 함수
 * G5 common.php 이후에 include 해야 합니다.
 */
if (!defined('_GNUBOARD_')) exit;

define('CONS_TIME_TABLE',  $g5['db_pre'] . 'consultation_times');
define('CONS_APP_TABLE',   $g5['db_pre'] . 'consultation_applications');
define('CONS_BOOTH_TABLE', $g5['db_pre'] . 'consultation_booth_assignments');
define('CONS_LOG_TABLE',   $g5['db_pre'] . 'consultation_status_logs');

function cons_esc($str) {
    global $g5;
    if (isset($g5['db']) && is_object($g5['db']) && method_exists($g5['db'], 'real_escape_string'))
        return $g5['db']->real_escape_string($str);
    return addslashes($str);
}

function cons_type_label($type)  { return $type === 'middle' ? '중등' : '고등'; }
function cons_format_time($t)    { return substr($t, 0, 5); }

function cons_status_label($s) {
    $m = array('confirmed'=>'신청 확정','waiting'=>'대기 중','cancelled'=>'취소','completed'=>'참여 완료');
    return isset($m[$s]) ? $m[$s] : $s;
}

function cons_booth_list($type, $max = 20) {
    $p = ($type === 'middle') ? 'A' : 'B';
    $list = array();
    for ($i = 1; $i <= $max; $i++) $list[] = $p . str_pad($i, 2, '0', STR_PAD_LEFT);
    return $list;
}

function cons_get_times($type = '', $active_only = true) {
    $w = array();
    if ($active_only) $w[] = "ct_is_active = 1";
    if ($type) $w[] = "ct_type = '" . cons_esc($type) . "'";
    $sql = "SELECT * FROM " . CONS_TIME_TABLE;
    if ($w) $sql .= " WHERE " . implode(' AND ', $w);
    $sql .= " ORDER BY ct_type, ct_round";
    $res = sql_query($sql);
    $rows = array();
    while ($row = sql_fetch_array($res)) $rows[] = $row;
    return $rows;
}

function cons_get_time($ct_id) {
    return sql_fetch("SELECT * FROM " . CONS_TIME_TABLE . " WHERE ct_id = " . (int)$ct_id);
}

function cons_count_by_status($ct_id, $status) {
    $row = sql_fetch("SELECT COUNT(*) AS cnt FROM " . CONS_APP_TABLE
        . " WHERE ct_id = " . (int)$ct_id . " AND ca_status = '" . cons_esc($status) . "'");
    return (int)$row['cnt'];
}

function cons_get_slot_info($ct_id) {
    $t = cons_get_time($ct_id);
    if (!$t) return null;
    $c = cons_count_by_status($ct_id, 'confirmed');
    $w = cons_count_by_status($ct_id, 'waiting');
    return array(
        'time'      => $t,
        'confirmed' => $c,
        'waiting'   => $w,
        'is_full'   => $c >= (int)$t['ct_max_applicants'],
        'wait_full' => $w >= (int)$t['ct_max_waiting'],
    );
}

function cons_user_has_applied($mb_id, $ca_type) {
    $row = sql_fetch("SELECT ca_id,ct_id,ca_type,ca_status FROM " . CONS_APP_TABLE
        . " WHERE mb_id = '" . cons_esc($mb_id) . "'"
        . " AND ca_type = '" . cons_esc($ca_type) . "'"
        . " AND ca_status IN ('confirmed','waiting')");
    return !empty($row['ca_id']) ? $row : false;
}

function cons_get_user_applications($mb_id) {
    $sql = "SELECT a.*, t.ct_round, t.ct_start_time, t.ct_end_time, t.ct_type
            FROM " . CONS_APP_TABLE . " a
            LEFT JOIN " . CONS_TIME_TABLE . " t ON a.ct_id = t.ct_id
            WHERE a.mb_id = '" . cons_esc($mb_id) . "'
            ORDER BY a.ca_applied_at DESC";
    $res = sql_query($sql);
    $rows = array();
    while ($row = sql_fetch_array($res)) $rows[] = $row;
    return $rows;
}

function cons_auto_assign_booth($ca_id, $assigned_by = 'auto') {
    $ca_id = (int)$ca_id;
    $app = sql_fetch("SELECT * FROM " . CONS_APP_TABLE . " WHERE ca_id = $ca_id");
    if (!$app || $app['ca_status'] !== 'confirmed') return false;
    if ($app['ca_booth']) return $app['ca_booth'];

    $ct_id = (int)$app['ct_id'];
    $type  = $app['ca_type'];
    $res   = sql_query("SELECT ca_booth FROM " . CONS_APP_TABLE
        . " WHERE ct_id = $ct_id AND ca_type = '" . cons_esc($type) . "'"
        . " AND ca_status IN ('confirmed','completed') AND ca_booth IS NOT NULL");
    $used = array();
    while ($r = sql_fetch_array($res)) $used[] = $r['ca_booth'];

    $booth = null;
    foreach (cons_booth_list($type) as $b) {
        if (!in_array($b, $used)) { $booth = $b; break; }
    }
    if (!$booth) return false;

    $eb = cons_esc($booth); $eby = cons_esc($assigned_by); $et = cons_esc($type);
    sql_query("UPDATE " . CONS_APP_TABLE . " SET ca_booth='$eb', ca_booth_assigned_at=NOW() WHERE ca_id=$ca_id", false);
    sql_query("INSERT INTO " . CONS_BOOTH_TABLE . " (ct_id,ca_id,cba_booth,cba_type,cba_assigned_by)"
        . " VALUES ($ct_id,$ca_id,'$eb','$et','$eby')"
        . " ON DUPLICATE KEY UPDATE cba_booth='$eb', cba_assigned_by='$eby', cba_assigned_at=NOW()", false);
    return $booth;
}

function cons_promote_waiting($ct_id, $ca_type, $changed_by = 'system') {
    $ct_id  = (int)$ct_id;
    $waiter = sql_fetch("SELECT * FROM " . CONS_APP_TABLE
        . " WHERE ct_id=$ct_id AND ca_type='" . cons_esc($ca_type) . "' AND ca_status='waiting'"
        . " ORDER BY ca_applied_at ASC LIMIT 1");
    if (!$waiter) return false;
    $wid = (int)$waiter['ca_id'];
    $eby = cons_esc($changed_by);
    sql_query("UPDATE " . CONS_APP_TABLE . " SET ca_status='confirmed', ca_updated_at=NOW() WHERE ca_id=$wid", false);
    sql_query("INSERT INTO " . CONS_LOG_TABLE . " (ca_id,csl_prev_status,csl_new_status,csl_changed_by,csl_reason)"
        . " VALUES ($wid,'waiting','confirmed','$eby','대기자 자동 승격')", false);
    cons_auto_assign_booth($wid, $changed_by);
    return $wid;
}

function cons_change_status($ca_id, $new_status, $changed_by, $reason = '') {
    $ca_id = (int)$ca_id;
    $app   = sql_fetch("SELECT * FROM " . CONS_APP_TABLE . " WHERE ca_id=$ca_id");
    if (!$app) return array('ok'=>false,'msg'=>'신청 정보를 찾을 수 없습니다.');
    if (!in_array($new_status, array('confirmed','waiting','cancelled','completed')))
        return array('ok'=>false,'msg'=>'유효하지 않은 상태입니다.');
    $prev = $app['ca_status'];
    if ($prev === $new_status) return array('ok'=>false,'msg'=>'동일한 상태입니다.');

    $en = cons_esc($new_status); $eby = cons_esc($changed_by);
    $er = cons_esc($reason);     $ep  = cons_esc($prev);

    if (in_array($new_status, array('cancelled','waiting')) && $app['ca_booth']) {
        sql_query("DELETE FROM " . CONS_BOOTH_TABLE . " WHERE ca_id=$ca_id", false);
        sql_query("UPDATE " . CONS_APP_TABLE
            . " SET ca_status='$en',ca_booth=NULL,ca_booth_assigned_at=NULL,ca_updated_at=NOW()"
            . " WHERE ca_id=$ca_id", false);
    } else {
        sql_query("UPDATE " . CONS_APP_TABLE . " SET ca_status='$en',ca_updated_at=NOW() WHERE ca_id=$ca_id", false);
    }
    sql_query("INSERT INTO " . CONS_LOG_TABLE . " (ca_id,csl_prev_status,csl_new_status,csl_changed_by,csl_reason)"
        . " VALUES ($ca_id,'$ep','$en','$eby','$er')", false);

    if ($new_status === 'confirmed' && !$app['ca_booth']) cons_auto_assign_booth($ca_id, $changed_by);
    if ($new_status === 'cancelled' && $prev === 'confirmed')
        cons_promote_waiting((int)$app['ct_id'], $app['ca_type'], $changed_by);

    return array('ok'=>true,'msg'=>'상태가 변경되었습니다.');
}
