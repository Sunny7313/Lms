<?php
session_start();
include '../config.php';
include '../calculate_fines.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['id'])) {
    $member_id = $_GET['id'];
    
    // Delete related records in borrow_list
    $query = "DELETE FROM borrow_list WHERE member_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $member_id);
    $stmt->execute();

    // Delete related records in book_requests
    $query = "DELETE FROM book_requests WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $member_id);
    $stmt->execute();

    // Delete related records in fines
    $query = "DELETE FROM fines WHERE pin_number = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $member_id);
    $stmt->execute();

    // Delete the member
    $query = "DELETE FROM member_list WHERE pin_number = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $member_id);
    $stmt->execute();
}

header("Location: manage_members.php");
exit();
?>
