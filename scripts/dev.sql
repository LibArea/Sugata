-- Sugata (Sugata) demo database dump
-- Generated: 2026-10-03T05:45:29+02:00
-- Contenuto demo: 3 facts + 'О сайте' page + categories + sources.
-- All other tables are created empty (schema only).

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `facets`;
CREATE TABLE `facets` (
  `facet_id` int NOT NULL AUTO_INCREMENT,
  `facet_title` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `facet_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `facet_short_description` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `facet_info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `facet_slug` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `facet_path` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `facet_img` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'facet-default.png',
  `facet_cover_art` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'cover_art.jpeg',
  `facet_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `facet_seo_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `facet_entry_policy` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Политика вступления',
  `facet_view_policy` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Политика просмотра (1 - после подписки)',
  `facet_merged_id` int NOT NULL DEFAULT '0' COMMENT 'С кем слит',
  `facet_top_level` tinyint(1) NOT NULL DEFAULT '0',
  `facet_user_id` int NOT NULL DEFAULT '1',
  `facet_tl` tinyint(1) NOT NULL DEFAULT '0',
  `facet_post_related` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `facet_the_day` tinyint(1) NOT NULL DEFAULT '0',
  `facet_focus_count` int NOT NULL DEFAULT '0',
  `facet_count` int NOT NULL DEFAULT '0',
  `facet_sort` int NOT NULL DEFAULT '0',
  `facet_type` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'topic' COMMENT 'Topic, Group or Blog...',
  `facet_is_comments` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Are comments closed (posts, websites...)?',
  `facet_is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`facet_id`),
  UNIQUE KEY `unique_index` (`facet_slug`,`facet_type`),
  KEY `facet_slug` (`facet_slug`),
  KEY `facet_merged_id` (`facet_merged_id`),
  KEY `facet_type` (`facet_type`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `facets_items_relation`;
CREATE TABLE `facets_items_relation` (
  `relation_facet_id` int DEFAULT '0',
  `relation_item_id` int DEFAULT '0',
  KEY `relation_facet_id` (`relation_facet_id`) USING BTREE,
  KEY `relation_item_id` (`relation_item_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `facets_matching`;
CREATE TABLE `facets_matching` (
  `matching_parent_id` int DEFAULT NULL,
  `matching_chaid_id` int DEFAULT NULL,
  UNIQUE KEY `matching_parent_id` (`matching_parent_id`,`matching_chaid_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `facets_merge`;
CREATE TABLE `facets_merge` (
  `merge_id` int NOT NULL AUTO_INCREMENT,
  `merge_add_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `merge_source_id` int NOT NULL DEFAULT '0',
  `merge_target_id` int NOT NULL DEFAULT '0',
  `merge_user_id` int DEFAULT '0',
  PRIMARY KEY (`merge_id`),
  KEY `merge_source_id` (`merge_source_id`),
  KEY `merge_target_id` (`merge_target_id`),
  KEY `merge_user_id` (`merge_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `facets_posts_relation`;
CREATE TABLE `facets_posts_relation` (
  `relation_facet_id` int DEFAULT '0',
  `relation_post_id` int DEFAULT '0',
  KEY `relation_facet_id` (`relation_facet_id`),
  KEY `relation_content_id` (`relation_post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `facets_relation`;
CREATE TABLE `facets_relation` (
  `facet_parent_id` int DEFAULT NULL,
  `facet_chaid_id` int DEFAULT NULL,
  UNIQUE KEY `facet_parent_id` (`facet_parent_id`,`facet_chaid_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `facets_signed`;
CREATE TABLE `facets_signed` (
  `signed_id` int NOT NULL AUTO_INCREMENT,
  `signed_facet_id` int NOT NULL,
  `signed_user_id` int NOT NULL,
  PRIMARY KEY (`signed_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `facets_types`;
CREATE TABLE `facets_types` (
  `type_id` int NOT NULL AUTO_INCREMENT,
  `type_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `type_lang` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `type_title` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`type_id`),
  UNIQUE KEY `title_UNIQUE` (`type_code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `files`;
CREATE TABLE `files` (
  `file_id` int NOT NULL AUTO_INCREMENT,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `file_type` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `file_content_id` int unsigned DEFAULT NULL,
  `file_user_id` int unsigned DEFAULT NULL,
  `file_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `file_is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`file_id`),
  KEY `file_user_id` (`file_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `items`;
CREATE TABLE `items` (
  `item_id` int unsigned NOT NULL AUTO_INCREMENT,
  `item_title` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `item_slug` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `item_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `item_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `item_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `item_published` tinyint(1) NOT NULL DEFAULT '1',
  `item_user_id` int unsigned NOT NULL,
  `item_ip` varbinary(16) DEFAULT NULL,
  `item_content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `item_note` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `item_content_img` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `item_thumb_img` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `item_is_deleted` tinyint(1) DEFAULT '0',
  `item_views` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`item_id`),
  KEY `item_date` (`item_date`),
  KEY `item_user_id` (`item_user_id`,`item_date`),
  KEY `idx_item_views` (`item_views`),
  FULLTEXT KEY `item_title` (`item_title`,`item_content`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `sources`;
CREATE TABLE `sources` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `title` varchar(512) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `domain` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('unchecked','ok','broken','redirect','timeout') COLLATE utf8mb4_general_ci DEFAULT 'unchecked',
  `http_code` int DEFAULT NULL,
  `last_checked_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`),
  KEY `idx_status` (`status`),
  KEY `idx_last_checked` (`last_checked_at`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `article_sources`;
CREATE TABLE `article_sources` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `article_id` int unsigned NOT NULL,
  `source_id` int unsigned NOT NULL,
  `citation_text` text COLLATE utf8mb4_general_ci,
  `sort_order` tinyint unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_article_source` (`article_id`,`source_id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `broken_links`;
CREATE TABLE `broken_links` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `item_id` int unsigned NOT NULL DEFAULT '0',
  `article_title` varchar(250) COLLATE utf8mb4_general_ci DEFAULT '',
  `link` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `word` varchar(250) COLLATE utf8mb4_general_ci DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_item` (`item_id`),
  KEY `idx_link` (`link`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `item_faq`;
CREATE TABLE `item_faq` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `item_id` int unsigned NOT NULL,
  `question` text COLLATE utf8mb4_general_ci NOT NULL,
  `answer` text COLLATE utf8mb4_general_ci NOT NULL,
  `sort_order` tinyint unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_item` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `search_logs`;
CREATE TABLE `search_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `request` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `action_type` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Catalog, site...',
  `add_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `add_ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` int NOT NULL DEFAULT '0',
  `count_results` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `login` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `activated` tinyint(1) DEFAULT '0',
  `limiting_mode` tinyint(1) DEFAULT '0',
  `reg_ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `trust_level` int NOT NULL COMMENT 'Уровень доверия. По умолчанию 1 (10 - админ)',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `invitation_available` int NOT NULL DEFAULT '0',
  `invitation_id` int NOT NULL DEFAULT '0',
  `template` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'default',
  `lang` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'ru',
  `scroll` tinyint(1) NOT NULL DEFAULT '0',
  `whisper` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'noavatar.png',
  `cover_art` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'cover_art.jpeg',
  `color` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '#f56400',
  `about` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `website` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `location` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `public_email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `github` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `skype` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `twitter` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telegram` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `vk` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `rating` int DEFAULT '0',
  `my_post` int DEFAULT '0' COMMENT 'Пост выведенный в профиль',
  `nsfw` tinyint(1) NOT NULL DEFAULT '0',
  `post_design` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'The appearance of the post in the feed: 0 - classic, 1 - card ...',
  `ban_list` tinyint(1) DEFAULT '0',
  `hits_count` int DEFAULT '0',
  `up_count` int DEFAULT '0',
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `reg_ip` (`reg_ip`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `users_action_logs`;
CREATE TABLE `users_action_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL COMMENT 'User ID',
  `user_login` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'User login',
  `id_content` int NOT NULL COMMENT 'Content ID',
  `action_type` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `action_name` varchar(124) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Action name',
  `url_content` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'URL content',
  `add_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date added',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`) COMMENT 'uid'
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `users_activate`;
CREATE TABLE `users_activate` (
  `activate_id` int NOT NULL AUTO_INCREMENT,
  `activate_date` datetime NOT NULL,
  `activate_user_id` int NOT NULL,
  `activate_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `activate_flag` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`activate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `users_agent_logs`;
CREATE TABLE `users_agent_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `add_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int unsigned NOT NULL,
  `user_browser` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_os` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_ip` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `device_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_ip` (`user_ip`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS `users_auth_tokens`;
CREATE TABLE `users_auth_tokens` (
  `auth_id` int NOT NULL AUTO_INCREMENT,
  `auth_user_id` int NOT NULL,
  `auth_selector` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `auth_hashedvalidator` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `auth_expires` datetime NOT NULL,
  PRIMARY KEY (`auth_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `users_banlist`;
CREATE TABLE `users_banlist` (
  `banlist_id` int NOT NULL AUTO_INCREMENT,
  `banlist_user_id` int NOT NULL,
  `banlist_ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `banlist_bandate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `banlist_int_num` int NOT NULL,
  `banlist_int_period` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `banlist_status` tinyint(1) NOT NULL DEFAULT '1',
  `banlist_autodelete` tinyint(1) NOT NULL DEFAULT '0',
  `banlist_cause` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`banlist_id`),
  KEY `banlist_ip` (`banlist_ip`),
  KEY `banlist_user_id` (`banlist_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `users_email_activate`;
CREATE TABLE `users_email_activate` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pubdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `email_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email_activate_flag` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `users_email_story`;
CREATE TABLE `users_email_story` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pubdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email_activate_flag` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `facets_types` (`type_id`, `type_code`, `type_lang`, `type_title`) VALUES
(1, 'topic', 'topic', 'Темы'),
(2, 'blog', 'blog', 'Блог'),
(3, 'section', 'section', 'Секция'),
(4, 'category', 'category', 'Категории');

INSERT INTO `facets` (facet_id, facet_title, facet_description, facet_short_description, facet_info, facet_slug, facet_path, facet_img, facet_cover_art, facet_date, facet_seo_title, facet_entry_policy, facet_view_policy, facet_merged_id, facet_top_level, facet_user_id, facet_tl, facet_post_related, facet_the_day, facet_focus_count, facet_count, facet_sort, facet_type, facet_is_comments, facet_is_deleted) VALUES
(7, 'Бег', 'Факты посвященные бегу. Различные методики, рекомендации.', 'Безопасность', 'Факты посвященные бегу. Различные методики, рекомендации. ', 'run', 'sport/run', 'facet-default.png', 'cover_art.jpeg', '2022-02-09 06:02:11', 'Безопасность', 0, 0, 0, 0, 1, 0, '', 0, 1, 0, 0, 'category', 0, 0),
(23, 'Буддизм', NULL, NULL, NULL, 'buddhism', 'occultism/religion/buddhism', 'facet-default.png', 'cover_art.jpeg', '2026-10-02 09:57:26', NULL, 0, 0, 0, 0, 1, 0, NULL, 0, 0, 0, 0, 'category', 0, 0),
(5, 'Спорт', 'Факты по различным видам спорта. Бег и т.д.', 'Internet - это всё', 'Факты по различным видам спорта.', 'sport', 'sport', 'facet-default.png', 'cover_art.jpeg', '2022-02-09 05:52:33', 'Internet - это всё', 0, 0, 0, 0, 1, 0, '', 0, 1, 0, 0, 'category', 0, 0),
(4, 'Информация', 'Информация (помощь). Этот раздел содержит справочную информацию.', 'Информация ', 'Информация (помощь). Этот раздел содержит справочную информацию.', 'info', 'info', 'facet-default.png', 'cover_art.jpeg', '2021-12-21 11:07:54', 'Информация', 0, 0, 0, 0, 1, 0, '', 0, 1, 0, 0, 'category', 0, 0),
(18, 'Религия', NULL, NULL, NULL, 'religion', 'occultism/religion', 'facet-default.png', 'cover_art.jpeg', '2026-10-02 09:57:26', NULL, 0, 0, 0, 0, 1, 0, NULL, 0, 0, 0, 0, 'category', 0, 0),
(12, 'Непознанное', NULL, NULL, NULL, 'occultism', 'occultism', 'facet-default.png', 'cover_art.jpeg', '2026-10-02 09:57:26', NULL, 0, 0, 0, 0, 1, 0, NULL, 0, 0, 0, 0, 'category', 0, 0);

INSERT INTO `facets_items_relation` (`relation_facet_id`, `relation_item_id`) VALUES
(7, 1),
(23, 9),
(5, 40),
(4, 41);

INSERT INTO `items` (item_id, item_title, item_slug, item_date, item_modified, item_type, item_published, item_user_id, item_ip, item_content, item_note, item_content_img, item_thumb_img, item_is_deleted, item_views) VALUES
(1, 'Что такое восстановительный бег?', 'chto-takoe-vosstanovitelnyy-beg', '2025-12-05 10:06:23', '2026-10-02 09:57:26', 'fact', 1, 1, NULL, '****Восстановительный бег**** — это бег с низкой интенсивностью и лёгкими усилиями, который обычно выполняется ****в течение 24 часов после соревновательного забега или тяжёлой тренировки****. Также восстановительный бег может использоваться:

 - в период между активными тренировками, чтобы поддержать мышцы в состоянии тонуса;
- в период восстановления организма после травмы и длительного перерыва между тренировками;
- в режиме активных нагрузок, если накануне произошло переутомление, и спортсмену на более лёгкой нагрузке необходимо восстановить силы.

  ## Цели

 Основная цель восстановительного бега — улучшить кровообращение, что помогает уменьшить мышечную усталость и вывести продукты метаболизма, такие как молочная кислота, из мышц. Также бег:

 - ****Улучшает общую выносливость**** и помогает поддерживать уровень физической активности без риска перетренированности.
- ****Улучшает технику бега**** — на лёгкой тренировке не нужно контролировать темп и время (только пульс), поэтому можно сосредоточиться на эффективной технике — каденсе, работе рук, дыхании.
- ****Происходит жировая адаптация**** — медленные пробежки улучшают способность тела использовать жир в качестве источника энергии, что полезно во время длительных забегов.

  ### Методика выполнения

 Восстановительный бег выполняется ****в лёгком, «разговорном» темпе**** (усилие от 1 до 3 по шкале от 1 до 10, где 10 — полное усилие). Частота пульса во время пробежек — ниже 70% от максимальной, идеальным вариантом будет 60–65%.

 ****Продолжительность**** — относительно короткая, обычно 20–60 минут или дистанция примерно 6–10 километров, в зависимости от уровня физической подготовки, пробега за неделю и целевой дистанции.

 ****Важно не проводить две однотипные тренировки подряд**** — это может вызвать более сильные физиологические сдвиги в организме.', '', NULL, NULL, 0, 0),
(9, 'Что означает: «одна основа, два пути и два плода»?', 'chto-oznachaet-odna-osnova-dva-puti-i-dva-ploda', '2026-10-02 09:57:26', '2026-10-02 09:57:26', 'fact', 1, 1, NULL, 'Фраза «одна основа, два пути и два плода» встречается в учении дзогчен, в частности в тексте *«Устремление Самантабхадры»*.

> «Хо! У всего проявленного и сущего, у сансары и нирваны,
>  Есть одна основа, два пути и два плода,
>  Проявляющиеся волшебным образом
>  Через осознавание и неведение».

 Это философско-религиозная концепция, которая отражает дуальность и взаимосвязь явлений в мире.

 Значение компонентов фразы:

 - Одна основа — это *Дхармакайя* (абсолютная основа), которая является основой возникновения всех феноменов — как просветлённых, так и непросветлённых. То есть основа существования как Будды, так и, например, человека с негативными качествами — одна и та же *Дхармакайя*.
- Два пути — это два противоположных направления:
  - Первый путь — знание *Дхармакайи* как природы своего сознания и всего существующего.
  - Второй путь — абсолютное неведение, отсутствие осознания этой природы.
- Два плода — результаты этих путей:
  - Плод неведения — углубление в иллюзию, развитие в рамках сансары (цикла перерождений).
  - Плод знания — освобождение от иллюзии и возвращение к состоянию изначального совершенства — *Дхармакайи*.

 Таким образом, концепция подчёркивает, что всё в мире зависит от выбора пути — знания или неведения, и это определяет его итог.', NULL, NULL, NULL, 0, 0),
(40, 'Занятия физкультурой положительно влияют на здоровье сердца', 'zanyatiya-fizkulturoy-polozhitelno-vliyayut-na-zdorove-serdca', '2026-10-02 09:57:26', '2026-10-02 09:57:26', 'fact', 1, 1, NULL, '****Профессор Ла Жерш**** — руководитель исследования, опубликованного в журнале *JACC: Advances*. Учёные развеяли популярный миф о том, что занятия физкультурой негативно влияют на здоровье сердца и ускоряют его износ.

 Исследование показало, что у занимающихся спортом частота пульса в покое в среднем ниже на 10%, чем у людей, которые не занимаются спортом. У тренированных людей частота сердечных сокращений (ЧСС) в покое составляет 68 ударов в минуту, у остальных — 76 ударов в минуту.

> «Хотя сердце спортсменов работает интенсивнее во время тренировок, более низкие показатели в состоянии покоя с лихвой компенсируют этот недостаток», — заметил профессор Ла Жерш.

> «Даже если вы усиленно тренируетесь всего час в день, ваше сердце бьется медленнее оставшиеся 23 часа»

 ****Профессор Ла Жерш**** подчеркнул, что медленный пульс в состоянии покоя выступает показателем хорошей физической формы и долгосрочного здоровья.', NULL, NULL, NULL, 0, 0),
(41, 'О сайте', 'o-sajte', '2026-10-02 17:28:28', '2026-10-02 17:28:28', 'page', 1, 1, NULL, 'Аналог **Movable Type**, **LinkSQL** и т. д. Соскучились по реальному HTML. :)

## Какие технологии использует сайт?

С технической стороны сайт стремится использовать современные версии простых, надёжных, «скучных» технологий. Это особенно важно для проекта с открытым исходным кодом, рассчитывающего на участие извне, — людям гораздо легче участвовать.

Основные технологии: PHP, CSS, HTML (клиентская часть — только HTML и CSS) и разметка Markdown.

[HLEB2](https://github.com/phphleb/hleb) — PHP-фреймворк. Минимализм кода и скорость работы.

[Rose](https://github.com/parpalak/rose) — поисковая система с поддержкой морфологии.

[Djot PHP](https://github.com/php-collective/djot-php) — PHP-парсер для Djot, современного легковесного языка разметки.

---

## Репозиторий проекта

[https://github.com/LibArea/Sugata](https://github.com/LibArea/Sugata) — генератор статических веб-сайтов.

**PHP >= 8.2, MySQL 8+ или MariaDB 10.2.2**
', NULL, NULL, NULL, 0, 0);

INSERT INTO `sources` (id, url, title, domain, status, http_code, last_checked_at, created_at) VALUES
(1, 'https://www.sports.ru/athletics/blogs/3246745.html', 'Десять типов тренировок, которые должен знать каждый Бегун', 'www.sports.ru', 'ok', 200, '2026-10-03 06:09:41', '2026-10-03 05:24:30'),
(9, 'https://dzen.ru/a/aDg88g3pM3aoepLS', 'Дзогчен — великое совершенство (Дзен.ru)', 'dzen.ru', 'ok', 200, '2026-10-03 06:09:32', '2026-10-03 05:24:30'),
(35, 'https://www.jacc.org/doi/10.1016/j.jacadv.2025.102140', 'Balancing Exercise Benefits Against Heartbeat Consumption in Elite Cyclists', 'www.jacc.org', 'broken', 403, '2026-10-03 06:09:18', '2026-10-03 05:24:30');

INSERT INTO `article_sources` (`article_id`, `source_id`, `sort_order`) VALUES
(1, 1, 0),
(9, 9, 0),
(40, 35, 0);

INSERT INTO `facets_relation` (`facet_parent_id`, `facet_chaid_id`) VALUES
(5, 7),
(18, 23),
(12, 18);

INSERT INTO `users` (id, login, name, email, password, activated, limiting_mode, reg_ip, trust_level, created_at, updated_at, invitation_available, invitation_id, template, lang, scroll, whisper, avatar, cover_art, color, about, website, location, public_email, github, skype, twitter, telegram, vk, rating, my_post, nsfw, post_design, ban_list, hits_count, up_count, is_deleted) VALUES
(1, 'AdreS', 'Олег', 'ss@sdf.ru', '$2y$10$oR5VZ.zk7IN/og70gQq/f.0Sb.GQJ33VZHIES4pyIpU3W2vF6aiaW', 1, 0, '127.0.0.1', 10, '2021-03-08 21:37:04', '2021-03-08 21:37:04', 0, 0, 'default', 'ru', 0, '', 'img_1.jpg', 'cover_art.jpeg', '#f56400', 'Тестовый аккаунт', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0);

SET FOREIGN_KEY_CHECKS = 1;
