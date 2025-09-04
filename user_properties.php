<?php
require_once 'db.php';
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'users') {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// --- Handle Add to Favorites Request ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_favorites'])) {
    $property_id_to_add = (int)$_POST['property_id'];

    // Check if it's already a favorite to prevent duplicates
    $check_stmt = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND property_id = ?");
    $check_stmt->bind_param("ii", $user_id, $property_id_to_add);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        // Not a favorite yet, so add it
        $insert_stmt = $conn->prepare("INSERT INTO favorites (user_id, property_id) VALUES (?, ?)");
        $insert_stmt->bind_param("ii", $user_id, $property_id_to_add);
        $insert_stmt->execute();
        $insert_stmt->close();
    }
    $check_stmt->close();
    
    // Redirect to the same page to show the updated status, preserving search query
    $query_params = http_build_query($_GET);
    header("Location: user_properties.php?" . $query_params);
    exit();
}

// Fetch all of the user's favorite property IDs to check against
$fav_ids = [];
$fav_stmt = $conn->prepare("SELECT property_id FROM favorites WHERE user_id = ?");
$fav_stmt->bind_param("i", $user_id);
$fav_stmt->execute();
$fav_result = $fav_stmt->get_result();
while ($fav_row = $fav_result->fetch_assoc()) {
    $fav_ids[] = $fav_row['property_id'];
}
$fav_stmt->close();


// --- Pagination and Search settings ---
$limit = 6;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_param = '%' . $search . '%';

// Get total rows for pagination with search
$count_sql = "SELECT COUNT(p.id) FROM properties p WHERE p.title LIKE ? OR p.location LIKE ?";
$stmt = $conn->prepare($count_sql);
$stmt->bind_param('ss', $search_param, $search_param);
$stmt->execute();
$stmt->bind_result($total_rows);
$stmt->fetch();
$stmt->close();
$total_pages = ceil($total_rows / $limit);

// Get properties with agent info and first image
$sql = "SELECT 
            p.*, 
            a.name AS agent_name, 
            a.phone AS agent_phone,
            (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY id ASC LIMIT 1) AS image
        FROM properties p
        JOIN agents a ON p.agent_id = a.id
        WHERE p.title LIKE ? OR p.location LIKE ?
        GROUP BY p.id
        ORDER BY p.created_at DESC
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ssii', $search_param, $search_param, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Browse Properties</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen font-sans">

<!-- Navbar -->
<header class="bg-white shadow-md sticky top-0 z-50">
    <div class="container mx-auto px-6 py-4 flex justify-between items-center">
      <a href="user_dashboard.php" class="text-2xl font-bold text-red-600">DreamSpace</a>
      <div class="flex items-center gap-4">
        <a href="user_dashboard.php" class="text-gray-600 hover:text-red-600">Dashboard</a>
        <a href="logout.php" class="text-sm text-white bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg">Logout</a>
      </div>
    </div>
</header>

<div class="container mx-auto p-6">
  <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
    <h2 class="text-3xl font-bold text-gray-800">Available Properties</h2>
    <form method="GET" action="user_properties.php" class="flex space-x-2 w-full md:w-auto">
      <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by title or location..." class="w-full p-2 border rounded-lg shadow-sm" />
      <button type="submit" class="px-5 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 shadow-sm">Search</button>
    </form>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    <?php if ($result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:-translate-y-2 transition-transform duration-300 flex flex-col">
          <a href="property-details.php?id=<?= $row['id'] ?>">
            <img src="<?= htmlspecialchars($row['image'] ?: 'https://placehold.co/400x250/EFEFEF/AAAAAA&text=No+Image') ?>" alt="Property Image" class="w-full h-56 object-cover">
          </a>
          <div class="p-5 flex flex-col flex-grow">
            <a href="property-details.php?id=<?= $row['id'] ?>" class="hover:text-red-600">
                <h3 class="text-xl font-bold text-gray-900"><?= htmlspecialchars($row['title']) ?></h3>
            </a>
            <p class="text-gray-600 mt-1"><?= htmlspecialchars($row['location']) ?></p>
            <p class="text-2xl font-bold text-red-600 mt-2">$<?= number_format($row['price']) ?></p>
            
            <div class="mt-4 pt-4 border-t">
                <p class="text-sm text-gray-700 font-semibold">Agent: <?= htmlspecialchars($row['agent_name']) ?></p>
                <p class="text-sm text-gray-500">Phone: <?= htmlspecialchars($row['agent_phone'] ?: 'Not available') ?></p>
            </div>

            <div class="mt-auto pt-4 flex justify-between items-center">
              <a href="message_user.php?property_id=<?= $row['id'] ?>" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 font-semibold">Message Agent</a>
              
              <?php if (in_array($row['id'], $fav_ids)): ?>
                <button class="bg-red-200 text-red-700 px-4 py-2 rounded-lg cursor-not-allowed font-semibold" disabled>❤️ Favorited</button>
              <?php else: ?>
                <form method="POST" action="">
                    <input type="hidden" name="property_id" value="<?= $row['id'] ?>">
                    <button type="submit" name="add_to_favorites" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 font-semibold">🤍 Favorite</button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p class="text-center col-span-full text-gray-500 text-xl py-10">No properties found matching your search.</p>
    <?php endif; ?>
  </div>

  <!-- Pagination -->
  <div class="mt-8 flex justify-center">
    <nav class="inline-flex rounded-md shadow-sm -space-x-px">
      <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" class="relative inline-flex items-center px-4 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</a>
      <?php endif; ?>
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 <?= ($i === $page) ? 'bg-red-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' ?> text-sm font-medium">
          <?= $i ?>
        </a>
      <?php endfor; ?>
      <?php if ($page < $total_pages): ?>
        <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" class="relative inline-flex items-center px-4 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>
      <?php endif; ?>
    </nav>
  </div>
</div>

</body>
</html>
