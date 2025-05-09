# Technical Documentation - Student Management System

## 1. Database Layer Details

### Connection Management (`config/db.php`)
```php
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
}
```
- Uses MySQLi object-oriented approach
- Exception handling for connection failures
- Auto-closes connection via shutdown function
- Persistent connection management

Database Schema Details:
```sql
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,  -- Unique identifier, auto-incrementing
    name VARCHAR(100) NOT NULL,         -- Required, max 100 chars
    email VARCHAR(100) NOT NULL,        -- Required, max 100 chars
    phone VARCHAR(15),                  -- Optional, formatted for international
    course VARCHAR(50),                 -- Optional, current enrollment
    created_at TIMESTAMP               -- Auto-sets on creation
);
```

Index Optimization:
- Primary key on `id`
- Index on `email` for duplicate checks
- Index on `created_at` for sorting

## 2. Authentication & Security

### SQL Injection Prevention
```php
// Unsafe Method (Never Use):
$query = "SELECT * FROM students WHERE id = " . $_GET['id'];

// Safe Method (Always Use):
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $_GET['id']);
```

### XSS Prevention Methods
```php
// Output Sanitization
htmlspecialchars($data, ENT_QUOTES, 'UTF-8')

// Input Validation
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
$phone = preg_replace("/[^0-9]/", "", $_POST['phone']);
```

## 3. Theme System Architecture

### Cookie Management
```javascript
// Cookie Structure
{
    name: 'theme',
    value: 'dark' | 'light',
    expiry: 365 days,
    path: '/'
}

// Theme Toggle Logic
function toggleTheme() {
    1. Check current theme
    2. Toggle body class
    3. Update cookie
    4. Trigger UI updates
}
```

### CSS Variables Inheritance
```css
:root {
    /* Light Theme */
    --bg-primary: rgba(255, 255, 255, 0.95);
    --text-primary: #2c3e50;
    
    /* Dark Theme */
    .dark-theme {
        --bg-primary: rgba(30, 30, 30, 0.95);
        --text-primary: #ffffff;
    }
}
```

## 4. CRUD Operations Deep Dive

### Create Operation (`add_student.php`)
```php
Process Flow:
1. Form Submission
   - Method: POST
   - Validation: Server-side
   - Sanitization: Input cleaning

2. Data Validation
   - Required fields check
   - Email format validation
   - Phone number formatting
   - Course name sanitization

3. Database Insertion
   - Prepared statement creation
   - Parameter binding
   - Execute statement
   - Error handling

4. Response Handling
   - Success/Error message setting
   - Session message storage
   - Redirect with status
```

### Read Operation (`view_students.php`)
```php
Pagination Implementation:
1. Page Size Calculation
   $limit = 10;  // Configurable
   $offset = ($page - 1) * $limit;

2. Query Optimization
   - Uses LIMIT and OFFSET
   - Orders by created_at DESC
   - Includes only necessary columns

3. Performance Considerations
   - Indexed column usage
   - Result set size control
   - Memory management
```

### Search Functionality
```javascript
Search Algorithm:
1. Input Event Handling
   - Debouncing (300ms)
   - Case-insensitive comparison
   - Real-time filtering

2. DOM Manipulation
   - Table row visibility toggle
   - Performance optimization
   - Memory efficient approach
```

### Update Operation (`edit_student.php`)
```php
State Management:
1. Initial State
   - Fetch current data
   - Populate form fields
   - Store original values

2. Update Process
   - Compare changed fields
   - Validate modifications
   - Optimize update query

3. Transaction Handling
   - Begin transaction
   - Update operation
   - Commit/Rollback logic
```

### Delete Operation (`delete_student.php`)
```php
Safety Measures:
1. Confirmation System
   - Modal dialog
   - Prevent accidental deletion
   - Record verification

2. Cascade Handling
   - Check dependencies
   - Maintain data integrity
   - Clean up related records
```

## 5. Performance Optimizations

### Query Optimization
```sql
-- Efficient Pagination Query
SELECT SQL_CALC_FOUND_ROWS *
FROM students
ORDER BY created_at DESC
LIMIT ?, ?

-- Total Count (Single Query)
SELECT FOUND_ROWS();
```

### Caching Strategies
```php
// Result Set Caching
$cache_key = "students_page_" . $page;
$result = cache()->get($cache_key);

if (!$result) {
    // Database query
    // Store in cache
    cache()->set($cache_key, $result, 3600);
}
```

### Front-end Performance
```javascript
// DOM Updates
- Use DocumentFragment for batch updates
- Debounce search function
- Lazy load images/assets
- Minimize repaints/reflows
```

## 6. Error Handling System

### Error Types & Handling
```php
try {
    // Database Operations
    if (!$stmt->execute()) {
        throw new DatabaseException($stmt->error);
    }
    
    // Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new ValidationException("Invalid email format");
    }
    
    // File Operations
    if (!is_writable($directory)) {
        throw new FileSystemException("Permission denied");
    }
} catch (Exception $e) {
    // Log error
    error_log($e->getMessage());
    // Set user-friendly message
    $_SESSION['error'] = "Operation failed";
}
```

## 7. Notification System Architecture

### Message Queue System
```php
Class NotificationManager {
    private $queue = [];
    
    public function addMessage($message, $type) {
        $this->queue[] = [
            'message' => $message,
            'type' => $type,
            'timestamp' => time()
        ];
    }
    
    public function display() {
        foreach ($this->queue as $notification) {
            // Render notification
            // Auto-dismiss after 3s
            // Animation handling
        }
    }
}
```

---
Last Updated: 06-05-2025
Version: 1.0.0
