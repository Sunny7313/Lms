<?php
session_start();
include 'config.php';
include 'calculate_fines.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $stmt = $conn->prepare("SELECT * FROM member_list WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if ($password == $user['password']) {
                $_SESSION['branch'] = $user['branch'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['pin_number'] = $user['pin_number'];
                session_write_close(); 
                if (isset($_SESSION['previous_page'])) {
                    $previous_page = $_SESSION['previous_page'];
                    unset($_SESSION['previous_page']);
                    header("Location: $previous_page");
                } else {
                    if ($user['role'] == 'admin') {
                        header("Location: admin/index.php");
                    } elseif ($user['role'] == 'supervisor') {
                        header("Location: supervisor/index.php");
                    } else {
                        header("Location: user/index.php");
                    }
                }
                exit();
            } else {
                $_SESSION['error_message'] = "Invalid password.";
                header("Location: error.php");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "No user found with that email.";
            header("Location: error.php");
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
        header("Location: error.php");
        exit();
    }
}
$conn->close();
?>
