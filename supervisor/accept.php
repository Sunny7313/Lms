<?php
session_start();
include '../config.php';
include 'db_functions.php'; 
include '../calculate_fines.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'supervisor') {
    header("Location: ../index.php");
    
    exit();
}

if (isset($_GET['id'])) {
    error_log("Received raw request ID: " . $_GET['id']);

    $req_id = $_GET['id'];
    $status = 'Accepted';

    $conn->begin_transaction();
    error_log("Starting transaction for request ID: $req_id");

    try {
        // Step 1: Retrieve the `book_id` associated with the request
        $query = "SELECT book_id, user_id FROM book_requests WHERE request_id = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Error preparing SELECT statement: " . $conn->error);
        }
        $stmt->bind_param("s", $req_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            throw new Exception("No book found for the given request ID.");
        }
        $row = $result->fetch_assoc();
        $book_id = $row['book_id'];
        $member_id = $row['user_id']; 

        // Log the retrieved book_id and member_id
        error_log("Retrieved book_id: $book_id, member_id: $member_id");
        $query2 = "SELECT role FROM member_list WHERE pin_number = ?";
        $stmt2 = $conn->prepare($query2);
        if (!$stmt2) {
            throw new Exception("Error preparing SELECT statement for member_list: " . $conn->error);
        }
        $stmt2->bind_param("s", $member_id);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        if ($result2->num_rows === 0) {
            throw new Exception("No member found for the given member ID.");
        }
        $row2 = $result2->fetch_assoc();
        $role = $row2['role'];

        $query1 = "UPDATE book_requests SET status = ? WHERE request_id = ?";
        $stmt = $conn->prepare($query1);
        if (!$stmt) {
            throw new Exception("Error preparing UPDATE statement for book_requests: " . $conn->error);
        }
        $stmt->bind_param("ss", $status, $req_id);
        if (!$stmt->execute()) {
            throw new Exception("Error executing UPDATE on book_requests: " . $stmt->error);
        }
        $borrow_id = uniqid($req_id . ''); 
        $borrow_date = date('Y-m-d'); 
        if ($role == 'faculty') {
            $return_date = date('Y-m-d', strtotime('+14 days'));
        } else { 
            $return_date = date('Y-m-d', strtotime('+7 days'));
        }
        $query = "DELETE FROM book_requests WHERE request_id = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Error preparing DELETE statement for book_requests: " . $conn->error);
        }
        $stmt->bind_param("s", $req_id);
        $stmt->execute();
        $query = "UPDATE books SET stock = stock - 1 WHERE book_id = ? AND stock > 0";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Error preparing UPDATE statement for books: " . $conn->error);
        }
        $stmt->bind_param("s", $book_id);
        if (!$stmt->execute()) {
            throw new Exception("Error executing UPDATE on books: " . $stmt->error);
        }
        if ($stmt->affected_rows === 0) {
            throw new Exception("Book stock is already 0, cannot decrement.");
        }

        // Update the borrow count of the book
        $query = "UPDATE books SET borrow_count = borrow_count + 1 WHERE book_id = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Error preparing UPDATE statement for borrow count: " . $conn->error);
        }
        $stmt->bind_param("s", $book_id);
        if (!$stmt->execute()) {
            throw new Exception("Error executing UPDATE on borrow count: " . $stmt->error);
        }

        // Debugging statement
        error_log("Inserting borrow details for book_id: $book_id, member_id: $member_id");
        if (!insertBorrowDetails($conn, $borrow_id, $book_id, $member_id, $borrow_date, $return_date, 1)) {
            throw new Exception("Error inserting borrow details.");
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error processing request: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred while processing your request: " . $e->getMessage();
        $_SESSION['error_type'] = 'error';
        echo "Error: " . $e->getMessage();
        exit();
    }
}

$_SESSION['message'] = "Request accepted successfully.";
header("Location: manage_book_requests.php?status=success");
exit();
?>
