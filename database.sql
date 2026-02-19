-- Создание базы данных
CREATE DATABASE IF NOT EXISTS lms_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lms_platform;

-- Таблица пользователей
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица курсов
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    video_path VARCHAR(500),
    files_path JSON, -- Хранение массива путей к файлам
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Вставка администратора по умолчанию (логин: admin, пароль: admin123)
INSERT INTO users (login, password, role) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Вставка тестового курса
INSERT INTO courses (title, description, video_path) VALUES 
('Введение в программирование', 'Курс для новичков в программировании', 'uploads/test_video.mp4');