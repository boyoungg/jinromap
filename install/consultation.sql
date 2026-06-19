-- ============================================================
-- 컨설팅 신청 관리 시스템 DB 스키마
-- ============================================================

CREATE TABLE IF NOT EXISTS `g5_consultation_times` (
  `ct_id`             INT(11) NOT NULL AUTO_INCREMENT,
  `ct_round`          TINYINT(2) NOT NULL COMMENT '회차(1~8)',
  `ct_start_time`     TIME NOT NULL,
  `ct_end_time`       TIME NOT NULL,
  `ct_type`           ENUM('middle','high') NOT NULL COMMENT '중등/고등',
  `ct_max_applicants` SMALLINT(4) NOT NULL DEFAULT 20,
  `ct_max_waiting`    SMALLINT(4) NOT NULL DEFAULT 3,
  `ct_is_active`      TINYINT(1) NOT NULL DEFAULT 1,
  `ct_created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ct_updated_at`     DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ct_id`),
  UNIQUE KEY `uq_round_type` (`ct_round`,`ct_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='컨설팅 시간대';

CREATE TABLE IF NOT EXISTS `g5_consultation_applications` (
  `ca_id`               INT(11) NOT NULL AUTO_INCREMENT,
  `ct_id`               INT(11) NOT NULL,
  `mb_id`               VARCHAR(20) NOT NULL,
  `ca_name`             VARCHAR(50) NOT NULL,
  `ca_school`           VARCHAR(100) NOT NULL,
  `ca_grade`            TINYINT(1) NOT NULL COMMENT '학년',
  `ca_phone`            VARCHAR(20) NOT NULL,
  `ca_parent_phone`     VARCHAR(20) NOT NULL,
  `ca_career`           TEXT NOT NULL COMMENT '희망 진로',
  `ca_type`             ENUM('middle','high') NOT NULL,
  `ca_status`           ENUM('confirmed','waiting','cancelled','completed') NOT NULL DEFAULT 'confirmed',
  `ca_booth`            VARCHAR(10) DEFAULT NULL,
  `ca_booth_assigned_at` DATETIME DEFAULT NULL,
  `ca_applied_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ca_updated_at`       DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ca_id`),
  KEY `idx_ct_id` (`ct_id`),
  KEY `idx_mb_id` (`mb_id`),
  KEY `idx_status` (`ca_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='컨설팅 신청';

CREATE TABLE IF NOT EXISTS `g5_consultation_booth_assignments` (
  `cba_id`          INT(11) NOT NULL AUTO_INCREMENT,
  `ct_id`           INT(11) NOT NULL,
  `ca_id`           INT(11) NOT NULL,
  `cba_booth`       VARCHAR(10) NOT NULL,
  `cba_type`        ENUM('middle','high') NOT NULL,
  `cba_assigned_by` VARCHAR(20) NOT NULL DEFAULT 'auto',
  `cba_assigned_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cba_id`),
  UNIQUE KEY `uq_ca_id` (`ca_id`),
  KEY `idx_ct_id` (`ct_id`),
  KEY `idx_booth` (`ct_id`,`cba_booth`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='부스 배정 이력';

CREATE TABLE IF NOT EXISTS `g5_consultation_status_logs` (
  `csl_id`          INT(11) NOT NULL AUTO_INCREMENT,
  `ca_id`           INT(11) NOT NULL,
  `csl_prev_status` VARCHAR(20) DEFAULT NULL,
  `csl_new_status`  VARCHAR(20) NOT NULL,
  `csl_changed_by`  VARCHAR(20) NOT NULL,
  `csl_reason`      VARCHAR(255) DEFAULT '',
  `csl_changed_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`csl_id`),
  KEY `idx_ca_id` (`ca_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='상태 변경 이력';

-- 기본 시간대 16개 (중등 8 + 고등 8)
INSERT INTO `g5_consultation_times` (`ct_round`,`ct_start_time`,`ct_end_time`,`ct_type`,`ct_max_applicants`,`ct_max_waiting`) VALUES
(1,'10:00:00','10:30:00','middle',20,3),(2,'10:40:00','11:10:00','middle',20,3),
(3,'11:20:00','11:50:00','middle',20,3),(4,'12:00:00','12:30:00','middle',20,3),
(5,'13:30:00','14:00:00','middle',20,3),(6,'14:10:00','14:40:00','middle',20,3),
(7,'14:50:00','15:20:00','middle',20,3),(8,'15:30:00','16:00:00','middle',20,3),
(1,'10:00:00','10:30:00','high',20,3),(2,'10:40:00','11:10:00','high',20,3),
(3,'11:20:00','11:50:00','high',20,3),(4,'12:00:00','12:30:00','high',20,3),
(5,'13:30:00','14:00:00','high',20,3),(6,'14:10:00','14:40:00','high',20,3),
(7,'14:50:00','15:20:00','high',20,3),(8,'15:30:00','16:00:00','high',20,3);
