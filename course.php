<?php
// Подключение к базе данных
require_once 'config/db_config.php';

// Начало сессии
session_start();

// Проверка, что ID курса передан
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$course_id = (int)$_GET['id'];

try {
    // Получение информации о курсе
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch();
    
    if(!$course) {
        header('Location: index.php');
        exit;
    }
    
    // Декодирование JSON с файлами
    $files = json_decode($course['files_path'], true) ?: [];
} catch(PDOException $e) {
    die("Ошибка получения курса: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?> - LMS Платформа</title>
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
            <div class="course-detail">
                <h1><?php echo htmlspecialchars($course['title']); ?></h1>
                <p class="course-description"><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
                
                <?php if($course['video_path']): ?>
                    <div class="video-player">
                        <video controls>
                            <source src="<?php echo htmlspecialchars($course['video_path']); ?>" type="video/mp4">
                            Ваш браузер не поддерживает видео.
                        </video>
                    </div>
                <?php endif; ?>
                
                <?php if(!empty($files)): ?>
                    <div class="download-section">
                        <h3>Файлы для скачивания</h3>
                        <div class="download-files">
                            <?php foreach($files as $file): ?>
                                <a href="<?php echo htmlspecialchars($file); ?>" class="download-file" download>
                                    <?php echo basename($file); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>