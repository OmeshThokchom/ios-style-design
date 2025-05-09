# System Documentation - Student Management System

## File Structure & Purpose

### 1. Database Configuration (`config/db.php`)
```php
$servername = "localhost"    // Database host
$username = "student_admin"  // Database user
$password = "omesh2001"     // Database password
$dbname = "student_management" // Database name

Key Functions:
- Database connection handling
- Auto-close connection on script end
```

### 2. Main Page (`index.php`)
```php
Features:
- Theme toggle functionality
- Recent students display (LIMIT 5)
- Action cards for navigation

Key Functions:
- toggleTheme(): Switches between light/dark themes
- setCookie(): Stores theme preference
- getCookie(): Retrieves stored theme
```

### 3. View Students (`view_students.php`)
```php
Variables:
$limit = 10     // Students per page
$page          // Current page number
$offset        // Pagination offset

Functions:
- searchStudents(): Real-time student search
  Input: query (string)
  Process: Filters table rows based on content
  Output: Shows/hides matching rows

Pagination Logic:
offset = (page_number - 1) * items_per_page
```

### 4. Add Student (`add_student.php`)
```php
Form Fields:
- name (required)
- email (required)
- phone
- course

Process Flow:
1. Form submission (POST)
2. Data validation
3. SQL insertion
4. Redirect with success/error message
```

### 5. Edit Student (`edit_student.php`)
```php
Parameters:
- id: Student ID (GET parameter)

Process Flow:
1. Fetch existing student data
2. Display in form
3. Handle form submission (POST)
4. Update database
5. Redirect with status
```

### 6. Delete Student (`delete_student.php`)
```php
Parameters:
- id: Student ID (GET parameter)
- confirm: Deletion confirmation

Security:
- Prepared statements
- Confirmation prompt
```

### 7. Styling (`assets/css/style.css`)
```css
Theme Variables:
--primary-color: Main theme color
--primary-dark: Darker shade
--text-light: Light theme text
--text-dark: Dark theme text

Components:
- Glassmorphism effects
- Responsive breakpoints
- Dark/Light theme variants
```

## Common Functions

### Theme Management
```javascript
toggleTheme():
- Purpose: Switch between dark/light themes
- Process: Toggles 'dark-theme' class on body
- Storage: Uses cookies for persistence

getCookie(name):
- Purpose: Retrieve cookie value
- Input: Cookie name
- Output: Cookie value or empty string

setCookie(name, value, days):
- Purpose: Store theme preference
- Parameters:
  name: Cookie name
  value: Theme value
  days: Expiration
```

### Database Operations
```php
Prepared Statements:
- Used for all database operations
- Prevents SQL injection
- Format: $stmt->bind_param("types", ...variables)

Types:
s: String
i: Integer
d: Double
b: Blob
```

### Security Measures
```php
XSS Prevention:
- htmlspecialchars() on output
- Input validation on forms

SQL Injection:
- Prepared statements
- Parameter binding
- Type checking
```

## Pagination Logic
```php
Formula:
total_pages = ceil(total_records / records_per_page)
offset = (current_page - 1) * records_per_page

Example:
For 25 records, 10 per page:
- total_pages = ceil(25/10) = 3
- Page 1 offset = (1-1)*10 = 0
- Page 2 offset = (2-1)*10 = 10
```

## Error Handling
```php
Types:
1. Database Errors
   - Connection failures
   - Query errors
2. Form Validation
   - Required fields
   - Email format
   - Phone format
3. File Operations
   - Permission issues
   - Upload errors
```

## Notification System
```php
Session-based messages:
$_SESSION['message'] = 'Success/Error message';
$_SESSION['message_type'] = 'success/error';

Display:
- iOS-style notification
- Auto-dismiss after 3 seconds
- Position: top-center
```

---

## Quick Reference

### URL Parameters
```
view_students.php?page=X
- X: Page number

edit_student.php?id=X
- X: Student ID

delete_student.php?id=X
- X: Student ID
```

### Database Tables
```sql
students
- id (INT, AUTO_INCREMENT)
- name (VARCHAR(100))
- email (VARCHAR(100))
- phone (VARCHAR(15))
- course (VARCHAR(50))
- created_at (TIMESTAMP)
```

### Status Codes
```
Success Messages:
- Student added successfully
- Student updated successfully
- Student deleted successfully

Error Messages:
- Database connection error
- Invalid input
- Operation failed
```

---
Last Updated: [Current Date]
