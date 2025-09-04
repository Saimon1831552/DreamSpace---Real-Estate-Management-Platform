<?php
session_start();
include 'db.php';

// Check if an agent is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agents') {
    header("Location: login.php");
    exit();
}

$agentId = $_SESSION['user_id'];
$propertyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';

// Handle form submission for updating the property
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve form data
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $location = trim($_POST['location']);
    $property_type = trim($_POST['property_type']);
    $bedrooms = (int)$_POST['bedrooms'];
    $bathrooms = (int)$_POST['bathrooms'];
    $area_sqft = (int)$_POST['area_sqft'];

    if (empty($title) || empty($price) || empty($location)) {
        $error = "Title, Price, and Location are required.";
    } else {
        $stmt = $conn->prepare(
            "UPDATE properties SET title=?, description=?, price=?, location=?, property_type=?, bedrooms=?, bathrooms=?, area_sqft=? 
             WHERE id=? AND agent_id=?"
        );
        $stmt->bind_param(
            "ssdssiiiis",
            $title, $description, $price, $location, $property_type, $bedrooms, $bathrooms, $area_sqft, $propertyId, $agentId
        );
        
        if ($stmt->execute()) {
            $success = "Property updated successfully!";
        } else {
            $error = "Failed to update property. Please try again.";
        }
        $stmt->close();
    }
}

// Fetch the current property details to show in the form
$stmt = $conn->prepare("SELECT * FROM properties WHERE id = ? AND agent_id = ?");
$stmt->bind_param("ii", $propertyId, $agentId);
$stmt->execute();
$result = $stmt->get_result();
$property = $result->fetch_assoc();

if (!$property) {
    die("Property not found or you don't have permission to edit it.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Property</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<nav class="bg-red-600 text-white shadow-md px-6 py-4 flex justify-between items-center">
    <div class="text-xl font-bold">Agent Dashboard</div>
    <div class="space-x-4">
        <a href="agent_dashboard.php" class="hover:text-yellow-200">Home</a>
        <a href="agent_properties.php" class="hover:text-yellow-200">My Properties</a>
        <a href="logout.php" class="hover:text-yellow-200">Logout</a>
    </div>
</nav>

<div class="container mx-auto p-6 max-w-4xl">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">Edit Property</h2>

    <?php if ($success): ?>
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="bg-white p-8 rounded-lg shadow-md space-y-6">
        <div>
            <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
            <input type="text" name="title" id="title" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="<?= htmlspecialchars($property['title']) ?>" required>
        </div>
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" id="description" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"><?= htmlspecialchars($property['description']) ?></textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="price" class="block text-sm font-medium text-gray-700">Price ($)</label>
                <input type="number" name="price" id="price" step="0.01" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="<?= htmlspecialchars($property['price']) ?>" required>
            </div>
            <div>
                <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                <input type="text" name="location" id="location" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="<?= htmlspecialchars($property['location']) ?>" required>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div>
                <label for="property_type" class="block text-sm font-medium text-gray-700">Property Type</label>
                <input type="text" name="property_type" id="property_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="<?= htmlspecialchars($property['property_type']) ?>">
            </div>
            <div>
                <label for="bedrooms" class="block text-sm font-medium text-gray-700">Bedrooms</label>
                <input type="number" name="bedrooms" id="bedrooms" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="<?= htmlspecialchars($property['bedrooms']) ?>">
            </div>
            <div>
                <label for="bathrooms" class="block text-sm font-medium text-gray-700">Bathrooms</label>
                <input type="number" name="bathrooms" id="bathrooms" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="<?= htmlspecialchars($property['bathrooms']) ?>">
            </div>
            <div>
                <label for="area_sqft" class="block text-sm font-medium text-gray-700">Area (sqft)</label>
                <input type="number" name="area_sqft" id="area_sqft" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="<?= htmlspecialchars($property['area_sqft']) ?>">
            </div>
        </div>
        <div class="flex justify-end space-x-4">
             <a href="agent_properties.php" class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600">Back</a>
            <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-md hover:bg-red-700">Save Changes</button>
        </div>
    </form>
</div>

</body>
</html>