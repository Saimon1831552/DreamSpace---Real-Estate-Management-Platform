<?php
// api.php

// --- Enhanced Error Reporting for Debugging ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ---------------------------------------------

// Use the existing db.php for database connection
require_once "db.php"; 

// --- HELPER FUNCTIONS ---

/**
 * Deletes a record from a specified table.
 * Now correctly maps singular types to plural table names.
 */
function deleteRecord($conn, $type, $id) {
    // Check for valid database connection
    if (!$conn) {
        return ['success' => false, 'message' => 'Database connection is not valid.'];
    }
    
    // Map the singular type from the frontend to the plural database table name
    $table_map = [
        'user' => 'users',
        'agent' => 'agents',
        'property' => 'properties'
    ];

    // Check if the provided type is valid
    if (!isset($table_map[$type])) {
        return ['success' => false, 'message' => 'Invalid table specified.'];
    }
    
    $table = $table_map[$type]; // Get the correct, plural table name
    
    // Prepare the SQL statement with backticks for safety
    $stmt = $conn->prepare("DELETE FROM `$table` WHERE `id` = ?");
    if (!$stmt) {
        return ['success' => false, 'message' => 'Failed to prepare statement: ' . $conn->error];
    }
    
    $stmt->bind_param("i", $id);
    
    // Execute the statement and check for errors
    if ($stmt->execute()) {
        // Check if any rows were actually deleted
        if ($stmt->affected_rows > 0) {
            $stmt->close();
            return ['success' => true, 'message' => ucfirst($type) . ' deleted successfully.'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Record not found or already deleted.'];
        }
    } else {
        // If execution fails, provide a detailed error
        $error_message = 'Failed to delete record: ' . $stmt->error;
        // Specifically check for foreign key constraint errors (MySQL error code 1451)
        if ($conn->errno === 1451) {
            $error_message = "Cannot delete this " . $type . " because they are linked to other data (e.g., properties or messages). Please remove the linked data first.";
        }
        $stmt->close();
        return ['success' => false, 'message' => $error_message];
    }
}

/**
 * Updates a property's details.
 */
function updateProperty($conn, $id, $data) {
    if (empty($id) || empty($data)) return false;
    $stmt = $conn->prepare("UPDATE `properties` SET `title` = ?, `description` = ?, `price` = ?, `location` = ?, `property_type` = ?, `bedrooms` = ?, `bathrooms` = ?, `area_sqft` = ? WHERE `id` = ?");
    if (!$stmt) return false;
    $stmt->bind_param("ssdssiiid", $data['title'], $data['description'], $data['price'], $data['location'], $data['property_type'], $data['bedrooms'], $data['bathrooms'], $data['area_sqft'], $id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Updates a user's details.
 */
function updateUser($conn, $id, $data) {
    if (empty($id) || empty($data) || !isset($data['name']) || !isset($data['email'])) return false;
    $stmt = $conn->prepare("UPDATE `users` SET `name` = ?, `email` = ? WHERE `id` = ?");
    if (!$stmt) return false;
    $stmt->bind_param("ssi", $data['name'], $data['email'], $id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Updates an agent's details.
 */
function updateAgent($conn, $id, $data) {
    if (empty($id) || empty($data) || !isset($data['name']) || !isset($data['email'])) return false;
    $stmt = $conn->prepare("UPDATE `agents` SET `name` = ?, `email` = ?, `phone` = ?, `company_name` = ? WHERE `id` = ?");
    if (!$stmt) return false;
    $phone = $data['phone'] ?? '';
    $company = $data['company_name'] ?? '';
    $stmt->bind_param("ssssi", $data['name'], $data['email'], $phone, $company, $id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}


// --- API LOGIC ---

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];
$response = [];

try {
    if ($method === 'GET') {
        if (!isset($_GET['data'])) {
            http_response_code(400);
            $response['error'] = 'Bad Request: "data" parameter is missing.';
            echo json_encode($response);
            exit;
        }
        $request = $_GET['data'];
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id > 0) { // Single item requests
            $stmt = null;
            switch ($request) {
                case 'user':
                    $stmt = $conn->prepare("SELECT id, name, email, gender, created_at FROM users WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    break;
                case 'agent':
                    $stmt = $conn->prepare("SELECT id, name, email, phone, company_name, created_at FROM agents WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    break;
                case 'property':
                    $stmt = $conn->prepare("SELECT p.*, a.name as agent_name, a.email as agent_email FROM properties p LEFT JOIN agents a ON p.agent_id = a.id WHERE p.id = ?");
                    $stmt->bind_param("i", $id);
                    break;
            }

            if ($stmt) {
                $stmt->execute();
                $result = $stmt->get_result();
                $data = $result->fetch_assoc();
                
                if ($request === 'agent' && $data) {
                    $prop_stmt = $conn->prepare("SELECT id, title, price, location FROM properties WHERE agent_id = ? ORDER BY created_at DESC");
                    $prop_stmt->bind_param("i", $id);
                    $prop_stmt->execute();
                    $prop_result = $prop_stmt->get_result();
                    $data['properties'] = $prop_result->fetch_all(MYSQLI_ASSOC);
                    $prop_stmt->close();
                }
                
                if ($request === 'property' && $data) {
                    $img_stmt = $conn->prepare("SELECT id, image_path FROM property_images WHERE property_id = ?");
                    $img_stmt->bind_param("i", $id);
                    $img_stmt->execute();
                    $img_result = $img_stmt->get_result();
                    $data['images'] = $img_result->fetch_all(MYSQLI_ASSOC);
                    $img_stmt->close();
                }

                $response[$request] = $data;
                $stmt->close();
            }

        } else { // List requests
             switch ($request) {
                case 'stats':
                    $stats = [];
                    $queries = [
                        'totalUsers' => "SELECT COUNT(id) as count FROM users",
                        'totalAgents' => "SELECT COUNT(id) as count FROM agents",
                        'totalProperties' => "SELECT COUNT(id) as count FROM properties",
                        'totalValue' => "SELECT SUM(price) as count FROM properties"
                    ];
                    foreach ($queries as $key => $sql) {
                        $result = mysqli_query($conn, $sql);
                        $stats[$key] = mysqli_fetch_assoc($result)['count'] ?? 0;
                    }
                    $chart_sql = "SELECT property_type, COUNT(id) as count FROM properties GROUP BY property_type";
                    $chart_result = mysqli_query($conn, $chart_sql);
                    $stats['propertyTypes'] = mysqli_fetch_all($chart_result, MYSQLI_ASSOC);
                    $response['stats'] = $stats;
                    break;
                case 'users':
                    $result = mysqli_query($conn, "SELECT id, name, email, created_at FROM users ORDER BY created_at DESC");
                    $response['users'] = mysqli_fetch_all($result, MYSQLI_ASSOC);
                    break;
                case 'agents':
                    $result = mysqli_query($conn, "SELECT id, name, email, company_name, created_at FROM agents ORDER BY created_at DESC");
                    $response['agents'] = mysqli_fetch_all($result, MYSQLI_ASSOC);
                    break;
                case 'properties':
                    $result = mysqli_query($conn, "SELECT p.id, p.title, p.price, p.location, a.name as agent_name, p.created_at FROM properties p LEFT JOIN agents a ON p.agent_id = a.id ORDER BY p.created_at DESC");
                    $response['properties'] = mysqli_fetch_all($result, MYSQLI_ASSOC);
                    break;
                default:
                    http_response_code(400);
                    $response['error'] = "Invalid value for 'data' parameter.";
                    break;
            }
        }

    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['action'], $data['type'], $data['id'])) {
            http_response_code(400);
            $response['error'] = 'Bad Request: Missing action, type, or id.';
        } else {
            $id = (int)$data['id'];
            $payload = $data['payload'];
            $success = false;

            switch ($data['action']) {
                case 'update_property':
                    $success = updateProperty($conn, $id, $payload);
                    break;
                case 'update_user':
                    $success = updateUser($conn, $id, $payload);
                    break;
                case 'update_agent':
                    $success = updateAgent($conn, $id, $payload);
                    break;
                default:
                    http_response_code(400);
                    $response['error'] = "Invalid action specified.";
                    echo json_encode($response);
                    exit;
            }

            if ($success) {
                $response['success'] = true;
                $response['message'] = ucfirst(str_replace('_', ' ', $data['type'])) . " updated successfully.";
            } else {
                http_response_code(500);
                $response['error'] = "Failed to update " . $data['type'] . ".";
            }
        }
    } elseif ($method === 'DELETE') {
        parse_str(file_get_contents("php://input"), $delete_vars);
        if (!isset($delete_vars['type'], $delete_vars['id'])) {
             http_response_code(400);
             $response['error'] = 'Bad Request: Missing type or id for deletion.';
        } else {
            $type = $delete_vars['type']; // This will be 'user', 'agent', etc.
            $id = (int)$delete_vars['id'];
            $result = deleteRecord($conn, $type, $id); // Pass the singular type to our fixed function
            if ($result['success']) {
                $response = $result;
            } else {
                http_response_code(422); // Unprocessable Entity - a better error code for this case
                $response['error'] = $result['message'];
            }
        }
    } else {
        http_response_code(405);
        $response['error'] = 'Method Not Allowed';
    }
} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = 'Internal Server Error: ' . $e->getMessage();
    error_log($e->getMessage());
}

mysqli_close($conn);
echo json_encode($response);
?>
