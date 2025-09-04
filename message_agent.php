<?php
require_once 'db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please <a href='login.php'>login</a> first.");
}

$errors = [];
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION['user_id'];
    $agent_id = $_POST['agent_id'] ?? null;
    $property_id = $_POST['property_id'] ?? null;
    $message = trim($_POST['message']);

    // Basic validation
    if (empty($agent_id) || empty($property_id) || empty($message)) {
        $errors[] = "All fields are required.";
    } else {
        // Insert message securely
        $stmt = $conn->prepare("INSERT INTO messages (user_id, agent_id, property_id, message) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("iiis", $user_id, $agent_id, $property_id, $message);
            if ($stmt->execute()) {
                $success = "Message sent successfully!";
            } else {
                $errors[] = "Failed to send message.";
            }
            $stmt->close();
        } else {
            $errors[] = "Database error.";
        }
    }
}

// Optional: Get agent/property data to show context
$property_title = "";
if (isset($_GET['property_id'])) {
    $pid = (int)$_GET['property_id'];
    $stmt = $conn->prepare("SELECT p.title, a.id as agent_id, a.name as agent_name 
                            FROM properties p 
                            JOIN agents a ON p.agent_id = a.id 
                            WHERE p.id = ?");
    $stmt->bind_param("i", $pid);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $property_title = htmlspecialchars($row['title']);
        $agent_id = $row['agent_id'];
        $agent_name = htmlspecialchars($row['agent_name']);
        $property_id = $pid;
    } else {
        $errors[] = "Property not found.";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Message Agent</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen font-sans">

<!-- Navbar -->
<nav class="bg-red-600 text-white shadow-md px-6 py-4 flex justify-between items-center">
  <div class="text-xl font-bold">User Dashboard</div>
  <div class="space-x-4">
    <a href="user_dashboard.php" class="hover:text-yellow-200">Home</a>
    <a href="logout.php" class="hover:text-yellow-200">Logout</a>
  </div>
</nav>

<div class="max-w-xl mx-auto bg-white shadow p-6 rounded">
    <h2 class="text-2xl font-semibold mb-4">Message Agent</h2>

    <?php if (!empty($success)): ?>
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
            <ul class="list-disc pl-5">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($property_title) && isset($agent_id)): ?>
        <form method="POST" action="">
            <input type="hidden" name="agent_id" value="<?= (int)$agent_id ?>">
            <input type="hidden" name="property_id" value="<?= (int)$property_id ?>">

            <div class="mb-4">
                <label class="block font-medium">Property</label>
                <input type="text" class="w-full border rounded px-3 py-2 bg-gray-100" value="<?= $property_title ?>" disabled>
            </div>

            <div class="mb-4">
                <label class="block font-medium">Agent</label>
                <input type="text" class="w-full border rounded px-3 py-2 bg-gray-100" value="<?= $agent_name ?>" disabled>
            </div>

            <div class="mb-4">
                <label class="block font-medium">Your Message</label>
                <textarea name="message" required class="w-full border rounded px-3 py-2" rows="5"></textarea>
            </div>

            <div class="flex justify-center">
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Send Message</button>
            
            </div>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
