<?php
// search.php
require_once 'db.php';

// Get search parameters from the request
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$property_type = isset($_GET['type']) ? trim($_GET['type']) : '';

// Base SQL query
$sql = "SELECT p.*, MIN(pi.image_path) AS image 
        FROM properties p 
        LEFT JOIN property_images pi ON pi.property_id = p.id";

$where_clauses = [];
$params = [];
$types = '';

// Add conditions based on search parameters
if (!empty($keyword)) {
    $where_clauses[] = "(p.title LIKE ? OR p.description LIKE ?)";
    $keyword_param = "%" . $keyword . "%";
    $params[] = $keyword_param;
    $params[] = $keyword_param;
    $types .= 'ss';
}

if (!empty($location)) {
    $where_clauses[] = "p.location LIKE ?";
    $params[] = "%" . $location . "%";
    $types .= 's';
}

if (!empty($property_type) && $property_type !== 'All') {
    $where_clauses[] = "p.property_type = ?";
    $params[] = $property_type;
    $types .= 's';
}

// Append WHERE clauses if any
if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

// Group by property ID to get one image per property
$sql .= " GROUP BY p.id ORDER BY p.created_at DESC";

// Prepare and execute the statement
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$properties = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $properties[] = $row;
    }
}

// Close the statement and connection
$stmt->close();
$conn->close();

// Return results as JSON
header('Content-Type: application/json');
echo json_encode($properties);
?>
