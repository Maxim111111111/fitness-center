-- Создаем таблицу для тренеров, если она еще не существует
CREATE TABLE
    IF NOT EXISTS `trainers` (
        `id` int (11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `specialization` varchar(100) DEFAULT NULL,
        `bio` text DEFAULT NULL,
        `photo` varchar(255) DEFAULT NULL,
        `active` tinyint (1) NOT NULL DEFAULT 1,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Вставляем демо-данные для тренеров, если таблица пуста
INSERT INTO
    `trainers` (`name`, `specialization`, `bio`, `active`)
SELECT
    'Иванов Иван',
    'Силовые тренировки',
    'Профессиональный тренер с опытом работы более 5 лет',
    1
WHERE
    NOT EXISTS (
        SELECT
            1
        FROM
            `trainers`
        LIMIT
            1
    );

INSERT INTO
    `trainers` (`name`, `specialization`, `bio`, `active`)
SELECT
    'Петрова Мария',
    'Йога, пилатес',
    'Сертифицированный инструктор по йоге и пилатесу',
    1
WHERE
    NOT EXISTS (
        SELECT
            1
        FROM
            `trainers`
        WHERE
            `id` = 2
    );

INSERT INTO
    `trainers` (`name`, `specialization`, `bio`, `active`)
SELECT
    'Сидоров Алексей',
    'Кроссфит',
    'Мастер спорта по тяжелой атлетике, тренер по кроссфиту',
    1
WHERE
    NOT EXISTS (
        SELECT
            1
        FROM
            `trainers`
        WHERE
            `id` = 3
    );

INSERT INTO
    `trainers` (`name`, `specialization`, `bio`, `active`)
SELECT
    'Козлова Анна',
    'Плавание',
    'Мастер спорта по плаванию, тренер с 10-летним опытом',
    1
WHERE
    NOT EXISTS (
        SELECT
            1
        FROM
            `trainers`
        WHERE
            `id` = 4
    );

-- Создаем таблицу для тренировок, если она еще не существует
CREATE TABLE
    IF NOT EXISTS `training_sessions` (
        `id` int (11) NOT NULL AUTO_INCREMENT,
        `user_id` int (11) DEFAULT NULL,
        `reference_code` varchar(20) DEFAULT NULL,
        `name` varchar(100) NOT NULL,
        `phone` varchar(20) NOT NULL,
        `email` varchar(100) NOT NULL,
        `trainer_id` int (11) NOT NULL,
        `date` date NOT NULL,
        `time` varchar(5) NOT NULL,
        `comments` text DEFAULT NULL,
        `status` enum ('pending', 'confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`),
        KEY `trainer_id` (`trainer_id`),
        CONSTRAINT `training_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
        CONSTRAINT `training_sessions_ibfk_2` FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Создаем таблицу для отзывов
CREATE TABLE
    IF NOT EXISTS `reviews` (
        `id` int (11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `email` varchar(100) NOT NULL,
        `rating` int (1) NOT NULL,
        `text` text NOT NULL,
        `status` enum ('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Вставляем демо-данные для отзывов, если таблица пуста
INSERT INTO
    `reviews` (`name`, `email`, `rating`, `text`, `status`)
SELECT
    'Ольга',
    'olga@example.com',
    5,
    'Мореон Фитнес – семейный премиум фитнес-клуб с бассейном, 40 видами групповых программ, детским клубом, школой единоборств и скалодромом. Оборудование тренажерного зала поставляет эксклюзивный партнер',
    'approved'
WHERE
    NOT EXISTS (
        SELECT
            1
        FROM
            `reviews`
        LIMIT
            1
    );