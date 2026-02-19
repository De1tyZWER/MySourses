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
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Проверка на заполнение полей
    if(empty($login) || empty($password) || empty($confirm_password)) {
        $error = 'Пожалуйста, заполните все поля';
    } elseif(strlen($login) < 3) {
        $error = 'Логин должен содержать не менее 3 символов';
    } elseif(strlen($password) < 6) {
        $error = 'Пароль должен содержать не менее 6 символов';
    } elseif($password !== $confirm_password) {
        $error = 'Пароли не совпадают';
    } else {
        try {
            // Проверка уникальности логина
            $stmt = $pdo->prepare("SELECT id FROM users WHERE login = ?");
            $stmt->execute([$login]);
            $existing_user = $stmt->fetch();
            
            if($existing_user) {
                $error = 'Пользователь с таким логином уже существует';
            } else {
                // Хеширование пароля
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Подготовленный запрос для добавления нового пользователя
                $stmt = $pdo->prepare("INSERT INTO users (login, password, role) VALUES (?, ?, 'user')");
                $stmt->execute([$login, $hashed_password]);
                
                $success = 'Регистрация прошла успешно! Теперь вы можете войти.';
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
    <title>Регистрация - LMS Платформа</title>
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
                <a href="login.php">Вход</a>
            </nav>
        </header>

        <!-- Форма регистрации -->
        <div class="form-container">
            <h2>Регистрация</h2>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group">
                    <label for="login">Логин</label>
                    <input type="text" id="login" name="login" placeholder="Введите желаемый логин" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" id="password" name="password" placeholder="Введите пароль" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Подтверждение пароля</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Повторите пароль" required>
                </div>
                
                <button type="submit" class="btn">Зарегистрироваться</button>
            </form>
            
            <p style="color: rgba(255,255,255,0.8); text-align: center; margin-top: 20px;">
                Уже есть аккаунт? <a href="login.php" style="color: white; text-decoration: underline;">Войти</a>
            </p>
        </div>
    </div>
</body>
</html>