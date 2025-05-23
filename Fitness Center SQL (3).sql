-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: localhost:8889
-- Время создания: Май 23 2025 г., 19:29
-- Версия сервера: 8.0.40
-- Версия PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `fitness_center`
--

-- --------------------------------------------------------

--
-- Структура таблицы `api_tokens`
--

CREATE TABLE `api_tokens` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `api_tokens`
--

INSERT INTO `api_tokens` (`id`, `user_id`, `token`, `created_at`, `expires_at`, `last_used_at`, `is_active`) VALUES
(1, 40, '70615e97b17e73e4f10bf11dd02265437a1872b9ca07a692abbd70a20e8a693b', '2025-05-18 17:37:00', '2025-06-17 13:37:00', '2025-05-18 17:51:10', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int NOT NULL,
  `action` enum('create','update','delete','login','logout','other') NOT NULL,
  `details` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `audit_log`
--

INSERT INTO `audit_log` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 40, 'user', 40, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-18 17:37:00'),
(2, 40, 'user', 40, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-18 18:11:11'),
(3, 40, 'training', 2, 'create', 'Training session booked: pool on 2025-05-19 at 12:00', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-18 18:25:02'),
(4, 40, 'user', 40, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-18 18:26:31'),
(5, 1, 'user', 1, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-18 18:27:21'),
(6, 1, 'user', 1, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-18 18:51:49'),
(7, 40, 'user', 40, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-18 18:57:29'),
(8, 40, 'user', 40, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-18 19:01:34'),
(9, 40, 'user', 40, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-19 09:19:14'),
(10, 40, 'training', 8, 'create', 'Training session booked: group on 2025-05-23 at 09:00', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-21 20:12:49'),
(11, 40, 'user', 40, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-21 20:48:56'),
(12, 52, 'user', 52, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-21 20:50:13'),
(13, 40, 'user', 40, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-21 20:52:39'),
(14, 53, 'user', 53, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-21 20:53:28'),
(15, 40, 'user', 40, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-21 20:54:47'),
(16, 53, 'user', 53, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-21 20:55:44'),
(17, 40, 'user', 40, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-21 21:03:38'),
(18, 40, 'user', 40, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-23 12:03:17'),
(19, 40, 'training', 10, 'create', 'Training session booked: personal on 2025-05-24 at 08:00', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-23 18:09:25'),
(20, 40, 'training', 11, 'create', 'Training session booked: personal on 2025-05-23 at 20:00', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-23 18:20:26'),
(21, 40, 'training', 12, 'create', 'Training session booked: group on 2025-05-23 at 19:00', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-23 18:34:56'),
(22, 40, 'training', 13, 'create', 'Training session booked: personal on 2025-05-24 at 11:00', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-23 18:38:16');

-- --------------------------------------------------------

--
-- Структура таблицы `notifications`
--

CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('booking','system','promo') NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 40, 'Запись на тренировку', 'Вы записаны на тренировку personal 2025-05-21 в 08:00. Ожидайте подтверждения.', 'booking', 0, '2025-05-18 17:06:00'),
(2, 40, 'Запись на тренировку', 'Вы успешно записались на тренировку 19.05.2025 в 12:00. Номер брони: 9F5D72C3', 'booking', 0, '2025-05-18 18:25:02'),
(3, 40, 'Запись на тренировку', 'Вы успешно записались на тренировку 23.05.2025 в 09:00. Номер брони: 867CD3BB', 'booking', 0, '2025-05-21 20:12:49'),
(4, 40, 'Абонемент успешно приобретен', 'Вы успешно приобрели абонемент \"Абонемент на месяц\". Срок действия: до 20.06.2025.', 'system', 0, '2025-05-21 21:29:28'),
(5, 40, 'Абонемент успешно продлен', 'Вы успешно продлили абонемент \"Абонемент на месяц\". Новый срок действия: до 20.07.2025.', 'system', 0, '2025-05-21 21:29:41'),
(6, 40, 'Абонемент отменен', 'Ваш абонемент был отменен. Если у вас есть вопросы, пожалуйста, обратитесь к администратору.', 'system', 0, '2025-05-21 21:29:51'),
(7, 40, 'Абонемент успешно приобретен', 'Вы успешно приобрели абонемент \"Абонемент на месяц\". Срок действия: до 22.06.2025.', 'system', 0, '2025-05-23 18:03:26'),
(8, 40, 'Абонемент успешно продлен', 'Вы успешно продлили абонемент \"Абонемент на месяц\". Новый срок действия: до 22.07.2025.', 'system', 0, '2025-05-23 18:08:24'),
(9, 40, 'Абонемент отменен', 'Ваш абонемент был отменен. Если у вас есть вопросы, пожалуйста, обратитесь к администратору.', 'system', 0, '2025-05-23 18:08:29'),
(10, 40, 'Абонемент успешно приобретен', 'Вы успешно приобрели абонемент \"Персональные тренировки - 12\". Срок действия: до 21.08.2025.', 'system', 0, '2025-05-23 18:08:50'),
(11, 40, 'Запись на тренировку', 'Вы успешно записались на тренировку 24.05.2025 в 08:00. Номер брони: 6743D9D6', 'booking', 0, '2025-05-23 18:09:25'),
(12, 40, 'Абонемент отменен', 'Ваш абонемент был отменен. Если у вас есть вопросы, пожалуйста, обратитесь к администратору.', 'system', 0, '2025-05-23 18:10:39'),
(13, 40, 'Абонемент успешно приобретен', 'Вы успешно приобрели абонемент \"Разовое посещение\". Срок действия: до 24.05.2025.', 'system', 0, '2025-05-23 18:19:55'),
(14, 40, 'Запись на тренировку', 'Вы успешно записались на тренировку 23.05.2025 в 20:00. Номер брони: 65823B63', 'booking', 0, '2025-05-23 18:20:26'),
(15, 40, 'Абонемент отменен', 'Ваш абонемент был отменен. Если у вас есть вопросы, пожалуйста, обратитесь к администратору.', 'system', 0, '2025-05-23 18:31:48'),
(16, 40, 'Абонемент успешно приобретен', 'Вы успешно приобрели абонемент \"Персональные тренировки - 4\". Срок действия: до 22.06.2025.', 'system', 0, '2025-05-23 18:32:27'),
(17, 40, 'Запись на тренировку', 'Вы успешно записались на тренировку 23.05.2025 в 19:00. Номер брони: 467FDBCE', 'booking', 0, '2025-05-23 18:34:55'),
(18, 40, 'Запись на тренировку', 'Вы успешно записались на тренировку 24.05.2025 в 11:00. Номер брони: E0689F23', 'booking', 0, '2025-05-23 18:38:16');

-- --------------------------------------------------------

--
-- Структура таблицы `payments`
--

CREATE TABLE `payments` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `subscription_id` int DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('card','cash','bank_transfer') NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `payment_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `subscription_id`, `amount`, `payment_method`, `transaction_id`, `status`, `payment_date`, `notes`, `created_at`, `updated_at`) VALUES
(1, 40, 2, 4500.00, 'card', NULL, 'completed', '2025-05-21 21:29:28', 'Оплата абонемента Абонемент на месяц', '2025-05-21 21:29:28', '2025-05-21 21:29:28'),
(2, 40, 2, 4500.00, 'card', NULL, 'completed', '2025-05-21 21:29:41', 'Оплата абонемента Абонемент на месяц', '2025-05-21 21:29:41', '2025-05-21 21:29:41'),
(3, 40, 2, 4500.00, 'card', NULL, 'completed', '2025-05-23 18:03:26', 'Оплата абонемента Абонемент на месяц', '2025-05-23 18:03:26', '2025-05-23 18:03:26'),
(4, 40, 2, 4500.00, 'card', NULL, 'completed', '2025-05-23 18:08:24', 'Оплата абонемента Абонемент на месяц', '2025-05-23 18:08:24', '2025-05-23 18:08:24'),
(5, 40, 8, 24000.00, 'card', NULL, 'completed', '2025-05-23 18:08:50', 'Оплата абонемента Персональные тренировки - 12', '2025-05-23 18:08:50', '2025-05-23 18:08:50'),
(6, 40, 1, 500.00, 'card', NULL, 'completed', '2025-05-23 18:19:55', 'Оплата абонемента Разовое посещение', '2025-05-23 18:19:55', '2025-05-23 18:19:55'),
(7, 40, 6, 9000.00, 'card', NULL, 'completed', '2025-05-23 18:32:27', 'Оплата абонемента Персональные тренировки - 4', '2025-05-23 18:32:27', '2025-05-23 18:32:27');

-- --------------------------------------------------------

--
-- Структура таблицы `permissions`
--

CREATE TABLE `permissions` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `description`) VALUES
(1, 'admin_panel_access', 'Полный доступ к админ-панели'),
(2, 'manage_users', 'Управление пользователями и назначение ролей'),
(3, 'manage_trainers', 'Управление тренерами'),
(4, 'manage_services', 'Управление услугами'),
(5, 'manage_schedule', 'Управление расписанием'),
(6, 'manage_bookings', 'Управление записями на тренировки'),
(7, 'moderate_reviews', 'Модерация отзывов');

-- --------------------------------------------------------

--
-- Структура таблицы `reviews`
--

CREATE TABLE `reviews` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `rating` int NOT NULL,
  `text` text NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `reviews`
--

INSERT INTO `reviews` (`id`, `name`, `email`, `rating`, `text`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Ольга', 'olga@example.com', 5, 'Мореон Фитнес – семейный премиум фитнес-клуб с бассейном, 40 видами групповых программ, детским клубом, школой единоборств и скалодромом. Оборудование тренажерного зала поставляет эксклюзивный партнер', 'approved', '2025-05-18 19:58:44', NULL),
(2, 'Алексей', 'alexey@example.com', 4, 'Отличный фитнес-клуб с прекрасными тренерами. Очень доволен результатами после трех месяцев тренировок.', 'approved', '2025-05-18 19:58:44', NULL),
(3, 'Ирина', 'irina@example.com', 5, 'Просторный тренажерный зал, отличный бассейн, множество групповых программ - все что нужно для полноценных тренировок.', 'approved', '2025-05-18 19:58:44', NULL),
(5, 'Макс', 'msprog@icloud.com', 3, 'Плохой сервис', 'approved', '2025-05-23 18:48:59', '2025-05-23 18:49:20');

-- --------------------------------------------------------

--
-- Структура таблицы `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role` enum('admin','manager','user') NOT NULL,
  `permission_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `role_permissions`
--

INSERT INTO `role_permissions` (`role`, `permission_id`) VALUES
('admin', 1),
('admin', 2),
('admin', 3),
('admin', 4),
('admin', 5),
('manager', 5),
('admin', 6),
('manager', 6),
('admin', 7),
('manager', 7);

-- --------------------------------------------------------

--
-- Структура таблицы `services`
--

CREATE TABLE `services` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `duration` int DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `max_participants` int DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

--
-- Дамп данных таблицы `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `duration`, `price`, `max_participants`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Персональная тренировка', 'Индивидуальная тренировка с персональным тренером', 60, 2500.00, 1, 1, '2025-05-18 15:59:41', '2025-05-18 15:59:41'),
(2, 'Групповая тренировка - Йога', 'Групповое занятие йогой для всех уровней подготовки', 90, 1000.00, 15, 1, '2025-05-18 15:59:41', '2025-05-18 15:59:41'),
(3, 'Групповая тренировка - CrossFit', 'Высокоинтенсивная функциональная тренировка', 60, 1200.00, 12, 1, '2025-05-18 15:59:41', '2025-05-18 15:59:41'),
(4, 'Групповая тренировка - Пилатес', 'Система упражнений для всего тела', 60, 900.00, 10, 1, '2025-05-18 15:59:41', '2025-05-18 15:59:41'),
(5, 'Групповая тренировка - Бокс', 'Основы бокса и кардионагрузка', 60, 1100.00, 8, 1, '2025-05-18 15:59:41', '2025-05-18 15:59:41'),
(6, 'Групповая тренировка - Танцы', 'Танцевальные тренировки для похудения', 60, 800.00, 20, 1, '2025-05-18 15:59:41', '2025-05-18 15:59:41'),
(7, 'Сплит-тренировка', 'Персональная тренировка с акцентом на определенные группы мышц', 75, 3000.00, 1, 1, '2025-05-18 15:59:41', '2025-05-18 15:59:41'),
(8, 'Консультация по питанию', 'Составление индивидуального плана питания', 45, 2000.00, 1, 1, '2025-05-18 15:59:41', '2025-05-18 15:59:41'),
(9, 'Реабилитационная тренировка', 'Тренировка для восстановления после травм', 60, 2800.00, 1, 1, '2025-05-18 15:59:41', '2025-05-18 15:59:41'),
(10, 'Мастер-класс', 'Интенсив по определенному направлению', 120, 1500.00, 25, 1, '2025-05-18 15:59:41', '2025-05-18 15:59:41'),
(11, 'personal', NULL, 60, 1000.00, 1, 1, '2025-05-18 17:06:00', '2025-05-18 17:06:00');

-- --------------------------------------------------------

--
-- Структура таблицы `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`, `description`, `created_at`, `updated_at`) VALUES
('address', 'Москва, ул. Примерная, д. 123', 'Адрес фитнес-центра', '2025-05-18 15:50:17', '2025-05-23 18:02:16'),
('available_languages', 'ru,en', 'Доступные языки, через запятую', '2025-05-18 15:50:17', '2025-05-18 15:50:17'),
('booking_advance_days', '7', NULL, '2025-05-18 16:12:43', '2025-05-23 18:02:16'),
('cancellation_hours', '24', NULL, '2025-05-18 16:12:43', '2025-05-23 18:02:16'),
('cancellation_policy_hours', '24', 'За сколько часов можно отменить тренировку без штрафа', '2025-05-18 15:50:17', '2025-05-18 15:50:17'),
('contact_email', 'info@moreonfitness.com', 'Контактный email', '2025-05-18 15:50:17', '2025-05-23 18:02:16'),
('contact_phone', '+7 (999) 123-45-67', 'Контактный телефон', '2025-05-18 15:50:17', '2025-05-23 18:02:16'),
('default_language', 'ru', 'Язык по умолчанию', '2025-05-18 15:50:17', '2025-05-18 15:50:17'),
('enable_online_booking', '1', 'Возможность онлайн-бронирования (1 - включено, 0 - выключено)', '2025-05-18 15:50:17', '2025-05-23 18:02:16'),
('facebook_url', 'https://facebook.com/moreonfitness', 'Facebook URL', '2025-05-18 15:50:17', '2025-05-23 18:02:16'),
('instagram_url', 'https://instagram.com/moreonfitness', 'Instagram URL', '2025-05-18 15:50:17', '2025-05-23 18:02:16'),
('maintenance_message', 'Сайт находится на техническом обслуживании. Пожалуйста, зайдите позже.', NULL, '2025-05-18 16:12:43', '2025-05-23 18:02:16'),
('maintenance_mode', '0', 'Режим обслуживания (1 - включен, 0 - выключен)', '2025-05-18 15:50:17', '2025-05-23 18:02:16'),
('max_booking_days_ahead', '14', 'Максимальное количество дней для предварительного бронирования', '2025-05-18 15:50:17', '2025-05-18 15:50:17'),
('site_description', 'Фитнес-центр премиум класса', NULL, '2025-05-18 16:12:43', '2025-05-23 18:02:16'),
('site_name', 'Moreon Fitness', 'Название сайта', '2025-05-18 15:50:17', '2025-05-23 18:02:16'),
('vk_url', 'https://vk.com/moreonfitness', 'VK URL', '2025-05-18 15:50:17', '2025-05-23 18:02:16'),
('working_hours', 'Пн-Пт: 7:00-23:00, Сб-Вс: 9:00-22:00', 'Часы работы', '2025-05-18 15:50:17', '2025-05-23 18:02:16'),
('youtube_url', 'https://youtube.com/moreonfitness', 'YouTube URL', '2025-05-18 15:50:17', '2025-05-23 18:02:16');

-- --------------------------------------------------------

--
-- Структура таблицы `specializations`
--

CREATE TABLE `specializations` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `specializations`
--

INSERT INTO `specializations` (`id`, `name`, `description`) VALUES
(1, 'Силовые тренировки', 'Тренировки, направленные на развитие силы и мышечной массы'),
(2, 'Кардио', 'Тренировки для укрепления сердечно-сосудистой системы'),
(3, 'Йога', 'Практики для развития гибкости и баланса'),
(4, 'Пилатес', 'Система упражнений для укрепления мышц кора'),
(5, 'Функциональный тренинг', 'Тренировки, имитирующие повседневные движения'),
(6, 'Танцевальные направления', 'Танцевальные программы для поддержания физической формы'),
(7, 'Бокс и единоборства', 'Тренировки по боксу и другим боевым искусствам'),
(8, 'Стретчинг', 'Упражнения на растяжку'),
(9, 'CrossFit', 'Программа физической подготовки высокой интенсивности'),
(10, 'Реабилитация', 'Восстановление после травм и операций'),
(11, 'Силовые тренировки', 'Тренировки, направленные на развитие силы и мышечной массы'),
(12, 'Кардио', 'Тренировки для укрепления сердечно-сосудистой системы'),
(13, 'Йога', 'Практики для развития гибкости и баланса'),
(14, 'Пилатес', 'Система упражнений для укрепления мышц кора'),
(15, 'Функциональный тренинг', 'Тренировки, имитирующие повседневные движения'),
(16, 'Танцевальные направления', 'Танцевальные программы для поддержания физической формы'),
(17, 'Бокс и единоборства', 'Тренировки по боксу и другим боевым искусствам'),
(18, 'Стретчинг', 'Упражнения на растяжку'),
(19, 'CrossFit', 'Программа физической подготовки высокой интенсивности'),
(20, 'Реабилитация', 'Восстановление после травм и операций'),
(21, 'Силовой тренинг', 'Работа с весами для набора мышечной массы и силы'),
(22, 'Кардиотренировки', 'Упражнения для укрепления сердечно-сосудистой системы'),
(23, 'Йога', 'Комплекс упражнений для тела и ума'),
(24, 'Растяжка', 'Упражнения на гибкость и подвижность суставов'),
(25, 'Кроссфит', 'Высокоинтенсивные функциональные тренировки'),
(26, 'Бокс', 'Техники и тренировки по боксу'),
(27, 'Танцевальные программы', 'Фитнес с элементами танца'),
(28, 'Пилатес', 'Система упражнений для развития мышц тела');

-- --------------------------------------------------------

--
-- Структура таблицы `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `duration_days` int NOT NULL,
  `sessions_count` int DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

--
-- Дамп данных таблицы `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `name`, `description`, `duration_days`, `sessions_count`, `price`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Разовое посещение', 'Однократное посещение тренажерного зала', 1, 1, 500.00, 1, '2025-05-18 15:59:41', '2025-05-18 15:59:41'),
(2, 'Абонемент на месяц', 'Безлимитное посещение тренажерного зала в течение месяца', 30, NULL, 4500.00, 1, '2025-05-18 15:59:41', '2025-05-18 15:59:41'),
(3, 'Абонемент на 3 месяца', 'Безлимитное посещение тренажерного зала в течение 3 месяцев', 90, NULL, 12000.00, 1, '2025-05-18 15:59:41', '2025-05-18 15:59:41'),
(4, 'Абонемент на 6 месяцев', 'Безлимитное посещение тренажерного зала в течение 6 месяцев', 180, NULL, 21000.00, 1, '2025-05-18 15:59:41', '2025-05-18 15:59:41'),
(5, 'Абонемент на 12 месяцев', 'Безлимитное посещение тренажерного зала в течение года', 365, NULL, 36000.00, 1, '2025-05-18 15:59:41', '2025-05-18 15:59:41'),
(6, 'Персональные тренировки - 4', 'Пакет из 4 персональных тренировок', 30, 4, 9000.00, 1, '2025-05-18 15:59:41', '2025-05-18 15:59:41'),
(7, 'Персональные тренировки - 8', 'Пакет из 8 персональных тренировок', 60, 8, 17000.00, 1, '2025-05-18 15:59:41', '2025-05-18 15:59:41'),
(8, 'Персональные тренировки - 12', 'Пакет из 12 персональных тренировок', 90, 12, 24000.00, 1, '2025-05-18 15:59:41', '2025-05-18 15:59:41'),
(9, 'Групповые занятия - 8', 'Пакет из 8 групповых занятий', 61, 8, 7000.00, 1, '2025-05-18 15:59:41', '2025-05-19 09:39:07'),
(10, 'Групповые занятия - 16', 'Пакет из 16 групповых занятий', 90, 16, 12000.00, 1, '2025-05-18 15:59:41', '2025-05-18 15:59:41');

-- --------------------------------------------------------

--
-- Структура таблицы `trainers`
--

CREATE TABLE `trainers` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `experience_years` int DEFAULT NULL,
  `bio` text,
  `photo_url` varchar(255) DEFAULT NULL,
  `achievements` text,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

--
-- Дамп данных таблицы `trainers`
--

INSERT INTO `trainers` (`id`, `user_id`, `experience_years`, `bio`, `photo_url`, `achievements`, `is_active`, `created_at`, `updated_at`) VALUES
(7, 43, 10, 'Профессиональный тренер с многолетним опытом работы. Специализируется на силовом тренинге и коррекции фигуры.', 'assets/img/trainers/trainer-1.jpg', 'Мастер спорта по пауэрлифтингу, призер чемпионата России 2018', 1, '2025-05-19 08:24:01', '2025-05-19 08:24:01'),
(8, 44, 5, 'Сертифицированный тренер по йоге и пилатесу. Помогает клиентам достичь гармонии тела и духа.', 'assets/img/trainers/trainer-2.jpg', 'Сертифицированный инструктор по йоге международного класса', 1, '2025-05-19 08:24:01', '2025-05-19 08:24:01'),
(9, 45, 8, 'Тренер по кроссфиту и функциональным тренировкам. Специализируется на высокоинтенсивных тренировках.', 'assets/img/trainers/trainer-3.jpg', 'Победитель региональных соревнований по кроссфиту 2019, 2020', 1, '2025-05-19 08:24:01', '2025-05-19 08:24:01'),
(10, 46, 6, 'Тренер по танцевальным направлениям и растяжке. Поможет развить гибкость и грацию.', 'assets/img/trainers/trainer-4.jpg', 'Хореограф-постановщик, участница танцевальных шоу', 1, '2025-05-19 08:24:01', '2025-05-19 08:24:01'),
(11, 47, 12, 'Опытный тренер по боксу и ММА. Научит правильной технике и дисциплине.', 'assets/img/trainers/trainer-5.jpg', 'Мастер спорта по боксу, тренер национальной сборной 2015-2017', 1, '2025-05-19 08:24:01', '2025-05-19 08:24:01'),
(12, 48, 4, 'Специалист по кардиотренировкам и снижению веса. Поможет достичь желаемой формы.', 'assets/img/trainers/trainer-6.jpg', 'Сертифицированный нутрициолог, специалист по снижению веса', 1, '2025-05-19 08:24:01', '2025-05-19 08:24:01'),
(13, 49, 9, 'Тренер по функциональным тренировкам и реабилитации. Помогает восстановиться после травм.', 'assets/img/trainers/trainer-7.jpg', 'Физиотерапевт, специалист по спортивной реабилитации', 1, '2025-05-19 08:24:01', '2025-05-19 08:24:01'),
(14, 50, 7, 'Мастер групповых программ и пилатеса. Создает индивидуальный подход к каждому клиенту.', 'assets/img/trainers/trainer-8.jpg', 'Победитель фитнес-конвенций, автор методики \"Осознанный пилатес\"', 1, '2025-05-19 08:24:01', '2025-05-19 08:24:01'),
(15, 51, 2, 'Тестовый тренер для проверки функциональности', NULL, NULL, 1, '2025-05-21 20:41:40', '2025-05-21 20:41:40'),
(16, 53, 0, 'Новый тренер', NULL, NULL, 1, '2025-05-21 20:52:47', '2025-05-21 20:52:47');

-- --------------------------------------------------------

--
-- Структура таблицы `trainer_certificates`
--

CREATE TABLE `trainer_certificates` (
  `id` int NOT NULL,
  `trainer_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `issuing_organization` varchar(255) NOT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `certificate_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `trainer_education`
--

CREATE TABLE `trainer_education` (
  `id` int NOT NULL,
  `trainer_id` int NOT NULL,
  `institution` varchar(255) NOT NULL,
  `degree` varchar(100) DEFAULT NULL,
  `field_of_study` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `trainer_education`
--

INSERT INTO `trainer_education` (`id`, `trainer_id`, `institution`, `degree`, `field_of_study`, `start_date`, `end_date`) VALUES
(7, 7, 'Российский Государственный Университет Физической Культуры', 'Бакалавр', 'Физическая культура', '2008-09-01', '2012-06-30');

-- --------------------------------------------------------

--
-- Структура таблицы `trainer_schedule`
--

CREATE TABLE `trainer_schedule` (
  `id` int NOT NULL,
  `trainer_id` int NOT NULL,
  `day_of_week` tinyint NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `trainer_specializations`
--

CREATE TABLE `trainer_specializations` (
  `trainer_id` int NOT NULL,
  `specialization_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `trainer_specializations`
--

INSERT INTO `trainer_specializations` (`trainer_id`, `specialization_id`) VALUES
(7, 1),
(12, 1),
(13, 1),
(9, 2),
(12, 2),
(8, 3),
(10, 4),
(13, 4),
(9, 5),
(11, 6),
(10, 7),
(14, 7),
(8, 8),
(14, 8);

-- --------------------------------------------------------

--
-- Структура таблицы `training_sessions`
--

CREATE TABLE `training_sessions` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `trainer_id` int DEFAULT NULL,
  `service_id` int DEFAULT NULL,
  `session_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `training_sessions`
--

INSERT INTO `training_sessions` (`id`, `user_id`, `trainer_id`, `service_id`, `session_date`, `start_time`, `end_time`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 40, NULL, 11, '2025-05-21', '08:00:00', '09:00:00', 'completed', 'cool', '2025-05-18 17:06:00', '2025-05-18 18:58:24'),
(2, 40, NULL, 3, '2025-05-19', '12:00:00', '13:00:00', 'completed', 'cool', '2025-05-18 18:25:02', '2025-05-18 18:58:27'),
(8, 40, 11, 2, '2025-05-23', '09:00:00', '10:00:00', 'completed', 'с', '2025-05-21 20:12:49', '2025-05-23 18:21:22'),
(9, 40, 16, 1, '2025-05-23', '10:00:00', '11:00:00', 'completed', NULL, '2025-05-21 21:01:34', '2025-05-23 18:21:20'),
(10, 40, 13, 1, '2025-05-24', '08:00:00', '09:00:00', 'completed', '', '2025-05-23 18:09:25', '2025-05-23 18:09:57'),
(11, 40, 13, 1, '2025-05-23', '20:00:00', '21:00:00', 'completed', '', '2025-05-23 18:20:26', '2025-05-23 18:21:17'),
(12, 40, 10, 2, '2025-05-23', '19:00:00', '20:00:00', 'completed', 'с', '2025-05-23 18:34:55', '2025-05-23 18:35:14'),
(13, 40, 11, 1, '2025-05-24', '11:00:00', '12:00:00', 'completed', 'с', '2025-05-23 18:38:16', '2025-05-23 18:38:49');

-- --------------------------------------------------------

--
-- Структура таблицы `translations`
--

CREATE TABLE `translations` (
  `id` int NOT NULL,
  `locale` varchar(10) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int NOT NULL,
  `field` varchar(50) NOT NULL,
  `translation` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','manager','trainer','user') NOT NULL DEFAULT 'user',
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `gender` enum('male','female') DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `height` int DEFAULT NULL,
  `weight` float DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `role`, `first_name`, `last_name`, `phone`, `birthdate`, `gender`, `avatar_url`, `height`, `weight`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin@example.com', '$2y$12$TGXyExHP1UsSozalRQiZEuMgBxz2WJnrvz0JFYIf46vFfdt7b5aR.', 'admin', 'Administrator', 'System', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2025-05-18 18:51:49', '2025-05-18 15:50:17', '2025-05-18 18:51:49'),
(22, 'manager@example.com', '$2y$12$TGXyExHP1UsSozalRQiZEuMgBxz2WJnrvz0JFYIf46vFfdt7b5aR.', 'manager', 'Марина', 'Менеджер', '+79002345678', '1990-05-20', 'female', NULL, NULL, NULL, 1, NULL, '2025-05-18 15:52:51', '2025-05-18 15:52:51'),
(23, 'trainer1@example.com', '$2y$12$TGXyExHP1UsSozalRQiZEuMgBxz2WJnrvz0JFYIf46vFfdt7b5aR.', 'user', 'Сергей', 'Петров', '+79003456789', '1988-07-12', 'male', NULL, NULL, NULL, 1, NULL, '2025-05-18 15:52:51', '2025-05-18 15:52:51'),
(24, 'trainer2@example.com', '$2y$12$TGXyExHP1UsSozalRQiZEuMgBxz2WJnrvz0JFYIf46vFfdt7b5aR.', 'user', 'Елена', 'Иванова', '+79004567890', '1992-11-23', 'female', NULL, NULL, NULL, 1, NULL, '2025-05-18 15:52:51', '2025-05-18 15:52:51'),
(25, 'trainer3@example.com', '$2y$12$TGXyExHP1UsSozalRQiZEuMgBxz2WJnrvz0JFYIf46vFfdt7b5aR.', 'user', 'Алексей', 'Смирнов', '+79005678901', '1986-04-05', 'male', NULL, NULL, NULL, 1, NULL, '2025-05-18 15:52:51', '2025-05-18 15:52:51'),
(26, 'user1@example.com', '$2y$12$TGXyExHP1UsSozalRQiZEuMgBxz2WJnrvz0JFYIf46vFfdt7b5aR.', 'user', 'Ольга', 'Кузнецова', '+79006789012', '1995-08-17', 'female', NULL, NULL, NULL, 1, NULL, '2025-05-18 15:52:51', '2025-05-18 15:52:51'),
(27, 'user2@example.com', '$2y$12$TGXyExHP1UsSozalRQiZEuMgBxz2WJnrvz0JFYIf46vFfdt7b5aR.', 'user', 'Иван', 'Соколов', '+79007890123', '1991-02-28', 'male', NULL, NULL, NULL, 1, NULL, '2025-05-18 15:52:51', '2025-05-18 15:52:51'),
(28, 'user3@example.com', '$2y$12$TGXyExHP1UsSozalRQiZEuMgBxz2WJnrvz0JFYIf46vFfdt7b5aR.', 'user', 'Наталья', 'Новикова', '+79008901234', '1993-09-10', 'female', NULL, NULL, NULL, 1, NULL, '2025-05-18 15:52:51', '2025-05-18 15:52:51'),
(29, 'user4@example.com', '$2y$12$TGXyExHP1UsSozalRQiZEuMgBxz2WJnrvz0JFYIf46vFfdt7b5aR.', 'user', 'Дмитрий', 'Козлов', '+79009012345', '1989-12-05', 'male', NULL, NULL, NULL, 1, NULL, '2025-05-18 15:52:51', '2025-05-18 15:52:51'),
(30, 'user5@example.com', '$2y$12$TGXyExHP1UsSozalRQiZEuMgBxz2WJnrvz0JFYIf46vFfdt7b5aR.', 'user', 'Екатерина', 'Морозова', '+79000123456', '1994-06-30', 'female', NULL, NULL, NULL, 1, NULL, '2025-05-18 15:52:51', '2025-05-18 15:52:51'),
(40, 'msprog@icloud.com', '$2y$10$IRBcYoK.l.I8M7nbyb7I2.Kz24Cmq2EH5g.rf9pBGuLOyHo1S2hm.', 'admin', 'Максим', 'Васильев', '89082515044', '2007-11-22', 'male', 'uploads/avatars/avatar_40_1747648561.jpg', 178, 70, 1, '2025-05-23 12:03:17', '2025-05-18 16:39:57', '2025-05-23 12:03:17'),
(41, 'maxim.loof@icloud.com', '$2y$10$FJ0/WKViF/dx2r7YLZk1H.XmCXyn6brvV0kKpx.JL2Zfez.caRCZa', 'manager', 'Никита', '', '89082515044', NULL, NULL, NULL, NULL, NULL, 1, '2025-05-18 19:00:15', '2025-05-18 19:00:15', '2025-05-21 20:46:27'),
(43, 'ivan.petrov@example.com', '$2y$12$TGXyExHP1UsSozalRQiZEuMgBxz2WJnrvz0JFYIf46vFfdt7b5aR.', 'trainer', 'Иван', 'Петров', '+7 (999) 123-45-67', NULL, 'male', NULL, NULL, NULL, 1, NULL, '2025-05-19 08:24:01', '2025-05-21 20:33:49'),
(44, 'elena.smirnova@example.com', '$2y$12$TGXyExHP1UsSozalRQiZEuMgBxz2WJnrvz0JFYIf46vFfdt7b5aR.', 'trainer', 'Елена', 'Смирнова', '+7 (999) 234-56-78', NULL, 'female', NULL, NULL, NULL, 1, NULL, '2025-05-19 08:24:01', '2025-05-21 20:33:49'),
(45, 'sergey.kozlov@example.com', '$2y$12$TGXyExHP1UsSozalRQiZEuMgBxz2WJnrvz0JFYIf46vFfdt7b5aR.', 'trainer', 'Сергей', 'Козлов', '+7 (999) 345-67-89', NULL, 'male', NULL, NULL, NULL, 1, NULL, '2025-05-19 08:24:01', '2025-05-21 20:33:49'),
(46, 'anna.ivanova@example.com', '$2y$12$TGXyExHP1UsSozalRQiZEuMgBxz2WJnrvz0JFYIf46vFfdt7b5aR.', 'trainer', 'Анна', 'Иванова', '+7 (999) 456-78-90', NULL, 'female', NULL, NULL, NULL, 1, NULL, '2025-05-19 08:24:01', '2025-05-21 20:33:49'),
(47, 'dmitry.sokolov@example.com', '$2y$12$TGXyExHP1UsSozalRQiZEuMgBxz2WJnrvz0JFYIf46vFfdt7b5aR.', 'trainer', 'Дмитрий', 'Соколов', '+7 (999) 567-89-01', NULL, 'male', NULL, NULL, NULL, 1, NULL, '2025-05-19 08:24:01', '2025-05-21 20:33:49'),
(48, 'maria.kuznetsova@example.com', '$2y$12$TGXyExHP1UsSozalRQiZEuMgBxz2WJnrvz0JFYIf46vFfdt7b5aR.', 'trainer', 'Мария', 'Кузнецова', '+7 (999) 678-90-12', NULL, 'female', NULL, NULL, NULL, 1, NULL, '2025-05-19 08:24:01', '2025-05-21 20:33:49'),
(49, 'alexey.morozov@example.com', '$2y$12$TGXyExHP1UsSozalRQiZEuMgBxz2WJnrvz0JFYIf46vFfdt7b5aR.', 'trainer', 'Алексей', 'Морозов', '+7 (999) 789-01-23', NULL, 'male', NULL, NULL, NULL, 1, NULL, '2025-05-19 08:24:01', '2025-05-21 20:33:49'),
(50, 'natalia.orlova@example.com', '$2y$12$TGXyExHP1UsSozalRQiZEuMgBxz2WJnrvz0JFYIf46vFfdt7b5aR.', 'trainer', 'Наталья', 'Орлова', '+7 (999) 890-12-34', NULL, 'female', NULL, NULL, NULL, 1, NULL, '2025-05-19 08:24:01', '2025-05-21 20:33:49'),
(51, 'test_trainer@example.com', '482c811da5d5b4bc6d497ffa98491e38', 'trainer', 'Тестовый', 'Тренер', '+79001234567', NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-05-21 20:41:17', '2025-05-21 20:41:17'),
(52, 'm@m.m', '$2y$10$TyiDqM/MGWgq.zL3etZ0gOidV3ib58z.dy2X7rk3FPvVGmRpX6UkO', 'manager', 'Максим', '', '89082515077', NULL, NULL, NULL, NULL, NULL, 1, '2025-05-21 20:50:13', '2025-05-21 20:48:15', '2025-05-21 20:50:13'),
(53, 'q@q.q', '$2y$10$LQFm9GoLXWy1e8MUaCjXqOKky/iKtMgh7gkxCS1ZGq4QL37zwXMf.', 'trainer', 'Максим', 'Васильев', '89083334447', NULL, NULL, NULL, NULL, NULL, 1, '2025-05-21 20:55:44', '2025-05-21 20:51:47', '2025-05-22 04:54:06');

-- --------------------------------------------------------

--
-- Структура таблицы `user_subscriptions`
--

CREATE TABLE `user_subscriptions` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `subscription_id` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `remaining_sessions` int DEFAULT NULL,
  `status` enum('active','expired','cancelled') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `user_subscriptions`
--

INSERT INTO `user_subscriptions` (`id`, `user_id`, `subscription_id`, `start_date`, `end_date`, `remaining_sessions`, `status`, `created_at`, `updated_at`) VALUES
(2, 40, 2, '2025-05-21', '2025-07-20', NULL, 'cancelled', '2025-05-21 21:29:28', '2025-05-23 18:37:22'),
(3, 40, 2, '2025-05-23', '2025-07-22', NULL, 'cancelled', '2025-05-23 18:03:26', '2025-05-23 18:37:22'),
(4, 40, 8, '2025-05-23', '2025-08-21', 12, 'cancelled', '2025-05-23 18:08:50', '2025-05-23 18:37:22'),
(5, 40, 1, '2025-05-23', '2025-05-24', 1, 'cancelled', '2025-05-23 18:19:55', '2025-05-23 18:37:22'),
(6, 40, 6, '2025-05-23', '2025-06-22', 3, 'active', '2025-05-23 18:32:27', '2025-05-23 18:38:49');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `api_tokens`
--
ALTER TABLE `api_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_api_tokens_expires` (`expires_at`,`is_active`);

--
-- Индексы таблицы `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_audit_log_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_audit_log_action` (`action`);

--
-- Индексы таблицы `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user` (`user_id`,`is_read`);

--
-- Индексы таблицы `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `subscription_id` (`subscription_id`),
  ADD KEY `idx_payments_status` (`status`),
  ADD KEY `idx_payments_date` (`payment_date`);

--
-- Индексы таблицы `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Индексы таблицы `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Индексы таблицы `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_services_is_active` (`is_active`);

--
-- Индексы таблицы `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Индексы таблицы `specializations`
--
ALTER TABLE `specializations`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `trainers`
--
ALTER TABLE `trainers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_trainers_is_active` (`is_active`);

--
-- Индексы таблицы `trainer_certificates`
--
ALTER TABLE `trainer_certificates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trainer_id` (`trainer_id`);

--
-- Индексы таблицы `trainer_education`
--
ALTER TABLE `trainer_education`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trainer_id` (`trainer_id`);

--
-- Индексы таблицы `trainer_schedule`
--
ALTER TABLE `trainer_schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_trainer_schedule` (`trainer_id`,`day_of_week`);

--
-- Индексы таблицы `trainer_specializations`
--
ALTER TABLE `trainer_specializations`
  ADD PRIMARY KEY (`trainer_id`,`specialization_id`),
  ADD KEY `specialization_id` (`specialization_id`);

--
-- Индексы таблицы `training_sessions`
--
ALTER TABLE `training_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `idx_training_sessions_date` (`session_date`),
  ADD KEY `idx_training_sessions_status` (`status`),
  ADD KEY `idx_training_sessions_trainer_date` (`trainer_id`,`session_date`),
  ADD KEY `idx_training_sessions_user_date` (`user_id`,`session_date`);

--
-- Индексы таблицы `translations`
--
ALTER TABLE `translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `locale` (`locale`,`entity_type`,`entity_id`,`field`),
  ADD KEY `idx_translations_lookup` (`locale`,`entity_type`,`field`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_role` (`role`);

--
-- Индексы таблицы `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `subscription_id` (`subscription_id`),
  ADD KEY `idx_user_subscriptions_status` (`status`),
  ADD KEY `idx_user_subscriptions_dates` (`start_date`,`end_date`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `api_tokens`
--
ALTER TABLE `api_tokens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT для таблицы `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT для таблицы `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `services`
--
ALTER TABLE `services`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `specializations`
--
ALTER TABLE `specializations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT для таблицы `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `trainers`
--
ALTER TABLE `trainers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `trainer_certificates`
--
ALTER TABLE `trainer_certificates`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `trainer_education`
--
ALTER TABLE `trainer_education`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `trainer_schedule`
--
ALTER TABLE `trainer_schedule`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT для таблицы `training_sessions`
--
ALTER TABLE `training_sessions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT для таблицы `translations`
--
ALTER TABLE `translations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `api_tokens`
--
ALTER TABLE `api_tokens`
  ADD CONSTRAINT `api_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `trainers`
--
ALTER TABLE `trainers`
  ADD CONSTRAINT `trainers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `trainer_certificates`
--
ALTER TABLE `trainer_certificates`
  ADD CONSTRAINT `trainer_certificates_ibfk_1` FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `trainer_education`
--
ALTER TABLE `trainer_education`
  ADD CONSTRAINT `trainer_education_ibfk_1` FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `trainer_schedule`
--
ALTER TABLE `trainer_schedule`
  ADD CONSTRAINT `trainer_schedule_ibfk_1` FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `trainer_specializations`
--
ALTER TABLE `trainer_specializations`
  ADD CONSTRAINT `trainer_specializations_ibfk_1` FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `trainer_specializations_ibfk_2` FOREIGN KEY (`specialization_id`) REFERENCES `specializations` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `training_sessions`
--
ALTER TABLE `training_sessions`
  ADD CONSTRAINT `training_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `training_sessions_ibfk_2` FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `training_sessions_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  ADD CONSTRAINT `user_subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_subscriptions_ibfk_2` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
