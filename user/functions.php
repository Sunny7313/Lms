<?php
// Database connection
include("../config.php");

include '../calculate_fines.php';
function getDb() {
    static $db = null;
    if ($db === null) {
        $db = new PDO(
            "mysql:host=localhost;dbname=library",
            "root",
            "",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
    return $db;
}
 
// User functions
function getUserById($userId) {
    $db = getDb();
    $stmt = $db->prepare("SELECT * FROM member_list WHERE pin_number = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function searchBooks($query = '') {
    $db = getDb();
    $sql = "SELECT * FROM books WHERE 1=1";
    $params = [];
    
    if ($query) {
        $sql .= " AND (title LIKE ? OR author LIKE ? OR category LIKE ?)";
        $searchTerm = "%$query%";
        $params = [$searchTerm, $searchTerm, $searchTerm];
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function requestBook($bookId, $userId) {
    $db = getDb();
    
    try {
        // Check if book exists and is in stock
        $stmt = $db->prepare("SELECT stock FROM books WHERE book_id = ?");
        $stmt->execute([$bookId]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$book || $book['stock'] <= 0) {
            return ['success' => false, 'message' => 'Book is not available'];
        }
        
        // Check if user already requested the book
        $stmt = $db->prepare("SELECT * FROM book_requests WHERE book_id = ? AND user_id = ?");
        $stmt->execute([$bookId, $userId]);
        $existingRequest = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingRequest) {
            return ['success' => false, 'message' => 'You have already requested this book'];
        }
        
        // Insert book request
        $stmt = $db->prepare("INSERT INTO book_requests (book_id, user_id, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$bookId, $userId]);
        
        return ['success' => true, 'message' => 'Book request submitted successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

function changePassword($userId, $currentPassword, $newPassword) {
    $db = getDb();
    
    try {
        // Verify current password
        $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }
        
        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $userId]);
        
        return ['success' => true, 'message' => 'Password updated successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}