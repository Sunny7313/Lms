<?php
function insertBorrowDetails($conn, $borrow_id, $book_id, $member_id, $borrow_date, $return_date, $stock) {
    $query = "INSERT INTO borrow_list (borrow_id, book_id, member_id, borrow_date, return_date, stock, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Database query failed: " . $conn->error);
        return false; // Return false instead of dying
    }
    $status = "Borrowed";
    $stmt->bind_param("sssssss", $borrow_id, $book_id, $member_id, $borrow_date, $return_date, $stock, $status);
    if (!$stmt->execute()) {
        error_log("Error inserting borrow details: " . $stmt->error);
        return false; // Return false on error
    }
    return true; // Return true on success
}

function fetchBorrowRequestStatus($conn) {
    $sql = "SELECT status, COUNT(*) as count FROM book_requests GROUP BY status";
    $result = $conn->query($sql);

    $borrowRequestStatus = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $borrowRequestStatus[] = $row;
        }
    }
    return $borrowRequestStatus;
}
?>
