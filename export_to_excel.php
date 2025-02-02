<?php
require 'vendor/autoload.php';
require 'includes/db_config.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Фильтры
$employee_id = $_POST['employee_id'] ?? null;
$project_name = $_POST['project_name'] ?? null;

// Запрос данных для экспорта
$employee_stats_query = "
    SELECT 
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

// Создание Excel-файла
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Заголовки таблицы
$sheet->setCellValue('A1', 'Исполнитель');
$sheet->setCellValue('B1', 'Всего часов');
$sheet->setCellValue('C1', 'Дни');
$sheet->setCellValue('D1', 'Количество задач');

// Заполнение данными
$row = 2;
foreach ($employee_stats as $stat) {
    $sheet->setCellValue('A' . $row, $stat['name']);
    $sheet->setCellValue('B' . $row, round($stat['total_hours'] / 60, 1));
    $sheet->setCellValue('C' . $row, round($stat['total_hours'] / 480, 1));
    $sheet->setCellValue('D' . $row, $stat['total_tasks']);
    $row++;
}

// Общая загрузка
$sheet->setCellValue('A' . $row, 'Всего');
$sheet->setCellValue('B' . $row, round($total_employee_stats['total_hours'] / 60, 1));
$sheet->setCellValue('C' . $row, round($total_employee_stats['total_hours'] / 480, 1));
$sheet->setCellValue('D' . $row, $total_employee_stats['total_tasks']);

// Настройка ширины столбцов
$sheet->getColumnDimension('A')->setWidth(20);
$sheet->getColumnDimension('B')->setWidth(15);
$sheet->getColumnDimension('C')->setWidth(15);
$sheet->getColumnDimension('D')->setWidth(15);

// Заголовок файла
$sheet->setTitle('Загрузка исполнителей');

// Сохранение файла
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="employee_stats.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;