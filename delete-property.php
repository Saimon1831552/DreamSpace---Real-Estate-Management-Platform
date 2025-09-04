<?php
session_start();
include 'db.php';

// Check if an agent is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agents') {
    header("Location: login.php");
    exit();
}

$agentId = $_SESSION['user_id'];

// Validate the property ID from the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: agent_properties.php?error=Invalid property ID");
    exit();
}

$propertyId = (int) $_GET['id'];

// Start a transaction to ensure both deletions succeed or fail together
$conn->begin_transaction();

try {
    // Verify the agent owns this property before deleting
    $checkStmt = $conn->prepare("SELECT id FROM properties WHERE id = ? AND agent_id = ?");
    $checkStmt->bind_param("ii", $propertyId, $agentId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows === 0) {
        throw new Exception("Unauthorized or property not found.");
    }

    // First, delete associated images from the 'property_images' table
    $deleteImagesStmt = $conn->prepare("DELETE FROM property_images WHERE property_id = ?");
    $deleteImagesStmt->bind_param("i", $propertyId);
    $deleteImagesStmt->execute();
    $deleteImagesStmt->close();

    // Then, delete the property itself
    $deletePropStmt = $conn->prepare("DELETE FROM properties WHERE id = ?");
    $deletePropStmt->bind_param("i", $propertyId);
    $deletePropStmt->execute();
    $deletePropStmt->close();

    // If all went well, commit the changes
    $conn->commit();
    header("Location: agent_properties.php?success=Property deleted successfully");

} catch (Exception $e) {
    // If anything went wrong, roll back the transaction
    $conn->rollback();
    header("Location: agent_properties.php?error=" . urlencode($e->getMessage()));
}

exit();
?>