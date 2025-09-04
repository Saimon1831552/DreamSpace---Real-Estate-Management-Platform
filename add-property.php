<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agents') {
    header("Location: login.html");
    exit;
}

$agent_id = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $location = trim($_POST['location']);
    $type = trim($_POST['property_type']);
    $bedrooms = intval($_POST['bedrooms']);
    $bathrooms = intval($_POST['bathrooms']);
    $area = floatval($_POST['area_sqft']);

    $stmt = $conn->prepare("INSERT INTO properties 
        (agent_id, title, description, price, location, property_type, bedrooms, bathrooms, area_sqft) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdssiid", $agent_id, $title, $description, $price, $location, $type, $bedrooms, $bathrooms, $area);

    if ($stmt->execute()) {
        $property_id = $stmt->insert_id;

        $uploadDir = "uploads/";
        foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
            if ($_FILES['images']['error'][$index] === UPLOAD_ERR_OK) {
                $filename = uniqid() . "_" . basename($_FILES['images']['name'][$index]);
                $targetPath = $uploadDir . $filename;

                if (move_uploaded_file($tmpName, $targetPath)) {
                    $imgStmt = $conn->prepare("INSERT INTO property_images (property_id, image_path) VALUES (?, ?)");
                    $imgStmt->bind_param("is", $property_id, $targetPath);
                    $imgStmt->execute();
                }
            }
        }

        $success = "Property added successfully!";
        header("Location: agent_dashboard.php");
        exit;
    } else {
        $error = "Error adding property: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Add Property</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

  <!-- Navbar (Same design as Edit Profile) -->
  <nav class="bg-red-600 text-white shadow-md px-6 py-4 flex justify-between items-center">
    <div class="text-xl font-bold">Agent Dashboard</div>
    <div class="space-x-4">
      <a href="agent_dashboard.php" class="hover:text-yellow-200">Home</a>
      <a href="logout.php" class="hover:text-yellow-200">Logout</a>
    </div>
  </nav>

  <!-- Page Content -->
  <div class="max-w-4xl mx-auto mt-10 bg-white p-8 rounded-lg shadow">

    <h1 class="text-2xl font-bold text-red-500 mb-6">➕ Add New Property</h1>

    <?php if ($success): ?>
      <div class="bg-green-100 text-green-800 p-3 mb-4 rounded"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
      <div class="bg-red-100 text-red-800 p-3 mb-4 rounded"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

<form action="" method="POST" enctype="multipart/form-data" class="space-y-8 bg-gray-50 p-8 rounded-xl shadow-lg">

  <h2 class="text-xl font-semibold text-red-600 mb-4">Property Details</h2>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    <div>
      <label class="block font-medium mb-1">Title</label>
      <input name="title" required class="w-full border border-gray-300 px-4 py-2 rounded-lg focus:ring-2 focus:ring-red-400 focus:border-red-400 transition" placeholder="e.g. Modern Apartment in Gulshan" />
    </div>
    <div>
      <label class="block font-medium mb-1">Location</label>
      <input name="location" required class="w-full border border-gray-300 px-4 py-2 rounded-lg focus:ring-2 focus:ring-red-400 focus:border-red-400 transition" placeholder="e.g. Gulshan, Dhaka" />
    </div>
  </div>

  <div>
    <label class="block font-medium mb-1">Description</label>
    <textarea name="description" required rows="4" class="w-full border border-gray-300 px-4 py-2 rounded-lg focus:ring-2 focus:ring-red-400 focus:border-red-400 transition" placeholder="Describe the property..."></textarea>
  </div>

  <h2 class="text-xl font-semibold text-red-600 mb-4">Specifications</h2>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    <div>
      <label class="block font-medium mb-1">Price (BDT)</label>
      <input type="number" step="0.01" name="price" required class="w-full border border-gray-300 px-4 py-2 rounded-lg focus:ring-2 focus:ring-red-400 focus:border-red-400 transition" placeholder="e.g. 25000000" />
    </div>
    <div>
      <label class="block font-medium mb-1">Area (sqft)</label>
      <input type="number" step="0.1" name="area_sqft" required class="w-full border border-gray-300 px-4 py-2 rounded-lg focus:ring-2 focus:ring-red-400 focus:border-red-400 transition" placeholder="e.g. 1800" />
    </div>
    <div>
      <label class="block font-medium mb-1">Bedrooms</label>
      <input type="number" name="bedrooms" required class="w-full border border-gray-300 px-4 py-2 rounded-lg focus:ring-2 focus:ring-red-400 focus:border-red-400 transition" min="0" placeholder="e.g. 3" />
    </div>
    <div>
      <label class="block font-medium mb-1">Bathrooms</label>
      <input type="number" name="bathrooms" required class="w-full border border-gray-300 px-4 py-2 rounded-lg focus:ring-2 focus:ring-red-400 focus:border-red-400 transition" min="0" placeholder="e.g. 2" />
    </div>
    <div>
      <label class="block font-medium mb-1">Property Type</label>
      <select name="property_type" required class="w-full border border-gray-300 px-4 py-2 rounded-lg focus:ring-2 focus:ring-red-400 focus:border-red-400 transition">
        <option value="" disabled selected>Select type</option>
        <option value="Apartment">Apartment</option>
        <option value="House">House</option>
        <option value="Office">Office</option>
        <option value="Commercial">Commercial</option>
      </select>
    </div>
  </div>

  <h2 class="text-xl font-semibold text-red-600 mb-4">Images</h2>
  <div>
    <label class="block font-medium mb-1">Upload Images</label>
    <input type="file" name="images[]" accept="image/*" multiple required class="w-full border border-gray-300 p-2 rounded-lg bg-white focus:ring-2 focus:ring-red-400 focus:border-red-400 transition" />
    <p class="text-xs text-gray-500 mt-1">You can upload multiple images (jpg, png, etc.)</p>
  </div>

  <div class="flex justify-end pt-6">
    <button type="submit" class="bg-red-500 text-white px-8 py-3 rounded-lg font-semibold shadow hover:bg-red-600 transition">Submit Property</button>
  </div>
</form>