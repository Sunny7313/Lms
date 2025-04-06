<?php
session_start();
include '../config.php';
include '../calculate_fines.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'supervisor') {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['id'])) {
    $book_id = $_GET['id'];

    // Delete related records in borrow_list
    $query = "DELETE FROM borrow_list WHERE book_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $book_id);
    if (!$stmt->execute()) {
        error_log("Error deleting related records in borrow_list: " . $stmt->error);
        $_SESSION['error'] = "Error deleting related records in borrow_list.";
        header("Location: manage_books.php?status=error");
        exit();
    }

    // Delete related records in book_requests
    $query = "DELETE FROM book_requests WHERE book_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $book_id);
    if (!$stmt->execute()) {
        error_log("Error deleting related records in book_requests: " . $stmt->error);
        $_SESSION['error'] = "Error deleting related records in book_requests.";
        header("Location: manage_books.php?status=error");
        exit();
    }

    // Delete related records in fines
    $query = "DELETE FROM fines WHERE book_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $book_id);
    if (!$stmt->execute()) {
        error_log("Error deleting related records in fines: " . $stmt->error);
        $_SESSION['error'] = "Error deleting related records in fines.";
        header("Location: manage_books.php?status=error");
        exit();
    }

    // Delete the book
    $query = "DELETE FROM books WHERE book_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $book_id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Book deleted successfully.";
        $_SESSION['message_type'] = 'success';
        header("Location: manage_books.php?status=success");
        exit();
    } else {
        error_log("Error deleting the book: " . $stmt->error);
        $_SESSION['error'] = "Error deleting the book. Please try again.";
        header("Location: manage_books.php?status=error");
    }
} else {
    $_SESSION['error'] = "No book ID provided.";
    header("Location: manage_books.php?status=error");
}
?>
