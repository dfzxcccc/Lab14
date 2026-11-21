<?php
$baseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($baseUrl === '/' || $baseUrl === '.') {
    $baseUrl = '';
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Менеджер завдань') ?></title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/style.css">
</head>
<body>
    <header class="app-header">
        <div class="header-container">
            <a href="<?= $baseUrl ?>/" class="app-logo">TaskFlow MVC</a>
            <nav class="app-nav">
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <a href="<?= $baseUrl ?>/">Мої завдання</a>
                    <a href="<?= $baseUrl ?>/tasks/create" class="btn-nav">+ Створити завдання</a>
                    <span class="user-greeting">Привіт, <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'Користувач') ?></strong></span>
                    <a href="<?= $baseUrl ?>/logout" class="nav-logout">Вийти</a>
                <?php else: ?>
                    <a href="<?= $baseUrl ?>/login">Увійти</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="app-content">
        <?= $content ?>
    </main>

    <footer class="app-footer">
        <p>&copy; <?= date('Y') ?> «Менеджер завдань» на базі PHP MVC</p>
    </footer>
</body>
</html>