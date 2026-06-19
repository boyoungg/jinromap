<?php
$sub_menu = '900200';
require_once('../common.php');
require_once('./admin.lib.php');
require_once(G5_BBS_PATH . '/consultation_config.php');
if (!$is_admin) { alert('최고관리자만 접근 가능합니다.'); exit; }

$ct_id = isset($_GET['ct_id']) ? (int)$_GET['ct_id'] : 0;
$mode  = $ct_id ? 'edit' : 'add';
$time  = array();

if ($mode === 'edit') {
    $time = cons_get_time($ct_id);
    if (!$time) { alert('해당 시간대를 찾을 수 없습니다.', './consultation_times_list.php'); exit; }
}

$token = get_token();
$g5['title'] = ($mode === 'edit') ? '시간대 수정' : '시간대 추가';
include_once('./admin.head.php');
?>
<style>
.ft{width:100%;max-width:640px;border-collapse:collapse;font-size:13px}
.ft th{background:#f5f6fa;padding:10px 14px;border:1px solid #dde1e9;text-align:left;font-weight:700;color:#555;width:150px;white-space:nowrap;vertical-align:middle}
.ft td{padding:8px 14px;border:1px solid #eef0f5;vertical-align:middle}
.ft select,.ft input[type=time],.ft input[type=number]{padding:7px 10px;border:1px solid #ccc;border-radius:4px;font-size:13px}
.ft input[type=number]{width:80px}
.hint{font-size:12px;color:#888;margin-left:8px}
</style>
<div class="content-head">
  <h2 class="h_2"><?php echo htmlspecialchars($g5['title']) ?></h2>
  <div style="float:right"><a href="./consultation_times_list.php" class="btn_02">목록으로</a></div>
</div>
<form method="post" action="./consultation_times_proc.php" onsubmit="return vf(this)">
  <input type="hidden" name="mode"  value="<?php echo $mode ?>">
  <input type="hidden" name="ct_id" value="<?php echo $ct_id ?>">
  <input type="hidden" name="token" value="<?php echo $token ?>">
  <table class="ft">
    <tr>
      <th>구분 <span style="color:#e03">*</span></th>
      <td>
        <select name="ct_type" required>
          <option value="">-- 선택 --</option>
          <option value="middle" <?php if (isset($time['ct_type']) && $time['ct_type']==='middle') echo 'selected' ?>>중등</option>
          <option value="high"   <?php if (isset($time['ct_type']) && $time['ct_type']==='high')   echo 'selected' ?>>고등</option>
        </select>
      </td>
    </tr>
    <tr>
      <th>회차 <span style="color:#e03">*</span></th>
      <td>
        <select name="ct_round" required>
          <?php for ($i=1;$i<=8;$i++): ?>
          <option value="<?php echo $i ?>" <?php if (isset($time['ct_round']) && (int)$time['ct_round']===$i) echo 'selected' ?>><?php echo $i ?>차</option>
          <?php endfor ?>
        </select>
        <span class="hint">1차~8차</span>
      </td>
    </tr>
    <tr>
      <th>시작 시간 <span style="color:#e03">*</span></th>
      <td><input type="time" name="ct_start_time" value="<?php echo isset($time['ct_start_time'])?substr($time['ct_start_time'],0,5):'10:00' ?>" required></td>
    </tr>
    <tr>
      <th>종료 시간 <span style="color:#e03">*</span></th>
      <td><input type="time" name="ct_end_time"   value="<?php echo isset($time['ct_end_time'])?substr($time['ct_end_time'],0,5):'10:30' ?>" required></td>
    </tr>
    <tr>
      <th>최대 신청 인원</th>
      <td><input type="number" name="ct_max_applicants" min="1" max="100" value="<?php echo isset($time['ct_max_applicants'])?(int)$time['ct_max_applicants']:20 ?>"><span class="hint">팀 (기본 20)</span></td>
    </tr>
    <tr>
      <th>최대 대기 인원</th>
      <td><input type="number" name="ct_max_waiting" min="0" max="50" value="<?php echo isset($time['ct_max_waiting'])?(int)$time['ct_max_waiting']:3 ?>"><span class="hint">팀 (기본 3)</span></td>
    </tr>
    <tr>
      <th>사용 여부</th>
      <td>
        <label style="margin-right:14px"><input type="radio" name="ct_is_active" value="1" <?php if (!isset($time['ct_is_active'])||(int)$time['ct_is_active']===1) echo 'checked' ?>> 사용</label>
        <label><input type="radio" name="ct_is_active" value="0" <?php if (isset($time['ct_is_active'])&&(int)$time['ct_is_active']===0) echo 'checked' ?>> 미사용</label>
      </td>
    </tr>
  </table>
  <div style="margin-top:16px;display:flex;gap:8px">
    <button type="submit" class="btn_01"><?php echo $mode==='edit'?'수정 저장':'시간대 추가' ?></button>
    <a href="./consultation_times_list.php" class="btn_02">취소</a>
  </div>
</form>
<script>
function vf(f){
  var s=f.ct_start_time.value,e=f.ct_end_time.value;
  if(s&&e&&s>=e){alert('종료 시간은 시작 시간보다 늦어야 합니다.');f.ct_end_time.focus();return false;}
  return true;
}
</script>
<?php include_once('./admin.tail.php') ?>
