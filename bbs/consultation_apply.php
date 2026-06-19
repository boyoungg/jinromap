<?php
require_once('../common.php');
require_once(G5_BBS_PATH . '/consultation_config.php');

if (!$is_member) { alert('로그인이 필요합니다.', G5_BBS_URL.'/login.php?url='.urlencode($_SERVER['REQUEST_URI'])); exit; }

$ct_id = isset($_GET['ct_id']) ? (int)$_GET['ct_id'] : 0;
if (!$ct_id) { alert('올바르지 않은 접근입니다.', G5_URL); exit; }

$slot = cons_get_slot_info($ct_id);
if (!$slot) { alert('해당 시간대를 찾을 수 없습니다.', G5_URL); exit; }
if (!(int)$slot['time']['ct_is_active']) { alert('비활성 시간대입니다.', G5_URL); exit; }
if ($slot['is_full'] && $slot['wait_full']) { alert('신청 및 대기 모두 마감된 시간대입니다.', G5_URL); exit; }

// 이미 신청 여부 확인
$type = $slot['time']['ct_type'];
if (cons_user_has_applied($member['mb_id'], $type)) {
    alert(cons_type_label($type).' 컨설팅은 1인 1회만 신청 가능합니다.', G5_URL); exit;
}

$status_to_apply = $slot['is_full'] ? 'waiting' : 'confirmed';
$token = get_token();

$g5['title'] = '컨설팅 신청';
include_once(G5_PATH . '/head.php');
?>
<link rel="stylesheet" href="<?php echo G5_URL ?>/css/consultation.css">
<div class="cons-wrap">
  <h2 class="cons-section-title">컨설팅 신청</h2>
  <div class="cons-alert cons-alert-info" style="margin-bottom:20px">
    <strong><?php echo cons_type_label($type) ?> <?php echo $slot['time']['ct_round'] ?>차</strong>
    (<?php echo cons_format_time($slot['time']['ct_start_time']).'~'.cons_format_time($slot['time']['ct_end_time']) ?>)
    <?php if ($status_to_apply==='waiting'): ?>
    — <span style="color:#e65100">대기 신청</span> (대기 <?php echo $slot['waiting'] ?>/<?php echo $slot['time']['ct_max_waiting'] ?>)
    <?php endif ?>
  </div>
  <form class="cons-form" method="post" action="./consultation_apply_proc.php" onsubmit="return consValidateApply(this)">
    <input type="hidden" name="ct_id"   value="<?php echo $ct_id ?>">
    <input type="hidden" name="ca_type" value="<?php echo htmlspecialchars($type) ?>">
    <input type="hidden" name="token"   value="<?php echo $token ?>">

    <div class="cons-form-group">
      <label class="cons-form-label">이름 <span class="req">*</span></label>
      <input type="text" name="ca_name" class="cons-form-input" value="<?php echo htmlspecialchars($member['mb_name']) ?>" maxlength="50" required>
    </div>
    <div class="cons-form-group">
      <label class="cons-form-label">학교명 <span class="req">*</span></label>
      <input type="text" name="ca_school" class="cons-form-input" maxlength="100" required>
    </div>
    <div class="cons-form-group">
      <label class="cons-form-label">학년 <span class="req">*</span></label>
      <select name="ca_grade" class="cons-form-select" required>
        <option value="">-- 선택 --</option>
        <?php for($i=1;$i<=3;$i++) echo '<option value="'.$i.'">'.$i.'학년</option>' ?>
      </select>
    </div>
    <div class="cons-form-group">
      <label class="cons-form-label">연락처 <span class="req">*</span></label>
      <input type="tel" name="ca_phone" class="cons-form-input" value="<?php echo htmlspecialchars($member['mb_hp'] ?? '') ?>" placeholder="010-0000-0000" maxlength="20" required>
    </div>
    <div class="cons-form-group">
      <label class="cons-form-label">보호자 연락처 <span class="req">*</span></label>
      <input type="tel" name="ca_parent_phone" class="cons-form-input" placeholder="010-0000-0000" maxlength="20" required>
    </div>
    <div class="cons-form-group">
      <label class="cons-form-label">희망 진로 <span class="req">*</span></label>
      <textarea name="ca_career" class="cons-form-textarea" rows="4" required placeholder="희망하는 진로 및 상담받고 싶은 내용을 자유롭게 작성해주세요."></textarea>
    </div>
    <div style="display:flex;gap:10px;margin-top:20px">
      <button type="submit" class="cons-btn cons-btn-primary" style="width:auto;padding:10px 30px">
        <?php echo $status_to_apply==='waiting'?'대기 신청하기':'신청하기' ?>
      </button>
      <a href="<?php echo G5_URL ?>" class="cons-btn cons-btn-disabled" style="width:auto;padding:10px 20px">취소</a>
    </div>
  </form>
</div>
<script src="<?php echo G5_URL ?>/js/consultation.js"></script>
<?php include_once(G5_PATH . '/tail.php') ?>
