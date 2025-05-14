<?php
require_once 'config.php';

// Создание таблиц базы данных
try {
    // Таблица пользователей
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            role ENUM('admin', 'manager', 'trainer', 'member') NOT NULL DEFAULT 'member',
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            last_login DATETIME,
            profile_image VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Таблица инструкторов
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS trainers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            specialization VARCHAR(255),
            education TEXT,
            experience TEXT,
            biography TEXT,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Таблица услуг
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS services (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            duration INT NOT NULL COMMENT 'Длительность в минутах',
            image VARCHAR(255),
            service_category_id INT,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Таблица категорий услуг
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS service_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Добавление внешнего ключа для категорий услуг
    $pdo->exec("ALTER TABLE services ADD CONSTRAINT fk_service_category FOREIGN KEY (service_category_id) REFERENCES service_categories(id) ON DELETE SET NULL");

    // Таблица расписания
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS schedule (
            id INT AUTO_INCREMENT PRIMARY KEY,
            trainer_id INT,
            service_id INT NOT NULL,
            start_time DATETIME NOT NULL,
            end_time DATETIME NOT NULL,
            max_participants INT NOT NULL DEFAULT 1,
            current_participants INT NOT NULL DEFAULT 0,
            status ENUM('active', 'cancelled', 'completed') NOT NULL DEFAULT 'active',
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (trainer_id) REFERENCES trainers(id) ON DELETE SET NULL,
            FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Таблица бронирований
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS bookings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            schedule_id INT NOT NULL,
            status ENUM('pending', 'confirmed', 'cancelled', 'completed') NOT NULL DEFAULT 'pending',
            payment_status ENUM('unpaid', 'paid', 'refunded') NOT NULL DEFAULT 'unpaid',
            payment_amount DECIMAL(10,2),
            payment_date DATETIME,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (schedule_id) REFERENCES schedule(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Таблица абонементов
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS subscriptions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            duration INT NOT NULL COMMENT 'Продолжительность в днях, неделях или месяцах',
            duration_type ENUM('day', 'week', 'month') NOT NULL DEFAULT 'month',
            visit_limit INT NULL COMMENT 'Ограничение по количеству посещений, NULL - безлимитно',
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Таблица приобретенных абонементов пользователей
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_subscriptions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            subscription_id INT NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            visits_left INT NULL COMMENT 'Оставшееся количество посещений, NULL - безлимитно',
            status ENUM('active', 'expired', 'cancelled') NOT NULL DEFAULT 'active',
            payment_status ENUM('unpaid', 'paid', 'refunded') NOT NULL DEFAULT 'unpaid',
            payment_amount DECIMAL(10,2),
            payment_date DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Таблица для отзывов клиентов
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS testimonials (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            name VARCHAR(100) NOT NULL COMMENT 'Имя автора отзыва, если не привязан к пользователю',
            text TEXT NOT NULL,
            rating TINYINT NOT NULL COMMENT 'Оценка от 1 до 5',
            is_approved TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            approved_at TIMESTAMP NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Таблица для записей блога/новостей
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS blog_posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            content TEXT NOT NULL,
            excerpt TEXT,
            image VARCHAR(255),
            author_id INT,
            status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
            published_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Таблица для кадров списания и транзакций
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            type ENUM('payment', 'refund', 'adjustment') NOT NULL,
            status ENUM('pending', 'completed', 'failed') NOT NULL DEFAULT 'pending',
            description TEXT,
            related_entity_type VARCHAR(50) COMMENT 'Тип связанной сущности: booking, subscription и т.д.',
            related_entity_id INT COMMENT 'ID связанной сущности',
            payment_method VARCHAR(50),
            transaction_id VARCHAR(255) COMMENT 'Внешний ID транзакции платежной системы',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Таблица настроек системы
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS settings (
            setting_key VARCHAR(100) PRIMARY KEY,
            setting_value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Создаем аккаунт администратора по умолчанию, если аккаунты отсутствуют
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $userCount = $stmt->fetchColumn();
    
    if ($userCount == 0) {
        $defaultAdminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password, role, is_active)
            VALUES ('Administrator', 'admin@moreonfitness.com', ?, 'admin', 1)
        ");
        $stmt->execute([$defaultAdminPassword]);
        
        echo "Администратор по умолчанию создан: admin@moreonfitness.com / admin123\n";
    }

    // Создаем базовые настройки системы
    $defaultSettings = [
        'site_name' => 'Moreon Fitness',
        'site_description' => 'Фитнес-центр премиум класса',
        'contact_email' => 'info@moreonfitness.com',
        'contact_phone' => '+7 (800) 123-45-67',
        'address' => 'г. Москва, ул. Примерная, д. 123',
        'working_hours' => 'Пн-Пт: 7:00-23:00, Сб-Вс: 9:00-22:00',
        'enable_online_booking' => '1',
        'booking_advance_days' => '7',
        'cancellation_hours' => '24',
        'maintenance_mode' => '0'
    ];
    
    foreach ($defaultSettings as $key => $value) {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO settings (setting_key, setting_value)
            VALUES (?, ?)
        ");
        $stmt->execute([$key, $value]);
    }

    echo "База данных успешно инициализирована.\n";
} catch (PDOException $e) {
    die("Ошибка инициализации базы данных: " . $e->getMessage());
} 