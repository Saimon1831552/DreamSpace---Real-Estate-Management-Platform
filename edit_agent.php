<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agents') {
    header("Location: login.html?error=" . urlencode("Unauthorized access."));
    exit;
}

$agent_id = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = trim($_POST['fname'] ?? '');
    $lname = trim($_POST['lname'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $company = trim($_POST['company_name'] ?? '');

    if (empty($fname) || empty($lname) || empty($phone) || empty($company)) {
        $error = "All fields are required.";
    } else {
        $stmt = $conn->prepare("UPDATE agents SET fname = ?, lname = ?, phone = ?, company_name = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $fname, $lname, $phone, $company, $agent_id);

        if ($stmt->execute()) {
            $success = "Profile updated successfully.";
        } else {
            $error = "Error updating profile.";
        }
    }
}

$stmt = $conn->prepare("SELECT fname, lname, email, phone, company_name FROM agents WHERE id = ?");
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$result = $stmt->get_result();
$agent = $result->fetch_assoc();

if (!$agent) {
    die("Agent not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Agent Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-red-100 via-white to-gray-100 min-h-screen font-sans">

  <!-- NAVBAR -->
  <nav class="bg-red-600 text-white shadow-md px-6 py-4 flex justify-between items-center">
    <div class="text-xl font-bold">Agent Dashboard</div>
    <div class="space-x-4">
      <a href="agent_dashboard.php" class="hover:text-yellow-200">Home</a>
      <a href="logout.php" class="hover:text-yellow-200">Logout</a>
    </div>
  </nav>

  <!-- MAIN CONTENT -->
  <div class="flex items-center justify-center py-10 px-4">
    <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-xl">
      <h2 class="text-2xl font-bold mb-6 text-center text-red-500">Edit Profile</h2>

      <?php if ($success): ?>
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded"><?= $success ?></div>
      <?php elseif ($error): ?>
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded"><?= $error ?></div>
      <?php endif; ?>

      <form method="POST" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block mb-1 font-medium">First Name</label>
          <input type="text" name="fname" value="<?= htmlspecialchars($agent['fname']) ?>" required
                 class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-red-400" />
        </div>

        <div>
          <label class="block mb-1 font-medium">Last Name</label>
          <input type="text" name="lname" value="<?= htmlspecialchars($agent['lname']) ?>" required
                 class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-red-400" />
        </div>

        <div class="sm:col-span-2">
          <label class="block mb-1 font-medium">Email</label>
          <input type="email" name="email" value="<?= htmlspecialchars($agent['email']) ?>" required
                 class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-red-400" />
        </div>

        <div>
          <label class="block mb-1 font-medium">Phone</label>
          <input type="text" name="phone" value="<?= htmlspecialchars($agent['phone']) ?>" required
                 class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-red-400" />
        </div>

        <div>
          <label class="block mb-1 font-medium">Company</label>
          <input type="text" name="company_name" value="<?= htmlspecialchars($agent['company_name']) ?>" required
                 class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-red-400" />
        </div>

        <div class="sm:col-span-2 flex justify-between items-center mt-6">
          <button type="submit" class="bg-red-500 text-white px-6 py-2 rounded hover:bg-red-600">
            Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>

</body>
</html>
