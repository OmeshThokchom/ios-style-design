<?php
require_once 'config/db.php';

// Fetch the 5 most recently added students
$sql = "SELECT * FROM students ORDER BY created_at DESC LIMIT 5";
$result = $conn->query($sql);

// Count total students
$countSql = "SELECT COUNT(*) as total FROM students";
$countResult = $conn->query($countSql);
$totalStudents = $countResult->fetch_assoc()['total'] ?? 0;

// Count unique courses
$courseSql = "SELECT COUNT(DISTINCT course) as total FROM students";
$courseResult = $conn->query($courseSql);
$totalCourses = $courseResult->fetch_assoc()['total'] ?? 0;

// Count students by course
$courseSql = "SELECT course, COUNT(*) as count FROM students GROUP BY course ORDER BY count DESC LIMIT 5";
$courseResult = $conn->query($courseSql);

// Helper function to format text display
// If the course is empty, display 'Not Specified'
function formatDisplay($text) {
    return empty($text) ? 'Not Specified' : htmlspecialchars($text);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management System - iOS Style</title>
    <link rel="stylesheet" href="assets/css/apple.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        // Function to show delete confirmation modal
        function showDeleteModal(studentId, studentName) {
            console.log('Delete modal called for student:', studentId, studentName);
            const modal = document.getElementById('deleteModal');
            const studentIdField = document.getElementById('deleteStudentId');
            const studentNameSpan = document.getElementById('deleteStudentName');
            
            if (!modal || !studentIdField || !studentNameSpan) {
                console.error('Modal elements not found:', {
                    modal: !!modal,
                    studentIdField: !!studentIdField,
                    studentNameSpan: !!studentNameSpan
                });
                return;
            }
            
            studentIdField.value = studentId;
            studentNameSpan.textContent = studentName;
            
            // Show modal with fade-in effect
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('show');
                document.querySelector('.apple-modal-content').classList.add('show');
            }, 10);
        }
        
        // Function to close the modal
        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            const modalContent = document.querySelector('.apple-modal-content');
            
            modalContent.classList.remove('show');
            modal.classList.remove('show');
            
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
        
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
            
            // Live search functionality
            document.getElementById('student-search').addEventListener('input', function() {
                const searchText = this.value.toLowerCase();
                const studentCards = document.querySelectorAll('.apple-student-card');
                
                studentCards.forEach(card => {
                    const studentName = card.querySelector('h3').textContent.toLowerCase();
                    const studentCourse = card.querySelector('.apple-student-info p').textContent.toLowerCase();
                    const studentEmail = card.querySelector('.apple-student-detail:nth-child(1) p').textContent.toLowerCase();
                    const studentPhone = card.querySelector('.apple-student-detail:nth-child(2) p').textContent.toLowerCase();
                    
                    if (studentName.includes(searchText) || 
                        studentCourse.includes(searchText) || 
                        studentEmail.includes(searchText) || 
                        studentPhone.includes(searchText)) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    </script>
    <style>
        /* Additional custom styles */
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
        
        .apple-hero {
            text-align: center;
            padding: 60px 0;
            margin-bottom: 40px;
            background: linear-gradient(120deg, #f5f5f7, #e5e5e7);
            border-radius: 15px;
        }
        
        body.dark-theme .apple-hero {
            background: linear-gradient(120deg, #1c1c1e, #2c2c2e);
        }
        
        .apple-hero h1 {
            font-size: 2.5em;
            margin-bottom: 15px;
            font-weight: 700;
        }
        
        .apple-hero p {
            font-size: 1.2em;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }
        
        body.dark-theme .apple-hero p {
            color: #aaa;
        }
        
        .apple-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .apple-stat-card {
            padding: 25px;
            text-align: center;
        }
        
        .apple-stat-icon {
            font-size: 2em;
            margin-bottom: 15px;
            color: #007AFF;
        }
        
        .apple-stat-value {
            font-size: 2.2em;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .apple-stat-label {
            color: #666;
            font-size: 0.9em;
        }
        
        body.dark-theme .apple-stat-label {
            color: #aaa;
        }
        
        .apple-section-title {
            font-size: 1.8em;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .apple-search-container {
            display: flex;
            align-items: center;
            position: relative;
            margin-bottom: 30px;
        }
        
        .apple-search-icon {
            position: absolute;
            left: 15px;
            color: #888;
        }
        
        #student-search {
            width: 100%;
            padding: 15px 15px 15px 45px;
            font-size: 1em;
            border: none;
            border-radius: 10px;
            background: rgba(142, 142, 147, 0.12);
            transition: all 0.3s ease;
        }
        
        #student-search:focus {
            outline: none;
            box-shadow: 0 0 0 4px rgba(0, 122, 255, 0.2);
            background: rgba(142, 142, 147, 0.18);
        }
        
        body.dark-theme #student-search {
            background: rgba(44, 44, 46, 0.8);
            color: #fff;
        }
        
        body.dark-theme #student-search:focus {
            background: rgba(44, 44, 46, 1);
        }
        
        .apple-student-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }
        
        .apple-student-card {
            padding: 0;
            overflow: hidden;
        }
        
        .apple-student-header {
            display: flex;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        body.dark-theme .apple-student-header {
            border-bottom-color: rgba(255, 255, 255, 0.05);
        }
        
        .apple-avatar {
            width: 50px;
            height: 50px;
            background-color: #007AFF;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2em;
            font-weight: bold;
            margin-right: 15px;
        }
        
        .apple-student-info {
            flex: 1;
        }
        
        .apple-student-info h3 {
            margin: 0;
            font-size: 1.2em;
            font-weight: 600;
        }
        
        .apple-student-info p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 0.9em;
        }
        
        body.dark-theme .apple-student-info p {
            color: #aaa;
        }
        
        .apple-student-details {
            padding: 15px 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .apple-student-detail {
            display: flex;
            flex-direction: column;
        }
        
        .apple-student-detail span {
            font-size: 0.8em;
            color: #888;
            margin-bottom: 5px;
        }
        
        body.dark-theme .apple-student-detail span {
            color: #aaa;
        }
        
        .apple-student-detail p {
            margin: 0;
            font-size: 0.95em;
            font-weight: 500;
        }
        
        .apple-student-content {
            padding: 15px 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .apple-student-content p {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .apple-student-content p i {
            width: 20px;
            color: #007AFF;
        }
        
        body.dark-theme .apple-student-content p i {
            color: #0A84FF;
        }
        
        .apple-student-actions {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 15px 20px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        body.dark-theme .apple-student-actions {
            border-top-color: rgba(255, 255, 255, 0.05);
        }
        
        /* Fix for button styling consistency */
        .apple-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 15px;
            border-radius: 8px;
            font-size: 0.9em;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }
        
        .apple-btn-secondary {
            background-color: rgba(0, 122, 255, 0.1);
            color: #007AFF;
        }
        
        .apple-btn-secondary:hover {
            background-color: rgba(0, 122, 255, 0.2);
        }
        
        .apple-btn-danger {
            background-color: rgba(255, 59, 48, 0.1);
            color: #FF3B30;
        }
        
        .apple-btn-danger:hover {
            background-color: rgba(255, 59, 48, 0.2);
        }
        
        body.dark-theme .apple-btn-secondary {
            background-color: rgba(10, 132, 255, 0.15);
            color: #0A84FF;
        }
        
        body.dark-theme .apple-btn-danger {
            background-color: rgba(255, 69, 58, 0.15);
            color: #FF453A;
        }
        
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
            <li class="apple-nav-item"><a href="index.php" class="apple-nav-link active">Dashboard</a></li>
            <li class="apple-nav-item"><a href="add_student.php" class="apple-nav-link">Add Student</a></li>
            <li class="apple-nav-item"><a href="view_students.php" class="apple-nav-link">All Students</a></li>
            <li class="apple-nav-item"><a href="analytics.php" class="apple-nav-link">Analytics</a></li>
            <li class="apple-nav-item"><a href="backup.php" class="apple-nav-link">Backup</a></li>
            <li class="apple-nav-item"><a href="documentation.php" class="apple-nav-link">Documentation</a></li>
        </ul>
    </nav>

    <!-- Hero Section -->
    <div class="apple-hero apple-container">
        <h1>Student Management System</h1>
        <p>A modern approach to managing student information with iOS-inspired design</p>
    </div>

    <div class="apple-container">
        <!-- Stats Overview -->
        <div class="apple-stats-grid">
            <div class="apple-card apple-stat-card">
                <i class="fas fa-users"></i>
                <h3><?php echo $totalStudents; ?></h3>
                <p>Total Students</p>
            </div>
            <div class="apple-card apple-stat-card">
                <i class="fas fa-book"></i>
                <h3><?php echo $totalCourses; ?></h3>
                <p>Unique Courses</p>
            </div>
            <div class="apple-card apple-stat-card">
                <i class="fas fa-user-graduate"></i>
                <h3>95%</h3>
                <p>Success Rate</p>
            </div>
            <div class="apple-card apple-stat-card">
                <i class="fas fa-chart-line"></i>
                <h3>100%</h3>
                <p>System Uptime</p>
            </div>
        </div>

        <!-- Quick Actions -->
        <h2 class="apple-section-title">Quick Actions</h2>
        <div class="apple-d-flex apple-gap-20 apple-flex-wrap">
            <a href="add_student.php" class="apple-btn">
                <i class="fas fa-user-plus"></i> Add New Student
            </a>
            <a href="view_students.php" class="apple-btn apple-btn-secondary">
                <i class="fas fa-search"></i> Find Students
            </a>
        </div>

        <!-- Recent Students -->
        <h2 class="apple-section-title">Recently Added Students</h2>
        <?php if ($result && $result->num_rows > 0): ?>
        <div class="apple-student-grid">
            <?php while ($row = $result->fetch_assoc()): 
                $initials = strtoupper(substr($row['name'], 0, 1));
            ?>
            <div class="apple-card apple-student-card">
                <div class="apple-student-header">
                    <div class="apple-avatar">
                        <?php echo $initials; ?>
                    </div>
                    <div class="apple-student-info">
                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                        <p><?php echo formatDisplay($row['course']); ?></p>
                    </div>
                </div>
                <div class="apple-student-content">
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($row['email']); ?></p>
                    <p><i class="fas fa-phone"></i> <?php echo formatDisplay($row['phone']); ?></p>
                    <p><i class="fas fa-clock"></i> Added <?php echo date('M d, Y', strtotime($row['created_at'])); ?></p>
                </div>
                <div class="apple-student-actions">
                    <a href="edit_student.php?id=<?php echo $row['id']; ?>" class="apple-btn apple-btn-secondary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <button type="button" class="apple-btn apple-btn-danger" onclick="showDeleteModal(<?php echo $row['id']; ?>, '<?php echo addslashes(htmlspecialchars($row['name'])); ?>')">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="apple-card">
            <p class="apple-text-center">No students have been added yet.</p>
            <div class="apple-text-center" style="margin-top: 20px;">
                <a href="add_student.php" class="apple-btn">
                    <i class="fas fa-user-plus"></i> Add Your First Student
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Course Distribution -->
        <h2 class="apple-section-title">Course Distribution</h2>
        <?php if ($courseResult && $courseResult->num_rows > 0): ?>
        <div class="apple-table-container">
            <table class="apple-table">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Number of Students</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($courseRow = $courseResult->fetch_assoc()): 
                        $percentage = ($courseRow['count'] / $totalStudents) * 100;
                    ?>
                    <tr>
                        <td><?php echo formatDisplay($courseRow['course']); ?></td>
                        <td><?php echo $courseRow['count']; ?></td>
                        <td>
                            <div style="background: #f5f5f7; border-radius: 10px; height: 8px; width: 100%; overflow: hidden;">
                                <div style="background: linear-gradient(to right, #58b5f0, #532df5); height: 100%; width: <?php echo $percentage; ?>%;"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="apple-card">
            <p class="apple-text-center">No course data available yet.</p>
        </div>
        <?php endif; ?>
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
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="apple-modal">
        <div class="apple-modal-content">
            <div class="apple-modal-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Delete Student</h3>
                <button type="button" class="apple-modal-close" onclick="closeDeleteModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="apple-modal-body">
                <p>Are you sure you want to delete <strong><span id="deleteStudentName"></span></strong>?</p>
                <p class="apple-modal-warning">This action cannot be undone. The student will be removed from the system but can be restored from the backup page if needed.</p>
                
                <form action="delete_student.php" method="GET">
                    <input type="hidden" id="deleteStudentId" name="id" value="">
                    <div class="apple-modal-buttons">
                        <button type="button" class="apple-btn apple-btn-secondary" onclick="closeDeleteModal()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="apple-btn apple-btn-danger">
                            <i class="fas fa-trash"></i> Delete Student
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
