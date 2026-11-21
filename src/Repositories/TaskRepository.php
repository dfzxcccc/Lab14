<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/Task.php';
require_once __DIR__ . '/TaskStorageInterface.php';
require_once __DIR__ . '/../Exceptions/Exceptions.php';
require_once __DIR__ . '/../Models/Database.php';

class TaskRepository implements TaskStorageInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findAllByUser(int $userId, int $limit, int $offset): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM tasks WHERE user_id = :user_id ORDER BY id DESC LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return array_map(fn($row) => Task::fromArray($row), $rows);
    }

    public function countByUser(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM tasks WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function findById(int $id): ?Task
    {
        $stmt = $this->db->prepare('SELECT * FROM tasks WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? Task::fromArray($row) : null;
    }

    public function add(string $title, ?string $deadline = null, int $userId = 1): Task
    {
        $errors = [];
        $cleanTitle = trim($title);

        if ($cleanTitle === '') {
            $errors[] = 'Назва завдання є обов\'язковою для заповнення.';
        } elseif (mb_strlen($cleanTitle) < 3) {
            $errors[] = 'Назва завдання повинна містити щонайменше 3 символи.';
        }

        if ($deadline !== null && $deadline !== '') {
            $timestamp = strtotime($deadline);
            if ($timestamp === false) {
                $errors[] = 'Вказано некоректну дату дедлайну.';
            }
        } else {
            $deadline = null;
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        $stmt = $this->db->prepare(
            'INSERT INTO tasks (user_id, title, is_completed, deadline, created_at, updated_at) VALUES (:user_id, :title, 0, :deadline, NOW(), NOW())'
        );
        $stmt->execute([
            'user_id' => $userId,
            'title' => $cleanTitle,
            'deadline' => $deadline
        ]);

        $id = (int) $this->db->lastInsertId();
        return $this->findById($id);
    }

    public function complete(int $id): bool
    {
        $task = $this->findById($id);
        if ($task === null) {
            throw new TaskNotFoundException($id);
        }

        $stmt = $this->db->prepare('UPDATE tasks SET is_completed = 1, updated_at = NOW() WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function delete(int $id): bool
    {
        $task = $this->findById($id);
        if ($task === null) {
            throw new TaskNotFoundException($id);
        }

        $stmt = $this->db->prepare('DELETE FROM tasks WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function countCompletedByUser(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM tasks WHERE user_id = :user_id AND is_completed = 1');
        $stmt->execute(['user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function countOverdueByUser(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM tasks WHERE user_id = :user_id AND is_completed = 0 AND deadline IS NOT NULL AND deadline < NOW()');
        $stmt->execute(['user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }
}