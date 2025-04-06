<?php
session_start();
include '../config.php';
include '../calculate_fines.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['id'])) {
    $book_id = $_GET['id'];

    // Delete related records in borrow_list
    $query = "DELETE FROM borrow_list WHERE book_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $book_id);
    $stmt->execute();

    // Delete related records in book_requests
    $query = "DELETE FROM book_requests WHERE book_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $book_id);
    $stmt->execute();

    // Delete related records in fines
    $query = "DELETE FROM fines WHERE book_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $book_id);
    $stmt->execute();

    // Delete the book
    $query = "DELETE FROM books WHERE book_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $book_id);
    $stmt->execute();
}

header("Location: manage_books.php");
exit();
?>
