<?php
session_start();
include '../config.php';
include '../calculate_fines.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'supervisor') {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['id'])) {
    $borrow_id = $_GET['id'];
    $query = "SELECT book_id FROM borrow_list WHERE borrow_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $borrow_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $book_id = $row['book_id'];
    $stmt->close(); // Close the previous statement

    $query = "DELETE FROM borrow_list WHERE borrow_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $borrow_id);
    $stmt->execute();
    $affected_rows = $stmt->affected_rows; 
    $stmt->close();

    $query = "UPDATE books SET stock = stock + 1 WHERE book_id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Error preparing UPDATE statement for books: " . $conn->error);
    }
    $stmt->bind_param("s", $book_id);
    if (!$stmt->execute()) {
        throw new Exception("Error executing UPDATE on books: " . $stmt->error);
    }
    $stmt->close(); // Close the previous statement
}

if ($affected_rows > 0) {
    $_SESSION['message'] = "Borrow record deleted successfully.";
} else {
    $_SESSION['message'] = "Error deleting borrow record.";
}
header("Location: manage_borrow.php?message=" . urlencode($_SESSION['message']));
exit();
?>
