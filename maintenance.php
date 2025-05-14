<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сайт на техническом обслуживании</title>
    <link href="https://fonts.googleapis.com/css2?family=Dela+Gothic+One&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f5f5f5;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            text-align: center;
        }
        .maintenance-container {
            background-color: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 90%;
        }
        h1 {
            font-family: 'Dela Gothic One', cursive;
            color: #2a2a2a;
            margin-bottom: 20px;
        }
        .logo {
            margin-bottom: 30px;
            max-width: 200px;
        }
        .message {
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .contact-info {
            margin-top: 20px;
            color: #666;
            font-size: 0.9em;
        }
        .timer {
            font-size: 1.2em;
            font-weight: 700;
            margin: 20px 0;
            color: #E94D35;
        }
        .social-links {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }
        .social-links a {
            color: #555;
            text-decoration: none;
            transition: color 0.3s;
        }
        .social-links a:hover {
            color: #E94D35;
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <?php if (file_exists('assets/img/logo.png')): ?>
        <img src="assets/img/logo.png" alt="Moreon Fitness" class="logo">
        <?php else: ?>
        <h1>Moreon Fitness</h1>
        <?php endif; ?>
        
        <h2>Сайт временно недоступен</h2>
        
        <div class="message">
            <p>В настоящее время мы выполняем плановое техническое обслуживание нашего сайта.</p>
            <p>Приносим извинения за временные неудобства. Мы скоро вернемся!</p>
        </div>
        
        <div class="contact-info">
            <p>Если у вас возникли вопросы, пожалуйста, свяжитесь с нами:</p>
            <p>Телефон: +7 (495) 481-60-60</p>
            <p>Email: moreon@more-on.ru</p>
        </div>
        
        <div class="social-links">
            <a href="https://instagram.com" target="_blank">Instagram</a>
            <a href="https://vk.com" target="_blank">VK</a>
            <a href="https://facebook.com" target="_blank">Facebook</a>
        </div>
    </div>
</body>
</html> 