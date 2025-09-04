<?php
// admin_functions.php

/**
 * Deletes a record from a specified table.
 *
 * @param mysqli $conn The database connection object from db.php.
 * @param string $table The name of the table.
 * @param int $id The ID of the record to delete.
 * @return bool True on success, false on failure.
 */
function deleteRecord($conn, $table, $id) {
    // A list of tables that are allowed to be deleted from.
    $allowed_tables = ['properties', 'users', 'agents'];
    if (!in_array($table, $allowed_tables)) {
        return false;
    }

    $stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Updates a property's details.
 *
 * @param mysqli $conn The database connection object from db.php.
 * @param int $id The ID of the property to update.
 * @param array $data The new data for the property.
 * @return bool True on success, false on failure.
 */
function updateProperty($conn, $id, $data) {
    // Basic validation to ensure we have the data we need.
    if (empty($id) || empty($data) || !is_array($data)) {
        return false;
    }

    // Prepare the SQL statement to update the property.
    $stmt = $conn->prepare("UPDATE properties SET title = ?, description = ?, price = ?, location = ?, property_type = ?, bedrooms = ?, bathrooms = ?, area_sqft = ? WHERE id = ?");
    if (!$stmt) {
        return false;
    }

    // Bind the parameters to the statement. 'd' is used for decimal/double values.
    $stmt->bind_param(
        "ssdssiiid",
        $data['title'],
        $data['description'],
        $data['price'],
        $data['location'],
        $data['property_type'],
        $data['bedrooms'],
        $data['bathrooms'],
        $data['area_sqft'],
        $id
    );

    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

?>
