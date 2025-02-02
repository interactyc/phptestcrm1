<?php
require_once 'includes/db_config.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Создание нового изделия
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Валидация данных
        if (empty($data['project_name']) || empty($data['factory_number']) || empty($data['name'])) {
            throw new Exception('Все поля обязательны для заполнения');
        }

        // Проверка уникальности заводского номера
        $stmt = $pdo->prepare("SELECT id FROM products WHERE factory_number = ?");
        $stmt->execute([$data['factory_number']]);
        if ($stmt->fetch()) {
            throw new Exception('Заводской номер уже существует');
        }

        // Создание записи
        $stmt = $pdo->prepare("INSERT INTO products (project_name, factory_number, name) VALUES (?, ?, ?)");
        $stmt->execute([$data['project_name'], $data['factory_number'], $data['name']]);
        
        echo json_encode([
            'id' => $pdo->lastInsertId(),
            'name' => $data['name']
        ]);
    } else {
        // Поиск существующих изделий
        $term = $_GET['term'] ?? '';
        $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? OR factory_number LIKE ?");
        $stmt->execute(["%$term%", "%$term%"]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($results);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}