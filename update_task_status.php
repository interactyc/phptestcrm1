<?php
// update_task_status.php

require 'includes/db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $taskId = $data['task_id'] ?? null;
    $status = $data['status'] ?? null;

    if ($taskId && in_array($status, ['new', 'in_progress', 'completed'])) {
        try {
            $stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ?");
            $stmt->execute([$status, $taskId]);

            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Неверные данные']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Метод не поддерживается']);
}