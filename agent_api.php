<?php
// Start session and include your database connection
session_start();
require_once "db.php";

// Protect this API: only logged-in agents can access it.
if (!isset($_SESSION["agent_loggedin"]) || $_SESSION["agent_loggedin"] !== true) {
    http_response_code(401); // Unauthorized
    echo json_encode(["error" => "Access denied. Please log in."]);
    exit;
}

header('Content-Type: application/json');

$agent_id = $_SESSION["agent_id"];
$request = $_GET['data'] ?? '';
$response = [];

try {
    // Use the $conn variable from your db.php file
    switch ($request) {
        case 'profile':
            $stmt = $conn->prepare("SELECT id, name, email, phone, company_name FROM agents WHERE id = ?");
            $stmt->bind_param("i", $agent_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $response['profile'] = $result->fetch_assoc();
            $stmt->close();
            break;

        case 'properties':
            $stmt = $conn->prepare("SELECT id, title, price, location, created_at FROM properties WHERE agent_id = ? ORDER BY created_at DESC");
            $stmt->bind_param("i", $agent_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $response['properties'] = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            break;
        
        default:
            http_response_code(400);
            $response['error'] = "Invalid request for 'data' parameter.";
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = 'Internal Server Error.';
    error_log($e->getMessage());
}

mysqli_close($conn);
echo json_encode($response);
?>
