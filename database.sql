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
        height INT CHECK (
            height IS NULL
            OR height > 0
        ),
        weight FLOAT CHECK (
            weight IS NULL
            OR weight > 0
        ),
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
        duration_days INT NOT NULL CHECK (duration_days > 0),
        sessions_count INT CHECK (
            sessions_count IS NULL
            OR sessions_count > 0
        ),
        price DECIMAL(10, 2) NOT NULL CHECK (price >= 0),
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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

-- Таблица платежей
CREATE TABLE
    payments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        subscription_id INT,
        amount DECIMAL(10, 2) NOT NULL,
        payment_method ENUM ('card', 'cash', 'bank_transfer') NOT NULL,
        transaction_id VARCHAR(255),
        status ENUM ('pending', 'completed', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
        payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
        FOREIGN KEY (subscription_id) REFERENCES subscriptions (id) ON DELETE SET NULL
    );

-- Таблица тренеров
CREATE TABLE
    trainers (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        experience_years INT CHECK (experience_years >= 0),
        bio TEXT,
        photo_url VARCHAR(255),
        achievements TEXT,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
    );

-- Таблица специализаций
CREATE TABLE
    specializations (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        description TEXT
    );

-- Таблица связей тренеров и специализаций
CREATE TABLE
    trainer_specializations (
        trainer_id INT NOT NULL,
        specialization_id INT NOT NULL,
        PRIMARY KEY (trainer_id, specialization_id),
        FOREIGN KEY (trainer_id) REFERENCES trainers (id) ON DELETE CASCADE,
        FOREIGN KEY (specialization_id) REFERENCES specializations (id) ON DELETE CASCADE
    );

-- Таблица образования тренеров
CREATE TABLE
    trainer_education (
        id INT PRIMARY KEY AUTO_INCREMENT,
        trainer_id INT NOT NULL,
        institution VARCHAR(255) NOT NULL,
        degree VARCHAR(100),
        field_of_study VARCHAR(255),
        start_date DATE,
        end_date DATE,
        FOREIGN KEY (trainer_id) REFERENCES trainers (id) ON DELETE CASCADE
    );

-- Таблица сертификатов тренеров
CREATE TABLE
    trainer_certificates (
        id INT PRIMARY KEY AUTO_INCREMENT,
        trainer_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        issuing_organization VARCHAR(255) NOT NULL,
        issue_date DATE,
        expiry_date DATE,
        certificate_url VARCHAR(255),
        FOREIGN KEY (trainer_id) REFERENCES trainers (id) ON DELETE CASCADE
    );

-- Таблица услуг/тренировок
CREATE TABLE
    services (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        duration INT CHECK (duration > 0), -- в минутах
        price DECIMAL(10, 2) NOT NULL CHECK (price >= 0),
        max_participants INT CHECK (max_participants > 0),
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

-- Таблица настроек сайта
CREATE TABLE
    settings (
        setting_key VARCHAR(100) PRIMARY KEY,
        setting_value TEXT,
        description VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

-- Таблица локализации
CREATE TABLE
    translations (
        id INT PRIMARY KEY AUTO_INCREMENT,
        locale VARCHAR(10) NOT NULL,
        entity_type VARCHAR(50) NOT NULL,
        entity_id INT NOT NULL,
        field VARCHAR(50) NOT NULL,
        translation TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY (locale, entity_type, entity_id, field)
    );

-- Таблица аудита
CREATE TABLE
    audit_log (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        entity_type VARCHAR(50) NOT NULL,
        entity_id INT NOT NULL,
        action ENUM (
            'create',
            'update',
            'delete',
            'login',
            'logout',
            'other'
        ) NOT NULL,
        details TEXT,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
    );

-- Таблица API токенов для мобильных приложений и внешних интеграций
CREATE TABLE
    api_tokens (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        token VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NOT NULL,
        last_used_at TIMESTAMP NULL,
        is_active BOOLEAN DEFAULT TRUE,
        UNIQUE KEY (token),
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
    );

-- Создание индексов
CREATE INDEX idx_users_email ON users (email);

CREATE INDEX idx_users_role ON users (role);

CREATE INDEX idx_training_sessions_date ON training_sessions (session_date);

CREATE INDEX idx_reviews_trainer ON reviews (trainer_id);

CREATE INDEX idx_notifications_user ON notifications (user_id, is_read);

CREATE INDEX idx_trainer_schedule ON trainer_schedule (trainer_id, day_of_week);

-- Дополнительные индексы для повышения производительности
CREATE INDEX idx_trainers_is_active ON trainers (is_active);

CREATE INDEX idx_services_is_active ON services (is_active);

CREATE INDEX idx_user_subscriptions_status ON user_subscriptions (status);

CREATE INDEX idx_user_subscriptions_dates ON user_subscriptions (start_date, end_date);

CREATE INDEX idx_payments_status ON payments (status);

CREATE INDEX idx_payments_date ON payments (payment_date);

CREATE INDEX idx_training_sessions_status ON training_sessions (status);

CREATE INDEX idx_training_sessions_trainer_date ON training_sessions (trainer_id, session_date);

CREATE INDEX idx_training_sessions_user_date ON training_sessions (user_id, session_date);

CREATE INDEX idx_audit_log_entity ON audit_log (entity_type, entity_id);

CREATE INDEX idx_audit_log_action ON audit_log (action);

CREATE INDEX idx_translations_lookup ON translations (locale, entity_type, field);

CREATE INDEX idx_api_tokens_expires ON api_tokens (expires_at, is_active);

-- Вставка базовых настроек
INSERT INTO
    settings (setting_key, setting_value, description)
VALUES
    ('site_name', 'Moreon Fitness', 'Название сайта'),
    (
        'maintenance_mode',
        '0',
        'Режим обслуживания (1 - включен, 0 - выключен)'
    ),
    (
        'enable_online_booking',
        '1',
        'Возможность онлайн-бронирования (1 - включено, 0 - выключено)'
    ),
    (
        'contact_email',
        'info@moreonfitness.com',
        'Контактный email'
    ),
    (
        'contact_phone',
        '+7 (999) 123-45-67',
        'Контактный телефон'
    ),
    (
        'address',
        'Москва, ул. Примерная, д. 123',
        'Адрес фитнес-центра'
    ),
    (
        'working_hours',
        'Пн-Пт: 7:00-23:00, Сб-Вс: 9:00-22:00',
        'Часы работы'
    ),
    (
        'facebook_url',
        'https://facebook.com/moreonfitness',
        'Facebook URL'
    ),
    (
        'instagram_url',
        'https://instagram.com/moreonfitness',
        'Instagram URL'
    ),
    (
        'vk_url',
        'https://vk.com/moreonfitness',
        'VK URL'
    ),
    (
        'youtube_url',
        'https://youtube.com/moreonfitness',
        'YouTube URL'
    ),
    ('default_language', 'ru', 'Язык по умолчанию'),
    (
        'available_languages',
        'ru,en',
        'Доступные языки, через запятую'
    ),
    (
        'max_booking_days_ahead',
        '14',
        'Максимальное количество дней для предварительного бронирования'
    ),
    (
        'cancellation_policy_hours',
        '24',
        'За сколько часов можно отменить тренировку без штрафа'
    );

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

-- Добавление специализаций для тренеров
INSERT INTO
    specializations (name, description)
VALUES
    (
        'Силовой тренинг',
        'Работа с весами для набора мышечной массы и силы'
    ),
    (
        'Кардиотренировки',
        'Упражнения для укрепления сердечно-сосудистой системы'
    ),
    ('Йога', 'Комплекс упражнений для тела и ума'),
    (
        'Растяжка',
        'Упражнения на гибкость и подвижность суставов'
    ),
    (
        'Кроссфит',
        'Высокоинтенсивные функциональные тренировки'
    ),
    ('Бокс', 'Техники и тренировки по боксу'),
    (
        'Танцевальные программы',
        'Фитнес с элементами танца'
    ),
    (
        'Пилатес',
        'Система упражнений для развития мышц тела'
    );

-- Добавление тренеров (сначала пользователи, затем тренеры)
-- Тренер 1
INSERT INTO
    users (
        email,
        password_hash,
        role,
        first_name,
        last_name,
        phone,
        gender,
        is_active
    )
VALUES
    (
        'ivan.petrov@example.com',
        '$2y$12$TGXyExHP1UsSozalRQiZEuMgBxz2WJnrvz0JFYIf46vFfdt7b5aR.',
        'user',
        'Иван',
        'Петров',
        '+7 (999) 123-45-67',
        'male',
        TRUE
    );

INSERT INTO
    trainers (
        user_id,
        experience_years,
        bio,
        photo_url,
        achievements,
        is_active
    )
VALUES
    (
        LAST_INSERT_ID (),
        10,
        'Профессиональный тренер с многолетним опытом работы. Специализируется на силовом тренинге и коррекции фигуры.',
        'images/trainers/trainer-1.jpg',
        'Мастер спорта по пауэрлифтингу, призер чемпионата России 2018',
        TRUE
    );

INSERT INTO
    trainer_specializations (trainer_id, specialization_id)
VALUES
    (LAST_INSERT_ID (), 1);

INSERT INTO
    trainer_education (
        trainer_id,
        institution,
        degree,
        field_of_study,
        start_date,
        end_date
    )
VALUES
    (
        LAST_INSERT_ID (),
        'Российский Государственный Университет Физической Культуры',
        'Бакалавр',
        'Физическая культура',
        '2008-09-01',
        '2012-06-30'
    );

-- Тренер 2
INSERT INTO
    users (
        email,
        password_hash,
        role,
        first_name,
        last_name,
        phone,
        gender,
        is_active
    )
VALUES
    (
        'elena.smirnova@example.com',
        '$2y$12$TGXyExHP1UsSozalRQiZEuMgBxz2WJnrvz0JFYIf46vFfdt7b5aR.',
        'user',
        'Елена',
        'Смирнова',
        '+7 (999) 234-56-78',
        'female',
        TRUE
    );

INSERT INTO
    trainers (
        user_id,
        experience_years,
        bio,
        photo_url,
        achievements,
        is_active
    )
VALUES
    (
        LAST_INSERT_ID (),
        5,
        'Сертифицированный тренер по йоге и пилатесу. Помогает клиентам достичь гармонии тела и духа.',
        'images/trainers/trainer-2.jpg',
        'Сертифицированный инструктор по йоге международного класса',
        TRUE
    );

INSERT INTO
    trainer_specializations (trainer_id, specialization_id)
VALUES
    (LAST_INSERT_ID (), 3);

INSERT INTO
    trainer_specializations (trainer_id, specialization_id)
VALUES
    (LAST_INSERT_ID (), 8);

-- Тренер 3
INSERT INTO
    users (
        email,
        password_hash,
        role,
        first_name,
        last_name,
        phone,
        gender,
        is_active
    )
VALUES
    (
        'sergey.kozlov@example.com',
        '$2y$12$TGXyExHP1UsSozalRQiZEuMgBxz2WJnrvz0JFYIf46vFfdt7b5aR.',
        'user',
        'Сергей',
        'Козлов',
        '+7 (999) 345-67-89',
        'male',
        TRUE
    );

INSERT INTO
    trainers (
        user_id,
        experience_years,
        bio,
        photo_url,
        achievements,
        is_active
    )
VALUES
    (
        LAST_INSERT_ID (),
        8,
        'Тренер по кроссфиту и функциональным тренировкам. Специализируется на высокоинтенсивных тренировках.',
        'images/trainers/trainer-3.jpg',
        'Победитель региональных соревнований по кроссфиту 2019, 2020',
        TRUE
    );

INSERT INTO
    trainer_specializations (trainer_id, specialization_id)
VALUES
    (LAST_INSERT_ID (), 5);

INSERT INTO
    trainer_specializations (trainer_id, specialization_id)
VALUES
    (LAST_INSERT_ID (), 2);

-- Тренер 4
INSERT INTO
    users (
        email,
        password_hash,
        role,
        first_name,
        last_name,
        phone,
        gender,
        is_active
    )
VALUES
    (
        'anna.ivanova@example.com',
        '$2y$12$TGXyExHP1UsSozalRQiZEuMgBxz2WJnrvz0JFYIf46vFfdt7b5aR.',
        'user',
        'Анна',
        'Иванова',
        '+7 (999) 456-78-90',
        'female',
        TRUE
    );

INSERT INTO
    trainers (
        user_id,
        experience_years,
        bio,
        photo_url,
        achievements,
        is_active
    )
VALUES
    (
        LAST_INSERT_ID (),
        6,
        'Тренер по танцевальным направлениям и растяжке. Поможет развить гибкость и грацию.',
        'images/trainers/trainer-4.jpg',
        'Хореограф-постановщик, участница танцевальных шоу',
        TRUE
    );

INSERT INTO
    trainer_specializations (trainer_id, specialization_id)
VALUES
    (LAST_INSERT_ID (), 7);

INSERT INTO
    trainer_specializations (trainer_id, specialization_id)
VALUES
    (LAST_INSERT_ID (), 4);

-- Тренер 5
INSERT INTO
    users (
        email,
        password_hash,
        role,
        first_name,
        last_name,
        phone,
        gender,
        is_active
    )
VALUES
    (
        'dmitry.sokolov@example.com',
        '$2y$12$TGXyExHP1UsSozalRQiZEuMgBxz2WJnrvz0JFYIf46vFfdt7b5aR.',
        'user',
        'Дмитрий',
        'Соколов',
        '+7 (999) 567-89-01',
        'male',
        TRUE
    );

INSERT INTO
    trainers (
        user_id,
        experience_years,
        bio,
        photo_url,
        achievements,
        is_active
    )
VALUES
    (
        LAST_INSERT_ID (),
        12,
        'Опытный тренер по боксу и ММА. Научит правильной технике и дисциплине.',
        'images/trainers/trainer-5.jpg',
        'Мастер спорта по боксу, тренер национальной сборной 2015-2017',
        TRUE
    );

INSERT INTO
    trainer_specializations (trainer_id, specialization_id)
VALUES
    (LAST_INSERT_ID (), 6);

-- Тренер 6
INSERT INTO
    users (
        email,
        password_hash,
        role,
        first_name,
        last_name,
        phone,
        gender,
        is_active
    )
VALUES
    (
        'maria.kuznetsova@example.com',
        '$2y$12$TGXyExHP1UsSozalRQiZEuMgBxz2WJnrvz0JFYIf46vFfdt7b5aR.',
        'user',
        'Мария',
        'Кузнецова',
        '+7 (999) 678-90-12',
        'female',
        TRUE
    );

INSERT INTO
    trainers (
        user_id,
        experience_years,
        bio,
        photo_url,
        achievements,
        is_active
    )
VALUES
    (
        LAST_INSERT_ID (),
        4,
        'Специалист по кардиотренировкам и снижению веса. Поможет достичь желаемой формы.',
        'images/trainers/trainer-6.jpg',
        'Сертифицированный нутрициолог, специалист по снижению веса',
        TRUE
    );

INSERT INTO
    trainer_specializations (trainer_id, specialization_id)
VALUES
    (LAST_INSERT_ID (), 2);

INSERT INTO
    trainer_specializations (trainer_id, specialization_id)
VALUES
    (LAST_INSERT_ID (), 1);

-- Тренер 7
INSERT INTO
    users (
        email,
        password_hash,
        role,
        first_name,
        last_name,
        phone,
        gender,
        is_active
    )
VALUES
    (
        'alexey.morozov@example.com',
        '$2y$12$TGXyExHP1UsSozalRQiZEuMgBxz2WJnrvz0JFYIf46vFfdt7b5aR.',
        'user',
        'Алексей',
        'Морозов',
        '+7 (999) 789-01-23',
        'male',
        TRUE
    );

INSERT INTO
    trainers (
        user_id,
        experience_years,
        bio,
        photo_url,
        achievements,
        is_active
    )
VALUES
    (
        LAST_INSERT_ID (),
        9,
        'Тренер по функциональным тренировкам и реабилитации. Помогает восстановиться после травм.',
        'images/trainers/trainer-7.jpg',
        'Физиотерапевт, специалист по спортивной реабилитации',
        TRUE
    );

INSERT INTO
    trainer_specializations (trainer_id, specialization_id)
VALUES
    (LAST_INSERT_ID (), 1);

INSERT INTO
    trainer_specializations (trainer_id, specialization_id)
VALUES
    (LAST_INSERT_ID (), 4);

-- Тренер 8
INSERT INTO
    users (
        email,
        password_hash,
        role,
        first_name,
        last_name,
        phone,
        gender,
        is_active
    )
VALUES
    (
        'natalia.orlova@example.com',
        '$2y$12$TGXyExHP1UsSozalRQiZEuMgBxz2WJnrvz0JFYIf46vFfdt7b5aR.',
        'user',
        'Наталья',
        'Орлова',
        '+7 (999) 890-12-34',
        'female',
        TRUE
    );

INSERT INTO
    trainers (
        user_id,
        experience_years,
        bio,
        photo_url,
        achievements,
        is_active
    )
VALUES
    (
        LAST_INSERT_ID (),
        7,
        'Мастер групповых программ и пилатеса. Создает индивидуальный подход к каждому клиенту.',
        'images/trainers/trainer-8.jpg',
        'Победитель фитнес-конвенций, автор методики "Осознанный пилатес"',
        TRUE
    );

INSERT INTO
    trainer_specializations (trainer_id, specialization_id)
VALUES
    (LAST_INSERT_ID (), 8);

INSERT INTO
    trainer_specializations (trainer_id, specialization_id)
VALUES
    (LAST_INSERT_ID (), 7);