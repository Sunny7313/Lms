<?php
session_start();
include '../config.php';
include '../calculate_fines.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'supervisor') {
    header("Location: ../index.php");
    exit();
}

// Query to fetch data from borrow_list
$borrowListQuery = "SELECT * FROM borrow_list";
$borrowListResult = $conn->query($borrowListQuery);
if ($borrowListResult === false) {
    error_log("Error fetching borrow_list: " . $conn->error);
}

// Query to fetch data from book_requests
$bookRequestsQuery = "SELECT * FROM book_requests";
$bookRequestsResult = $conn->query($bookRequestsQuery);
if ($bookRequestsResult === false) {
    error_log("Error fetching book_requests: " . $conn->error);
}

// Display results
echo "<h1>Borrow List</h1>";
if ($borrowListResult && $borrowListResult->num_rows > 0) {
    echo "<table border='1'><tr><th>Borrow ID</th><th>Book ID</th><th>Member ID</th><th>Borrow Date</th><th>Return Date</th><th>Stock</th><th>Status</th></tr>";
    while ($row = $borrowListResult->fetch_assoc()) {
        echo "<tr><td>{$row['borrow_id']}</td><td>{$row['book_id']}</td><td>{$row['member_id']}</td><td>{$row['borrow_date']}</td><td>{$row['return_date']}</td><td>{$row['stock']}</td><td>{$row['status']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "No records found in borrow_list.";
}

echo "<h1>Book Requests</h1>";
if ($bookRequestsResult && $bookRequestsResult->num_rows > 0) {
    echo "<table border='1'><tr><th>Request ID</th><th>Book ID</th><th>User ID</th><th>Status</th><th>Request Date</th></tr>";
    while ($row = $bookRequestsResult->fetch_assoc()) {
        echo "<tr><td>{$row['request_id']}</td><td>{$row['book_id']}</td><td>{$row['user_id']}</td><td>{$row['status']}</td><td>{$row['request_date']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "No records found in book_requests.";
}
?>
