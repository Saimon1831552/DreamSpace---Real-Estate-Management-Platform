<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $password = trim($_POST['password']);
  $confirm = trim($_POST['confirm_password']);
  $role = $_POST['role'];


  if ($password !== $confirm || !$role || !$username || !$email) {
    header("Location: registration.html?error=" . urlencode("Invalid input or password mismatch."));
    exit();
  }


  $hashedPassword = password_hash($password, PASSWORD_DEFAULT);


  if ($role === 'user') {
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
  } elseif ($role === 'agent') {
    $stmt = $conn->prepare("INSERT INTO agents (name, email, password) VALUES (?, ?, ?)");
  } else {
    header("Location: registration.html?error=" . urlencode("Invalid role."));
    exit();
  }

  $stmt->bind_param("sss", $username, $email, $hashedPassword);

  if ($stmt->execute()) {
    header("Location: login.php?success=1");
  } else {
    $error = $stmt->error;
    header("Location: registration.html?error=" . urlencode("Registration failed: $error"));
  }

  $stmt->close();
  $conn->close();
}
?>
