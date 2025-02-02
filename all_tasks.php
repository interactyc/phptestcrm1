<?php
require 'includes/auth.php';
require 'includes/db_config.php';

// Получаем GET-параметры для фильтрации
$employee_id = $_GET['employee_id'] ?? null;
$project_name = $_GET['project_name'] ?? null;
$sort_by = $_GET['sort'] ?? 'deadline'; // По умолчанию сортировка по дедлайну
$order = $_GET['order'] ?? 'ASC'; // По умолчанию сортировка по возрастанию

// Основной запрос на получение задач
$query = "
    SELECT tasks.*, products.name as product_name, users.name as employee_name 
    FROM tasks 
    JOIN products ON tasks.product_id = products.id
    JOIN users ON tasks.assigned_to = users.id
    WHERE 1=1
";
$params = [];

if ($employee_id) {
    $query .= " AND tasks.assigned_to = ?";
    $params[] = $employee_id;
}
if ($project_name) {
    $query .= " AND products.project_name = ?";
    $params[] = $project_name;
}

// Добавляем сортировку
$query .= " ORDER BY $sort_by $order";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

// Статистика по исполнителям с фильтрами
$employee_stats_query = "
    SELECT 
        users.id, 
        users.name, 
        SUM(tasks.estimated_time) as total_hours,
        COUNT(tasks.id) as total_tasks
    FROM tasks
    JOIN users ON tasks.assigned_to = users.id
    JOIN products ON tasks.product_id = products.id
    WHERE 1=1
";
$employee_stats_params = [];
if ($employee_id) {
    $employee_stats_query .= " AND tasks.assigned_to = ?";
    $employee_stats_params[] = $employee_id;
}
if ($project_name) {
    $employee_stats_query .= " AND products.project_name = ?";
    $employee_stats_params[] = $project_name;
}
$employee_stats_query .= " GROUP BY users.id";

$employee_stats_stmt = $pdo->prepare($employee_stats_query);
$employee_stats_stmt->execute($employee_stats_params);
$employee_stats = $employee_stats_stmt->fetchAll();

// Общая загрузка всех исполнителей
$total_employee_stats_query = "
    SELECT 
        SUM(tasks.estimated_time) as total_hours,
        COUNT(tasks.id) as total_tasks
    FROM tasks
    JOIN users ON tasks.assigned_to = users.id
    JOIN products ON tasks.product_id = products.id
    WHERE 1=1
";
$total_employee_stats_params = [];
if ($employee_id) {
    $total_employee_stats_query .= " AND tasks.assigned_to = ?";
    $total_employee_stats_params[] = $employee_id;
}
if ($project_name) {
    $total_employee_stats_query .= " AND products.project_name = ?";
    $total_employee_stats_params[] = $project_name;
}

$total_employee_stats_stmt = $pdo->prepare($total_employee_stats_query);
$total_employee_stats_stmt->execute($total_employee_stats_params);
$total_employee_stats = $total_employee_stats_stmt->fetch();

// Получаем список всех исполнителей и проектов для фильтров
$employees = $pdo->query("SELECT id, name FROM users WHERE role = 'employee'")->fetchAll();
$projects = $pdo->query("SELECT DISTINCT project_name FROM products")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Все задачи</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <h1>Все задачи</h1>

    <!-- Фильтры -->
    <form method="GET" style="margin-bottom: 20px;">
        <label for="employee_id">Фильтр по исполнителю:</label>
        <select name="employee_id" id="employee_id">
            <option value="">Все исполнители</option>
            <?php foreach ($employees as $e): ?>
                <option value="<?= $e['id'] ?>" <?= $employee_id == $e['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($e['name']) ?>
                </option>
            <?php endforeach; ?>
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
        <label for="project_name">Фильтр по проекту:</label>
        <select name="project_name" id="project_name">
            <option value="">Все проекты</option>
            <?php foreach ($projects as $p): ?>
                <option value="<?= $p['project_name'] ?>" <?= $project_name == $p['project_name'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['project_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Применить</button>
    </form>

    <!-- Кнопка экспорта в Excel -->
    <form action="export_to_excel.php" method="POST" style="margin-bottom: 20px;">
        <input type="hidden" name="employee_id" value="<?= $employee_id ?>">
        <input type="hidden" name="project_name" value="<?= $project_name ?>">
        <button type="submit" class="btn-export">Экспорт в Excel</button>
    </form>

    <!-- Статистика загрузки исполнителей -->
    <h2>Загрузка исполнителей</h2>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Исполнитель</th>
                <th>Всего часов</th>
                <th>Дни</th>
                <th>Количество задач</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($employee_stats as $stat): ?>
                <tr>
                    <td><?= htmlspecialchars($stat['name']) ?></td>
                    <td><?= round($stat['total_hours'] / 60, 1) ?> ч</td>
                    <td><?= round($stat['total_hours'] / 480, 1) ?> дн</td>
                    <td><?= $stat['total_tasks'] ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td><strong>Всего</strong></td>
                <td><strong><?= round($total_employee_stats['total_hours'] / 60, 1) ?> ч</strong></td>
                <td><strong><?= round($total_employee_stats['total_hours'] / 480, 1) ?> дн</strong></td>
                <td><strong><?= $total_employee_stats['total_tasks'] ?></strong></td>
            </tr>
        </tbody>
    </table>

    <!-- Таблица задач -->
    <h2>Список задач</h2>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Изделие</th>
                <th>Количество</th>
                <th>Исполнитель</th>
                <th>Дедлайн</th>
                <th>Статус</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><?= htmlspecialchars($task['product_name']) ?></td>
                    <td><?= htmlspecialchars($task['quantity']) ?></td>
                    <td><?= htmlspecialchars($task['employee_name']) ?></td>
                    <td><?= htmlspecialchars($task['deadline']) ?></td>
                    <td><?= htmlspecialchars($task['status']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>