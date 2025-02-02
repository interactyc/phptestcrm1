<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'includes/db_config.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'manager') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Доступ запрещен!']);
    exit;
}

$product_id = $_POST['product_id'] ?? null;
$quantity = $_POST['quantity'] ?? null;
$employee_id = $_POST['employee_id'] ?? null;
$deadline = $_POST['deadline'] ?? null;
$estimated_time = intval($_POST['estimated_time']) * 60; // Конвертация часов в минуты

if (!$product_id || !$quantity || !$employee_id || !$deadline || !$estimated_time) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Все поля обязательны!']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO tasks 
        (product_id, quantity, assigned_to, deadline, estimated_time) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$product_id, $quantity, $employee_id, $deadline, $estimated_time]);
    
    echo json_encode(['status' => 'success', 'message' => 'Задача создана!']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
}