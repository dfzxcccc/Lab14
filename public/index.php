<?php
declare(strict_types=1);

session_start();

spl_autoload_register(function (string $className): void {
    $dirs = [
        __DIR__ . '/../src/',
        __DIR__ . '/../src/Controllers/',
        __DIR__ . '/../src/Models/',
        __DIR__ . '/../src/Repositories/',
        __DIR__ . '/../src/Exceptions/',
    ];

    foreach ($dirs as $dir) {
        $file = $dir . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

$router = new Router();

$router->get('/', [TaskController::class, 'index']);
$router->get('/tasks/create', [TaskController::class, 'create']);
$router->post('/tasks/create', [TaskController::class, 'store']);
$router->post('/tasks/complete', [TaskController::class, 'complete']);
$router->post('/tasks/delete', [TaskController::class, 'delete']);

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH) ?? '/';

$basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
if ($basePath !== '/' && $basePath !== '' && str_starts_with($path, $basePath)) {
    $path = substr($path, strlen($basePath));
}
$path = '/' . ltrim($path, '/');

$router->dispatch($method, $path);