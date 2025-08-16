
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `goals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL DEFAULT '1',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `age` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `height` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_weight` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `medical_conditions` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fitness_goal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_weight` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deadline` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `activity_level` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `workout_style` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dietary_preferences` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `food_allergies` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `plan_generated` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `goals_user_id_foreign` (`user_id`),
  CONSTRAINT `goals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fullname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;













INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(2, '0001_01_01_000001_create_cache_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(3, '0001_01_01_000002_create_jobs_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(4, '2025_08_08_234238_create_personal_access_tokens_table', 1),
(5, '2025_08_11_220555_create_goals_table', 2);



INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 1, 'token', '345e937fb444e16ba5fc765fbdf7244df50aa0498e8fc33351b21d65b0675d09', '[\"*\"]', NULL, NULL, '2025-08-11 21:28:35', '2025-08-11 21:28:35');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(2, 'App\\Models\\User', 1, 'token', '7ed3cfd66d114d6b89188c05d2f0b99525a7030a1435cb4fdb913f8d0884ad0d', '[\"*\"]', NULL, NULL, '2025-08-11 21:28:52', '2025-08-11 21:28:52');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(3, 'App\\Models\\User', 1, 'token', 'bc1ff1ec5fe0607c0abbd535f54a14da403cbb414b7b55041d5f1a03a6a9ff22', '[\"*\"]', NULL, NULL, '2025-08-12 20:13:07', '2025-08-12 20:13:07');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(4, 'App\\Models\\User', 2, 'token', 'fd6c2306f591e4152217623e64e8e92dd18c1c53cd3dcf3b408e23c78ce5294e', '[\"*\"]', NULL, NULL, '2025-08-12 21:44:45', '2025-08-12 21:44:45'),
(5, 'App\\Models\\User', 1, 'token', '65c833d9573aeeeeb4a4498360e462f30656282920e0fa179395971e29e49c4b', '[\"*\"]', NULL, NULL, '2025-08-12 21:45:17', '2025-08-12 21:45:17'),
(6, 'App\\Models\\User', 3, 'token', '737686f1a476241b75ddb401b0322b4c60eef0077dd908ca01bcbc9ec8370095', '[\"*\"]', NULL, NULL, '2025-08-14 02:18:35', '2025-08-14 02:18:35'),
(7, 'App\\Models\\User', 4, 'token', '8c6f3cfda08421da09de58590038fa59dda50d73dd1efa1fb2f6d43fc672e65e', '[\"*\"]', NULL, NULL, '2025-08-14 02:20:26', '2025-08-14 02:20:26'),
(8, 'App\\Models\\User', 5, 'token', '16847e3b5e734099cfdbf4657cc616e9c6729fd5bd88c500a36f154e12a3604d', '[\"*\"]', NULL, NULL, '2025-08-14 02:27:22', '2025-08-14 02:27:22'),
(9, 'App\\Models\\User', 6, 'token', 'a30008caa40c52023b2f961f4bbb78bfa146b833d86ef5e19a17866544b36a0c', '[\"*\"]', NULL, NULL, '2025-08-14 02:27:50', '2025-08-14 02:27:50'),
(10, 'App\\Models\\User', 7, 'token', '9c41c4b02b34c4366407cb3939860a3a197246368000954cc98f69921ffc80ed', '[\"*\"]', '2025-08-14 18:45:03', NULL, '2025-08-14 18:44:21', '2025-08-14 18:45:03'),
(11, 'App\\Models\\User', 8, 'token', '00d39d166adf15a102adea78893fef0ae85d3e7f9758469b296612e54c7eb24e', '[\"*\"]', NULL, NULL, '2025-08-14 18:46:05', '2025-08-14 18:46:05'),
(12, 'App\\Models\\User', 9, 'token', '18d37d14ae386cf20991ecf944ac288396d977bbb9d19956c9f552d16e255292', '[\"*\"]', NULL, NULL, '2025-08-14 18:46:16', '2025-08-14 18:46:16'),
(13, 'App\\Models\\User', 10, 'token', '75d0e2e419c75f469e2d9dba70496091575ad9bb1f5f16de14837cb24076ace1', '[\"*\"]', '2025-08-14 18:48:18', NULL, '2025-08-14 18:46:30', '2025-08-14 18:48:18'),
(14, 'App\\Models\\User', 11, 'token', '552bcaf6e23174edab328b72a25c52957060242ba9ca4fa3fd17e784463dfa04', '[\"*\"]', '2025-08-14 18:51:49', NULL, '2025-08-14 18:48:36', '2025-08-14 18:51:49'),
(15, 'App\\Models\\User', 12, 'token', '51db982c5bffaac8ef5a85b2327cd74683acf88e785625e3a868d1da3730cd74', '[\"*\"]', '2025-08-14 18:52:50', NULL, '2025-08-14 18:51:55', '2025-08-14 18:52:50'),
(16, 'App\\Models\\User', 13, 'token', '10c72ac8dc2676ef26782875fc700fd14fa286ee5cfad68c62c0752c26c65f04', '[\"*\"]', NULL, NULL, '2025-08-14 18:52:52', '2025-08-14 18:52:52'),
(17, 'App\\Models\\User', 14, 'token', '3415a8a0a596b8fb275117efd71c4c9d8dbf1d69469655bc80f26636f2ea6612', '[\"*\"]', NULL, NULL, '2025-08-14 18:53:51', '2025-08-14 18:53:51'),
(18, 'App\\Models\\User', 15, 'token', 'e714b519c16ed740cdd0ec1ed3e1269f6dbadf4f90f67639157ef46036c0aae0', '[\"*\"]', '2025-08-14 19:09:21', NULL, '2025-08-14 18:58:28', '2025-08-14 19:09:21'),
(20, 'App\\Models\\User', 11, 'token', '2b14aec691644f0c1f72045716ca43819db767c6d9cbba8322a8f61c533e4036', '[\"*\"]', '2025-08-14 19:16:25', NULL, '2025-08-14 19:15:56', '2025-08-14 19:16:25'),
(32, 'App\\Models\\User', 1, 'token', 'f2a31fa0474e306c9b34d918b0b7cec3d691c8552350499e43fd7ea9884ba52b', '[\"*\"]', '2025-08-14 21:45:31', NULL, '2025-08-14 21:45:13', '2025-08-14 21:45:31');



INSERT INTO `users` (`id`, `fullname`, `email`, `email_verified_at`, `password`, `role`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Fady', 'faadi3media@gmail.com', NULL, '$2y$12$WBkiKEwLWXCSud83AmSw7Oe4A6RyITgA0m0N8C.Cm6CzvhN52ZnWO', 'novice', NULL, '2025-08-14 23:10:30', '2025-08-14 23:10:30');

