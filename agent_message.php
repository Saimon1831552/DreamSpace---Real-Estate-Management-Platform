<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agents') {
    header("Location: login.php");
    exit;
}
$agent_id = $_SESSION['user_id'];
$active_user_id = null;
$active_property_id = null;

// Handle agent's reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply'], $_POST['user_id'], $_POST['property_id'])) {
    $reply = trim($_POST['reply']);
    $user_id = (int)$_POST['user_id'];
    $property_id = (int)$_POST['property_id'];

    if (!empty($reply)) {
        // **THIS IS THE CRITICAL FIX**: Set sender_role to 'agent'
        $sender_role = 'agent';
        $stmt = $conn->prepare("INSERT INTO messages (user_id, agent_id, property_id, sender_role, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiss", $user_id, $agent_id, $property_id, $sender_role, $reply);
        
        if ($stmt->execute()) {
            header("Location: agent_message.php?user_id=$user_id&property_id=$property_id");
            exit();
        }
    }
}

// Get all unique conversations for the agent
$conv_sql = "
    SELECT m.user_id, m.property_id, u.name AS user_name, p.title AS property_title
    FROM messages m
    JOIN users u ON m.user_id = u.id
    JOIN properties p ON m.property_id = p.id
    WHERE m.agent_id = ?
    GROUP BY m.user_id, m.property_id
    ORDER BY MAX(m.sent_at) DESC
";
$stmt = $conn->prepare($conv_sql);
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$conversations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get full messages for a selected conversation
$fullMessages = [];
if (isset($_GET['user_id']) && isset($_GET['property_id'])) {
    $active_user_id = (int)$_GET['user_id'];
    $active_property_id = (int)$_GET['property_id'];

    $msg_sql = "SELECT * FROM messages WHERE agent_id = ? AND user_id = ? AND property_id = ? ORDER BY sent_at ASC";
    $stmt = $conn->prepare($msg_sql);
    $stmt->bind_param("iii", $agent_id, $active_user_id, $active_property_id);
    $stmt->execute();
    $fullMessages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Agent Messages</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" /><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin /><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
      body { font-family: "Inter", sans-serif; }
      .message-bubble-sent { background-color: #1d4ed8; color: white; border-bottom-right-radius: 6px; }
      .message-bubble-received { background-color: #e5e7eb; color: #1f2937; border-bottom-left-radius: 6px; }
      .message-avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
  </style>
</head>
<body class="bg-gray-100 font-sans">
<nav class="bg-red-600 text-white shadow-md px-6 py-4 flex justify-between items-center">
  <div class="text-xl font-bold">Agent Dashboard</div>
  <div class="space-x-4"><a href="agent_dashboard.php" class="hover:text-yellow-200">Home</a><a href="logout.php" class="hover:text-yellow-200">Logout</a></div>
</nav>

<div class="container mx-auto p-4 md:p-6">
<div class="flex flex-col md:flex-row gap-6 bg-white rounded-xl shadow-lg p-4" style="height: 85vh;">
    <div class="md:w-2/5 lg:w-1/3 border-r pr-4 flex flex-col">
        <h2 class="text-xl font-bold mb-4 p-2">Conversations</h2>
        <div class="overflow-y-auto">
        <?php if (count($conversations) > 0): ?>
            <ul class="space-y-2">
                <?php foreach ($conversations as $conv):
                    $isActive = ($conv['user_id'] == $active_user_id && $conv['property_id'] == $active_property_id);
                ?>
                    <li>
                        <a href="?user_id=<?= (int)$conv['user_id'] ?>&property_id=<?= (int)$conv['property_id'] ?>" class="flex items-center gap-3 p-3 rounded-lg <?= $isActive ? 'bg-blue-100' : 'hover:bg-gray-100' ?>">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($conv['user_name']) ?>&background=random" alt="User Avatar" class="message-avatar flex-shrink-0">
                            <div>
                                <div class="font-bold text-gray-800"><?= htmlspecialchars($conv['user_name'] ?? 'Unknown User') ?></div>
                                <div class="text-sm text-gray-600 truncate">Re: <?= htmlspecialchars($conv['property_title'] ?? 'N/A') ?></div>
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-gray-500 p-2">No messages from users yet.</p>
        <?php endif; ?>
        </div>
    </div>
    <div class="md:w-3/5 lg:w-2/3 flex flex-col">
        <?php if ($fullMessages): ?>
            <div class="border-b pb-4 mb-4"><h2 class="text-xl font-bold">Chat History</h2></div>
            <div class="flex-grow space-y-4 mb-4 p-4 overflow-y-auto">
                <?php foreach ($fullMessages as $msg): ?>
                    <?php if ($msg['sender_role'] === 'agent'): ?>
                        <div class="flex justify-end">
                            <div class="p-3 rounded-lg max-w-lg message-bubble-sent">
                                <p><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                                <div class="text-xs text-blue-200 mt-1 text-right"><?= date('g:i A', strtotime($msg['sent_at'])) ?></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="flex justify-start">
                            <div class="p-3 rounded-lg max-w-lg message-bubble-received">
                                <p><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                                <div class="text-xs text-gray-500 mt-1 text-right"><?= date('g:i A', strtotime($msg['sent_at'])) ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <form method="POST" class="mt-auto pt-4 border-t">
                <input type="hidden" name="user_id" value="<?= $active_user_id ?>">
                <input type="hidden" name="property_id" value="<?= $active_property_id ?>">
                <div class="flex items-center gap-2">
                    <input name="reply" required class="w-full bg-gray-100 border-transparent focus:ring-blue-500 focus:border-blue-500 rounded-full px-4 py-2" placeholder="Type your reply..."/>
                    <button type="submit" class="bg-blue-600 text-white p-2 rounded-full hover:bg-blue-700">
                         <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="flex items-center justify-center h-full text-center">
                 <div>
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No conversation selected</h3>
                    <p class="mt-1 text-sm text-gray-500">Select a conversation from the left to begin.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
</div>
</body>
</html>