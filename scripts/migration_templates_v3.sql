-- Новые типы полей шаблонов: relation (ссылка на запись) и gallery (несколько фото).
-- Плюс варианты для select.

ALTER TABLE template_fields
  MODIFY field_type ENUM('text','textarea','number','date','url','image','select','link','relation','gallery')
  NOT NULL DEFAULT 'text';

CREATE TABLE IF NOT EXISTS template_field_options (
  option_id     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  field_id      INT UNSIGNED NOT NULL,
  option_value  VARCHAR(191) NOT NULL,
  option_label  VARCHAR(191) NOT NULL,
  option_sort   SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (option_id),
  KEY idx_options_field (field_id, option_sort)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
