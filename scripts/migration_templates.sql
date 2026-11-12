-- Шаблоны оформления: схемы (templates + template_fields),
-- экземпляры (template_instances) и метаданные медиафайлов (media_meta).
-- Только добавление таблиц: существующие данные не меняются.

CREATE TABLE IF NOT EXISTS templates (
  template_id        INT UNSIGNED      NOT NULL AUTO_INCREMENT,
  template_code      VARCHAR(64)       NOT NULL,
  template_title     VARCHAR(150)      NOT NULL,
  template_scope     ENUM('infobox','inline') NOT NULL DEFAULT 'infobox',
  template_description VARCHAR(255)    NULL,
  template_sort      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  template_is_active TINYINT(1)        NOT NULL DEFAULT 1,
  template_created   TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (template_id),
  UNIQUE KEY uq_templates_code (template_code),
  KEY idx_templates_scope (template_scope, template_is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS template_fields (
  field_id        INT UNSIGNED      NOT NULL AUTO_INCREMENT,
  template_id     INT UNSIGNED      NOT NULL,
  field_key       VARCHAR(64)       NOT NULL,
  field_label     VARCHAR(150)      NOT NULL,
  field_type      ENUM('text','textarea','number','date','url','image','select') NOT NULL DEFAULT 'text',
  field_required  TINYINT(1)        NOT NULL DEFAULT 0,
  field_sort      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  field_default   VARCHAR(255)      NULL,
  field_hint      VARCHAR(255)      NULL,
  PRIMARY KEY (field_id),
  UNIQUE KEY uq_fields_key (template_id, field_key),
  KEY idx_fields_sort (template_id, field_sort)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS template_instances (
  instance_id      INT UNSIGNED      NOT NULL AUTO_INCREMENT,
  template_id      INT UNSIGNED      NOT NULL,
  item_id          INT UNSIGNED      NOT NULL,
  instance_slot    ENUM('infobox','inline') NOT NULL DEFAULT 'infobox',
  instance_anchor  VARCHAR(191)      NULL,
  instance_sort    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  instance_values  JSON              NOT NULL,
  instance_is_active TINYINT(1)      NOT NULL DEFAULT 1,
  instance_created TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  instance_updated TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (instance_id),
  KEY idx_instances_item (item_id, instance_slot, instance_sort),
  KEY idx_instances_template (template_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS media_meta (
  media_id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  file_id         INT          NULL,
  media_path      VARCHAR(255) NOT NULL,
  media_alt       VARCHAR(255) NULL,
  media_caption   VARCHAR(255) NULL,
  media_author    VARCHAR(150) NULL,
  media_source_url VARCHAR(500) NULL,
  media_license   VARCHAR(100) NULL,
  media_created   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (media_id),
  UNIQUE KEY uq_media_path (media_path(191)),
  KEY idx_media_file (file_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Базовые типы шаблонов (только если их ещё нет).
INSERT INTO templates (template_code, template_title, template_scope, template_description, template_sort)
SELECT 'person', 'Персона', 'infobox', 'Фото, годы жизни, традиция, учитель, труды', 10
WHERE NOT EXISTS (SELECT 1 FROM templates WHERE template_code = 'person');

INSERT INTO templates (template_code, template_title, template_scope, template_description, template_sort)
SELECT 'site', 'Сайт', 'infobox', 'Скриншот, адрес, тематика, ТИЦ', 20
WHERE NOT EXISTS (SELECT 1 FROM templates WHERE template_code = 'site');

INSERT INTO templates (template_code, template_title, template_scope, template_description, template_sort)
SELECT 'term', 'Понятие', 'infobox', 'Фото, краткое определение, раздел', 30
WHERE NOT EXISTS (SELECT 1 FROM templates WHERE template_code = 'term');

INSERT INTO templates (template_code, template_title, template_scope, template_description, template_sort)
SELECT 'quote', 'Цитата', 'inline', 'Цитата с автором и источником', 40
WHERE NOT EXISTS (SELECT 1 FROM templates WHERE template_code = 'quote');
