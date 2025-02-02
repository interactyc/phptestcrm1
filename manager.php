<?php
require_once 'includes/auth.php';
require_once 'includes/db_config.php';
require_once 'includes/header.php';

$stmt = $pdo->query("SELECT * FROM users WHERE role = 'employee'");
$employees = $stmt->fetchAll();
?>

<div id="product-section">
    <input type="text" id="product-search" placeholder="Начните вводить название изделия...">
    <input type="hidden" id="product-id" name="product_id">
  <input type="number" id="quantity" placeholder="Количество">
  <select id="employee">
    <?php foreach ($employees as $e): ?>
      <option value="<?= $e['id'] ?>"><?= $e['name'] ?></option>
    <?php endforeach; ?>
  </select>
  <input type="date" id="deadline">
<!-- Измените поле времени -->
<input type="number" id="estimated-time" placeholder="Планируемое время (часы)" step="0.5">
  <button id="create-task-btn" type="button">Создать задачу</button>
 <a href="all_tasks.php" class="btn">Все задачи</a>
  </div>
</div>





<!-- Добавьте в начало manager.php перед вашими скриптами -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
<script src="assets/js/manager.js"></script>

<?php require_once 'includes/footer.php'; ?>

<!-- Перемещенная кнопка выхода внизу страницы -->
<div class="logout-footer">
  <form action="logout.php" method="POST">
    <button type="submit">Выйти</button>
  </form>
</div>
