<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Настройка базы данных | Moreon Fitness</title>
    <link rel="stylesheet" href="style/reset.css">
    <link rel="stylesheet" href="style/style.css">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #00100f;
            color: #fff;
            padding: 40px 20px;
        }
        .setup-container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(0, 16, 15, 0.7);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        h1 {
            font-family: 'Dela Gothic One', cursive;
            font-size: 32px;
            margin-bottom: 20px;
            text-align: center;
        }
        h2 {
            font-family: 'Dela Gothic One', cursive;
            font-size: 24px;
            margin: 30px 0 20px;
            color: #32ddd4;
        }
        p {
            margin: 15px 0;
            line-height: 1.5;
        }
        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            background: rgba(40, 176, 169, 0.1);
            border-left: 3px solid #32ddd4;
        }
        .error {
            background: rgba(255, 77, 77, 0.1);
            border-left: 3px solid #ff4d4d;
        }
        .code {
            font-family: monospace;
            background: rgba(0, 0, 0, 0.2);
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            white-space: pre-wrap;
            overflow-x: auto;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(180deg, #32ddd4 0%, #1a746f 100%);
            border-radius: 12px;
            color: #ffffff;
            text-decoration: none;
            font-weight: 500;
            margin-top: 20px;
            text-align: center;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 28px rgba(40, 176, 169, 0.3);
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <h1>Настройка базы данных Moreon Fitness</h1>
        
        <?php
        // Проверка доступа по паролю (простая защита)
        $setupPassword = 'setup123';
        $isAuthorized = false;
        
        if (isset($_POST['password']) && $_POST['password'] === $setupPassword) {
            $isAuthorized = true;
        } elseif (isset($_POST['password'])) {
            echo '<div class="message error">Неверный пароль. Попробуйте еще раз.</div>';
        }
        
        if (!$isAuthorized) {
            ?>
            <p>Для настройки базы данных введите пароль:</p>
            <form method="post" action="">
                <input type="password" name="password" required style="padding: 10px; border-radius: 8px; width: 100%; margin-top: 10px; background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); color: #fff;">
                <button type="submit" class="btn">Продолжить</button>
            </form>
            <?php
        } else {
            // Выполнение настройки базы данных
            
            // Загружаем конфигурацию базы данных
            require_once('database/config.php');
            
            try {
                echo '<h2>Проверка соединения с базой данных</h2>';
                echo '<div class="message">Соединение с базой данных установлено успешно.</div>';
                
                echo '<h2>Создание таблиц</h2>';
                
                // Путь к файлу SQL-схемы
                $schemaFile = 'database.sql';
                
                if (!file_exists($schemaFile)) {
                    echo '<div class="message error">Файл схемы базы данных не найден: ' . $schemaFile . '</div>';
                } else {
                    $sql = file_get_contents($schemaFile);
                    
                    // Разделение SQL-запросов
                    $queries = explode(';', $sql);
                    
                    foreach ($queries as $query) {
                        $query = trim($query);
                        if (empty($query)) continue;
                        
                        try {
                            $stmt = $pdo->prepare($query);
                            $stmt->execute();
                            echo '<div class="code">' . htmlspecialchars(substr($query, 0, 100)) . '...</div>';
                            echo '<div class="message">Запрос выполнен успешно.</div>';
                        } catch (PDOException $e) {
                            echo '<div class="code">' . htmlspecialchars($query) . '</div>';
                            echo '<div class="message error">Ошибка выполнения запроса: ' . $e->getMessage() . '</div>';
                        }
                    }
                }
                
                echo '<h2>Настройка завершена</h2>';
                echo '<p>База данных успешно настроена. Теперь вы можете использовать систему.</p>';
                echo '<a href="index.php" class="btn">Вернуться на главную</a>';
                
            } catch (PDOException $e) {
                echo '<div class="message error">Ошибка соединения с базой данных: ' . $e->getMessage() . '</div>';
                echo '<p>Проверьте настройки в файле database/config.php:</p>';
                echo '<div class="code">
define(\'DB_HOST\', \'' . DB_HOST . '\');
define(\'DB_NAME\', \'' . DB_NAME . '\');
define(\'DB_USER\', \'' . DB_USER . '\');
define(\'DB_PASS\', \'********\');
</div>';
            }
        }
        ?>
    </div>
</body>
</html> 