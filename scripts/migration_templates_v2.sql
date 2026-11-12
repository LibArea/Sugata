-- Версионирование схемы шаблонов:
-- мягкое удаление полей и журнал изменений схемы.
-- Только добавления: существующие данные не меняются.

ALTER TABLE template_fields
  ADD COLUMN field_is_deleted TINYINT(1) NOT NULL DEFAULT 0 AFTER field_hint,
  ADD COLUMN field_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS template_field_history (
  history_id   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  template_id  INT UNSIGNED NOT NULL,
  field_id     INT UNSIGNED NULL,
  action       ENUM('create','update','rename','delete','restore') NOT NULL,
  field_key    VARCHAR(64)  NOT NULL,
  old_key      VARCHAR(64)  NULL,
  user_id      INT UNSIGNED NULL,
  created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (history_id),
  KEY idx_history_template (template_id, created_at),
  KEY idx_history_field (field_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
