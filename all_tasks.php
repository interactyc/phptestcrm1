<?php
require_once 'includes/auth.php';
require_once 'includes/db_config.php';
require_once 'includes/header.php';
require 'vendor/autoload.php';

// Фильтры
$employee_id = $_GET['employee_id'] ?? null;
$project_name = $_GET['project_name'] ?? null;

// Запрос всех задач с фильтрами
$query = "
    SELECT 
        tasks.*, 
        products.name as product_name, 
        products.project_name as project_name,
        users.name as employee_name 
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

// Статистика по проектам с фильтрами
$project_stats_query = "
    SELECT 
        products.project_name,
        SUM(tasks.estimated_time) as total_hours,
        COUNT(tasks.id) as total_tasks
    FROM tasks
    JOIN products ON tasks.product_id = products.id
    WHERE 1=1
";

$project_stats_params = [];

if ($employee_id) {
    $project_stats_query .= " AND tasks.assigned_to = ?";
    $project_stats_params[] = $employee_id;
}

if ($project_name) {
    $project_stats_query .= " AND products.project_name = ?";
    $project_stats_params[] = $project_name;
}

$project_stats_query .= " GROUP BY products.project_name";
$project_stats_stmt = $pdo->prepare($project_stats_query);
$project_stats_stmt->execute($project_stats_params);
$project_stats = $project_stats_stmt->fetchAll();

// Получить список всех исполнителей и проектов для фильтров
$employees = $pdo->query("SELECT id, name FROM users WHERE role = 'employee'")->fetchAll();
$projects = $pdo->query("SELECT DISTINCT project_name FROM products")->fetchAll();
?>

<!-- Фильтры -->
<h2>Фильтры</h2>
<form method="GET" class="filters">
    <select name="employee_id">
        <option value="">Все исполнители</option>
        <?php foreach ($employees as $e): ?>
            <option value="<?= $e['id'] ?>" <?= $e['id'] == $employee_id ? 'selected' : '' ?>>
                <?= htmlspecialchars($e['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="project_name">
        <option value="">Все проекты</option>
        <?php foreach ($projects as $p): ?>
            <option value="<?= $p['project_name'] ?>" <?= $p['project_name'] == $project_name ? 'selected' : '' ?>>
                <?= htmlspecialchars($p['project_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Применить</button>
</form>

<!-- Статистика по исполнителям -->
<h3>Загрузка исполнителей</h3>
<table class="stats">
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
                <td><?= round($stat['total_hours'] / 480, 1) ?> дн</td> <!-- 480 минут = 8 часов -->
                <td><?= $stat['total_tasks'] ?></td>
            </tr>
        <?php endforeach; ?>
        <!-- Общая загрузка всех исполнителей -->
        <tr>
            <td><strong>Всего</strong></td>
            <td><strong><?= round($total_employee_stats['total_hours'] / 60, 1) ?> ч</strong></td>
            <td><strong><?= round($total_employee_stats['total_hours'] / 480, 1) ?> дн</strong></td>
            <td><strong><?= $total_employee_stats['total_tasks'] ?></strong></td>
        </tr>
    </tbody>
</table>

<!-- Статистика по проектам -->
<h3>Загрузка проектов</h3>
<table class="stats">
    <thead>
        <tr>
            <th>Проект</th>
            <th>Всего часов</th>
            <th>Дни</th>
            <th>Количество задач</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($project_stats as $stat): ?>
            <tr>
                <td><?= htmlspecialchars($stat['project_name']) ?></td>
                <td><?= round($stat['total_hours'] / 60, 1) ?> ч</td>
                <td><?= round($stat['total_hours'] / 480, 1) ?> дн</td> <!-- 480 минут = 8 часов -->
                <td><?= $stat['total_tasks'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Стили -->
<style>
    .filters {
        margin: 20px 0;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 5px;
    }

    .filters select, .filters button {
        padding: 8px;
        margin-right: 10px;
    }

    .stats {
        width: 100%;
        margin: 20px 0;
        border-collapse: collapse;
    }

    .stats th, .stats td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: left;
    }

    .stats th {
        background: #f4f4f4;
    }
</style>

<!-- Кнопка экспорта -->
<form method="POST" action="export_to_excel.php" style="margin: 20px 0;">
    <input type="hidden" name="employee_id" value="<?= $employee_id ?>">
    <input type="hidden" name="project_name" value="<?= $project_name ?>">
    <button type="submit" class="btn-export">Экспорт в Excel</button>
</form>

<?php require_once 'includes/footer.php'; ?>