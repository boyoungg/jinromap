<?php
require_once('../common.php');
require_once(G5_BBS_PATH . '/consultation_config.php');

if (!$is_member) { alert('로그인이 필요합니다.', G5_BBS_URL.'/login.php?url='.urlencode($_SERVER['REQUEST_URI'])); exit; }

$apps = cons_get_user_applications($member['mb_id']);

$g5['title'] = '나의 컨설팅 신청';
include_once(G5_PATH . '/head.php');
?>
<link rel="stylesheet" href="<?php echo G5_URL ?>/css/consultation.css">
<div class="cons-wrap">
  <h2 class="cons-section-title">나의 컨설팅 신청 현황</h2>

  <?php if (empty($apps)): ?>
  <div class="cons-alert cons-alert-info">신청 내역이 없습니다.</div>
  <a href="<?php echo G5_URL ?>" class="cons-btn cons-btn-primary" style="width:auto;padding:10px 24px;display:inline-block;margin-top:10px">신청하러 가기</a>

  <?php else: ?>
  <?php foreach ($apps as $app):
      $slot = isset($app['ct_round']) ? $app : null; ?>
  <div class="cons-my-card">
    <div class="cons-my-card-header">
      <span class="cons-my-title">
        <?php echo cons_type_label($app['ca_type']) ?> <?php echo isset($app['ct_round'])?$app['ct_round'].'차':'' ?>
        컨설팅
      </span>
      <span class="cons-badge cons-badge-<?php echo $app['ca_status'] ?>">
        <?php echo cons_status_label($app['ca_status']) ?>
      </span>
    </div>

    <?php if ($slot && isset($slot['ct_start_time'])): ?>
    <div class="cons-my-row">
      <span class="cons-my-label">시간</span>
      <span class="cons-my-value"><?php echo cons_format_time($app['ct_start_time']).' ~ '.cons_format_time($app['ct_end_time']) ?></span>
    </div>
    <?php endif ?>

    <div class="cons-my-row">
      <span class="cons-my-label">신청자</span>
      <span class="cons-my-value"><?php echo htmlspecialchars($app['ca_name']) ?> / <?php echo htmlspecialchars($app['ca_school']) ?> <?php echo $app['ca_grade'] ?>학년</span>
    </div>
    <div class="cons-my-row">
      <span class="cons-my-label">신청일시</span>
      <span class="cons-my-value"><?php echo isset($app['ca_applied_at'])?substr($app['ca_applied_at'],0,16):'-' ?></span>
    </div>

    <?php if ($app['ca_booth']): ?>
    <div class="cons-my-booth">배정 부스: <?php echo htmlspecialchars($app['ca_booth']) ?></div>
    <?php elseif ($app['ca_status']==='waiting'): ?>
    <div class="cons-alert cons-alert-warning" style="margin-top:10px">대기 중입니다. 자리가 생기면 자동 확정됩니다.</div>
    <?php endif ?>

    <?php if (in_array($app['ca_status'],array('confirmed','waiting'))): ?>
    <div style="margin-top:12px">
      <a href="./consultation_cancel_proc.php?ca_id=<?php echo $app['ca_id'] ?>&token=<?php echo get_token() ?>"
         class="cons-btn cons-btn-disabled" style="width:auto;padding:7px 20px;display:inline-block;font-size:13px;background:#fce4ec;color:#c62828;cursor:pointer"
         onclick="return confirm('정말 취소하시겠습니까?')">신청 취소</a>
    </div>
    <?php endif ?>
  </div>
  <?php endforeach ?>
  <?php endif ?>
</div>
<script src="<?php echo G5_URL ?>/js/consultation.js"></script>
<?php include_once(G5_PATH . '/tail.php') ?>
