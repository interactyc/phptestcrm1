<?php
session_start();
require 'includes/db_config.php';
require 'includes/auth.php';

$userId = $_SESSION['user_id'];
$filter = $_GET['filter'] ?? 'active'; // По умолчанию показываем активные задачи
$project_filter = $_GET['project'] ?? null;

// Формируем SQL-запрос в зависимости от фильтра
$query = "
    SELECT tasks.*, products.factory_number, products.project_name, products.name 
    FROM tasks 
    JOIN products ON tasks.product_id = products.id 
    WHERE tasks.assigned_to = :user_id
";

$params = [':user_id' => $userId];

if ($filter === 'active') {
    $query .= " AND tasks.status IN ('new', 'in_progress')";
} elseif ($filter === 'completed') {
    $query .= " AND tasks.status = 'completed'";
}

if ($project_filter) {
    $query .= " AND products.project_name = :project_name";
    $params[':project_name'] = $project_filter;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

// Получаем список всех проектов для фильтра
$projects = $pdo->query("SELECT DISTINCT project_name FROM products")->fetchAll(PDO::FETCH_COLUMN);
?>

<!-- employee.php -->

<h2>Фильтр</h2>

<!-- Фильтры -->
<div style="margin-bottom: 20px;">
    <form method="GET" style="display: inline-block; margin-right: 20px;">
        <label for="filter">Показать:</label>
        <select name="filter" id="filter" onchange="this.form.submit()">
            <option value="active" <?= $filter === 'active' ? 'selected' : '' ?>>Активные задачи</option>
            <option value="completed" <?= $filter === 'completed' ? 'selected' : '' ?>>Завершенные задачи</option>
        </select>
    </form>

    <form method="GET" style="display: inline-block;">
        <label for="project">Проект:</label>
        <select name="project" id="project" onchange="this.form.submit()">
            <option value="">Все проекты</option>
            <?php foreach ($projects as $project): ?>
                <option value="<?= htmlspecialchars($project) ?>" <?= $project_filter === $project ? 'selected' : '' ?>>
                    <?= htmlspecialchars($project) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<!-- employee.php -->

<table border="1" cellpadding="10" style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th>ПЛАС.</th>
            <th>ПРОЕКТ</th>
            <th>НАЗВАНИЕ</th>
            <th>КОЛИЧЕСТВО</th>
            <th>ДЕДЛАЙН</th>
            <th>СТАТУС</th>
            <th>ДЕЙСТВИЯ</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tasks as $task): ?>
            <tr>
                <td><?= htmlspecialchars($task['factory_number']) ?></td>
                <td><?= htmlspecialchars($task['project_name']) ?></td>
                <td><?= htmlspecialchars($task['name']) ?></td>
                <td><?= htmlspecialchars($task['quantity']) ?></td>
                <td><?= htmlspecialchars($task['deadline']) ?></td>
                <td><?= htmlspecialchars($task['status']) ?></td>
                <td>
                    <?php if ($task['status'] !== 'completed'): ?>
                        <button onclick="updateTaskStatus(<?= $task['id'] ?>, 'in_progress')">В работу</button>
                        <button onclick="updateTaskStatus(<?= $task['id'] ?>, 'completed')">Завершить</button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
		
	<!-- employee.php -->
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
    <!-- Таблица задач -->
    <table>
        <!-- Строки задач -->
    </table>

    <!-- Подключение JavaScript -->
    <script src="assets/js/employee.js"></script>
</body>
</html>
	
    </tbody>
</table>