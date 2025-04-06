<?php
if (!function_exists('searchBooks')) {
    function searchBooks($conn, $searchTerm = '')
    {
        if (empty($searchTerm)) {
            $query = "SELECT * FROM books";
            $stmt = $conn->prepare($query);
        } else {
            $query = "SELECT * FROM books WHERE book_name LIKE ? OR author_name LIKE ? OR category LIKE ? OR rack LIKE ? OR book_id LIKE ?";
            $stmt = $conn->prepare($query);
            $searchTerm = "%$searchTerm%";
            $stmt->bind_param("sssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        }

        if (!$stmt->execute()) {
            error_log("Error executing searchBooks query: " . $stmt->error);
            return false;
        }
        return $stmt->get_result();
    }
}

if (!function_exists('retrieveBookRequests')) {
    function retrieveBookRequests($conn, $searchTerm)
    {
        $query = "SELECT * FROM book_requests WHERE request_date LIKE ? OR book_id LIKE ? OR user_id LIKE ? OR status LIKE ?";
        $stmt = $conn->prepare($query);
        $searchTerm = "%$searchTerm%";
        $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        
        if (!$stmt->execute()) {
            error_log("Error executing retrieveBookRequests query: " . $stmt->error);
            return false;
        }
        return $stmt->get_result();
    }
}

if (!function_exists("fetchFines")) {
    function fetchFines($conn)
    {
        $sql = "SELECT f.fine_id, f.book_id, f.fine_amount, f.due_date, f.pin_number,m.username,bl.book_name FROM fines f INNER JOIN books bl ON f.book_id=bl.book_id INNER JOIN member_list m ON f.pin_number=m.pin_number;";
        $result = $conn->query($sql);
        if ($result === false) {
            error_log("Error fetching fines: " . $conn->error);
            return false;
        }
        return $result;
    }
}

if (!function_exists("fetchUserProfile")) {
    function fetchUserProfile($conn, $email)
    {
        $query = "SELECT * FROM member_list WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            error_log("Error executing fetchUserProfile query: " . $stmt->error);
            return false;
        }
        return $stmt->get_result()->fetch_assoc();
    }
}

if (!function_exists("fetchBooks")) {
    function fetchBooks($conn)
    {
        $query = "SELECT * FROM books";
        $result = $conn->query($query);
        if ($result === false) {
            error_log("Error fetching books: " . $conn->error);
            return false;
        }
        return $result;
    }
}