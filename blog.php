<?php 
require 'config.php';
$pageTitle = 'Блог';
$pageDescription = 'Последние новости и статьи о бизнесе.';
$pageCanonical = 'https://vash-site.ru/blog.php';
include __DIR__ . '/inc/header.php'; 
?>

<div class="container py-5">
    <h1 class="mb-4 text-center">Статьи</h1>

    <?php
    // --- НАСТРОЙКИ ---
    $articles_per_page = 5; // Сколько статей показывать на одной странице

    // --- 1. Считаем общее количество статей для расчета страниц ---
    $total_stmt = $pdo->query("SELECT COUNT(id) FROM posts");
    $total_articles = $total_stmt->fetchColumn();
    
    // Определяем общее количество страниц
    $total_pages = ceil($total_articles / $articles_per_page);

    // --- 2. Определяем текущую страницу ---
    $current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    
    // Проверяем, чтобы страница не была меньше 1 или больше максимума
    if ($current_page < 1) $current_page = 1;
    if ($current_page > $total_pages) $current_page = $total_pages;

    // --- 3. Считаем смещение (LIMIT offset) ---
    $offset = ($current_page - 1) * $articles_per_page;

    // --- 4. Получаем только нужные статьи для текущей страницы ---
    $stmt = $pdo->prepare("SELECT id, title, slug, content, created_at FROM posts ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    
    // Привязываем параметры с указанием типа (это важно для LIMIT)
    $stmt->bindValue(':limit', $articles_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <!-- ВЫВОД СТАТЕЙ (цикл остается прежним) -->
    <div class="row justify-content-center">
        <?php if ($posts): ?>
            <?php foreach ($posts as $post): ?>
                <?php 
                $cleanText = strip_tags($post['content']);
                $shortText = strlen($cleanText) > 200 ? substr($cleanText, 0, 200) . '...' : $cleanText;
                ?>
                <div class="col-lg-8 col-md-10 col-sm-12 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($post['title']) ?></h5>
                            <p class="card-text"><?= nl2br(htmlspecialchars($shortText)) ?></p>
                            <a href="post.php?slug=<?= htmlspecialchars($post['slug']) ?>" class="btn btn-primary">Читать далее</a>
                        </div>
                        <div class="card-footer text-muted small">
                            Опубликовано: <?= date('d.m.Y', strtotime($post['created_at'])) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12"><div class="alert alert-info">Статей пока нет.</div></div>
        <?php endif; ?>
    </div>

    <!-- 5. ПАГИНАЦИЯ (Навигация внизу) -->
    <?php if ($total_pages > 1): ?>
    <nav aria-label="Навигация по страницам блога">
        <ul class="pagination justify-content-center">

            <!-- Кнопка "Назад" -->
            <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $current_page - 1 ?>">Назад</a>
            </li>

            <!-- Вывод номеров страниц (простой вариант: все подряд) -->
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($i === $current_page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <!-- Кнопка "Вперед" -->
            <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $current_page + 1 ?>">Вперед</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>

</div>

<?php include __DIR__ . '/inc/footer.php'; ?>