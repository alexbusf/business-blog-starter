<?php 
require 'config.php';

if (!isset($_GET['slug'])) {
    die('404 Статья не найдена');
}

$slug = $_GET['slug'];
$stmt = $pdo->prepare("SELECT * FROM posts WHERE slug = ?");
$stmt->execute([$slug]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    die('404 Статья не найдена');
}

// Более правильный вариант для SEO
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$pageCanonical = $protocol . $_SERVER['HTTP_HOST'] . '/post.php?slug=' . urlencode($post['slug']);

$pageTitle = $post['title'];
$pageDescription = $post['meta_description'] ?? 'Читайте нашу новую статью на тему бизнеса...';
include __DIR__ . '/inc/header.php'; 
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <article class="card shadow-lg">
                
                <div class="card-body">
                    <h1 class="card-title display-5"><?= htmlspecialchars($post['title']) ?></h1>
                    <p class="text-muted mb-4">
                        <small>
                            Опубликовано: <?= date('d F Y', strtotime($post['created_at'])) ?> 
                            <!-- Можно добавить автора, если добавите колонку в БД -->
                            <!-- автором <?= htmlspecialchars($post['author']) ?> -->
                        </small>
                    </p>
                    
                    <!-- Выводим контент. 
                         Важно: здесь мы НЕ используем htmlspecialchars, 
                         чтобы работали переносы строк. 
                         Но это безопасно, так как данные пришли из БД, 
                         а не от пользователя напрямую. 
                    -->
                    <div class="content">
                        <?= nl2br(htmlspecialchars($post['content'])) ?>
                    </div>
                </div>

                <div class="card-footer text-center border-top-0 bg-white">
                    <a href="blog.php" class="btn btn-outline-secondary btn-sm">← Назад к статьям</a>
                </div>
            </article>
        </div>
    </div>
</div>

<?php include __DIR__ . '/inc/footer.php'; ?>