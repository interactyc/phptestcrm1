<?php
session_start();
require_once 'includes/db_config.php';
require_once 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'];
  $password = $_POST['password'];
  
  $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
  $stmt->execute([$email]);
  $user = $stmt->fetch();
  
  if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    header('Location: index.php');
    exit;
  } else {
    $error = "Неверные учетные данные!";
  }
}
?>

<form method="POST">
  <input type="email" name="email" placeholder="Email" required>
  <input type="password" name="password" placeholder="Пароль" required>
  <button type="submit">Войти</button>
  <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
</form>

<?php require_once 'includes/footer.php'; ?>