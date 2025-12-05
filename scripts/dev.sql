--
-- Структура таблицы `facets`
--

CREATE TABLE `facets` (
  `facet_id` int NOT NULL,
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
  `facet_is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `facets`
--

INSERT INTO `facets` (`facet_id`, `facet_title`, `facet_description`, `facet_short_description`, `facet_info`, `facet_slug`, `facet_path`, `facet_img`, `facet_cover_art`, `facet_date`, `facet_seo_title`, `facet_entry_policy`, `facet_view_policy`, `facet_merged_id`, `facet_top_level`, `facet_user_id`, `facet_tl`, `facet_post_related`, `facet_the_day`, `facet_focus_count`, `facet_count`, `facet_sort`, `facet_type`, `facet_is_comments`, `facet_is_deleted`) VALUES
(1, 'SEO', 'Поисковая оптимизация — это комплекс мер по внутренней и внешней оптимизации для поднятия позиций сайта в результатах выдачи поисковых систем.', 'Краткое описание темы...', 'Комплекс мер по внутренней и внешней оптимизации для поднятия позиций сайта в результатах выдачи поисковых систем по определённым запросам пользователей.\r\n\r\n**Поисковая оптимизация** — это способ использования правил поиска поисковых систем для улучшения текущего естественного ранжирования веб-сайтов в соответствующих поисковых системах. \r\n\r\nЦелью SEO является предоставление экологического решения для саморекламы для веб-сайта, позволяющего веб-сайту занимать лидирующие позиции в отрасли, чтобы получить преимущества бренда. \r\n\r\nSEO включает как внешнее, так и внутреннее SEO. \r\n\r\nSEO средства получить от поисковых систем больше бесплатного трафика, разумное планирование с точки зрения структуры веб-сайта, плана построения контента, взаимодействия с пользователем и общения, страниц и т.д., чтобы сделать веб-сайт более подходящим для принципов индексации поисковых систем. \r\n\r\nПовышение пригодности веб-сайтов для поисковых систем также называется Оптимизацией для поисковых систем, может не только улучшить эффект SEO, но и сделать информацию, относящуюся к веб-сайту, отображаемую в поисковой системе, более привлекательной для пользователей.', 'seo', 'web-development/seo', 'facet-default.png', 'cover_art.jpeg', '2021-06-26 18:29:20', 'Поисковая оптимизация (SEO)', 0, 0, 0, 0, 1, 0, '1,2,3', 0, 1, 2, 0, 'category', 0, 0),
(2, 'Hi-Tech', 'Факты про высокие технологии — наиболее новые и прогрессивные технологии современности. ', 'Краткое описание темы...', 'Факты про высокие технологии.', 'sites', 'sites', 'facet-default.png', 'cover_art.jpeg', '2021-06-26 18:29:20', 'Интересные сайты', 0, 0, 0, 0, 1, 0, '', 0, 1, 2, 0, 'category', 0, 0),
(3, 'Веб-разработка', 'Веб-разработка — это работа, связанная с разработкой веб-сайта для Интернета (World Wide Web) или интрасети (частной сети).', 'Веб-разработка', 'Веб-разработка — это работа, связанная с разработкой веб-сайта для Интернета (World Wide Web) или интрасети (частной сети).', 'web-development', 'web-development', 'facet-default.png', 'cover_art.jpeg', '2021-11-03 20:04:41', 'Веб-разработка', 0, 0, 0, 0, 1, 0, '', 0, 1, 1, 0, 'category', 0, 0),
(4, 'Информация', 'Информация (помощь). Этот раздел содержит справочную информацию.', 'Информация ', 'Информация (помощь). Этот раздел содержит справочную информацию.', 'info', 'info', 'facet-default.png', 'cover_art.jpeg', '2021-12-21 11:07:54', 'Информация', 0, 0, 0, 0, 1, 0, '', 0, 1, 0, 0, 'category', 0, 0),
(5, 'Здоровье и Спорт', 'Факты по различным видам спорта. Бег и т.д.', 'Internet - это всё', 'Факты по различным видам спорта.', 'sport', 'sport', 'facet-default.png', 'cover_art.jpeg', '2022-02-09 05:52:33', 'Internet - это всё', 0, 0, 0, 0, 1, 0, '', 0, 1, 0, 0, 'category', 0, 0),
(6, 'Интернет', 'Факты про Интернет, сайты и др.', 'Справочная информация', 'Факты про Интернет, сайты и др.', 'internet', 'sites/internet', 'facet-default.png', 'cover_art.jpeg', '2022-02-09 05:58:47', 'Справочная информация', 0, 0, 0, 0, 1, 0, '', 0, 1, 0, 0, 'category', 0, 0),
(7, 'Бег', 'Факты посвященные бегу. Различные методики, рекомендации.', 'Безопасность', 'Факты посвященные бегу. Различные методики, рекомендации. ', 'run', 'sport/run', 'facet-default.png', 'cover_art.jpeg', '2022-02-09 06:02:11', 'Безопасность', 0, 0, 0, 0, 1, 0, '', 0, 1, 0, 0, 'category', 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `facets_items_relation`
--

CREATE TABLE `facets_items_relation` (
  `relation_facet_id` int DEFAULT '0',
  `relation_item_id` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `facets_items_relation`
--

INSERT INTO `facets_items_relation` (`relation_facet_id`, `relation_item_id`) VALUES
(5, 2),
(5, 3),
(6, 3),
(6, 5),
(5, 4),
(7, 4),
(7, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `facets_matching`
--

CREATE TABLE `facets_matching` (
  `matching_parent_id` int DEFAULT NULL,
  `matching_chaid_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `facets_merge`
--

CREATE TABLE `facets_merge` (
  `merge_id` int NOT NULL,
  `merge_add_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `merge_source_id` int NOT NULL DEFAULT '0',
  `merge_target_id` int NOT NULL DEFAULT '0',
  `merge_user_id` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `facets_posts_relation`
--

CREATE TABLE `facets_posts_relation` (
  `relation_facet_id` int DEFAULT '0',
  `relation_post_id` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `facets_posts_relation`
--

INSERT INTO `facets_posts_relation` (`relation_facet_id`, `relation_post_id`) VALUES
(1, 1),
(2, 2),
(2, 4),
(1, 4),
(3, 5),
(4, 7),
(3, 3),
(3, 8),
(2, 9),
(2, 10),
(4, 11),
(3, 11),
(4, 6),
(3, 6);

-- --------------------------------------------------------

--
-- Структура таблицы `facets_relation`
--

CREATE TABLE `facets_relation` (
  `facet_parent_id` int DEFAULT NULL,
  `facet_chaid_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `facets_relation`
--

INSERT INTO `facets_relation` (`facet_parent_id`, `facet_chaid_id`) VALUES
(2, 6),
(3, 1),
(5, 7);

-- --------------------------------------------------------

--
-- Структура таблицы `facets_signed`
--

CREATE TABLE `facets_signed` (
  `signed_id` int NOT NULL,
  `signed_facet_id` int NOT NULL,
  `signed_user_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `facets_signed`
--

INSERT INTO `facets_signed` (`signed_id`, `signed_facet_id`, `signed_user_id`) VALUES
(1, 1, 1),
(2, 2, 1),
(4, 3, 1),
(5, 4, 1),
(6, 5, 1),
(7, 6, 1),
(8, 7, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `facets_types`
--

CREATE TABLE `facets_types` (
  `type_id` int NOT NULL,
  `type_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `type_lang` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `type_title` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `facets_types`
--

INSERT INTO `facets_types` (`type_id`, `type_code`, `type_lang`, `type_title`) VALUES
(1, 'topic', 'topic', 'Темы'),
(2, 'blog', 'blog', 'Блог'),
(3, 'section', 'section', 'Секция'),
(4, 'category', 'category', 'Категории');

-- --------------------------------------------------------

--
-- Структура таблицы `facets_users_team`
--

CREATE TABLE `facets_users_team` (
  `team_id` int NOT NULL,
  `team_facet_id` int NOT NULL,
  `team_user_id` int NOT NULL,
  `team_user_access` int NOT NULL DEFAULT '0',
  `team_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `files`
--

CREATE TABLE `files` (
  `file_id` int NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `file_type` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `file_content_id` int UNSIGNED DEFAULT NULL,
  `file_user_id` int UNSIGNED DEFAULT NULL,
  `file_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `file_is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `files`
--

INSERT INTO `files` (`file_id`, `file_path`, `file_type`, `file_content_id`, `file_user_id`, `file_date`, `file_is_deleted`) VALUES
(1, '2021/c-1638777119.webp', 'post', 0, 1, '2021-12-04 22:52:00', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `items`
--

CREATE TABLE `items` (
  `item_id` int UNSIGNED NOT NULL,
  `item_title` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `item_slug` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `item_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `item_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `item_published` tinyint(1) NOT NULL DEFAULT '1',
  `item_user_id` int UNSIGNED NOT NULL,
  `item_ip` varbinary(16) DEFAULT NULL,
  `item_content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `item_note` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `item_source_title` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `item_source_url` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `item_content_img` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `item_thumb_img` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `item_is_deleted` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `items`
--

INSERT INTO `items` (`item_id`, `item_title`, `item_slug`, `item_date`, `item_modified`, `item_published`, `item_user_id`, `item_ip`, `item_content`, `item_note`, `item_source_title`, `item_source_url`, `item_content_img`, `item_thumb_img`, `item_is_deleted`) VALUES
(1, 'Что такое восстановительный бег?', 'chto-takoe-vosstanovitelnyy-beg', '2025-12-05 10:06:23', '2025-12-05 11:01:53', 1, 1, NULL, '**Восстановительный бег** — это бег с низкой интенсивностью и лёгкими усилиями, который обычно выполняется **в течение 24 часов после соревновательного забега или тяжёлой тренировки**. Также восстановительный бег может использоваться: \r\n\r\n* в период между активными тренировками, чтобы поддержать мышцы в состоянии тонуса;\r\n\r\n* в период восстановления организма после травмы и длительного перерыва между тренировками;\r\n\r\n* в режиме активных нагрузок, если накануне произошло переутомление, и спортсмену на более лёгкой нагрузке необходимо восстановить силы.', '', 'Десять типов тренировок, которые должен знать каждый Бегун', 'https://www.sports.ru/athletics/blogs/3246745.html', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `search_logs`
--

CREATE TABLE `search_logs` (
  `id` int NOT NULL,
  `request` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `action_type` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Catalog, site...',
  `add_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `add_ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` int NOT NULL DEFAULT '0',
  `count_results` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
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
  `is_deleted` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `login`, `name`, `email`, `password`, `activated`, `limiting_mode`, `reg_ip`, `trust_level`, `created_at`, `updated_at`, `invitation_available`, `invitation_id`, `template`, `lang`, `scroll`, `whisper`, `avatar`, `cover_art`, `color`, `about`, `website`, `location`, `public_email`, `github`, `skype`, `twitter`, `telegram`, `vk`, `rating`, `my_post`, `nsfw`, `post_design`, `ban_list`, `hits_count`, `up_count`, `is_deleted`) VALUES
(1, 'AdreS', 'Олег', 'ss@sdf.ru', '$2y$10$oR5VZ.zk7IN/og70gQq/f.0Sb.GQJ33VZHIES4pyIpU3W2vF6aiaW', 1, 0, '127.0.0.1', 10, '2021-03-08 21:37:04', '2021-03-08 21:37:04', 0, 0, 'default', 'ru', 0, '', 'img_1.jpg', 'cover_art.jpeg', '#f56400', 'Тестовый аккаунт', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0),
(2, 'test', NULL, 'test@test.ru', '$2y$10$Iahcsh3ima0kGqgk6S/SSui5/ETU5bQueYROFhOsjUU/z1.xynR7W', 1, 0, '127.0.0.1', 2, '2021-04-30 07:42:52', '2021-04-30 07:42:52', 0, 0, 'default', 'ru', 0, '', 'noavatar.png', 'cover_art.jpeg', '#339900', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 1, 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `users_action_logs`
--

CREATE TABLE `users_action_logs` (
  `id` int NOT NULL,
  `user_id` int NOT NULL COMMENT 'User ID',
  `user_login` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'User login',
  `id_content` int NOT NULL COMMENT 'Content ID',
  `action_type` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `action_name` varchar(124) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Action name',
  `url_content` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'URL content',
  `add_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date added'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `users_action_logs`
--

INSERT INTO `users_action_logs` (`id`, `user_id`, `user_login`, `id_content`, `action_type`, `action_name`, `url_content`, `add_date`) VALUES
(1, 1, 'AdreS', 8, 'post', 'added', '/posts/8/post-28-03-2025', '2025-03-27 20:37:02'),
(2, 1, 'AdreS', 9, 'post', 'added', '/notes/9/bolshie-yazykovye-modeli-kak-instrument-dlya-analiza-tehnicheskoy-dokumentacii-i-resheniya', '2025-03-27 20:38:14'),
(3, 1, 'AdreS', 10, 'post', 'added', '/question/10/naskolko-vam-nravitsya-sayt-habr', '2025-03-27 20:39:52'),
(4, 1, 'AdreS', 11, 'page', 'added', '/mod/admin/facets', '2025-03-27 20:45:22');

-- --------------------------------------------------------

--
-- Структура таблицы `users_activate`
--

CREATE TABLE `users_activate` (
  `activate_id` int NOT NULL,
  `activate_date` datetime NOT NULL,
  `activate_user_id` int NOT NULL,
  `activate_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `activate_flag` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `users_agent_logs`
--

CREATE TABLE `users_agent_logs` (
  `id` int UNSIGNED NOT NULL,
  `add_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int UNSIGNED NOT NULL,
  `user_browser` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_os` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_ip` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `device_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Дамп данных таблицы `users_agent_logs`
--

INSERT INTO `users_agent_logs` (`id`, `add_date`, `user_id`, `user_browser`, `user_os`, `user_ip`, `device_id`) VALUES
(1, '2021-09-19 10:09:38', 1, 'Firefox 92.0', 'Windows', '127.0.0.1', NULL),
(2, '2021-09-19 10:57:57', 2, 'Chrome 93.0.4577.82', 'Windows', '127.0.0.1', NULL),
(3, '2021-10-17 04:43:05', 1, 'Firefox 93.0', 'Windows', '127.0.0.1', NULL),
(4, '2021-10-25 09:24:03', 1, 'Firefox 93.0', 'Windows', '127.0.0.1', NULL),
(5, '2021-11-03 20:01:34', 1, 'Firefox 94.0', 'Windows', '127.0.0.1', NULL),
(6, '2021-12-04 12:38:15', 1, 'Firefox 94.0', 'Windows', '127.0.0.1', NULL),
(7, '2021-12-04 22:51:36', 1, 'Firefox 94.0', 'Windows', '127.0.0.1', NULL),
(8, '2021-12-06 18:25:29', 1, 'Firefox 95.0', 'Windows', '127.0.0.1', NULL),
(9, '2021-12-06 19:15:41', 2, 'Firefox 95.0', 'Windows', '127.0.0.1', NULL),
(10, '2021-12-07 01:40:13', 1, 'Firefox 95.0', 'Windows', '127.0.0.1', NULL),
(11, '2021-12-07 01:49:18', 2, 'Firefox 95.0', 'Windows', '127.0.0.1', NULL),
(12, '2021-12-21 11:03:39', 1, 'Firefox 95.0', 'Windows', '127.0.0.1', NULL),
(13, '2021-12-21 11:08:44', 1, 'Firefox 95.0', 'Windows', '127.0.0.1', NULL),
(14, '2021-12-21 11:09:08', 1, 'Firefox 95.0', 'Windows', '127.0.0.1', NULL),
(15, '2022-02-09 05:50:18', 1, 'Firefox 96.0', 'Windows', '127.0.0.1', NULL),
(16, '2025-03-27 20:35:44', 1, 'Firefox 115.0', 'Windows', '127.0.0.1', '687811917'),
(17, '2025-12-05 05:03:37', 1, '', '', '127.0.0.1', NULL),
(18, '2025-12-05 10:57:39', 1, '', '', '127.0.0.1', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `users_auth_tokens`
--

CREATE TABLE `users_auth_tokens` (
  `auth_id` int NOT NULL,
  `auth_user_id` int NOT NULL,
  `auth_selector` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `auth_hashedvalidator` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `auth_expires` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `users_banlist`
--

CREATE TABLE `users_banlist` (
  `banlist_id` int NOT NULL,
  `banlist_user_id` int NOT NULL,
  `banlist_ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `banlist_bandate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `banlist_int_num` int NOT NULL,
  `banlist_int_period` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `banlist_status` tinyint(1) NOT NULL DEFAULT '1',
  `banlist_autodelete` tinyint(1) NOT NULL DEFAULT '0',
  `banlist_cause` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `users_email_activate`
--

CREATE TABLE `users_email_activate` (
  `id` int NOT NULL,
  `pubdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `email_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email_activate_flag` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `users_email_story`
--

CREATE TABLE `users_email_story` (
  `id` int NOT NULL,
  `pubdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email_activate_flag` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `facets`
--
ALTER TABLE `facets`
  ADD PRIMARY KEY (`facet_id`),
  ADD UNIQUE KEY `unique_index` (`facet_slug`,`facet_type`),
  ADD KEY `facet_slug` (`facet_slug`),
  ADD KEY `facet_merged_id` (`facet_merged_id`),
  ADD KEY `facet_type` (`facet_type`);

--
-- Индексы таблицы `facets_items_relation`
--
ALTER TABLE `facets_items_relation`
  ADD KEY `relation_facet_id` (`relation_facet_id`) USING BTREE,
  ADD KEY `relation_item_id` (`relation_item_id`) USING BTREE;

--
-- Индексы таблицы `facets_matching`
--
ALTER TABLE `facets_matching`
  ADD UNIQUE KEY `matching_parent_id` (`matching_parent_id`,`matching_chaid_id`);

--
-- Индексы таблицы `facets_merge`
--
ALTER TABLE `facets_merge`
  ADD PRIMARY KEY (`merge_id`),
  ADD KEY `merge_source_id` (`merge_source_id`),
  ADD KEY `merge_target_id` (`merge_target_id`),
  ADD KEY `merge_user_id` (`merge_user_id`);

--
-- Индексы таблицы `facets_posts_relation`
--
ALTER TABLE `facets_posts_relation`
  ADD KEY `relation_facet_id` (`relation_facet_id`),
  ADD KEY `relation_content_id` (`relation_post_id`);

--
-- Индексы таблицы `facets_relation`
--
ALTER TABLE `facets_relation`
  ADD UNIQUE KEY `facet_parent_id` (`facet_parent_id`,`facet_chaid_id`);

--
-- Индексы таблицы `facets_signed`
--
ALTER TABLE `facets_signed`
  ADD PRIMARY KEY (`signed_id`);

--
-- Индексы таблицы `facets_types`
--
ALTER TABLE `facets_types`
  ADD PRIMARY KEY (`type_id`),
  ADD UNIQUE KEY `title_UNIQUE` (`type_code`);

--
-- Индексы таблицы `facets_users_team`
--
ALTER TABLE `facets_users_team`
  ADD PRIMARY KEY (`team_id`),
  ADD KEY `team_facet_id` (`team_facet_id`),
  ADD KEY `team_user_id` (`team_user_id`);

--
-- Индексы таблицы `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`file_id`),
  ADD KEY `file_user_id` (`file_user_id`);

--
-- Индексы таблицы `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `item_date` (`item_date`),
  ADD KEY `item_user_id` (`item_user_id`,`item_date`);
ALTER TABLE `items` ADD FULLTEXT KEY `item_title` (`item_title`,`item_content`);

--
-- Индексы таблицы `search_logs`
--
ALTER TABLE `search_logs`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reg_ip` (`reg_ip`);

--
-- Индексы таблицы `users_action_logs`
--
ALTER TABLE `users_action_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`) COMMENT 'uid';

--
-- Индексы таблицы `users_activate`
--
ALTER TABLE `users_activate`
  ADD PRIMARY KEY (`activate_id`);

--
-- Индексы таблицы `users_agent_logs`
--
ALTER TABLE `users_agent_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_ip` (`user_ip`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `users_auth_tokens`
--
ALTER TABLE `users_auth_tokens`
  ADD PRIMARY KEY (`auth_id`);

--
-- Индексы таблицы `users_banlist`
--
ALTER TABLE `users_banlist`
  ADD PRIMARY KEY (`banlist_id`),
  ADD KEY `banlist_ip` (`banlist_ip`),
  ADD KEY `banlist_user_id` (`banlist_user_id`);

--
-- Индексы таблицы `users_email_activate`
--
ALTER TABLE `users_email_activate`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users_email_story`
--
ALTER TABLE `users_email_story`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `facets`
--
ALTER TABLE `facets`
  MODIFY `facet_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `facets_merge`
--
ALTER TABLE `facets_merge`
  MODIFY `merge_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `facets_signed`
--
ALTER TABLE `facets_signed`
  MODIFY `signed_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `facets_types`
--
ALTER TABLE `facets_types`
  MODIFY `type_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `facets_users_team`
--
ALTER TABLE `facets_users_team`
  MODIFY `team_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `files`
--
ALTER TABLE `files`
  MODIFY `file_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `search_logs`
--
ALTER TABLE `search_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `users_action_logs`
--
ALTER TABLE `users_action_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `users_activate`
--
ALTER TABLE `users_activate`
  MODIFY `activate_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users_agent_logs`
--
ALTER TABLE `users_agent_logs`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT для таблицы `users_auth_tokens`
--
ALTER TABLE `users_auth_tokens`
  MODIFY `auth_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users_banlist`
--
ALTER TABLE `users_banlist`
  MODIFY `banlist_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users_email_activate`
--
ALTER TABLE `users_email_activate`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users_email_story`
--
ALTER TABLE `users_email_story`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

