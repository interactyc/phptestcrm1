<?php
require 'includes/auth.php';
require 'includes/db_config.php';

// Получаем ID текущего пользователя
$userId = $_SESSION['user_id'];

// Получаем GET-параметр для фильтрации по статусу и сортировки
$status_filter = $_GET['status'] ?? null;
$sort_by = $_GET['sort'] ?? 'deadline'; // По умолчанию сортировка по дедлайну
$order = $_GET['order'] ?? 'ASC'; // По умолчанию сортировка по возрастанию

// Загружаем задачи для текущего сотрудника с учетом фильтра и сортировки
$query = "
    SELECT tasks.*, products.name as product_name 
    FROM tasks 
    JOIN products ON tasks.product_id = products.id 
    WHERE assigned_to = ?
";
$params = [$userId];

if ($status_filter) {
    $query .= " AND status = ?";
    $params[] = $status_filter;
}

// Добавляем сортировку
$query .= " ORDER BY $sort_by $order";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$tasks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сотрудник</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <h1>Ваши задачи</h1>

    <!-- Фильтры -->
    <form method="GET" style="margin-bottom: 20px;">
        <label for="status">Фильтр по статусу:</label>
        <select name="status" id="status">
            <option value="">Все задачи</option>
            <option value="new" <?= $status_filter === 'new' ? 'selected' : '' ?>>Новые</option>
            <option value="in_progress" <?= $status_filter === 'in_progress' ? 'selected' : '' ?>>В работе</option>
            <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Завершенные</option>
        </select>

        <!-- Сортировка -->
        <label for="sort">Сортировать по:</label>
        <select name="sort" id="sort">
            <option value="deadline" <?= $sort_by === 'deadline' ? 'selected' : '' ?>>Дедлайн</option>
            <option value="created_at" <?= $sort_by === 'created_at' ? 'selected' : '' ?>>Дата создания</option>
        </select>

        <label for="order">Порядок:</label>
        <select name="order" id="order">
            <option value="ASC" <?= $order === 'ASC' ? 'selected' : '' ?>>По возрастанию</option>
            <option value="DESC" <?= $order === 'DESC' ? 'selected' : '' ?>>По убыванию</option>
        </select>

        <button type="submit">Применить</button>
    </form>

    <!-- Таблица задач -->
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Изделие</th>
                <th>Количество</th>
                <th>Дедлайн</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><?= htmlspecialchars($task['product_name']) ?></td>
                    <td><?= htmlspecialchars($task['quantity']) ?></td>
                    <td><?= htmlspecialchars($task['deadline']) ?></td>
                    <td><?= htmlspecialchars($task['status']) ?></td>
                    <td>
                        <?php if ($task['status'] === 'new'): ?>
                            <button onclick="updateTaskStatus(<?= $task['id'] ?>, 'in_progress')">В работу</button>
                        <?php elseif ($task['status'] === 'in_progress'): ?>
                            <button onclick="updateTaskStatus(<?= $task['id'] ?>, 'completed')">Завершить</button>
                        <?php elseif ($task['status'] === 'completed'): ?>
                            <button onclick="updateTaskStatus(<?= $task['id'] ?>, 'in_progress')">Вернуть в работу</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <script src="assets/js/employee.js"></script>
</body>
</html>