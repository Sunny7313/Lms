<?php
include 'config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $sql = "SELECT bl.*, m.role 
            FROM borrow_list bl 
            INNER JOIN member_list m 
            ON bl.member_id = m.pin_number 
            WHERE (
                (m.role = 'member' AND DATEDIFF(CURDATE(), bl.borrow_date) > 7 )
                OR (m.role = 'faculty' AND DATEDIFF(CURDATE(), bl.borrow_date) > 14)
            )";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Query preparation failed: " . $conn->error);
    }
    $fine_per_day = 5;

    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($user = $result->fetch_assoc()) {
            $user_id = $user["member_id"];
            $borrow_id = $user["borrow_id"];
            $book_id = $user["book_id"];
            $borrow_date = $user["borrow_date"];
            $role = $user["role"];
            $current_date = new DateTime();
            $borrow_date_obj = new DateTime($borrow_date);
            $overdue_days = $current_date->diff($borrow_date_obj)->days;

            if ($overdue_days > 7) {
                $fine_amount = $overdue_days * $fine_per_day;
                // Check if fine already exists
                $check_sql = "SELECT * FROM fines WHERE fine_id = ?";
                $check_stmt = $conn->prepare($check_sql);

                if (!$check_stmt) {
                    throw new Exception("Preparation failed: " . $conn->error);
                }

                $check_stmt->bind_param("s", $borrow_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows == 0) {
                    $insert_sql = "INSERT INTO fines (fine_id, book_id, fine_amount, due_date, pin_number) 
                                   VALUES (?, ?, ?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_sql);

                    if (!$insert_stmt) {
                        throw new Exception("Preparation failed: " . $conn->error);
                    }

                    $due_date = $current_date->format('Y-m-d');
                    $insert_stmt->bind_param("ssdss", $borrow_id, $book_id, $fine_amount, $due_date, $user_id);

                    if (!$insert_stmt->execute()) {
                        throw new Exception("Error adding fine for borrow ID: $borrow_id - " . $insert_stmt->error);
                    }
                } else {
                    $update_sql = "UPDATE fines SET fine_amount = ? WHERE fine_id = ?";
                    $update_stmt = $conn->prepare($update_sql);

                    if (!$update_stmt) {
                        throw new Exception("Preparation failed: " . $conn->error);
                    }

                    $update_stmt->bind_param("ds", $fine_amount, $borrow_id);

                    if (!$update_stmt->execute()) {
                        throw new Exception("Error updating fine for borrow ID: $borrow_id - " . $update_stmt->error);
                    }
                }
            }
        }
    } else {
        //echo "No overdue books found.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
