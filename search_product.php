<?php
require_once 'includes/db_config.php';

$term = $_GET['term'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ?");
$stmt->execute(["%$term%"]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($results);