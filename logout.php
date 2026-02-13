<?php
/**
 * Logout Page
 * 
 * Properly destroys the session and logs out the user.
 * Implements secure session cleanup to prevent session reuse.
 */

require_once 'includes/session.php';

// Initialize session
initSecureSession();

// Log the logout event
if (isset($_SESSION['username'])) {
    error_log("User logged out: " . $_SESSION['username'] . " from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
}

// Destroy session using secure method
logoutUser();

// Redirect to login page
header("Location: login.php");
exit;
