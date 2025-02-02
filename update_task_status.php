<?php
require_once 'includes/db_config.php';
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $taskId = $_POST['task_id'];
  $status = $_POST['status'];

  $allowedStatuses = ['new', 'in_progress', 'completed'];
  if (!in_array($status, $allowedStatuses)) {
    http_response_code(400);
    echo "Недопустимый статус!";
    exit;
  }

  $stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ?");
  $stmt->execute([$status, $taskId]);

  echo "Статус задачи обновлен!";
} else {
  http_response_code(405);
  echo "Метод не поддерживается!";
}