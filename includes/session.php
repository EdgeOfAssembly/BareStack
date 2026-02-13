<?php
/**
 * Session Management Module
 * 
 * This file provides secure session initialization and management functions.
 * Sessions are configured with security best practices to prevent hijacking.
 */

require_once __DIR__ . '/../config.php';

/**
 * Initialize a secure session
 * 
 * Configures session with security parameters:
 * - httponly: Prevents JavaScript access to session cookie
 * - secure: Only send cookie over HTTPS (detects automatically)
 * - samesite: Prevents CSRF attacks
 * 
 * Session regeneration should be performed on:
 * - Login (to prevent session fixation)
 * - Privilege escalation
 * - Sensitive operations
 * 
 * @return void
 */
function initSecureSession() {
    // Check if session is already started
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    
    // Set secure session parameters before starting session
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'domain' => '',
        // Only set secure flag if HTTPS is being used
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        // Prevent JavaScript access to session cookie (XSS protection)
        'httponly' => true,
        // Prevent CSRF by restricting cookie to same-site requests
        'samesite' => 'Strict'
    ]);
    
    // Start the session
    session_start();
    
    // Regenerate session ID periodically to prevent session fixation
    // This checks if the session is older than 30 minutes
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } elseif (time() - $_SESSION['created'] > 1800) {
        // Session started more than 30 minutes ago
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

/**
 * Check if user is logged in
 * 
 * Verifies that the user has a valid authenticated session.
 * 
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Require authentication
 * 
 * Redirects to login page if user is not authenticated.
 * Use this at the top of protected pages.
 * 
 * @param string $loginPage Path to login page (default: login.php)
 * @return void
 */
function requireLogin($loginPage = 'login.php') {
    if (!isLoggedIn()) {
        header("Location: $loginPage");
        exit;
    }
}

/**
 * Destroy session and logout user
 * 
 * Properly clears all session data and destroys the session.
 * 
 * @return void
 */
function logoutUser() {
    // Unset all session variables
    $_SESSION = array();
    
    // Delete the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy the session
    session_destroy();
}
