<?php
session_start();
require_once 'config/db.php';

// Function to back up students table
function backupStudentsTable($conn, $selectedIds = []) {
    $timestamp = date('Y-m-d_H-i-s');
    $filename = "backup_students_$timestamp.sql";
    $backup_dir = "backups/";
    
    // Create backups directory if it doesn't exist
    if (!file_exists($backup_dir)) {
        mkdir($backup_dir, 0777, true);
    }
    
    $filepath = $backup_dir . $filename;
    
    // Get table structure
    $result = $conn->query("SHOW CREATE TABLE students");
    $row = $result->fetch_row();
    $table_structure = $row[1] . ";\n\n";
    
    $file = fopen($filepath, 'w');
    fwrite($file, $table_structure);
    
    // Get table data
    if (!empty($selectedIds)) {
        // Convert array to comma-separated string for IN clause
        $idList = implode(',', array_map('intval', $selectedIds));
        $result = $conn->query("SELECT * FROM students WHERE id IN ($idList)");
    } else {
        $result = $conn->query("SELECT * FROM students");
    }
    
    $num_rows = $result->num_rows;
    
    if ($num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $sql = "INSERT INTO students VALUES (";
            foreach ($row as $value) {
                if (is_null($value)) {
                    $sql .= "NULL, ";
                } else {
                    $sql .= "'" . $conn->real_escape_string($value) . "', ";
                }
            }
            $sql = rtrim($sql, ", ") . ");\n";
            fwrite($file, $sql);
        }
    }
    
    fclose($file);
    
    return [
        'success' => true,
        'filename' => $filename,
        'path' => $filepath,
        'records' => $num_rows
    ];
}

// Function to get deleted students
function getDeletedStudents($conn) {
    $result = $conn->query("SELECT * FROM deleted_students ORDER BY deleted_at DESC");
    $students = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
    }
    
    return $students;
}

// Initialize messages
$backup_message = '';
$backup_count = 0;

// Handle backup request
if (isset($_POST['backup'])) {
    if (isset($_POST['student_ids']) && is_array($_POST['student_ids'])) {
        // Backup selected students
        $result = backupStudentsTable($conn, $_POST['student_ids']);
        
        if ($result['success']) {
            $backup_message = "Backup created successfully! {$result['records']} selected students saved to {$result['filename']}";
            $_SESSION['message'] = "<i class='fas fa-check-circle'></i> " . $backup_message;
            $_SESSION['message_type'] = 'success';
        } else {
            $backup_message = "Error creating backup of selected students.";
            $_SESSION['message'] = "<i class='fas fa-times-circle'></i> " . $backup_message;
            $_SESSION['message_type'] = 'error';
        }
    } else {
        // Backup all students
        $result = backupStudentsTable($conn);
        
        if ($result['success']) {
            $backup_message = "Backup created successfully! {$result['records']} records saved to {$result['filename']}";
            $_SESSION['message'] = "<i class='fas fa-check-circle'></i> " . $backup_message;
            $_SESSION['message_type'] = 'success';
        } else {
            $backup_message = "Error creating backup.";
            $_SESSION['message'] = "<i class='fas fa-times-circle'></i> " . $backup_message;
            $_SESSION['message_type'] = 'error';
        }
    }
}

// Get data for views
$deleted_students = getDeletedStudents($conn);
$deleted_count = count($deleted_students);

// Get current students for selective backup
$students_result = $conn->query("SELECT id, name, email, course FROM students ORDER BY name ASC");
$students = [];
if ($students_result->num_rows > 0) {
    while ($row = $students_result->fetch_assoc()) {
        $students[] = $row;
    }
}

// Get available backups
$backups = [];
$backup_dir = "backups/";
if (file_exists($backup_dir) && is_dir($backup_dir)) {
    $files = scandir($backup_dir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && strpos($file, 'backup_students_') === 0) {
            $backups[] = [
                'filename' => $file,
                'path' => $backup_dir . $file,
                'size' => filesize($backup_dir . $file),
                'date' => date("F d, Y H:i:s", filemtime($backup_dir . $file))
            ];
        }
    }
    
    // Sort backups by date (newest first)
    usort($backups, function($a, $b) {
        return filemtime($b['path']) - filemtime($a['path']);
    });
    
    $backup_count = count($backups);
}

// Handle backup deletion
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $file_to_delete = $backup_dir . basename($_GET['delete']);
    
    if (file_exists($file_to_delete)) {
        if (unlink($file_to_delete)) {
            $_SESSION['message'] = "<i class='fas fa-check-circle'></i> Backup deleted successfully.";
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = "<i class='fas fa-times-circle'></i> Error deleting backup.";
            $_SESSION['message_type'] = 'error';
        }
    }
    
    // Redirect to remove GET parameter
    header('Location: backup.php');
    exit();
}

// Handle backup download
if (isset($_GET['download']) && !empty($_GET['download'])) {
    $file_to_download = $backup_dir . basename($_GET['download']);
    
    if (file_exists($file_to_download)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_to_download) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_to_download));
        readfile($file_to_download);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Backup - iOS Style</title>
    <link rel="stylesheet" href="assets/css/apple.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Check for system dark mode preference
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.body.classList.add('dark-theme');
            }
            
            // Toggle theme function
            window.toggleAppleTheme = function() {
                document.body.classList.toggle('dark-theme');
                const isDarkMode = document.body.classList.contains('dark-theme');
                localStorage.setItem('dark-theme', isDarkMode ? 'true' : 'false');
                
                // Update icon
                const themeIcon = document.getElementById('theme-icon');
                themeIcon.className = isDarkMode ? 'fas fa-sun' : 'fas fa-moon';
            }
            
            // Check for saved theme preference
            const savedTheme = localStorage.getItem('dark-theme');
            if (savedTheme === 'true') {
                document.body.classList.add('dark-theme');
                document.getElementById('theme-icon').className = 'fas fa-sun';
            }
            
            // Initialize animation on cards
            const cards = document.querySelectorAll('.apple-card');
            cards.forEach((card, index) => {
                card.classList.add('apple-fade-in');
                card.style.animationDelay = `${index * 0.1}s`;
            });
            
            // Function to show delete confirmation modal
            window.showDeleteBackupModal = function(filename) {
                console.log('Show delete backup modal for:', filename);
                const modal = document.getElementById('deleteBackupModal');
                const fileNameField = document.getElementById('deleteBackupName');
                const fileNameDisplay = document.getElementById('displayBackupName');
                
                if (!modal || !fileNameField || !fileNameDisplay) {
                    console.error('Modal elements not found, using fallback confirmation:', {
                        modal: !!modal,
                        fileNameField: !!fileNameField,
                        fileNameDisplay: !!fileNameDisplay
                    });
                    // Fallback to standard confirmation if modal elements can't be found
                    if (confirm(`Are you sure you want to delete backup "${filename}"? This action cannot be undone.`)) {
                        window.location.href = `backup.php?delete=${filename}`;
                    }
                    return;
                }
                
                fileNameField.value = filename;
                fileNameDisplay.textContent = filename;
                
                // Show modal with fade-in effect
                modal.style.display = 'flex';
                setTimeout(() => {
                    modal.classList.add('show');
                    modal.querySelector('.apple-modal-content').classList.add('show');
                }, 10);
            }
            
            // Function to close the modal
            window.closeDeleteBackupModal = function() {
                const modal = document.getElementById('deleteBackupModal');
                if (!modal) return;
                
                const modalContent = modal.querySelector('.apple-modal-content');
                if (modalContent) modalContent.classList.remove('show');
                modal.classList.remove('show');
                
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
            }
            
            // Tab functionality
            const tabs = document.querySelectorAll('.apple-tab');
            const tabContents = document.querySelectorAll('.apple-tab-content');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    // Remove active class from all tabs
                    tabs.forEach(t => t.classList.remove('active'));
                    
                    // Add active class to clicked tab
                    tab.classList.add('active');
                    
                    // Hide all tab contents
                    tabContents.forEach(content => {
                        content.style.display = 'none';
                    });
                    
                    // Show the corresponding tab content
                    const contentId = tab.getAttribute('data-tab');
                    document.getElementById(contentId).style.display = 'block';
                });
            });
            
            // Student selection functionality
            const selectAllCheckbox = document.getElementById('select-all-students');
            const studentCheckboxes = document.querySelectorAll('input[name="student_ids[]"]');
            
            if (selectAllCheckbox) {
                // Select all checkbox functionality
                selectAllCheckbox.addEventListener('change', () => {
                    const isChecked = selectAllCheckbox.checked;
                    
                    studentCheckboxes.forEach(checkbox => {
                        if (!checkbox.parentElement.parentElement.style.display || 
                            checkbox.parentElement.parentElement.style.display !== 'none') {
                            checkbox.checked = isChecked;
                        }
                    });
                });
            }
            
            // Student search functionality
            const studentSearch = document.getElementById('student-search');
            
            if (studentSearch) {
                studentSearch.addEventListener('input', () => {
                    const searchText = studentSearch.value.toLowerCase();
                    const studentItems = document.querySelectorAll('.student-item');
                    
                    studentItems.forEach(item => {
                        const name = item.querySelector('.student-name').textContent.toLowerCase();
                        const email = item.querySelector('.student-email').textContent.toLowerCase();
                        const courseElement = item.querySelector('.student-course');
                        const course = courseElement ? courseElement.textContent.toLowerCase() : '';
                        
                        if (name.includes(searchText) || email.includes(searchText) || course.includes(searchText)) {
                            item.style.display = '';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                    
                    // Update select all checkbox state based on filtered items
                    updateSelectAllState();
                });
            }
            
            // Update select all checkbox state based on individual checkboxes
            function updateSelectAllState() {
                if (!selectAllCheckbox) return;
                
                const visibleCheckboxes = Array.from(studentCheckboxes).filter(checkbox => {
                    return !checkbox.parentElement.parentElement.style.display || 
                           checkbox.parentElement.parentElement.style.display !== 'none';
                });
                
                const allChecked = visibleCheckboxes.length > 0 && 
                                  visibleCheckboxes.every(checkbox => checkbox.checked);
                
                selectAllCheckbox.checked = allChecked;
            }
            
            // Update select all state when individual checkboxes change
            studentCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectAllState);
            });
        });
    </script>
    <style>
        /* Modal styles */
        .apple-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .apple-modal.show {
            opacity: 1;
        }
        
        .apple-modal-content {
            background-color: #fff;
            border-radius: 15px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            transform: translateY(20px);
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .apple-modal-content.show {
            transform: translateY(0);
            opacity: 1;
        }
        
        .apple-modal-header {
            padding: 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .apple-modal-header h3 {
            margin: 0;
            font-size: 1.3em;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .apple-modal-header h3 i {
            color: #FF3B30;
        }
        
        .apple-modal-close {
            background: none;
            border: none;
            font-size: 1.5em;
            cursor: pointer;
            color: #888;
            transition: color 0.2s ease;
        }
        
        .apple-modal-close:hover {
            color: #FF3B30;
        }
        
        .apple-modal-body {
            padding: 20px;
        }
        
        .apple-modal-warning {
            color: #888;
            font-size: 0.9em;
            margin-top: 10px;
        }
        
        .apple-modal-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 30px;
        }
        
        body.dark-theme .apple-modal-content {
            background-color: #1c1c1e;
            color: #fff;
        }
        
        body.dark-theme .apple-modal-header {
            border-bottom-color: rgba(255, 255, 255, 0.1);
        }
        
        body.dark-theme .apple-modal-warning {
            color: #aaa;
        }
        
        body.dark-theme .apple-modal-close {
            color: #aaa;
        }
        
        .backup-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            padding: 20px;
            text-align: center;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .stat-card i {
            font-size: 2em;
            margin-bottom: 15px;
            color: #007AFF;
        }
        
        .stat-value {
            font-size: 1.8em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #888;
            font-size: 0.9em;
        }
        
        body.dark-theme .stat-label {
            color: #aaa;
        }
        
        .backup-actions {
            margin-bottom: 30px;
        }
        
        .backup-list {
            margin-bottom: 40px;
        }
        
        .backup-file {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .backup-file:hover {
            transform: translateY(-2px);
        }
        
        .backup-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .backup-icon {
            font-size: 1.5em;
            color: #007AFF;
        }
        
        .backup-details h4 {
            margin: 0 0 5px 0;
            font-size: 1.1em;
        }
        
        .backup-meta {
            display: flex;
            gap: 15px;
            font-size: 0.85em;
            color: #888;
        }
        
        body.dark-theme .backup-meta {
            color: #aaa;
        }
        
        .backup-actions-buttons {
            display: flex;
            gap: 10px;
        }
        
        .file-empty {
            text-align: center;
            padding: 30px 0;
            color: #888;
        }
        
        body.dark-theme .file-empty {
            color: #aaa;
        }
        
        /* Tabs */
        .apple-tabs {
            display: flex;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            margin-top: 20px;
        }
        
        body.dark-theme .apple-tabs {
            border-bottom-color: rgba(255, 255, 255, 0.1);
        }
        
        .apple-tab {
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 500;
            position: relative;
            color: #666;
            transition: all 0.3s ease;
        }
        
        .apple-tab.active {
            color: #007AFF;
        }
        
        .apple-tab.active:after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #007AFF;
        }
        
        body.dark-theme .apple-tab {
            color: #aaa;
        }
        
        body.dark-theme .apple-tab.active {
            color: #0A84FF;
        }
        
        body.dark-theme .apple-tab.active:after {
            background-color: #0A84FF;
        }
        
        .apple-tab-content {
            display: none;
            padding: 20px 0;
        }
        
        /* Student selection */
        .student-selection {
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            margin-bottom: 20px;
            max-height: 300px;
            overflow-y: auto;
        }
        
        body.dark-theme .student-selection {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .selection-header {
            display: flex;
            justify-content: space-between;
            padding: 10px 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            z-index: 10;
        }
        
        body.dark-theme .selection-header {
            border-bottom-color: rgba(255, 255, 255, 0.1);
            background-color: rgba(30, 30, 30, 0.9);
        }
        
        .select-all-container {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        
        .select-all-label {
            margin-left: 8px;
            font-weight: 500;
        }
        
        .apple-search {
            border: none;
            background-color: rgba(142, 142, 147, 0.12);
            border-radius: 8px;
            padding: 8px 12px;
            width: 200px;
            font-size: 14px;
        }
        
        body.dark-theme .apple-search {
            background-color: rgba(44, 44, 46, 0.8);
            color: #fff;
        }
        
        .apple-search:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.2);
        }
        
        .student-list {
            padding: 10px 0;
        }
        
        .student-item {
            padding: 10px 15px;
            transition: background-color 0.2s ease;
        }
        
        .student-item:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        body.dark-theme .student-item:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .student-checkbox {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        
        .student-info {
            margin-left: 10px;
            display: flex;
            flex-direction: column;
        }
        
        .student-name {
            font-weight: 500;
        }
        
        .student-meta {
            display: flex;
            gap: 15px;
            font-size: 0.85em;
            color: #888;
            margin-top: 3px;
        }
        
        body.dark-theme .student-meta {
            color: #aaa;
        }
        
        .student-email {
            display: flex;
            align-items: center;
        }
        
        .student-email:before {
            content: '\f0e0';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            margin-right: 5px;
            font-size: 0.9em;
            color: #888;
        }
        
        .student-course:before {
            content: '\f19d';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            margin-right: 5px;
            font-size: 0.9em;
            color: #888;
        }
        
        /* Table styles */
        .apple-table-container {
            overflow-x: auto;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        
        .apple-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95em;
        }
        
        .apple-table th {
            background-color: rgba(0, 0, 0, 0.03);
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        body.dark-theme .apple-table th {
            background-color: rgba(255, 255, 255, 0.05);
            color: #f5f5f7;
            border-bottom-color: rgba(255, 255, 255, 0.1);
        }
        
        .apple-table td {
            padding: 12px 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        body.dark-theme .apple-table td {
            border-bottom-color: rgba(255, 255, 255, 0.05);
        }
        
        .apple-table tr:last-child td {
            border-bottom: none;
        }
        
        .apple-table tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        body.dark-theme .apple-table tr:hover {
            background-color: rgba(255, 255, 255, 0.02);
        }
        
        .footer-section h4 { 
            margin-bottom: 20px; 
            font-size: 1.2em; 
            color: #333; 
        } 
        
        .social-links { 
            display: flex; 
            gap: 15px; 
        } 
        
        .social-links a { 
            width: 35px; 
            height: 35px; 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            background: rgba(255, 255, 255, 0.2); 
            color: #333; 
            transition: all 0.3s ease; 
        } 
        
        .social-links a:hover { 
            transform: translateY(-3px); 
            background: #4CAF50; 
            color: white; 
        } 
        
        body.dark-theme .footer-section h4, 
        body.dark-theme .social-links a { 
            color: #fff; 
        }
    </style>
</head>
<body>
    <!-- Dark Mode Toggle -->
    <div class="theme-toggle" onclick="toggleAppleTheme()">
        <i id="theme-icon" class="fas fa-moon"></i>
    </div>
    
    <!-- Navigation -->
    <nav class="apple-nav apple-container">
        <a href="index.php" class="apple-nav-brand">
            <i class="fas fa-graduation-cap"></i>
            Student Management
        </a>
        <ul class="apple-nav-menu">
            <li class="apple-nav-item"><a href="index.php" class="apple-nav-link">Dashboard</a></li>
            <li class="apple-nav-item"><a href="add_student.php" class="apple-nav-link">Add Student</a></li>
            <li class="apple-nav-item"><a href="view_students.php" class="apple-nav-link">All Students</a></li>
            <li class="apple-nav-item"><a href="analytics.php" class="apple-nav-link">Analytics</a></li>
            <li class="apple-nav-item"><a href="backup.php" class="apple-nav-link active">Backup</a></li>
            <li class="apple-nav-item"><a href="documentation.php" class="apple-nav-link">Documentation</a></li>
        </ul>
    </nav>

    <div class="apple-container">
        <h1 class="apple-section-title">Database Backup</h1>
        
        <?php if (isset($_SESSION['message'])): ?>
        <div class="apple-alert <?php echo $_SESSION['message_type'] === 'success' ? 'apple-alert-success' : 'apple-alert-danger'; ?>">
            <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
        </div>
        <?php endif; ?>
        
        <!-- Backup Stats -->
        <div class="backup-stats">
            <div class="apple-card stat-card">
                <i class="fas fa-database"></i>
                <div class="stat-value"><?php echo $backup_count; ?></div>
                <div class="stat-label">Available Backups</div>
            </div>
            
            <div class="apple-card stat-card">
                <i class="fas fa-calendar-check"></i>
                <div class="stat-value"><?php echo !empty($backups) ? date('M d', strtotime($backups[0]['date'])) : 'N/A'; ?></div>
                <div class="stat-label">Last Backup</div>
            </div>
            
            <div class="apple-card stat-card">
                <i class="fas fa-trash-alt"></i>
                <div class="stat-value"><?php echo $deleted_count; ?></div>
                <div class="stat-label">Deleted Students</div>
            </div>
        </div>
        
        <!-- Backup Actions -->
        <div class="apple-card backup-actions">
            <h2 class="apple-card-title">Create Backup</h2>
            <p>Back up your student data to keep it safe. You can back up all students or select specific students to back up.</p>
            
            <div class="apple-tabs">
                <div class="apple-tab active" data-tab="full-backup">Full Backup</div>
                <div class="apple-tab" data-tab="selective-backup">Selective Backup</div>
            </div>
            
            <div class="apple-tab-content" id="full-backup" style="display: block;">
                <p>Create a complete backup of all students in the database.</p>
                <form method="POST" action="">
                    <button type="submit" name="backup" class="apple-btn">
                        <i class="fas fa-download"></i> Back Up All Students
                    </button>
                </form>
            </div>
            
            <div class="apple-tab-content" id="selective-backup" style="display: none;">
                <p>Select specific students to include in your backup:</p>
                <form method="POST" action="" class="selective-backup-form">
                    <div class="student-selection">
                        <?php if (empty($students)): ?>
                            <p>No students found in the database.</p>
                        <?php else: ?>
                            <div class="selection-header">
                                <label class="select-all-container">
                                    <input type="checkbox" id="select-all-students">
                                    <span class="select-all-label">Select All</span>
                                </label>
                                <input type="text" class="apple-search" id="student-search" placeholder="Search students...">
                            </div>
                            
                            <div class="student-list">
                                <?php foreach($students as $student): ?>
                                <div class="student-item">
                                    <label class="student-checkbox">
                                        <input type="checkbox" name="student_ids[]" value="<?php echo $student['id']; ?>">
                                        <span class="student-info">
                                            <span class="student-name"><?php echo htmlspecialchars($student['name']); ?></span>
                                            <span class="student-meta">
                                                <span class="student-email"><?php echo htmlspecialchars($student['email']); ?></span>
                                                <?php if (!empty($student['course'])): ?>
                                                <span class="student-course"><?php echo htmlspecialchars($student['course']); ?></span>
                                                <?php endif; ?>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" name="backup" class="apple-btn" <?php echo empty($students) ? 'disabled' : ''; ?>>
                        <i class="fas fa-download"></i> Back Up Selected Students
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Backup List -->
        <div class="apple-card backup-list">
            <h2 class="apple-card-title">Backup History</h2>
            
            <?php if (empty($backups)): ?>
            <div class="file-empty">
                <i class="fas fa-folder-open" style="font-size: 2em; margin-bottom: 15px;"></i>
                <p>No backups found. Create your first backup to see it here.</p>
            </div>
            <?php else: ?>
                <?php foreach ($backups as $backup): ?>
                <div class="apple-card backup-file">
                    <div class="backup-info">
                        <div class="backup-icon">
                            <i class="fas fa-file-code"></i>
                        </div>
                        <div class="backup-details">
                            <h4><?php echo htmlspecialchars($backup['filename']); ?></h4>
                            <div class="backup-meta">
                                <span><i class="fas fa-calendar"></i> <?php echo $backup['date']; ?></span>
                                <span><i class="fas fa-weight"></i> <?php echo round($backup['size'] / 1024, 2); ?> KB</span>
                            </div>
                        </div>
                    </div>
                    <div class="backup-actions-buttons">
                        <a href="backup.php?download=<?php echo urlencode($backup['filename']); ?>" class="apple-btn apple-btn-secondary" style="padding: 5px 10px;">
                            <i class="fas fa-download"></i> Download
                        </a>
                        <button type="button" onclick="showDeleteBackupModal('<?php echo addslashes($backup['filename']); ?>')" class="apple-btn apple-btn-danger" style="padding: 5px 10px;">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Deleted Students -->
        <div class="apple-card">
            <h2 class="apple-card-title">Deleted Students</h2>
            <p>Students that have been deleted from the system. You can restore them to the active database if needed.</p>
            
            <?php if (empty($deleted_students)): ?>
            <div class="file-empty">
                <i class="fas fa-trash-alt" style="font-size: 2em; margin-bottom: 15px;"></i>
                <p>No deleted students found. When you delete students, they will appear here for recovery.</p>
            </div>
            <?php else: ?>
                <div class="apple-table-container">
                    <table class="apple-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Course</th>
                                <th>Deleted On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($deleted_students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><?php echo !empty($student['course']) ? htmlspecialchars($student['course']) : 'N/A'; ?></td>
                                <td><?php echo date('M d, Y', strtotime($student['deleted_at'])); ?></td>
                                <td>
                                    <a href="restore_student.php?id=<?php echo $student['id']; ?>" class="apple-btn apple-btn-secondary" style="padding: 5px 10px;">
                                        <i class="fas fa-undo"></i> Restore
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Backup Tips -->
        <div class="apple-card">
            <h2 class="apple-card-title">Backup Tips</h2>
            <ul class="apple-list">
                <li>
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <h4>Regular Backups</h4>
                        <p>Create backups regularly to ensure your data is safe in case of emergencies.</p>
                    </div>
                </li>
                <li>
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <h4>Download Your Backups</h4>
                        <p>Always download your backup files and store them in a secure location outside this system.</p>
                    </div>
                </li>
                <li>
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <h4>Backup Before Updates</h4>
                        <p>Create a backup before making major changes to your database or updating the system.</p>
                    </div>
                </li>
            </ul>
        </div>
    </div>

    <!-- Footer -->
    <footer class="apple-footer">
        <div class="apple-container">
            <div class="apple-footer-content">
                <div class="footer-section">
                    <h4>About the System</h4>
                    <p>This student management system provides an intuitive interface for educational institutions to manage their student data efficiently.</p>
                </div>
                
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Dashboard</a></li>
                        <li><a href="add_student.php">Add Student</a></li>
                        <li><a href="view_students.php">View Students</a></li>
                        <li><a href="analytics.php">Analytics</a></li>
                        <li><a href="backup.php">Backup</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Connect With Us</h4>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="apple-footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Student Management System. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <!-- Delete Backup Confirmation Modal -->
    <div id="deleteBackupModal" class="apple-modal">
        <div class="apple-modal-content">
            <div class="apple-modal-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Delete Backup</h3>
                <button type="button" class="apple-modal-close" onclick="closeDeleteBackupModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="apple-modal-body">
                <p>Are you sure you want to delete backup <strong><span id="displayBackupName"></span></strong>?</p>
                <p class="apple-modal-warning">This action cannot be undone. The backup file will be permanently deleted.</p>
                
                <form action="backup.php" method="GET">
                    <input type="hidden" id="deleteBackupName" name="delete" value="">
                    <div class="apple-modal-buttons">
                        <button type="button" class="apple-btn apple-btn-secondary" onclick="closeDeleteBackupModal()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="apple-btn apple-btn-danger">
                            <i class="fas fa-trash"></i> Delete Backup
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
