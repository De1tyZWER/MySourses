<?php
// Начало сессии
session_start();

// Удаление всех переменных сессии
$_SESSION = array();

// Уничтожение сессии
session_destroy();

// Перенаправление на главную страницу
header('Location: index.php');
exit;
?>