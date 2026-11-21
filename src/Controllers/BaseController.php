<?php
declare(strict_types=1);

abstract class BaseController
{
    protected function render(string $view, array $data = []): void
    {
        extract($data);

        $viewFile = __DIR__ . '/../../views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            throw new RuntimeException("Файл представлення не знайдено: {$viewFile}");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $layoutFile = __DIR__ . '/../../views/layouts/main.php';

        if (!file_exists($layoutFile)) {
            throw new RuntimeException("Файл макета не знайдено: {$layoutFile}");
        }

        require $layoutFile;
    }

    protected function redirect(string $url): void
    {
        if (!preg_match('#^https?://#i', $url)) {
            $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
            $scriptDir = ($scriptDir === '/' || $scriptDir === '.' || $scriptDir === '\\') ? '' : rtrim($scriptDir, '/');
            $url = $scriptDir . '/' . ltrim($url, '/');
        }

        header("Location: {$url}");
        exit;
    }

    protected function requireAuth(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
    }
}