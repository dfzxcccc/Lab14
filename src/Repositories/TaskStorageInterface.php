<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/Task.php';

interface TaskStorageInterface
{
    public function findAllByUser(int $userId, int $limit, int $offset): array;
    public function countByUser(int $userId): int;
    public function findById(int $id): ?Task;
    public function add(string $title, ?string $deadline = null, int $userId = 1): Task;
    public function complete(int $id): bool;
    public function delete(int $id): bool;
    public function countCompletedByUser(int $userId): int;
    public function countOverdueByUser(int $userId): int;
}