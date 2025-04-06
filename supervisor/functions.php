<?php
/**
 * Functions for managing the library system.
 */

function fetchAdminProfile($conn, $email)
{
    $query = "SELECT * FROM member_list WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        error_log("Error executing fetchAdminProfile query: " . $stmt->error);
        return false;
    }
    return $stmt->get_result()->fetch_assoc();
}

function searchBooksInLibrary($conn, $searchTerm) // Renamed function
{
    $query = "SELECT * FROM books WHERE book_name LIKE ? OR author_name LIKE ? OR category LIKE ? OR rack LIKE ?";
    $stmt = $conn->prepare($query);
    $searchTerm = "%$searchTerm%";
    $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    if (!$stmt->execute()) {
        error_log("Error executing searchBooksInLibrary query: " . $stmt->error);
        return false;
    }
    return $stmt->get_result();
}

function fetchStatistics($conn)
{
    $stats = [];
    $stats['members'] = $conn->query("SELECT COUNT(*) AS count FROM member_list")->fetch_assoc()['count'];
    $stats['books'] = $conn->query("SELECT COUNT(*) AS count FROM books")->fetch_assoc()['count'];
    $stats['fines'] = $conn->query("SELECT COUNT(*) AS count FROM fines")->fetch_assoc()['count'];
    $stats['borrowed'] = $conn->query("SELECT COUNT(*) AS count FROM borrow_list")->fetch_assoc()['count'];
    $stats['requests'] = $conn->query("SELECT COUNT(*) AS count FROM book_requests")->fetch_assoc()['count'];
    return $stats;
}

function fetchBookCategories($conn)
{
    $query = "SELECT category, COUNT(*) AS count FROM books GROUP BY category";
    $result = $conn->query($query);
    if ($result === false) {
        error_log("Error fetching book categories: " . $conn->error);
        return false;
    }
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    return $categories;
}

function fetchMemberRoles($conn)
{
    $query = "SELECT role, COUNT(*) AS count FROM member_list GROUP BY role";
    $result = $conn->query($query);
    if ($result === false) {
        error_log("Error fetching member roles: " . $conn->error);
        return false;
    }
    $roles = [];
    while ($row = $result->fetch_assoc()) {
        $roles[] = $row;
    }
    return $roles;
}

function fetchMemberBranches($conn)
{
    $query = "SELECT branch, COUNT(*) AS count FROM member_list GROUP BY branch";
    $result = $conn->query($query);
    if ($result === false) {
        error_log("Error fetching member branches: " . $conn->error);
        return false;
    }
    $branches = [];
    while ($row = $result->fetch_assoc()) {
        $branches[] = $row;
    }
    return $branches;
}

function fetchMemberSections($conn)
{
    $query = "SELECT section, COUNT(*) AS count FROM member_list GROUP BY section";
    $result = $conn->query($query);
    if ($result === false) {
        error_log("Error fetching member sections: " . $conn->error);
        return false;
    }
    $sections = [];
    while ($row = $result->fetch_assoc()) {
        $sections[] = $row;
    }
    return $sections;
}

function fetchBorrowedBooks($conn)
{
    $query = "SELECT status, COUNT(*) AS count FROM borrow_list GROUP BY status";
    $result = $conn->query($query);
    if ($result === false) {
        error_log("Error fetching borrowed books: " . $conn->error);
        return false;
    }
    $borrowedBooks = [];
    while ($row = $result->fetch_assoc()) {
        $borrowedBooks[] = $row;
    }
    return $borrowedBooks;
}

function fetchFinesByAmount($conn)
{
    $query = "SELECT 
                CASE 
                    WHEN fine_amount < 5 THEN 'Less than ₹5'
                    WHEN fine_amount BETWEEN 5 AND 10 THEN '₹5 - ₹10'
                    WHEN fine_amount BETWEEN 10 AND 20 THEN '₹10 - ₹20'
                    ELSE 'More than ₹20'
                END AS amount_range,
                COUNT(*) AS count 
              FROM fines 
              GROUP BY amount_range";
    $result = $conn->query($query);
    if ($result === false) {
        error_log("Error fetching fines by amount: " . $conn->error);
        return false;
    }
    $fines = [];
    while ($row = $result->fetch_assoc()) {
        $fines[] = $row;
    }
    return $fines;
}

// Add other functions as needed
?>
