<?php
declare(strict_types=1);

class TaskController extends BaseController
{
    private TaskStorageInterface $repository;

    public function __construct()
    {
        $this->repository = new TaskRepository();
    }

    public function index(): void
    {
        $this->requireAuth();

        $userId = (int) $_SESSION['user_id'];
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 5;
        $offset = ($page - 1) * $perPage;

        $tasks = $this->repository->findAllByUser($userId, $perPage, $offset);
        $totalTasks = $this->repository->countByUser($userId);
        $totalPages = max(1, (int) ceil($totalTasks / $perPage));

        $this->render('tasks/index', [
            'pageTitle'  => 'Мої завдання — TaskFlow',
            'tasks'      => $tasks,
            'page'       => $page,
            'totalPages' => $totalPages,
            'totalTasks' => $totalTasks,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        $this->render('tasks/create', [
            'pageTitle' => 'Нове завдання — TaskFlow',
            'errors'    => [],
            'old'       => [],
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();

        $userId = (int) $_SESSION['user_id'];
        $title = trim($_POST['title'] ?? '');
        $deadlineRaw = trim($_POST['deadline'] ?? '');
        $deadline = $deadlineRaw !== '' ? $deadlineRaw : null;

        try {
            $this->repository->add($title, $deadline, $userId);
            $this->redirect('/');
        } catch (ValidationException $e) {
            $this->render('tasks/create', [
                'pageTitle' => 'Нове завдання — TaskFlow',
                'errors'    => $e->getErrors(),
                'old'       => ['title' => $title, 'deadline' => $deadlineRaw],
            ]);
        }
    }

    public function complete(): void
    {
        $this->requireAuth();

        $taskId = (int) ($_POST['id'] ?? 0);
        if ($taskId > 0) {
            $this->repository->complete($taskId);
        }

        $this->redirect('/');
    }

    public function delete(): void
    {
        $this->requireAuth();

        $taskId = (int) ($_POST['id'] ?? 0);
        if ($taskId > 0) {
            $this->repository->delete($taskId);
        }

        $this->redirect('/');
    }
}