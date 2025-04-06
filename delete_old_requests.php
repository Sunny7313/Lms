<?php
include 'config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $days_to_keep = 30; // Number of days to keep book requests
    $sql = "DELETE FROM book_requests WHERE request_date < NOW() - INTERVAL ? DAY";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Query preparation failed: " . $conn->error);
    }

    $stmt->bind_param("i", $days_to_keep);
    if ($stmt->execute()) {
        echo "Old book requests deleted successfully.";
    } else {
        throw new Exception("Error deleting old book requests: " . $stmt->error);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
