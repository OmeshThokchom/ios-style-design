<?php
session_start();
require_once 'config/db.php';

// Get total number of students
$total_students = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'];

// Get unique courses
$unique_courses = $conn->query("SELECT COUNT(DISTINCT course) AS total FROM students WHERE course IS NOT NULL AND course != ''")->fetch_assoc()['total'];

// Get students added in the last 30 days
$recent_students = $conn->query("SELECT COUNT(*) AS total FROM students WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_assoc()['total'];

// Get course distribution
$course_distribution = [];
$course_sql = "SELECT course, COUNT(*) as count FROM students WHERE course IS NOT NULL AND course != '' GROUP BY course ORDER BY count DESC LIMIT 5";
$course_result = $conn->query($course_sql);
while ($row = $course_result->fetch_assoc()) {
    $course_distribution[] = $row;
}

// Get monthly enrollment trend for the last 6 months
$enrollment_trend = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $month_name = date('M Y', strtotime("-$i months"));
    $month_start = date('Y-m-01', strtotime("-$i months"));
    $month_end = date('Y-m-t', strtotime("-$i months"));
    
    $enrollment_sql = "SELECT COUNT(*) as count FROM students WHERE created_at BETWEEN '$month_start' AND '$month_end'";
    $enrollment_count = $conn->query($enrollment_sql)->fetch_assoc()['count'];
    
    $enrollment_trend[] = [
        'month' => $month_name,
        'count' => $enrollment_count
    ];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - iOS Style</title>
    <link rel="stylesheet" href="assets/css/apple.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                
                // Update chart colors
                updateChartColors();
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
            
            // Initialize charts
            initCharts();
        });
        
        function initCharts() {
            // Course Distribution Chart
            const courseCtx = document.getElementById('courseChart').getContext('2d');
            window.courseChart = new Chart(courseCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode(array_column($course_distribution, 'course')); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_column($course_distribution, 'count')); ?>,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                font: {
                                    family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif',
                                    size: 12
                                }
                            }
                        },
                        title: {
                            display: true,
                            text: 'Course Distribution',
                            font: {
                                family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif',
                                size: 16,
                                weight: 'bold'
                            }
                        }
                    }
                }
            });
            
            // Enrollment Trend Chart
            const trendCtx = document.getElementById('trendChart').getContext('2d');
            window.trendChart = new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_column($enrollment_trend, 'month')); ?>,
                    datasets: [{
                        label: 'New Students',
                        data: <?php echo json_encode(array_column($enrollment_trend, 'count')); ?>,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 2,
                        tension: 0.3,
                        pointRadius: 4,
                        pointBackgroundColor: 'rgba(75, 192, 192, 1)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(200, 200, 200, 0.1)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Monthly Enrollment Trend',
                            font: {
                                family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif',
                                size: 16,
                                weight: 'bold'
                            }
                        },
                        legend: {
                            display: false
                        }
                    }
                }
            });
            
            updateChartColors();
        }
        
        function updateChartColors() {
            const isDarkMode = document.body.classList.contains('dark-theme');
            const textColor = isDarkMode ? '#ffffff' : '#333333';
            
            // Update Course Chart
            if (window.courseChart) {
                window.courseChart.options.plugins.legend.labels.color = textColor;
                window.courseChart.options.plugins.title.color = textColor;
                window.courseChart.update();
            }
            
            // Update Trend Chart
            if (window.trendChart) {
                window.trendChart.options.plugins.title.color = textColor;
                window.trendChart.options.scales.y.ticks = { color: textColor };
                window.trendChart.options.scales.x.ticks = { color: textColor };
                window.trendChart.update();
            }
        }
    </script>
    <style>
        .analytics-card {
            height: 350px;
            margin-bottom: 30px;
            position: relative;
        }
        
        .stat-card {
            text-align: center;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .stat-card i {
            font-size: 2.5em;
            margin-bottom: 15px;
            color: #4CAF50;
        }
        
        .stat-card .stat-value {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .stat-card .stat-label {
            font-size: 0.9em;
            color: #888;
        }
        
        body.dark-theme .stat-card .stat-label {
            color: #aaa;
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
            <li class="apple-nav-item"><a href="analytics.php" class="apple-nav-link active">Analytics</a></li>
            <li class="apple-nav-item"><a href="backup.php" class="apple-nav-link">Backup</a></li>
            <li class="apple-nav-item"><a href="documentation.php" class="apple-nav-link">Documentation</a></li>
        </ul>
    </nav>

    <div class="apple-container">
        <h1 class="apple-section-title">Analytics Dashboard</h1>
        
        <!-- Stats Summary -->
        <div class="apple-row">
            <div class="apple-col-md-4">
                <div class="apple-card stat-card">
                    <i class="fas fa-user-graduate"></i>
                    <div class="stat-value"><?php echo $total_students; ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
            </div>
            <div class="apple-col-md-4">
                <div class="apple-card stat-card">
                    <i class="fas fa-book"></i>
                    <div class="stat-value"><?php echo $unique_courses; ?></div>
                    <div class="stat-label">Unique Courses</div>
                </div>
            </div>
            <div class="apple-col-md-4">
                <div class="apple-card stat-card">
                    <i class="fas fa-user-plus"></i>
                    <div class="stat-value"><?php echo $recent_students; ?></div>
                    <div class="stat-label">New in Last 30 Days</div>
                </div>
            </div>
        </div>
        
        <!-- Charts -->
        <div class="apple-row">
            <div class="apple-col-md-6">
                <div class="apple-card analytics-card">
                    <canvas id="courseChart"></canvas>
                </div>
            </div>
            <div class="apple-col-md-6">
                <div class="apple-card analytics-card">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Course Distribution Table -->
        <div class="apple-row">
            <div class="apple-col-md-12">
                <div class="apple-card">
                    <h2 class="apple-card-title">Course Distribution</h2>
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
                                <?php foreach ($course_distribution as $course): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($course['course']); ?></td>
                                    <td><?php echo $course['count']; ?></td>
                                    <td><?php echo round(($course['count'] / $total_students) * 100, 1); ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
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
