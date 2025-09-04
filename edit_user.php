<?php
session_start();
include 'db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'users') {
    header("Location: login.html");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// Fetch current user data
$stmt = $conn->prepare("SELECT fname, lname, gender, name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User not found.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $gender = trim($_POST['gender']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    // Validation
    if (empty($fname) || empty($lname) || empty($gender) || empty($name) || empty($email)) {
        $message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } else {
        // Update user securely
        $updateStmt = $conn->prepare("
            UPDATE users 
            SET fname = ?, lname = ?, gender = ?, name = ?, email = ?
            WHERE id = ?
        ");
        $updateStmt->bind_param("sssssi", $fname, $lname, $gender, $name, $email, $user_id);

        if ($updateStmt->execute()) {
            $message = "Profile updated successfully.";
            // Refresh data
            $user['fname'] = $fname;
            $user['lname'] = $lname;
            $user['gender'] = $gender;
            $user['name'] = $name;
            $user['email'] = $email;
        } else {
            $message = "Failed to update. Please try again.";
        }
        $updateStmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Edit Profile</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

  <!-- Navbar -->
  <nav class="bg-red-600 text-white shadow-md px-6 py-4 flex justify-between items-center">
    <div class="text-xl font-bold">User Dashboard</div>
    <div class="space-x-4">
      <a href="user_dashboard.php" class="hover:text-yellow-200">Home</a>
      <a href="logout.php" class="hover:text-yellow-200">Logout</a>
    </div>
  </nav>

  <!-- Content Container -->
  <div class="flex justify-center items-center mt-10 px-4">
    <div class="bg-white shadow-lg rounded-xl p-6 w-full max-w-xl">
      <h2 class="text-2xl font-bold mb-4 text-red-600">✏️ Edit Profile</h2>

      <?php if ($message): ?>
        <div class="mb-4 text-sm text-center text-white px-4 py-2 rounded <?= strpos($message, 'successfully') ? 'bg-green-500' : 'bg-red-500' ?>">
          <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>

      <form method="POST" class="space-y-4">
        <div>
          <label class="block font-medium">First Name</label>
          <input type="text" name="fname" value="<?= htmlspecialchars($user['fname']) ?>" required class="w-full border px-3 py-2 rounded">
        </div>

        <div>
          <label class="block font-medium">Last Name</label>
          <input type="text" name="lname" value="<?= htmlspecialchars($user['lname']) ?>" required class="w-full border px-3 py-2 rounded">
        </div>

        <div>
          <label class="block font-medium">Gender</label>
          <select name="gender" required class="w-full border px-3 py-2 rounded">
            <option value="">-- Select Gender --</option>
            <option value="Male" <?= $user['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= $user['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
            <option value="Other" <?= $user['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
          </select>
        </div>

        <div>
          <label class="block font-medium">Username</label>
          <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required class="w-full border px-3 py-2 rounded">
        </div>

        <div>
          <label class="block font-medium">Email</label>
          <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required class="w-full border px-3 py-2 rounded">
        </div>

        <div class="flex justify-center mt-4">
          <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Update</button>
        </div>
      </form>
    </div>
  </div>

</body>
</html>
