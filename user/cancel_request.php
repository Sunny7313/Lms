<?php
session_start();
include '../config.php';
include '../calculate_fines.php';

if (!isset($_SESSION['email']) || ($_SESSION['role'] !== 'member' && $_SESSION['role'] !== 'faculty')) {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['request_id'])) {
    header("Location: book_requests_status.php");
    exit();
}

$requestId = $_GET['request_id'];
$email = $_SESSION['email'];

function cancelBookRequest($conn, $requestId, $email)
{
    $query = "DELETE br FROM book_requests br
              INNER JOIN member_list ml ON br.user_id = ml.pin_number
              WHERE br.request_id = ? AND ml.email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $requestId, $email);
    return $stmt->execute();
}

if (cancelBookRequest($conn, $requestId, $email)) {
    $_SESSION['message'] = "Book request cancelled successfully.";
} else {
    $_SESSION['message'] = "Failed to cancel book request.";
}

header("Location: book_requests_status.php");
exit();
?>
