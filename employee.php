<?php
require_once 'includes/auth.php';
require_once 'includes/db_config.php';
require_once 'includes/header.php';

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("
  SELECT tasks.*, products.name as product_name 
  FROM tasks 
  JOIN products ON tasks.product_id = products.id 
  WHERE assigned_to = ?
");
$stmt->execute([$userId]);
$tasks = $stmt->fetchAll();
?>

<h2>Ваши задачи</h2>
<table border="1">
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
          <button onclick="updateTaskStatus(<?= $task['id'] ?>, 'in_progress')">В работу</button>
          <button onclick="updateTaskStatus(<?= $task['id'] ?>, 'completed')">Завершить</button>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function updateTaskStatus(taskId, status) {
  $.post("update_task_status.php", { task_id: taskId, status: status }, function(response) {
    alert("Статус задачи обновлен!");
    location.reload();
  }).fail(function(error) {
    alert("Ошибка: " + error.responseText);
  });
}
</script>

<div class="logout-footer">
  <form action="logout.php" method="POST">
    <button type="submit">Выйти</button>
  </form>
</div>

<?php require_once 'includes/footer.php'; ?>