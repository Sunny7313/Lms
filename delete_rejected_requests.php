<?php
include 'config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $sql = "DELETE FROM book_requests WHERE status = 'Rejected' AND request_date < NOW() - INTERVAL 1 DAY";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Query preparation failed: " . $conn->error);
    }

    if ($stmt->execute()) {
        echo "Rejected book requests older than one day deleted successfully.";
    } else {
        throw new Exception("Error deleting rejected book requests: " . $stmt->error);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
