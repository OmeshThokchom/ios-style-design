<?php
session_start();
require_once 'config/db.php';

if(isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // First get the student data before deleting
    $get_sql = "SELECT * FROM students WHERE id = ?";
    $get_stmt = $conn->prepare($get_sql);
    $get_stmt->bind_param("i", $id);
    $get_stmt->execute();
    $result = $get_stmt->get_result();
    
    if ($student = $result->fetch_assoc()) {
        // Copy to deleted_students table
        $copy_sql = "INSERT INTO deleted_students (original_id, name, email, phone, course) VALUES (?, ?, ?, ?, ?)";
        $copy_stmt = $conn->prepare($copy_sql);
        $copy_stmt->bind_param("issss", $student['id'], $student['name'], $student['email'], $student['phone'], $student['course']);
        $copy_stmt->execute();
        
        // Now delete from students table
        $delete_sql = "DELETE FROM students WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $id);
        
        if($delete_stmt->execute()) {
            $_SESSION['message'] = '<i class="fas fa-check-circle"></i> Student deleted successfully!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = '<i class="fas fa-times-circle"></i> Error deleting student.';
            $_SESSION['message_type'] = 'error';
        }
    } else {
        $_SESSION['message'] = '<i class="fas fa-times-circle"></i> Student not found.';
        $_SESSION['message_type'] = 'error';
    }
}

header("Location: view_students.php");
exit();
?>