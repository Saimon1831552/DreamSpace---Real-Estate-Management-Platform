<?php
require_once 'db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'users') {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$errors = [];
$success = '';

// Get property ID from URL and fetch property and agent details
if (!isset($_GET['property_id'])) {
    die("No property specified.");
}
$property_id = (int)$_GET['property_id'];

$stmt = $conn->prepare(
    "SELECT p.title, p.agent_id, a.name as agent_name 
     FROM properties p 
     JOIN agents a ON p.agent_id = a.id 
     WHERE p.id = ?"
);
$stmt->bind_param("i", $property_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
if (!$data) {
    die("Property not found.");
}
$property_title = $data['title'];
$agent_id = $data['agent_id'];
$agent_name = $data['agent_name'];
$stmt->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $message = trim($_POST['message']);

    if (empty($message)) {
        $errors[] = "Message cannot be empty.";
    } else {
        // **MODIFIED**: Set sender_role to 'user'
        $sender_role = 'user';
        $stmt = $conn->prepare("INSERT INTO messages (user_id, agent_id, property_id, sender_role, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiss", $user_id, $agent_id, $property_id, $sender_role, $message);
        
        if ($stmt->execute()) {
            $success = "Message sent successfully! You will be redirected shortly.";
            header("Refresh: 3; URL=user_messages.php?agent_id=$agent_id&property_id=$property_id");
        } else {
            $errors[] = "Failed to send message.";
        }
        $stmt->close();
    }
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
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

<div class="max-w-xl w-full mx-auto bg-white shadow-lg p-8 rounded-xl">
    <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Contact Agent</h2>

    <?php if (!empty($success)): ?>
        <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-4 text-center"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 text-red-800 p-4 rounded-lg mb-4">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($success)): ?>
    <form method="POST" action="">
        <div class="mb-4">
            <label class="block font-medium text-gray-700">Property</label>
            <input type="text" class="w-full mt-1 border rounded-lg px-3 py-2 bg-gray-100" value="<?= htmlspecialchars($property_title) ?>" disabled>
        </div>
        <div class="mb-4">
            <label class="block font-medium text-gray-700">Agent</label>
            <input type="text" class="w-full mt-1 border rounded-lg px-3 py-2 bg-gray-100" value="<?= htmlspecialchars($agent_name) ?>" disabled>
        </div>
        <div class="mb-6">
            <label class="block font-medium text-gray-700">Your Message</label>
            <textarea name="message" required class="w-full mt-1 border rounded-lg px-3 py-2 focus:ring-red-500 focus:border-red-500" rows="5" placeholder="e.g., I'm interested in this property..."></textarea>
        </div>
        <div class="flex items-center justify-between">
            <a href="user_properties.php" class="text-gray-600 hover:text-red-600">← Back to Properties</a>
            <button type="submit" class="bg-red-600 text-white font-bold px-6 py-2 rounded-lg hover:bg-red-700 transition">Send Message</button>
        </div>
    </form>
    <?php endif; ?>
</div>

</body>
</html>