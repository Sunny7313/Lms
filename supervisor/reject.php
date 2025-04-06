<?php
session_start();
include '../config.php';
include '../calculate_fines.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'supervisor') {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['id'])) {
    $requestId = $_GET['id'];
    $query = "UPDATE book_requests SET status = 'rejected' WHERE request_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $requestId);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Book request rejected successfully.";
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = "Failed to reject the book request.";
        $_SESSION['message_type'] = 'error';
    }
    $stmt->close();
} else {
    $_SESSION['message'] = "Invalid request ID.";
    $_SESSION['message_type'] = 'error';
}

header("Location: manage_book_requests.php");
exit();
?>
