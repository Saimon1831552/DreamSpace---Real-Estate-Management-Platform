<?php
session_start();
include 'db.php';

// Allow public or agent viewing, but we'll keep the agent nav if logged in
$is_agent = isset($_SESSION['user_id']) && $_SESSION['role'] === 'agents';

$propertyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch main property details
$stmt = $conn->prepare("SELECT p.*, a.name as agent_name, a.email as agent_email FROM properties p JOIN agents a ON p.agent_id = a.id WHERE p.id = ?");
$stmt->bind_param("i", $propertyId);
$stmt->execute();
$result = $stmt->get_result();
$property = $result->fetch_assoc();

if (!$property) {
    die("Property not found.");
}

// Fetch all images for this property
$img_stmt = $conn->prepare("SELECT image_path FROM property_images WHERE property_id = ?");
$img_stmt->bind_param("i", $propertyId);
$img_stmt->execute();
$images_result = $img_stmt->get_result();
$images = [];
while ($row = $images_result->fetch_assoc()) {
    $images[] = $row['image_path'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Property Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<?php if ($is_agent): ?>
<nav class="bg-red-600 text-white shadow-md px-6 py-4 flex justify-between items-center">
    <div class="text-xl font-bold">Agent Dashboard</div>
    <div class="space-x-4">
        <a href="agent_dashboard.php" class="hover:text-yellow-200">Home</a>
        <a href="agent_properties.php" class="hover:text-yellow-200">My Properties</a>
        <a href="logout.php" class="hover:text-yellow-200">Logout</a>
    </div>
</nav>
<?php endif; ?>

<div class="container mx-auto p-6">
    <div class="bg-white rounded-lg shadow-xl overflow-hidden">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 p-4">
            <?php if (!empty($images)): ?>
                <?php foreach ($images as $index => $image): ?>
                    <div class="<?= $index === 0 ? 'md:col-span-2' : '' ?> flex items-center justify-center">
                        <img src="<?= htmlspecialchars($image) ?>" alt="Property Image" class="w-[60%] h-auto  object-cover rounded-lg">
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="md:col-span-2 text-center py-10">No images available.</div>
            <?php endif; ?>
        </div>

        <div class="p-6">
            <h1 class="text-4xl font-bold text-gray-900"><?= htmlspecialchars($property['title']) ?></h1>
            <p class="text-lg text-gray-600 mt-2"><?= htmlspecialchars($property['location']) ?></p>

            <div class="mt-4 text-4xl font-bold text-red-600">
                $<?= number_format($property['price'], 2) ?>
            </div>

            <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                <div class="bg-gray-100 p-4 rounded-lg">
                    <span class="text-xl font-bold"><?= (int)$property['bedrooms'] ?></span>
                    <span class="block text-sm text-gray-600">Bedrooms</span>
                </div>
                <div class="bg-gray-100 p-4 rounded-lg">
                    <span class="text-xl font-bold"><?= (int)$property['bathrooms'] ?></span>
                    <span class="block text-sm text-gray-600">Bathrooms</span>
                </div>
                <div class="bg-gray-100 p-4 rounded-lg">
                    <span class="text-xl font-bold"><?= htmlspecialchars($property['area_sqft']) ?></span>
                    <span class="block text-sm text-gray-600">sqft</span>
                </div>
                <div class="bg-gray-100 p-4 rounded-lg">
                    <span class="text-xl font-bold"><?= htmlspecialchars($property['property_type']) ?></span>
                    <span class="block text-sm text-gray-600">Type</span>
                </div>
            </div>

            <div class="mt-8">
                <h3 class="text-2xl font-semibold text-gray-800">Description</h3>
                <p class="mt-2 text-gray-700 leading-relaxed">
                    <?= nl2br(htmlspecialchars($property['description'])) ?>
                </p>
            </div>
             <div class="mt-8 pt-6 border-t">
                 <a href="agent_properties.php" class="bg-red-500 text-white px-6 py-2 rounded-md hover:bg-red-600">← Back to My Properties</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>