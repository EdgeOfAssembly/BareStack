<?php
/**
 * Router for PHP Built-in Server
 * 
 * This router provides security by blocking access to sensitive files
 * when using PHP's built-in development server (php -S).
 * 
 * Usage: php -S localhost:8080 router.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = $_SERVER['DOCUMENT_ROOT'] . $uri;

// Security: Block access to sensitive files and directories
$blocked_patterns = [
    '/\.db$/',           // Database files
    '/\.sqlite/',        // SQLite files
    '/\.htaccess$/',     // Apache config
    '/\.git/',           // Git directory
    '/\.env$/',          // Environment files
    '/\.log$/',          // Log files
    '/\.py$/',           // Python scripts
    '/\.sh$/',           // Shell scripts
    '/^\/config\.php$/', // Config file
    '/^\/data\//',       // Data directory
    '/^\/includes\//',   // Includes directory
    '/\.bak$/',          // Backup files
    '/\.backup$/',
    '/\.old$/',
    '/\.save$/',
    '/~$/',
];

foreach ($blocked_patterns as $pattern) {
    if (preg_match($pattern, $uri)) {
        http_response_code(403);
        header('HTTP/1.0 403 Forbidden');
        die('<h1>403 Forbidden</h1><p>Access to this resource is denied.</p>');
    }
}

// If it's a directory, don't allow directory listing
if (is_dir($path)) {
    // Check for index.php or index.html
    if (file_exists($path . '/index.php')) {
        require $path . '/index.php';
        return true;
    } elseif (file_exists($path . '/index.html')) {
        return false; // Let server handle HTML
    }
    
    // No index file - deny access
    http_response_code(403);
    die('<h1>403 Forbidden</h1><p>Directory listing is disabled.</p>');
}

// For PHP files, let the server execute them
if (file_exists($path) && preg_match('/\.php$/', $path)) {
    return false; // Let PHP server handle it
}

// For other existing files, serve them
if (file_exists($path)) {
    return false;
}

// File not found
http_response_code(404);
die('<h1>404 Not Found</h1><p>The requested resource was not found.</p>');
