<?php
$servername = "localhost";
$username = "student_admin";
$password = "omesh2001";
$dbname = "student_management";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Register shutdown function to close connection
register_shutdown_function(function() use ($conn) {
    if ($conn && !$conn->connect_error) {
        $conn->close();
    }
});
?>