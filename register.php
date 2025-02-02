<?php
session_start();
require_once 'includes/db_config.php';
require_once 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $password = trim($_POST['password']);
  $role = $_POST['role'];

  if (empty($name) || empty($email) || empty($password)) {
    $error = "Все поля обязательны для заполнения!";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Некорректный email!";
  } else {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
      $error = "Пользователь с таким email уже существует!";
    } else {
      $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
      $stmt->execute([$name, $email, $hashedPassword, $role]);
      header('Location: login.php');
      exit;
    }
  }
}
?>

<form method="POST">
  <input type="text" name="name" placeholder="Имя" required>
  <input type="email" name="email" placeholder="Email" required>
  <input type="password" name="password" placeholder="Пароль" required>
  <select name="role" required>
    <option value="manager">Руководитель</option>
    <option value="employee">Сотрудник</option>
  </select>
  <button type="submit">Зарегистрироваться</button>
  <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
</form>

<?php require_once 'includes/footer.php'; ?>