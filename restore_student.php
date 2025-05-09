<?php
session_start();
require_once 'config/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Get the deleted student data
    $sql = "SELECT * FROM deleted_students WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($deleted_student = $result->fetch_assoc()) {
        // Restore to students table
        $restore_sql = "INSERT INTO students (name, email, phone, course) VALUES (?, ?, ?, ?)";
        $restore_stmt = $conn->prepare($restore_sql);
        $restore_stmt->bind_param("ssss", $deleted_student['name'], $deleted_student['email'], 
                                  $deleted_student['phone'], $deleted_student['course']);
        
        if ($restore_stmt->execute()) {
            // Remove from deleted_students
            $delete_sql = "DELETE FROM deleted_students WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $id);
            $delete_stmt->execute();
            
            $_SESSION['message'] = '<i class="fas fa-check-circle"></i> Student restored successfully!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = '<i class="fas fa-times-circle"></i> Error restoring student.';
            $_SESSION['message_type'] = 'error';
        }
    } else {
        $_SESSION['message'] = '<i class="fas fa-times-circle"></i> Deleted student not found.';
        $_SESSION['message_type'] = 'error';
    }
}

header("Location: backup.php");
exit();
?>