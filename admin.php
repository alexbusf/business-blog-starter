<?php 
// 1. Сессия ОБЯЗАТЕЛЬНО должна быть первой строкой (или после пустых строк/пробелов, но лучше первой)
session_start(); 

// 2. Подключение к БД (ваш config.php)
require 'config.php';

// 3. Переменные для сообщений (обнуляем их, чтобы не было Notice-ов)
$error = null;
$success = null;

// 4. Защита паролем (HTTP Basic Auth)
if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] != 'admin' || $_SERVER['PHP_AUTH_PW'] != '12345') { // Замените '12345' на ваш пароль
    header('WWW-Authenticate: Basic realm="Admin Zone"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Доступ запрещен';
    exit;
}

// 5. Генерация CSRF-токена (один раз за сессию)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 6. Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Проверка CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Ошибка безопасности: неверный токен.";
    } else {
        // Получаем данные из формы
        $title = trim($_POST['title']);
        $slug = trim($_POST['slug']);
        $content = trim($_POST['content']);

        // Проверка на пустоту
        if (!$title || !$slug || !$content) {
            $error = "Все поля обязательны для заполнения.";
        } else {
            // Проверка: занят ли slug?
            $stmt_check = $pdo->prepare("SELECT id FROM posts WHERE slug = ?");
            $stmt_check->execute([$slug]);
            
            if ($stmt_check->fetchColumn()) {
                $error = "Ошибка: Статья с адресом '{$slug}' уже существует.";
            } else {
                // Если всё ок, вставляем в БД
                try {
                    $stmt = $pdo->prepare("INSERT INTO posts (title, slug, content) VALUES (?, ?, ?)");
                    $stmt->execute([$title, $slug, $content]);
                    
                    // Очищаем токен (опционально, для параноидальной безопасности)
                    unset($_SESSION['csrf_token']);
                    
                    // Редирект (чтобы при обновлении страницы форма не отправлялась снова)
                    header("Location: admin.php?success=1");
                    exit;
                } catch (\Exception $e) {
                    $error = "Ошибка базы данных при вставке: " . $e->getMessage();
                }
            }
        }
    }
}

// Если был успешный редирект, ловим флаг
if (isset($_GET['success'])) {
    $success = "Статья успешно опубликована!";
}

// Подключаем визуальную часть
$pageTitle = "Админка";
include __DIR__ . '/inc/header.php'; 
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-start mb-4"> 
        <h1 class="mb-0">Добавить новую статью</h1> 
        <a href="logout.php" class="btn btn-outline-danger btn-sm"> Выйти (Logout) </a> 
    </div>

    <!-- Сообщение об успехе -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Успех!</strong> <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Сообщение об ошибке -->
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Ошибка:</strong> <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Форма -->
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        
        <div class="mb-3">
            <label for="title" class="form-label">Заголовок</label>
            <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label for="slug" class="form-label">Slug (адрес статьи, только латиница)</label>
            <input type="text" class="form-control" id="slug" name="slug" value="<?= htmlspecialchars($_POST['slug'] ?? '') ?>" required>
            <div class="form-text">Например: kak-zarabotat-dengi</div>
        </div>

        <div class="mb-3">
            <label for="content" class="form-label">Текст статьи</label>
            <textarea class="form-control" id="content" name="content" rows="10" required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Опубликовать</button>
    </form>
</div>

<?php include __DIR__ . '/inc/footer.php'; ?>