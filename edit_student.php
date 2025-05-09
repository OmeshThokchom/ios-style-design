<?php
session_start();
require_once 'config/db.php';

// Save referer for cancel button
if (!isset($_SESSION['edit_referer']) && isset($_SERVER['HTTP_REFERER'])) {
    // Store only internal referers
    if (strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) !== false) {
        if (strpos($_SERVER['HTTP_REFERER'], 'index.php') !== false) {
            $_SESSION['edit_referer'] = 'index.php';
        } else {
            $_SESSION['edit_referer'] = 'view_students.php';
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $course = $_POST['course'];

    $sql = "UPDATE students SET name = ?, email = ?, phone = ?, course = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $name, $email, $phone, $course, $id);
    
    if($stmt->execute()) {
        $_SESSION['message'] = '<i class="fas fa-check-circle"></i> Student updated successfully!';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = '<i class="fas fa-times-circle"></i> Error updating student.';
        $_SESSION['message_type'] = 'error';
    }
    
    // Redirect to the appropriate page
    $redirect_to = isset($_SESSION['edit_referer']) ? $_SESSION['edit_referer'] : 'view_students.php';
    unset($_SESSION['edit_referer']); // Clear the referrer
    
    header("Location: " . $redirect_to);
    exit();
}

$id = $_GET['id'];
$sql = "SELECT * FROM students WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student - iOS Style</title>
    <link rel="stylesheet" href="assets/css/apple.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .form-tip {
            background-color: rgba(0, 122, 255, 0.1);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 0.9em;
            color: #555;
        }
        
        body.dark-theme .form-tip {
            background-color: rgba(0, 122, 255, 0.2);
            color: #ccc;
        }
        
        .required-field {
            color: #FF3B30;
            font-size: 14px;
            margin-left: 5px;
        }
        
        .required-note {
            font-size: 0.85em;
            color: #777;
            margin-top: 8px;
            display: block;
        }
        
        body.dark-theme .required-note {
            color: #999;
        }
        
        .apple-form-control {
            background-color: rgba(142, 142, 147, 0.12);
            border: none;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 16px;
            color: #000;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 5px;
        }
        
        .apple-form-control:focus {
            background-color: rgba(142, 142, 147, 0.2);
            box-shadow: 0 0 0 4px rgba(0, 122, 255, 0.2);
            outline: none;
        }
        
        body.dark-theme .apple-form-control {
            background-color: rgba(44, 44, 46, 0.8);
            color: #fff;
        }
        
        body.dark-theme .apple-form-control:focus {
            background-color: rgba(44, 44, 46, 1);
        }
        
        .apple-form-group {
            margin-bottom: 22px;
        }
        
        .apple-form-group label {
            font-weight: 500;
            display: block;
            margin-bottom: 5px;
            color: #333;
        }
        
        body.dark-theme .apple-form-group label {
            color: #f5f5f7;
        }
        
        .apple-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }
        
        body.dark-theme .apple-card {
            background: rgba(30, 30, 30, 0.8);
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.2);
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
        
        .profile-icon {
            font-size: 2.5em;
            margin-right: 15px;
            color: #007AFF;
            background: rgba(0, 122, 255, 0.1);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .editing-title {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .editing-title h2 {
            margin: 0;
            font-weight: 600;
        }
    </style>
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
        });
    </script>
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
            <li class="apple-nav-item"><a href="backup.php" class="apple-nav-link">Backup</a></li>
            <li class="apple-nav-item"><a href="documentation.php" class="apple-nav-link">Documentation</a></li>
        </ul>
    </nav>

    <div class="apple-container">
        <div class="apple-d-flex apple-justify-between apple-align-center" style="margin-bottom: 30px;">
            <h1 class="apple-section-title">Edit Student</h1>
            <a href="view_students.php" class="apple-btn apple-btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Students
            </a>
        </div>
        
        <div class="apple-card" style="margin-bottom: 30px;">
            <div class="editing-title">
                <div class="profile-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div>
                    <h2>Editing <?php echo htmlspecialchars($student['name']); ?></h2>
                    <p style="margin: 5px 0 0 0; color: #666;">Student ID: <?php echo $student['id']; ?></p>
                </div>
            </div>
            
            <div class="form-tip">
                <i class="fas fa-info-circle"></i> 
                Update the student information below. Fields marked with <span class="required-field">*</span> are required.
            </div>

            <form method="POST" class="apple-form">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($student['id']); ?>">
                
                <div class="apple-form-group">
                    <label for="name"><i class="fas fa-user"></i> Student Name <span class="required-field">*</span></label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required class="apple-form-control" autocomplete="off">
                </div>
                
                <div class="apple-form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email <span class="required-field">*</span></label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required class="apple-form-control" autocomplete="off">
                </div>
                
                <div class="apple-form-group">
                    <label for="phone"><i class="fas fa-phone"></i> Phone</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($student['phone']); ?>" class="apple-form-control" placeholder="Enter phone number" autocomplete="off">
                </div>
                
                <div class="apple-form-group">
                    <label for="course"><i class="fas fa-graduation-cap"></i> Course</label>
                    <input type="text" id="course" name="course" value="<?php echo htmlspecialchars($student['course']); ?>" class="apple-form-control" placeholder="Enter course name" autocomplete="off">
                </div>
                
                <small class="required-note"><span class="required-field">*</span> Required fields</small>
                
                <div class="apple-d-flex apple-justify-between apple-align-center" style="margin-top: 30px;">
                    <a href="<?php echo isset($_SESSION['edit_referer']) ? $_SESSION['edit_referer'] : 'view_students.php'; ?>" class="apple-btn apple-btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="apple-btn">
                        <i class="fas fa-save"></i> Update Student
                    </button>
                </div>
            </form>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
        <div class="apple-alert <?php echo $_SESSION['message_type'] === 'success' ? 'apple-alert-success' : 'apple-alert-danger'; ?>">
            <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
        </div>
        <?php endif; ?>
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
</body>
</html>
