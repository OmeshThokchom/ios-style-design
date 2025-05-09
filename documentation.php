<?php
// Include database connection
include('config/db.php');

// Scan the docs directory for markdown files
$validDocs = [];
$docsDir = 'docs';

if (is_dir($docsDir)) {
    $files = scandir($docsDir);
    foreach ($files as $file) {
        // Only process markdown files
        if (pathinfo($file, PATHINFO_EXTENSION) === 'md') {
            $baseName = pathinfo($file, PATHINFO_FILENAME);
            // Convert filename to title (replace underscores with spaces and capitalize words)
            $title = ucwords(str_replace('_', ' ', $baseName));
            $validDocs[$baseName] = $title;
        }
    }
}

// Sort docs alphabetically by title
asort($validDocs);

// If no docs found, add a placeholder
if (empty($validDocs)) {
    $validDocs['no_docs'] = 'No Documentation Found';
}

// Get the document to display (default to first document or system_documentation if exists)
$doc = isset($_GET['doc']) ? $_GET['doc'] : '';

// If no doc specified or invalid doc, use the first one in the list or system_documentation if it exists
if (empty($doc) || !array_key_exists($doc, $validDocs)) {
    if (array_key_exists('system_documentation', $validDocs)) {
        $doc = 'system_documentation';
    } else {
        // Get the first key from the validDocs array
        reset($validDocs);
        $doc = key($validDocs);
    }
}

$filePath = "docs/{$doc}.md";
$fileContent = '';

// Read the Markdown file if it exists
if (file_exists($filePath)) {
    $fileContent = file_get_contents($filePath);
} else {
    $fileContent = "# Document Not Found\n\nThe requested documentation file could not be found.";
}

// Simple function to convert Markdown to HTML (basic implementation)
function parseMarkdown($text) {
    // Headers
    $text = preg_replace('/^# (.*?)$/m', '<h1>$1</h1>', $text);
    $text = preg_replace('/^## (.*?)$/m', '<h2>$1</h2>', $text);
    $text = preg_replace('/^### (.*?)$/m', '<h3>$1</h3>', $text);
    $text = preg_replace('/^#### (.*?)$/m', '<h4>$1</h4>', $text);
    
    // Bold and Italic
    $text = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $text);
    $text = preg_replace('/\*(.*?)\*/s', '<em>$1</em>', $text);
    
    // Lists
    $text = preg_replace('/^- (.*?)$/m', '<li>$1</li>', $text);
    $text = str_replace(array("<li>", "</li>\n<li>", "</li>\n"), array("<ul>\n<li>", "</li>\n<li>", "</li>\n</ul>\n"), $text);
    
    // Code blocks
    $text = preg_replace_callback('/```([a-z]*)\n(.*?)\n```/ms', function($matches) {
        $language = !empty($matches[1]) ? " class=\"language-{$matches[1]}\"" : "";
        return "<pre><code{$language}>" . htmlspecialchars($matches[2]) . "</code></pre>";
    }, $text);
    
    // Inline code
    $text = preg_replace('/`(.*?)`/', '<code>$1</code>', $text);
    
    // Links
    $text = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2">$1</a>', $text);
    
    // Paragraphs
    $text = preg_replace('/\n\n(.*?)\n\n/s', "\n\n<p>$1</p>\n\n", $text);
    
    // Horizontal rule
    $text = preg_replace('/^\-\-\-$/m', '<hr>', $text);
    
    return $text;
}

// Get dark mode preference from cookie
$darkMode = isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $validDocs[$doc]; ?> - Student Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/atom-one-dark.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
    <style>
        :root {
            --bg-primary: #f5f5f7;
            --bg-secondary: #ffffff;
            --text-primary: #1d1d1f;
            --text-secondary: #6e6e73;
            --accent-color: #0071e3;
            --border-color: #d2d2d7;
            --code-bg: #f6f8fa;
            --blockquote-bg: #f9f9f9;
        }
        
        .dark-theme {
            --bg-primary: #000000;
            --bg-secondary: #1d1d1f;
            --text-primary: #f5f5f7;
            --text-secondary: #86868b;
            --border-color: #424245;
            --code-bg: #2c2c2e;
            --blockquote-bg: #1c1c1e;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Open Sans", sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            margin: 0;
            padding: 0;
            line-height: 1.6;
            transition: background-color 0.3s, color 0.3s;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            display: flex;
            flex-wrap: wrap;
        }
        
        .sidebar {
            width: 250px;
            padding: 20px;
            background-color: var(--bg-secondary);
            border-radius: 16px;
            margin-right: 30px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 20px;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
        }
        
        .content {
            flex: 1;
            min-width: 300px;
            background-color: var(--bg-secondary);
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            background-color: var(--bg-secondary);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
        }
        
        .theme-toggle {
            background: none;
            border: none;
            color: var(--text-primary);
            cursor: pointer;
            font-size: 20px;
            transition: transform 0.3s;
            padding: 8px;
            border-radius: 50%;
        }
        
        .theme-toggle:hover {
            background-color: rgba(0, 0, 0, 0.05);
            transform: scale(1.1);
        }
        
        .dark-theme .theme-toggle:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .nav-link {
            display: block;
            padding: 10px 15px;
            margin-bottom: 5px;
            text-decoration: none;
            color: var(--text-primary);
            border-radius: 8px;
            transition: all 0.2s;
        }
        
        .nav-link:hover, .nav-link.active {
            background-color: var(--accent-color);
            color: white;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            padding: 8px 12px;
            margin-bottom: 20px;
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.2s;
        }
        
        .back-link:hover {
            background-color: rgba(0, 113, 227, 0.1);
        }
        
        .dark-theme .back-link:hover {
            background-color: rgba(0, 113, 227, 0.2);
        }
        
        /* Markdown Styling */
        .markdown-content h1 {
            font-size: 2em;
            margin-top: 0;
            margin-bottom: 0.5em;
            padding-bottom: 0.3em;
            border-bottom: 1px solid var(--border-color);
        }
        
        .markdown-content h2 {
            font-size: 1.5em;
            margin-top: 1.5em;
            margin-bottom: 0.5em;
            padding-bottom: 0.3em;
            border-bottom: 1px solid var(--border-color);
        }
        
        .markdown-content h3 {
            font-size: 1.25em;
            margin-top: 1.5em;
            margin-bottom: 0.5em;
        }
        
        .markdown-content h4 {
            font-size: 1em;
            margin-top: 1.5em;
            margin-bottom: 0.5em;
        }
        
        .markdown-content ul, .markdown-content ol {
            padding-left: 2em;
            margin-bottom: 1em;
        }
        
        .markdown-content li {
            margin-bottom: 0.5em;
        }
        
        .markdown-content p {
            margin-bottom: 1em;
        }
        
        .markdown-content code {
            font-family: 'SFMono-Regular', 'Menlo', 'Monaco', 'Consolas', 'Liberation Mono', 'Courier New', monospace;
            background-color: var(--code-bg);
            padding: 0.2em 0.4em;
            border-radius: 3px;
            font-size: 0.9em;
        }
        
        .markdown-content pre {
            background-color: var(--code-bg);
            border-radius: 8px;
            padding: 16px;
            overflow-x: auto;
            margin-bottom: 1em;
        }
        
        .markdown-content pre code {
            background: none;
            padding: 0;
            border-radius: 0;
            font-size: 0.9em;
            color: inherit;
        }
        
        .markdown-content blockquote {
            border-left: 4px solid var(--accent-color);
            background-color: var(--blockquote-bg);
            margin: 0 0 1em 0;
            padding: 0.5em 1em;
            border-radius: 0 4px 4px 0;
        }
        
        .markdown-content hr {
            height: 1px;
            background-color: var(--border-color);
            border: none;
            margin: 2em 0;
        }
        
        .markdown-content a {
            color: var(--accent-color);
            text-decoration: none;
        }
        
        .markdown-content a:hover {
            text-decoration: underline;
        }
        
        .markdown-content img {
            max-width: 100%;
            border-radius: 8px;
            margin: 1em 0;
        }
        
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                margin-right: 0;
                margin-bottom: 20px;
                position: static;
            }
            
            .content {
                width: 100%;
            }
        }
    </style>
</head>
<body class="<?php echo $darkMode ? 'dark-theme' : ''; ?>">
    <div class="header">
        <h1>Student Management System Documentation</h1>
        <button class="theme-toggle" onclick="toggleTheme()">
            <i class="fas <?php echo $darkMode ? 'fa-sun' : 'fa-moon'; ?>"></i>
        </button>
    </div>
    
    <div class="container">
        <div class="sidebar">
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left" style="margin-right: 8px;"></i>
                Back to Dashboard
            </a>
            
            <h3>Documentation</h3>
            <?php foreach ($validDocs as $docKey => $docTitle): ?>
                <a href="documentation.php?doc=<?php echo $docKey; ?>" 
                   class="nav-link <?php echo $doc === $docKey ? 'active' : ''; ?>">
                    <?php echo $docTitle; ?>
                </a>
            <?php endforeach; ?>
        </div>
        
        <div class="content">
            <div class="markdown-content">
                <?php echo parseMarkdown($fileContent); ?>
            </div>
        </div>
    </div>
    
    <script>
        // Initialize syntax highlighting
        document.addEventListener('DOMContentLoaded', (event) => {
            document.querySelectorAll('pre code').forEach((block) => {
                hljs.highlightElement(block);
            });
        });
        
        // Theme toggle functionality
        function toggleTheme() {
            const body = document.body;
            const icon = document.querySelector('.theme-toggle i');
            
            body.classList.toggle('dark-theme');
            
            if (body.classList.contains('dark-theme')) {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
                setCookie('theme', 'dark', 365);
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
                setCookie('theme', 'light', 365);
            }
        }
        
        // Cookie functions
        function setCookie(name, value, days) {
            let expires = "";
            if (days) {
                const date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/";
        }
    </script>
</body>
</html>
