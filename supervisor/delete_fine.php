<?php
session_start();
include '../config.php';
include '../calculate_fines.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'supervisor') {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['id'])) {
    $fine_id = $_GET['id'];
    $query = "DELETE FROM fines WHERE fine_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $fine_id);
    $stmt->execute();
}

header("Location: manage_fines.php");
exit();
?>
