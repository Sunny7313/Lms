<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "library";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    session_start();
    $_SESSION['error_message'] = $e->getMessage();
    header("Location: error.php");
    exit();
}
?>