<?php
session_start();
require_once 'includes/auth.php';

if (isset($_SESSION['user_id'])) {
  header('Location: ' . ($_SESSION['role'] === 'manager' ? 'manager.php' : 'employee.php'));
  exit;
} else {
  header('Location: login.php');
  exit;
}