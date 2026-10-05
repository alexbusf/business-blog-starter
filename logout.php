<?php
// Запускаем сессию, чтобы иметь к ней доступ
session_start();

// Полностью уничтожаем сессию и все данные авторизации
session_unset();
session_destroy();

// Говорим браузеру, что HTTP-авторизация больше не действительна
// Это заставляет браузер "забыть" логин и пароль
header('HTTP/1.0 401 Unauthorized');
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Выход</title>
    <!-- Подключаем Bootstrap для красоты (опционально) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height: 100vh;">
    <div class="card text-center p-4 shadow-sm">
        <h3 class="text-success">Вы успешно вышли</h3>
        <p>Браузер больше не хранит данные для входа в админку.</p>
        <a href="admin.php" class="btn btn-primary btn-sm">Вернуться к форме входа</a>
    </div>
</body>
</html>