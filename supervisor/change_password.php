<?php
session_start();
include '../config.php';
include '../calculate_fines.php';

if (!isset($_SESSION['email']) || ($_SESSION['role'] !== 'supervisor')) {
    header("Location: ../index.php");
    exit();
}

$_SESSION['previous_page'] = $_SERVER['HTTP_REFERER'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $_SESSION['message'] = "New passwords do not match.";
        $_SESSION['message_type'] = 'error';
        header("Location: " . $_SESSION['previous_page']);
        exit();
    }

    $pin_number = $_SESSION['pin_number'];
    $query = "SELECT password FROM member_list WHERE pin_number = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $pin_number);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($current_password == $user['password']) {
        $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $update_query = "UPDATE member_list SET password = ? WHERE pin_number = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ss", $new_password_hashed, $pin_number);

        if ($update_stmt->execute()) {
            $_SESSION['message'] = "Password changed successfully.";
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = "Error updating password.";
            $_SESSION['message_type'] = 'error';
        }
    } else {
        $_SESSION['message'] = "Current password is incorrect.";
        $_SESSION['message_type'] = 'error';
    }

    header("Location: " . $_SESSION['previous_page']);
    exit();
}
?>
