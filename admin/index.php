<?php
// Подключение к базе данных
require_once '../config/db_config.php';

// Начало сессии
session_start();

// Проверка авторизации и роли администратора
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Обработка удаления курса
if(isset($_POST['delete_course']) && isset($_POST['course_id'])) {
    $course_id = (int)$_POST['course_id'];
    
    try {
        // Получение информации о курсе для удаления файлов
        $stmt = $pdo->prepare("SELECT video_path, files_path FROM courses WHERE id = ?");
        $stmt->execute([$course_id]);
        $course = $stmt->fetch();
        
        if($course) {
            // Удаление видео файла если он существует
            if($course['video_path'] && file_exists('../' . $course['video_path'])) {
                unlink('../' . $course['video_path']);
            }
            
            // Удаление прикрепленных файлов если они существуют
            $files = json_decode($course['files_path'], true) ?: [];
            foreach($files as $file) {
                if(file_exists('../' . $file)) {
                    unlink('../' . $file);
                }
            }
            
            // Удаление записи из базы данных
            $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
            $stmt->execute([$course_id]);
        }
    } catch(PDOException $e) {
        $error = "Ошибка при удалении курса: " . $e->getMessage();
    }
}

// Обработка удаления пользователя
if(isset($_POST['delete_user']) && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    
    // Запрещаем удаление самого себя
    if($user_id == $_SESSION['user_id']) {
        $error = "Нельзя удалить собственный аккаунт";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
        } catch(PDOException $e) {
            $error = "Ошибка при удалении пользователя: " . $e->getMessage();
        }
    }
}

// Обработка загрузки нового курса
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_course'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    
    if(empty($title)) {
        $error = "Название курса обязательно";
    } else {
        try {
            // Обработка видео файла
            $video_path = null;
            if(isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
                $allowed_video_types = ['video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/mkv'];
                $file_type = $_FILES['video']['type'];
                
                if(in_array($file_type, $allowed_video_types)) {
                    $upload_dir = '../uploads/';
                    $file_extension = pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION);
                    $filename = uniqid() . '.' . $file_extension;
                    $target_path = $upload_dir . $filename;
                    
                    if(move_uploaded_file($_FILES['video']['tmp_name'], $target_path)) {
                        $video_path = 'uploads/' . $filename;
                    } else {
                        $error = "Ошибка при загрузке видео";
                    }
                } else {
                    $error = "Недопустимый тип видео файла";
                }
            }
            
            // Обработка прикрепленных файлов
            $files_paths = [];
            if(isset($_FILES['files']) && count($_FILES['files']['name']) > 0) {
                $allowed_file_types = ['application/pdf', 'application/zip', 'application/x-rar-compressed', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                
                for($i = 0; $i < count($_FILES['files']['name']); $i++) {
                    if($_FILES['files']['error'][$i] == 0) {
                        $file_type = $_FILES['files']['type'][$i];
                        
                        if(in_array($file_type, $allowed_file_types)) {
                            $upload_dir = '../uploads/';
                            $file_extension = pathinfo($_FILES['files']['name'][$i], PATHINFO_EXTENSION);
                            $filename = uniqid() . '_' . $i . '.' . $file_extension;
                            $target_path = $upload_dir . $filename;
                            
                            if(move_uploaded_file($_FILES['files']['tmp_name'][$i], $target_path)) {
                                $files_paths[] = 'uploads/' . $filename;
                            }
                        }
                    }
                }
            }
            
            // Если нет ошибок, сохраняем курс в базу данных
            if(!isset($error)) {
                $files_json = json_encode($files_paths);
                
                $stmt = $pdo->prepare("INSERT INTO courses (title, description, video_path, files_path) VALUES (?, ?, ?, ?)");
                $stmt->execute([$title, $description, $video_path, $files_json]);
                
                $message = "Курс успешно добавлен";
            }
        } catch(PDOException $e) {
            $error = "Ошибка при добавлении курса: " . $e->getMessage();
        }
    }
}

// Получение списка курсов
try {
    $stmt = $pdo->query("SELECT * FROM courses ORDER BY created_at DESC");
    $courses = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Ошибка получения курсов: " . $e->getMessage());
}

// Получение списка пользователей
try {
    $stmt = $pdo->query("SELECT id, login, role, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Ошибка получения пользователей: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора - LMS Платформа</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Градиентный фон -->
    <div class="background"></div>
    
    <div class="container">
        <!-- Шапка сайта -->
        <header class="header">
            <h1>Панель администратора</h1>
            <nav class="nav-links">
                <a href="../index.php">Главная</a>
                <a href="../logout.php">Выход</a>
            </nav>
        </header>

        <!-- Основной контент -->
        <main>
            <div class="admin-dashboard">
                <h2>Управление платформой</h2>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if($message): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                
                <!-- Форма добавления курса -->
                <div class="admin-form">
                    <h3>Добавить новый курс</h3>
                    <form method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="title">Название курса *</label>
                            <input type="text" id="title" name="title" placeholder="Введите название курса" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Описание курса</label>
                            <textarea id="description" name="description" placeholder="Введите описание курса" rows="4" style="width:100%;padding:14px;border:none;border-radius:16px;background:rgba(255,255,255,0.2);backdrop-filter:blur(10px);color:white;font-size:1rem;"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="video">Видео курса (MP4)</label>
                            <input type="file" id="video" name="video" accept="video/mp4">
                        </div>
                        
                        <div class="form-group">
                            <label for="files">Дополнительные файлы (PDF, ZIP, DOCX)</label>
                            <input type="file" id="files" name="files[]" multiple accept=".pdf,.zip,.docx">
                        </div>
                        
                        <button type="submit" name="add_course" class="btn">Добавить курс</button>
                    </form>
                </div>
                
                <!-- Список курсов -->
                <div class="course-list">
                    <h3>Список курсов</h3>
                    <?php if(empty($courses)): ?>
                        <p style="color: rgba(255,255,255,0.8); padding: 15px; text-align: center;">Нет созданных курсов</p>
                    <?php else: ?>
                        <?php foreach($courses as $course): ?>
                            <div class="course-item">
                                <div class="course-info">
                                    <strong><?php echo htmlspecialchars($course['title']); ?></strong><br>
                                    <small>Создан: <?php echo date('d.m.Y H:i', strtotime($course['created_at'])); ?></small>
                                </div>
                                <form method="post" style="display:inline;" onsubmit="return confirm('Вы уверены, что хотите удалить этот курс?');">
                                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                    <button type="submit" name="delete_course" class="delete-btn">Удалить</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Список пользователей -->
                <div class="user-list">
                    <h3>Список пользователей</h3>
                    <?php if(empty($users)): ?>
                        <p style="color: rgba(255,255,255,0.8); padding: 15px; text-align: center;">Нет зарегистрированных пользователей</p>
                    <?php else: ?>
                        <?php foreach($users as $user): ?>
                            <div class="user-item">
                                <div class="user-info">
                                    <strong><?php echo htmlspecialchars($user['login']); ?></strong> 
                                    (<?php echo $user['role']; ?>)<br>
                                    <small>Зарегистрирован: <?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?></small>
                                </div>
                                <?php if($user['id'] != $_SESSION['user_id']): ?>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Вы уверены, что хотите удалить этого пользователя?');">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="delete_user" class="delete-btn">Удалить</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>