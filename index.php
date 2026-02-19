<?php
// Подключение к базе данных
require_once 'config/db_config.php';

// Начало сессии
session_start();

// Получение курсов из базы данных
try {
    $stmt = $pdo->query("SELECT * FROM courses ORDER BY created_at DESC");
    $courses = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Ошибка получения курсов: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Обучающая платформа LMS</title>
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
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="logout.php">Выход</a>
                    <?php if($_SESSION['role'] === 'admin'): ?>
                        <a href="admin/">Панель администратора</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="login.php">Вход</a>
                    <a href="register.php">Регистрация</a>
                <?php endif; ?>
            </nav>
        </header>

        <!-- Основной контент -->
        <main>
            <h2>Доступные курсы</h2>
            
            <?php if(empty($courses)): ?>
                <p style="color: white; text-align: center; margin-top: 30px;">Нет доступных курсов.</p>
            <?php else: ?>
                <div class="courses-grid">
                    <?php foreach($courses as $course): ?>
                        <div class="course-card">
                            <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                            <p><?php echo htmlspecialchars(substr($course['description'], 0, 150)) . (strlen($course['description']) > 150 ? '...' : ''); ?></p>
                            <a href="course.php?id=<?php echo $course['id']; ?>" class="course-link">Посмотреть курс</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>