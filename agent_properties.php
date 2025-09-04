<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$agentId = $_SESSION['user_id'];
$search = $_GET['search'] ?? '';

// Fetch properties with first image
$sql = "SELECT p.*, 
        (SELECT image_path FROM property_images WHERE property_id = p.id LIMIT 1) AS thumbnail 
    FROM properties p 
    WHERE p.agent_id = ? AND p.title LIKE CONCAT('%', ?, '%')";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $agentId, $search);
$stmt->execute();
$result = $stmt->get_result();

$properties = [];
$titles = [];
while ($row = $result->fetch_assoc()) {
    $properties[] = $row;
    $titles[] = $row['title'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Properties</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>


<body class="bg-gray-100 p-4 md:p-6 font-sans">
<nav class="bg-red-600 text-white shadow-md px-6 py-4 flex justify-between items-center">
    <div class="text-xl font-bold">Agent Dashboard</div>
    <div class="space-x-4">
      <a href="agent_dashboard.php" class="hover:text-yellow-200">Home</a>
      <a href="logout.php" class="hover:text-yellow-200">Logout</a>
    </div>
  </nav>
  <div class="max-w-7xl mx-auto bg-white p-4 md:p-6 rounded shadow-md">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
      <h2 class="text-2xl font-bold text-gray-800">📄 My Properties</h2>
      <form method="GET" class="flex flex-col sm:flex-row sm:space-x-2 w-full max-w-md relative">
        <div class="relative flex-1">
          <input 
            type="text" 
            name="search" 
            id="searchInput" 
            placeholder="Search by title..." 
            class="border px-4 py-2 w-full rounded shadow focus:outline-none"
            autocomplete="off"
            value="<?= htmlspecialchars($search) ?>"
          />
         
        </div>
        <button type="submit" class="mt-2 sm:mt-0 bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 shadow">
          Search
        </button>
      </form>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full table-auto border border-gray-200 text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-3 py-2 border">Image</th>
            <th class="px-3 py-2 border">Title</th>
            <th class="px-3 py-2 border">Location</th>
            <th class="px-3 py-2 border">Type</th>
            <th class="px-3 py-2 border">Price</th>
            <th class="px-3 py-2 border">Beds</th>
            <th class="px-3 py-2 border">Baths</th>
            <th class="px-3 py-2 border">Area</th>
            <th class="px-3 py-2 border">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($properties) === 0): ?>
            <tr><td colspan="9" class="text-center py-4 text-gray-600">No properties found.</td></tr>
          <?php else: ?>
            <?php foreach ($properties as $property): ?>
              <tr class="hover:bg-gray-100 text-center">
                <td class="px-3 py-2 border">
                  <?php if ($property['thumbnail']): ?>
                    <img src="<?= htmlspecialchars($property['thumbnail']) ?>" alt="Property Image" class="h-16 w-24 object-cover rounded">
                  <?php else: ?>
                    <span class="text-gray-400 italic">No image</span>
                  <?php endif; ?>
                </td>
                <td class="px-3 py-2 border"><?= htmlspecialchars($property['title']) ?></td>
                <td class="px-3 py-2 border"><?= htmlspecialchars($property['location']) ?></td>
                <td class="px-3 py-2 border"><?= htmlspecialchars($property['property_type']) ?></td>
                <td class="px-3 py-2 border">৳<?= number_format($property['price']) ?></td>
                <td class="px-3 py-2 border"><?= (int)$property['bedrooms'] ?></td>
                <td class="px-3 py-2 border"><?= (int)$property['bathrooms'] ?></td>
                <td class="px-3 py-2 border"><?= $property['area_sqft'] ?> sqft</td>
                <td class="px-3 py-2 border space-y-1 flex flex-col items-center">
                  <a href="edit-property.php?id=<?= $property['id'] ?>" class="text-xs bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 w-full text-center">Edit</a>
                  <a href="delete-property.php?id=<?= $property['id'] ?>" class="text-xs bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 w-full text-center" onclick="return confirm('Delete this property?')">Delete</a>
                  <a href="property-details.php?id=<?= $property['id'] ?>" class="text-xs bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600 w-full text-center">Details</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>



  <script>
    const titles = <?= json_encode($titles) ?>;
    const input = document.getElementById("searchInput");
    const suggestionBox = document.getElementById("suggestions");

    input.addEventListener("input", function () {
      const query = this.value.trim().toLowerCase();
      suggestionBox.innerHTML = "";

      if (!query) {
        suggestionBox.classList.add("hidden");
        return; 
      }

      const filtered = titles.filter(title => title.toLowerCase().includes(query)).slice(0, 10);

      if (filtered.length === 0) {
        suggestionBox.classList.add("hidden");
        return;
      }

      suggestionBox.classList.remove("hidden");

      filtered.forEach(title => {
        const li = document.createElement("li");
        li.textContent = title;
        li.className = "px-4 py-2 cursor-pointer hover:bg-gray-100";
        li.onclick = () => {
          input.value = title;
          suggestionBox.innerHTML = "";
          suggestionBox.classList.add("hidden");
          input.form.submit();
        };
        suggestionBox.appendChild(li);
      });
    });

    document.addEventListener("click", e => {
      if (!input.contains(e.target) && !suggestionBox.contains(e.target)) {
        suggestionBox.innerHTML = "";
        suggestionBox.classList.add("hidden");
      }
    });
  </script>

</body>
</html>
