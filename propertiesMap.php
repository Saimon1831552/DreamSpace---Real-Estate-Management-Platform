<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "dreamspace";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch properties with coordinates
$sql = "SELECT p.id, p.title, p.price, p.location, p.latitude, p.longitude, 
               (SELECT image_path FROM property_images WHERE property_id = p.id LIMIT 1) AS image
        FROM properties p
        WHERE p.latitude IS NOT NULL AND p.longitude IS NOT NULL";
$result = $conn->query($sql);

$properties = [];
while ($row = $result->fetch_assoc()) {
    $properties[] = $row;
}

header('Content-Type: application/json');
echo json_encode($properties);

$conn->close();
?>
