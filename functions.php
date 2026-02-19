<?php
/**
 * Вспомогательные функции для LMS платформы
 */

/**
 * Проверка авторизации пользователя
 * @param string $required_role Требуемая роль (admin или user), если null - проверяет только наличие сессии
 * @return bool
 */
function checkAuth($required_role = null) {
    session_start();
    
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    if ($required_role && $_SESSION['role'] !== $required_role) {
        return false;
    }
    
    return true;
}

/**
 * Получение информации о текущем пользователе
 * @return array|null
 */
function getCurrentUser() {
    session_start();
    
    if (isset($_SESSION['user_id'])) {
        return [
            'id' => $_SESSION['user_id'],
            'login' => $_SESSION['login'],
            'role' => $_SESSION['role']
        ];
    }
    
    return null;
}

/**
 * Проверка типа файла
 * @param string $file_path Путь к файлу
 * @param array $allowed_types Разрешенные MIME типы
 * @return bool
 */
function isValidFileType($file_path, $allowed_types) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file_path);
    finfo_close($finfo);
    
    return in_array($mime_type, $allowed_types);
}

/**
 * Безопасное имя файла
 * @param string $filename Оригинальное имя файла
 * @return string
 */
function sanitizeFileName($filename) {
    // Удаляем потенциально опасные символы
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    
    // Получаем расширение файла
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    $basename = pathinfo($filename, PATHINFO_FILENAME);
    
    // Ограничиваем длину имени файла
    $basename = substr($basename, 0, 100);
    
    return $basename . '.' . $extension;
}

/**
 * Получение размера файла в человекочитаемом формате
 * @param int $size Размер файла в байтах
 * @return string
 */
function formatFileSize($size) {
    $units = array('B', 'KB', 'MB', 'GB');
    $unit = 0;
    
    while ($size >= 1024 && $unit < count($units) - 1) {
        $size /= 1024;
        $unit++;
    }
    
    return round($size, 2) . ' ' . $units[$unit];
}

/**
 * Проверка максимального размера файла
 * @param int $file_size Размер файла в байтах
 * @param int $max_size Максимальный допустимый размер в байтах
 * @return bool
 */
function isFileSizeValid($file_size, $max_size = 100 * 1024 * 1024) { // По умолчанию 100MB
    return $file_size <= $max_size;
}

/**
 * Генерация уникального имени файла
 * @param string $original_name Оригинальное имя файла
 * @return string
 */
function generateUniqueFileName($original_name) {
    $extension = pathinfo($original_name, PATHINFO_EXTENSION);
    $basename = pathinfo($original_name, PATHINFO_FILENAME);
    
    // Очищаем имя файла
    $basename = preg_replace('/[^a-zA-Z0-9_-]/', '', $basename);
    
    // Создаем уникальное имя
    $unique_part = uniqid();
    
    // Ограничиваем длину имени файла
    $basename = substr($basename, 0, 50);
    
    if ($extension) {
        return $basename . '_' . $unique_part . '.' . $extension;
    } else {
        return $basename . '_' . $unique_part;
    }
}
?>