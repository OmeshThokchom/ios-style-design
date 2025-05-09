<?php
require_once 'config/db.php';

// Create deleted_students table
$sql = "CREATE TABLE IF NOT EXISTS deleted_students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    course VARCHAR(50),
    deleted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Deleted students table created successfully!";
} else {
    echo "Error creating table: " . $conn->error;
}

// Update delete_student.php to move deleted students to this table
$update_file = file_get_contents('delete_student.php');
if ($update_file === false) {
    echo "<br>Could not read delete_student.php file";
} else {
    // Check if file already contains the code to track deleted students
    if (strpos($update_file, 'deleted_students') === false) {
        $original_code = '<?php
session_start();
require_once \'config/db.php\';

if(isset($_GET[\'id\'])) {
    $id = $_GET[\'id\'];
    
    $sql = "DELETE FROM students WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if($stmt->execute()) {
        $_SESSION[\'message\'] = \'<i class="fas fa-check-circle"></i> Student deleted successfully!\';
        $_SESSION[\'message_type\'] = \'success\';
    } else {
        $_SESSION[\'message\'] = \'<i class="fas fa-times-circle"></i> Error deleting student.\';
        $_SESSION[\'message_type\'] = \'error\';
    }
}

header("Location: view_students.php");
exit();
?>';

        $new_code = '<?php
session_start();
require_once \'config/db.php\';

if(isset($_GET[\'id\'])) {
    $id = $_GET[\'id\'];
    
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
        $copy_stmt->bind_param("issss", $student[\'id\'], $student[\'name\'], $student[\'email\'], $student[\'phone\'], $student[\'course\']);
        $copy_stmt->execute();
        
        // Now delete from students table
        $delete_sql = "DELETE FROM students WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $id);
        
        if($delete_stmt->execute()) {
            $_SESSION[\'message\'] = \'<i class="fas fa-check-circle"></i> Student deleted successfully!\';
            $_SESSION[\'message_type\'] = \'success\';
        } else {
            $_SESSION[\'message\'] = \'<i class="fas fa-times-circle"></i> Error deleting student.\';
            $_SESSION[\'message_type\'] = \'error\';
        }
    } else {
        $_SESSION[\'message\'] = \'<i class="fas fa-times-circle"></i> Student not found.\';
        $_SESSION[\'message_type\'] = \'error\';
    }
}

header("Location: view_students.php");
exit();
?>';

        file_put_contents('delete_student.php', $new_code);
        echo "<br>Updated delete_student.php to track deleted students";
    } else {
        echo "<br>delete_student.php is already configured to track deleted students";
    }
}

// Create restore_student.php
$restore_file_content = '<?php
session_start();
require_once \'config/db.php\';

if (isset($_GET[\'id\'])) {
    $id = $_GET[\'id\'];
    
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
        $restore_stmt->bind_param("ssss", $deleted_student[\'name\'], $deleted_student[\'email\'], 
                                  $deleted_student[\'phone\'], $deleted_student[\'course\']);
        
        if ($restore_stmt->execute()) {
            // Remove from deleted_students
            $delete_sql = "DELETE FROM deleted_students WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $id);
            $delete_stmt->execute();
            
            $_SESSION[\'message\'] = \'<i class="fas fa-check-circle"></i> Student restored successfully!\';
            $_SESSION[\'message_type\'] = \'success\';
        } else {
            $_SESSION[\'message\'] = \'<i class="fas fa-times-circle"></i> Error restoring student.\';
            $_SESSION[\'message_type\'] = \'error\';
        }
    } else {
        $_SESSION[\'message\'] = \'<i class="fas fa-times-circle"></i> Deleted student not found.\';
        $_SESSION[\'message_type\'] = \'error\';
    }
}

header("Location: backup.php");
exit();
?>';

file_put_contents('restore_student.php', $restore_file_content);
echo "<br>Created restore_student.php for restoring deleted students";

echo "<br><br>Setup completed. <a href='backup.php'>Go to Backup Page</a>";
?>
