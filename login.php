<?php
// Подключение к базе данных
require_once 'config/db_config.php';

// Начало сессии
session_start();

// Если пользователь уже авторизован, перенаправляем на главную
if(isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    
    // Проверка на заполнение полей
    if(empty($login) || empty($password)) {
        $error = 'Пожалуйста, заполните все поля';
    } else {
        try {
            // Подготовленный запрос для безопасности
            $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
            $stmt->execute([$login]);
            $user = $stmt->fetch();
            
            // Проверка существования пользователя и правильности пароля
            if($user && password_verify($password, $user['password'])) {
                // Установка переменных сессии
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['login'] = $user['login'];
                $_SESSION['role'] = $user['role'];
                
                // Перенаправление на главную страницу
                header('Location: index.php');
                exit;
            } else {
                $error = 'Неправильный логин или пароль';
            }
        } catch(PDOException $e) {
            $error = 'Ошибка базы данных: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в аккаунт - LMS Платформа</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Градиентный фон -->
    <div class="background"></div>
    
    <div class="container">
        <!-- Шапка сайта -->
        <header class="header">
            <h1>LMS Платформа</h1>
            <nav class="nav-links">
                <a href="index.php">Главная</a>
                <a href="register.php">Регистрация</a>
            </nav>
        </header>

        <!-- Форма входа -->
        <div class="form-container">
            <h2>Вход в аккаунт</h2>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group">
                    <label for="login">Логин</label>
                    <input type="text" id="login" name="login" placeholder="Введите ваш логин" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" id="password" name="password" placeholder="Введите ваш пароль" required>
                </div>
                
                <button type="submit" class="btn">Войти</button>
            </form>
            
            <p style="color: rgba(255,255,255,0.8); text-align: center; margin-top: 20px;">
                Нет аккаунта? <a href="register.php" style="color: white; text-decoration: underline;">Зарегистрироваться</a>
            </p>
        </div>
    </div>
</body>
</html>