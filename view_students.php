<?php
session_start();
require_once 'config/db.php';

$limit = 10; // Number of students per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch students with pagination
$sql = "SELECT * FROM students ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Get total number of students for pagination
$total_students = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'];
$total_pages = ceil($total_students / $limit);

// Helper function to format display text
function formatDisplay($text) {
    return empty($text) ? 'Not Specified' : htmlspecialchars($text);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Students - iOS Style</title>
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
            
            // Live search functionality
            document.getElementById('student-search').addEventListener('input', function() {
                const searchText = this.value.toLowerCase();
                const studentRows = document.querySelectorAll('.apple-table tbody tr');
                
                studentRows.forEach(row => {
                    const studentName = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
                    const studentEmail = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
                    const studentCourse = row.querySelector('td:nth-child(5)')?.textContent.toLowerCase() || '';
                    
                    if (studentName.includes(searchText) || 
                        studentEmail.includes(searchText) || 
                        studentCourse.includes(searchText)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
        
        // Function to show delete confirmation modal
        function showDeleteModal(studentId, studentName) {
            const modal = document.getElementById('deleteModal');
            const studentIdField = document.getElementById('deleteStudentId');
            const studentNameSpan = document.getElementById('deleteStudentName');
            
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
    </script>
    <style>
        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .student-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .search-box {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 25px;
            padding: 5px 15px;
            width: 300px;
        }

        .search-box input {
            background: transparent;
            border: none;
            padding: 10px;
            width: 100%;
        }

        .search-box i {
            color: #4CAF50;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .pagination a {
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 5px;
            color: inherit;
            text-decoration: none;
        }

        .pagination a.active {
            background: #4CAF50;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            overflow: hidden;
        }
        th, td {
            padding: 15px;
            border: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5; /* Light gray hover effect */
        }

        .home-btn {
            background: #4CAF50;
            color: white;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        
        .action-buttons a {
            padding: 5px 10px;
            border-radius: 5px;
            color: white;
            text-decoration: none;
        }
        
        .edit-btn {
            background: #2196F3;
        }
        
        .delete-btn {
            background: #f44336;
        }
        
        .message-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-content {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 20px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: modalSlideUp 0.3s ease;
        }

        @keyframes modalSlideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-content h3 {
            color: #f44336;
            margin-bottom: 20px;
            font-size: 1.5em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .modal-content p {
            margin-bottom: 25px;
            color: #666;
        }

        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .modal-buttons a {
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: transform 0.2s ease;
        }

        .modal-buttons a:hover {
            transform: scale(1.05);
        }

        .delete-confirm-btn {
            background: #f44336;
            color: white;
        }

        .cancel-btn {
            background: rgba(0, 0, 0, 0.1);
            color: #666;
        }

        body.dark-theme .modal-content {
            background: rgba(30, 30, 30, 0.95);
        }

        body.dark-theme .modal-content p {
            color: #ccc;
        }

        body.dark-theme .cancel-btn {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
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
    <div class="theme-toggle" onclick="toggleAppleTheme()">
        <i id="theme-icon" class="fas fa-moon"></i>
    </div>
    
    <nav class="apple-nav apple-container">
        <a href="index.php" class="apple-nav-brand">
            <i class="fas fa-graduation-cap"></i>
            Student Management
        </a>
        <ul class="apple-nav-menu">
            <li class="apple-nav-item"><a href="index.php" class="apple-nav-link">Dashboard</a></li>
            <li class="apple-nav-item"><a href="add_student.php" class="apple-nav-link">Add Student</a></li>
            <li class="apple-nav-item"><a href="view_students.php" class="apple-nav-link active">All Students</a></li>
            <li class="apple-nav-item"><a href="analytics.php" class="apple-nav-link">Analytics</a></li>
            <li class="apple-nav-item"><a href="backup.php" class="apple-nav-link">Backup</a></li>
            <li class="apple-nav-item"><a href="documentation.php" class="apple-nav-link">Documentation</a></li>
        </ul>
    </nav>

    <div class="apple-container">
        <h1 class="apple-section-title">All Students</h1>
        
        <!-- Search and Filter Section -->
        <div class="apple-d-flex apple-justify-between apple-align-center" style="margin-bottom: 30px;">
            <div class="apple-search-container">
                <i class="fas fa-search apple-search-icon"></i>
                <input type="text" id="student-search" class="apple-search" placeholder="Search by name, email, or course...">
            </div>
            
            <a href="add_student.php" class="apple-btn">
                <i class="fas fa-user-plus"></i> Add New Student
            </a>
        </div>
        
        <!-- Students Table -->
        <div class="apple-table-container">
            <table class="apple-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Course</th>
                        <th>Date Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo formatDisplay($row['phone']); ?></td>
                            <td><?php echo formatDisplay($row['course']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <div class="apple-d-flex apple-gap-10">
                                    <a href="edit_student.php?id=<?php echo $row['id']; ?>" class="apple-btn apple-btn-secondary" style="padding: 5px 10px;">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="javascript:void(0)" class="apple-btn apple-btn-danger" style="padding: 5px 10px;" onclick="showDeleteModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars(addslashes($row['name']), ENT_QUOTES); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="apple-text-center">No students found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="apple-d-flex apple-justify-between apple-align-center" style="margin-top: 30px;">
            <div>
                <p>Showing <?php echo min(($page - 1) * $limit + 1, $total_students); ?>-<?php echo min($page * $limit, $total_students); ?> of <?php echo $total_students; ?> students</p>
            </div>
            <div class="apple-d-flex apple-gap-10">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="apple-btn apple-btn-secondary">
                        <i class="fas fa-angle-left"></i> Previous
                    </a>
                <?php endif; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="apple-btn apple-btn-secondary">
                        Next <i class="fas fa-angle-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
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
                <p class="apple-modal-warning">This action cannot be undone.</p>
                
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
