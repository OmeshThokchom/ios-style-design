-- Create database if not exists
CREATE DATABASE IF NOT EXISTS student_management;
USE student_management;

-- Create students table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    course VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create user with proper permissions
CREATE USER IF NOT EXISTS 'student_admin'@'localhost' IDENTIFIED BY 'omesh2001';
GRANT ALL PRIVILEGES ON student_management.* TO 'student_admin'@'localhost';
FLUSH PRIVILEGES;
