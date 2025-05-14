-- Создание базы данных
CREATE DATABASE IF NOT EXISTS fitness_center;

USE fitness_center;

-- Таблица пользователей
CREATE TABLE
    users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        email VARCHAR(255) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM ('admin', 'manager', 'user') NOT NULL DEFAULT 'user',
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        birthdate DATE,
        gender ENUM ('male', 'female') DEFAULT NULL,
        avatar_url VARCHAR(255),
        height INT,
        weight FLOAT,
        is_active BOOLEAN DEFAULT TRUE,
        last_login TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

-- Таблица разрешений
CREATE TABLE
    permissions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL UNIQUE,
        description TEXT
    );

-- Таблица связи ролей и разрешений
CREATE TABLE
    role_permissions (
        role ENUM ('admin', 'manager', 'user') NOT NULL,
        permission_id INT NOT NULL,
        PRIMARY KEY (role, permission_id),
        FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE
    );

-- Таблица абонементов
CREATE TABLE
    subscriptions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        duration_days INT NOT NULL,
        sessions_count INT,
        price DECIMAL(10, 2) NOT NULL,
        is_active BOOLEAN DEFAULT TRUE
    );

-- Таблица покупок абонементов
CREATE TABLE
    user_subscriptions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        subscription_id INT NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        remaining_sessions INT,
        status ENUM ('active', 'expired', 'cancelled') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
        FOREIGN KEY (subscription_id) REFERENCES subscriptions (id)
    );

-- Таблица тренеров
CREATE TABLE
    trainers (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        specialization VARCHAR(255) NOT NULL,
        experience_years INT,
        bio TEXT,
        photo_url VARCHAR(255),
        education TEXT,
        certificates TEXT,
        achievements TEXT,
        is_active BOOLEAN DEFAULT TRUE,
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
    );

-- Таблица услуг/тренировок
CREATE TABLE
    services (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        duration INT, -- в минутах
        price DECIMAL(10, 2) NOT NULL,
        max_participants INT,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

-- Таблица расписания тренеров
CREATE TABLE
    trainer_schedule (
        id INT PRIMARY KEY AUTO_INCREMENT,
        trainer_id INT NOT NULL,
        day_of_week TINYINT NOT NULL, -- 1 = Понедельник, 7 = Воскресенье
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        FOREIGN KEY (trainer_id) REFERENCES trainers (id) ON DELETE CASCADE
    );

-- Таблица записей на тренировки
CREATE TABLE
    training_sessions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        trainer_id INT,
        service_id INT,
        session_date DATE NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        status ENUM ('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL,
        FOREIGN KEY (trainer_id) REFERENCES trainers (id) ON DELETE SET NULL,
        FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE SET NULL
    );

-- Таблица отзывов
CREATE TABLE
    reviews (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        trainer_id INT,
        service_id INT,
        rating INT CHECK (
            rating >= 1
            AND rating <= 5
        ),
        comment TEXT NOT NULL,
        is_approved BOOLEAN DEFAULT FALSE,
        moderated_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL,
        FOREIGN KEY (trainer_id) REFERENCES trainers (id) ON DELETE SET NULL,
        FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE SET NULL,
        FOREIGN KEY (moderated_by) REFERENCES users (id) ON DELETE SET NULL
    );

-- Таблица уведомлений
CREATE TABLE
    notifications (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        type ENUM ('booking', 'system', 'promo') NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
    );

-- Создание индексов
CREATE INDEX idx_users_email ON users (email);

CREATE INDEX idx_users_role ON users (role);

CREATE INDEX idx_training_sessions_date ON training_sessions (session_date);

CREATE INDEX idx_reviews_trainer ON reviews (trainer_id);

CREATE INDEX idx_notifications_user ON notifications (user_id, is_read);

CREATE INDEX idx_trainer_schedule ON trainer_schedule (trainer_id, day_of_week);

-- Вставка базовых разрешений
INSERT INTO
    permissions (name, description)
VALUES
    (
        'admin_panel_access',
        'Полный доступ к админ-панели'
    ),
    (
        'manage_users',
        'Управление пользователями и назначение ролей'
    ),
    ('manage_trainers', 'Управление тренерами'),
    ('manage_services', 'Управление услугами'),
    ('manage_schedule', 'Управление расписанием'),
    (
        'manage_bookings',
        'Управление записями на тренировки'
    ),
    ('moderate_reviews', 'Модерация отзывов');

-- Назначение прав администратору
INSERT INTO
    role_permissions (role, permission_id)
SELECT
    'admin',
    id
FROM
    permissions;

-- Назначение ограниченных прав менеджеру
INSERT INTO
    role_permissions (role, permission_id)
SELECT
    'manager',
    id
FROM
    permissions
WHERE
    name IN (
        'manage_schedule',
        'manage_bookings',
        'moderate_reviews'
    );

-- Создание первого администратора
INSERT INTO
    users (
        email,
        password_hash,
        role,
        first_name,
        last_name,
        is_active
    )
VALUES
    (
        'admin@example.com',
        '$2y$12$TGXyExHP1UsSozalRQiZEuMgBxz2WJnrvz0JFYIf46vFfdt7b5aR.',
        'admin',
        'Administrator',
        'System',
        TRUE
    );