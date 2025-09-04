<?php
session_start();
include 'db.php';

// Ensure user is logged in and is a 'user', not an agent or admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'users') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$userName = htmlspecialchars($_SESSION['username']);

// --- Fetching Dashboard Stats ---

// 1. Total available properties
$totalProperties = $conn->query("SELECT COUNT(*) as total FROM properties")->fetch_assoc()['total'];

// 2. Total messages in the user's conversations
$totalMessages = $conn->query("SELECT COUNT(*) as total FROM messages WHERE user_id = $user_id")->fetch_assoc()['total'];

// 3. Total favorite properties marked by the user
$totalFavorites = $conn->query("SELECT COUNT(*) as total FROM favorites WHERE user_id = $user_id")->fetch_assoc()['total'];

// 4. Fetch the 3 most recently listed properties to display as a preview
$recent_properties_sql = "
    SELECT p.id, p.title, p.location, p.price, 
           (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY id ASC LIMIT 1) as image
    FROM properties p
    ORDER BY p.created_at DESC
    LIMIT 3
";
$recent_properties_result = $conn->query($recent_properties_sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>User Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen font-sans">

  <!-- Navbar -->
  <header class="bg-white shadow-md sticky top-0 z-50">
    <div class="container mx-auto px-6 py-4 flex justify-between items-center">
      <a href="user_dashboard.php" class="text-2xl font-bold text-red-600">DreamSpace</a>
      <div class="flex items-center gap-4">
        <span class="text-gray-700 font-semibold hidden sm:block">Welcome, <?= $userName ?>!</span>
        <a href="logout.php" class="text-sm text-white bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg shadow-md transition-transform transform hover:scale-105">Logout</a>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="container mx-auto p-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Your Dashboard</h1>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
      <div class="bg-white rounded-xl shadow-lg p-6 flex items-center justify-between">
        <div>
          <p class="text-gray-500 text-sm">Available Properties</p>
          <p class="text-3xl font-bold text-blue-600"><?= $totalProperties ?></p>
        </div>
        <div class="text-blue-500 text-4xl">🏘️</div>
      </div>
      <div class="bg-white rounded-xl shadow-lg p-6 flex items-center justify-between">
        <div>
          <p class="text-gray-500 text-sm">Your Messages</p>
          <p class="text-3xl font-bold text-green-600"><?= $totalMessages ?></p>
        </div>
        <div class="text-green-500 text-4xl">💬</div>
      </div>
      <div class="bg-white rounded-xl shadow-lg p-6 flex items-center justify-between">
        <div>
          <p class="text-gray-500 text-sm">Favorite Properties</p>
          <p class="text-3xl font-bold text-red-600"><?= $totalFavorites ?></p>
        </div>
        <div class="text-red-500 text-4xl">❤️</div>
      </div>
    </div>

    <!-- Navigation & Recent Properties Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      
      <!-- Navigation -->
      <div class="lg:col-span-1 bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-700 mb-4 border-b pb-2">Navigation</h2>
        <div class="flex flex-col space-y-3">
          <a href="user_properties.php" class="bg-blue-500 text-white px-5 py-3 rounded-lg hover:bg-blue-600 text-center font-semibold transition">Browse All Properties</a>
          <a href="user_messages.php" class="bg-green-500 text-white px-5 py-3 rounded-lg hover:bg-green-600 text-center font-semibold transition">View My Messages</a>
          <a href="fav_properties.php" class="bg-red-500 text-white px-5 py-3 rounded-lg hover:bg-red-600 text-center font-semibold transition">My Favorite Properties</a>
          <a href="edit_user.php" class="bg-yellow-500 text-white px-5 py-3 rounded-lg hover:bg-yellow-600 text-center font-semibold transition">Edit My Profile</a>
        </div>
      </div>

      <!-- Recent Properties -->
      <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-700 mb-4 border-b pb-2">✨ Recently Added Properties</h2>
        <div class="space-y-4">
          <?php if ($recent_properties_result->num_rows > 0): ?>
            <?php while($property = $recent_properties_result->fetch_assoc()): ?>
              <div class="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                <a href="property-details.php?id=<?= $property['id'] ?>">
                    <img src="<?= htmlspecialchars($property['image'] ?: 'https://placehold.co/100x80/EFEFEF/AAAAAA&text=No+Image') ?>" alt="Property" class="w-24 h-20 object-cover rounded-md">
                </a>
                <div class="flex-grow">
                  <a href="property-details.php?id=<?= $property['id'] ?>" class="font-bold text-gray-800 hover:text-red-600"><?= htmlspecialchars($property['title']) ?></a>
                  <p class="text-sm text-gray-600"><?= htmlspecialchars($property['location']) ?></p>
                  <p class="text-sm font-semibold text-blue-600">$<?= number_format($property['price']) ?></p>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p class="text-gray-500">No recent properties to show.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>

</body>
</html>
