<?php
session_start();
include '../config.php';
include '../calculate_fines.php';

if (!isset($_SESSION['email']) || ($_SESSION['role'] !== 'member' && $_SESSION['role'] !== 'faculty')) {
    header("Location: ../index.php");
    exit();
}
$_SESSION['previous_page'] = $_SERVER['HTTP_REFERER'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['book_id'])) {
        $bookId = $_POST['book_id'];
    } else {
        die("book_id is not set");
    }
 
    $pin_number = $_SESSION['pin_number'];
    $query = "SELECT * FROM member_list WHERE pin_number = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }
    $stmt->bind_param("s", $pin_number);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $query = "SELECT * FROM book_requests WHERE book_id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }
    $stmt->bind_param("ss", $bookId, $user['pin_number']);
    $stmt->execute();
    $existingRequest = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $query = "SELECT * FROM borrow_list WHERE book_id = ? AND member_id = ? AND status = 'Borrowed'";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }
    $stmt->bind_param("ss", $bookId, $user['pin_number']);
    $stmt->execute();
    $borrowedBook = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($existingRequest) {
        $_SESSION['message'] = "You have already requested this book.";
        $_SESSION['message_type'] = 'error';
    } elseif ($borrowedBook) {
        $_SESSION['message'] = "You have already borrowed this book.";
        $_SESSION['message_type'] = 'error';
    } else {
        $request_id = generateRequestId();
        $query = "INSERT INTO book_requests (request_id, book_id, user_id, status) VALUES (?, ?, ?, 'pending')";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }
        $stmt->bind_param("sss", $request_id, $bookId, $user['pin_number']);
        $stmt->execute();
        $stmt->close();
        $_SESSION['message'] = "Book request submitted successfully.";
        $_SESSION['message_type'] = 'success';
    }
    header("Location: " . $_SESSION['previous_page']);
    exit();
} else {
    die("Invalid request method");
}

function generateRequestId($length = 16)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return 'REQ-' . $randomString;
}
?>
