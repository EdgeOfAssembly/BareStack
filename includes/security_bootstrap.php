<?php
/**
 * Security Bootstrap - MUST be included at the top of EVERY public PHP file
 * 
 * This file provides protection when using FrankenPHP php-server mode
 * which doesn't use Caddyfile security rules.
 */

// Prevent direct access to this file
if (basename($_SERVER['PHP_SELF']) === 'security_bootstrap.php') {
    http_response_code(403);
    die('Access denied');
}

// Get the requested URI
$request_uri = $_SERVER['REQUEST_URI'] ?? '';
$script_name = $_SERVER['SCRIPT_NAME'] ?? '';

// Security: Block access if someone tries to access sensitive files directly
$blocked_files = [
    'config.php',
    'router.php', 
    'security_bootstrap.php',
    'Caddyfile',
    '.htaccess',
    '.gitignore',
];

foreach ($blocked_files as $file) {
    if (basename($script_name) === $file) {
        http_response_code(403);
        header('HTTP/1.0 403 Forbidden');
        die('<h1>403 Forbidden</h1><p>Access denied</p>');
    }
}

// Additional security checks for direct file requests
if (preg_match('/\.(db|sqlite|sqlite3|log|py|sh|bak|backup|old)$/i', $request_uri)) {
    http_response_code(403);
    header('HTTP/1.0 403 Forbidden');
    die('<h1>403 Forbidden</h1><p>Access denied</p>');
}

// Block access to includes and data directories
if (preg_match('#^/(includes|data|\.git)/#', $request_uri)) {
    http_response_code(403);
    header('HTTP/1.0 403 Forbidden');
    die('<h1>403 Forbidden</h1><p>Access denied</p>');
}
