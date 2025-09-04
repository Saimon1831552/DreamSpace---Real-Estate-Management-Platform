<?php
session_start();
include_once __DIR__ . '/../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agents') {
    header("Location: login.html");
    exit;
}

$agent_id = $_SESSION['user_id'];
$agentName = htmlspecialchars($_SESSION['username']);

// Stats
$totalUsers = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$totalProperties = $conn->query("SELECT COUNT(*) as total FROM properties")->fetch_assoc()['total'];

// Messages
$msgStmt = $conn->prepare("
    SELECT m.message, m.sent_at, u.name AS user_name, p.title AS property_title
    FROM messages m
    JOIN users u ON m.user_id = u.id
    JOIN properties p ON m.property_id = p.id
    WHERE m.agent_id = ?
    ORDER BY m.sent_at DESC
    LIMIT 3
");
$msgStmt->bind_param("i", $agent_id);
$msgStmt->execute();
$msgResult = $msgStmt->get_result();

// Properties
$propStmt = $conn->prepare("
    SELECT id, title, location, price, created_at
    FROM properties
    WHERE agent_id = ?
    ORDER BY created_at DESC
    LIMIT 3
");
$propStmt->bind_param("i", $agent_id);
$propStmt->execute();
$propResult = $propStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
    <body class="bg-gradient-to-br from-blue-100 to-gray-100 min-h-screen font-sans">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Agent Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
