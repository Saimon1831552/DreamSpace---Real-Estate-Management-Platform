<?php
session_start();
include 'db.php';

// Ensure the user is an agent
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agents') {
    header("Location: login.php?error=" . urlencode("Unauthorized access."));
    exit;
}

$agent_id = $_SESSION['user_id'];
$agent_name = $_SESSION['username'];

// Fetch agent-specific stats
// 1. Count of properties
$prop_stmt = $conn->prepare("SELECT COUNT(*) FROM properties WHERE agent_id = ?");
$prop_stmt->bind_param("i", $agent_id);
$prop_stmt->execute();
$property_count = $prop_stmt->get_result()->fetch_row()[0];

// 2. Count of messages received
$msg_stmt = $conn->prepare("SELECT COUNT(*) FROM messages WHERE agent_id = ?");
$msg_stmt->bind_param("i", $agent_id);
$msg_stmt->execute();
$message_count = $msg_stmt->get_result()->fetch_row()[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Agent Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen font-sans">

  <nav class="bg-red-600 text-white shadow-md px-6 py-4 flex justify-between items-center">
    <div class="text-xl font-bold">DreamSpace Agent Portal</div>
    <div class="space-x-4">
      <a href="agent_dashboard.php" class="text-yellow-200 font-bold">Home</a>
      <a href="agent_properties.php" class="hover:text-yellow-200">My Properties</a>
      <a href="all_properties.php" class="hover:text-yellow-200">All Properties</a>
      <a href="agent_message.php" class="hover:text-yellow-200">Messages</a>
      <a href="edit_agent.php" class="hover:text-yellow-200">Edit Profile</a>
      <a href="logout.php" class="bg-red-700 hover:bg-red-800 px-3 py-1 rounded">Logout</a>
    </div>
  </nav>

  <div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-4">Welcome, <?= htmlspecialchars($agent_name) ?>!</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

      <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-500">Total Properties Listed</p>
          <p class="text-3xl font-bold text-red-600"><?= $property_count ?></p>
        </div>
        <div class="text-red-500 text-4xl">🏠</div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-500">Total Messages Received</p>
          <p class="text-3xl font-bold text-red-600"><?= $message_count ?></p>
        </div>
        <div class="text-red-500 text-4xl">✉️</div>
      </div>
      
      <div class="bg-white p-6 rounded-lg shadow-md md:col-span-1 lg:col-span-1">
        <h3 class="font-bold text-lg mb-4">Quick Actions</h3>
        <div class="flex flex-col space-y-3">
            <a href="add-property.php" class="bg-red-500 text-white text-center py-2 rounded-lg hover:bg-red-600 transition">
                + Add New Property
            </a>
            <a href="agent_properties.php" class="bg-gray-200 text-gray-800 text-center py-2 rounded-lg hover:bg-gray-300 transition">
                Manage My Properties
            </a>
        </div>
      </div>

    </div>

    <div class="mt-8 bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-bold text-gray-700 mb-4">Navigation</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <a href="agent_properties.php" class="p-4 bg-red-50 rounded-lg hover:bg-red-100 transition">
                <div class="text-3xl">📄</div>
                <p class="mt-2 font-semibold">My Properties</p>
            </a>
            <a href="all_properties.php" class="p-4 bg-red-50 rounded-lg hover:bg-red-100 transition">
                <div class="text-3xl">🏘️</div>
                <p class="mt-2 font-semibold">View All Properties</p>
            </a>
            <a href="agent_message.php" class="p-4 bg-red-50 rounded-lg hover:bg-red-100 transition">
                <div class="text-3xl">💬</div>
                <p class="mt-2 font-semibold">Check Messages</p>
            </a>
            <a href="edit_agent.php" class="p-4 bg-red-50 rounded-lg hover:bg-red-100 transition">
                <div class="text-3xl">👤</div>
                <p class="mt-2 font-semibold">Update Profile</p>
            </a>
        </div>
    </div>
  </div>

</body>
</html>