<?php
session_start();
require_once 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $course = $_POST['course'];

    $sql = "INSERT INTO students (name, email, phone, course) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $email, $phone, $course);
    
    if($stmt->execute()) {
        $_SESSION['message'] = '<i class="fas fa-check-circle"></i> Student added successfully!';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = '<i class="fas fa-times-circle"></i> Error adding student.';
        $_SESSION['message_type'] = 'error';
    }
    header("Location: view_students.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Student - iOS Style</title>
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
        });
    </script>
    <style>
        /* Any additional custom styles can go here */
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
            <li class="apple-nav-item"><a href="add_student.php" class="apple-nav-link active">Add Student</a></li>
            <li class="apple-nav-item"><a href="view_students.php" class="apple-nav-link">All Students</a></li>
            <li class="apple-nav-item"><a href="analytics.php" class="apple-nav-link">Analytics</a></li>
            <li class="apple-nav-item"><a href="backup.php" class="apple-nav-link">Backup</a></li>
            <li class="apple-nav-item"><a href="documentation.php" class="apple-nav-link">Documentation</a></li>
        </ul>
    </nav>

    <div class="apple-container">
        <h1 class="apple-section-title">Add New Student</h1>
        <p style="margin-bottom: 30px;">Fill in the student details below to add them to the database.</p>

        <!-- Student Form Card -->
        <div class="apple-card">
            <form method="POST">
                <div class="apple-form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" class="apple-input" placeholder="Enter student's full name" required autocomplete="off">
                </div>
                
                <div class="apple-form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="apple-input" placeholder="Enter student's email address" required autocomplete="off">
                </div>
                
                <div class="apple-form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="apple-input" placeholder="Enter student's phone number" autocomplete="off">
                </div>
                
                <div class="apple-form-group">
                    <label for="course">Course</label>
                    <input type="text" id="course" name="course" class="apple-input" placeholder="Enter student's course" autocomplete="off">
                </div>
                
                <div class="apple-d-flex apple-gap-20" style="margin-top: 30px;">
                    <button type="submit" class="apple-btn apple-btn-success">
                        <i class="fas fa-plus-circle"></i> Add Student
                    </button>
                    
                    <a href="index.php" class="apple-btn apple-btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Information Cards -->
        <div class="apple-d-flex apple-gap-20 apple-flex-wrap" style="margin-top: 40px;">
            <div class="apple-card" style="flex: 1; min-width: 300px;">
                <h3><i class="fas fa-info-circle" style="color: var(--apple-accent-blue);"></i> Required Fields</h3>
                <p>Full Name and Email Address are required. Phone Number and Course are optional but recommended.</p>
            </div>
            
            <div class="apple-card" style="flex: 1; min-width: 300px;">
                <h3><i class="fas fa-lightbulb" style="color: var(--apple-accent-yellow);"></i> Did You Know?</h3>
                <p>You can view all students and filter them by course on the All Students page.</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="apple-footer">
        <div class="apple-container">
            <div class="apple-footer-content">
                <div class="apple-footer-section">
                    <h4>About the System</h4>
                    <p>This student management system provides an intuitive interface for educational institutions to manage their student data efficiently.</p>
                </div>
                
                <div class="apple-footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Dashboard</a></li>
                        <li><a href="add_student.php">Add Student</a></li>
                        <li><a href="view_students.php">View Students</a></li>
                        <li><a href="#">Documentation</a></li>
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
                <p>© <?php echo date('Y'); ?> Student Management System. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
