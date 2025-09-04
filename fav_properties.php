<?php
session_start();
require_once 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'users') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle unfavorite (unmark) request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unfavorite_id'])) {
    $fav_id = (int) $_POST['unfavorite_id'];
    $stmt = $conn->prepare("DELETE FROM favorites WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $fav_id, $user_id);
    $stmt->execute();
    $stmt->close();
    // Redirect to the same page to see the changes immediately
    header("Location: fav_properties.php");
    exit();
}

// Get favorite properties for the logged-in user
$sql = "SELECT f.id AS fav_id, p.*, 
               (SELECT image_path FROM property_images WHERE property_id = p.id LIMIT 1) AS image
        FROM favorites f
        JOIN properties p ON f.property_id = p.id
        WHERE f.user_id = ?
        ORDER BY f.marked_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Favorite Properties</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

<nav class="bg-red-600 text-white px-6 py-4 flex justify-between items-center shadow">
  <h1 class="text-xl font-bold">Favorite Properties</h1>
  <div class="space-x-4">
    <a href="user_dashboard.php" class="hover:text-yellow-200">Dashboard</a>
    <a href="logout.php" class="hover:text-yellow-200">Logout</a>
  </div>
</nav>

<div class="max-w-6xl mx-auto py-8 px-4">
  <h2 class="text-2xl font-semibold mb-6">Your Favorites</h2>

  <?php if ($result->num_rows > 0): ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden flex flex-col">
            <img src="<?= htmlspecialchars($row['image'] ?? 'https://placehold.co/400x250/EFEFEF/AAAAAA&text=No+Image') ?>"
                 alt="Property Image"
                 class="w-full h-48 object-cover">
          </a>

          <div class="p-4 flex flex-col flex-grow">
            <a  class="hover:text-red-600">
                <h3 class="text-lg font-bold"><?= htmlspecialchars($row['title']) ?></h3>
            </a>
            <p class="text-gray-600 mt-1"><?= htmlspecialchars($row['location']) ?> • <?= htmlspecialchars($row['property_type']) ?></p>
            <p class="text-gray-800 mt-2">Price: <strong>$<?= number_format($row['price']) ?></strong></p>
            <p class="text-sm text-gray-500 mt-1"><?= $row['bedrooms'] ?> Beds • <?= $row['bathrooms'] ?> Baths • <?= $row['area_sqft'] ?> sqft</p>

            <div class="mt-auto pt-4">
                <form method="POST" action="fav_properties.php">
                  <input type="hidden" name="unfavorite_id" value="<?= $row['fav_id'] ?>">
                  <button type="submit" class="w-full text-sm bg-red-600 text-white px-3 py-2 rounded hover:bg-red-700 transition-colors">
                    Unmark Favorite
                  </button>
                </form>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <p class="text-center text-gray-600 mt-8">You haven't marked any properties as favorite yet.</p>
  <?php endif; ?>

</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
