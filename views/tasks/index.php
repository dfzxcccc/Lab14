<?php
$baseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($baseUrl === '/' || $baseUrl === '.') {
    $baseUrl = '';
}
?>
<div class="tasks-header">
    <h2>Список моїх завдань</h2>
    <a href="<?= $baseUrl ?>/tasks/create" class="btn btn-primary">+ Нове завдання</a>
</div>

<?php if (empty($tasks)): ?>
    <div class="empty-state">
        <p>У вас ще немає створених завдань.</p>
        <a href="<?= $baseUrl ?>/tasks/create">Створіть своє перше завдання прямо зараз!</a>
    </div>
<?php else: ?>
    <div class="tasks-list">
        <?php foreach ($tasks as $task): ?>
            <div class="task-card <?= $task->isCompleted() ? 'is-done' : '' ?>">
                <div class="task-info">
                    <h3 class="task-title"><?= htmlspecialchars($task->getTitle()) ?></h3>
                    <div class="task-meta">
                        <?php if ($task->getDeadline()): ?>
                            <span class="deadline">📅 Дедлайн: <?= htmlspecialchars($task->getDeadline()) ?></span>
                        <?php endif; ?>
                        <span class="badge <?= $task->isCompleted() ? 'badge-success' : 'badge-pending' ?>">
                            <?= htmlspecialchars($task->getStatusLabel()) ?>
                        </span>
                    </div>
                </div>

                <div class="task-actions">
                    <?php if (!$task->isCompleted()): ?>
                        <form method="POST" action="<?= $baseUrl ?>/tasks/complete" style="display:inline">
                            <input type="hidden" name="id" value="<?= $task->getId() ?>">
                            <button type="submit" class="btn btn-icon btn-toggle" title="Змінити статус">
                                ✓ Завершити
                            </button>
                        </form>
                    <?php endif; ?>

                    <form method="POST" action="<?= $baseUrl ?>/tasks/delete" style="display:inline">
                        <input type="hidden" name="id" value="<?= $task->getId() ?>">
                        <button type="submit" class="btn btn-icon btn-delete" onclick="return confirm('Видалити завдання?')" title="Видалити">
                            ✕
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="<?= $baseUrl ?>/?page=<?= $page - 1 ?>" class="page-link">« Попередня</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="page-link active"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= $baseUrl ?>/?page=<?= $i ?>" class="page-link"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="<?= $baseUrl ?>/?page=<?= $page + 1 ?>" class="page-link">Наступна »</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>