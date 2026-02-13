<?php
/**
 * Input Validation Module
 * 
 * This file provides validation functions for user input.
 * Proper input validation is critical for security.
 */

/**
 * Validate username format
 * 
 * Usernames must be:
 * - Between 3 and 20 characters
 * - Contain only letters, numbers, and underscores
 * - Not be a reserved/blacklisted name
 * 
 * @param string $username Username to validate
 * @return bool True if valid, false otherwise
 */
function validateUsername($username) {
    // Check length
    if (strlen($username) < 3 || strlen($username) > 20) {
        return false;
    }
    
    // Check format (alphanumeric + underscore only)
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        return false;
    }
    
    // Blacklist of reserved usernames (prevent impersonation)
    $blacklist = ['admin', 'root', 'administrator', 'system', 'guest', 'user', 'test'];
    if (in_array(strtolower($username), $blacklist)) {
        return false;
    }
    
    return true;
}

/**
 * Validate password strength
 * 
 * Passwords must be:
 * - Between 8 and 128 characters
 * - Not be too common (optional: implement password dictionary check)
 * 
 * @param string $password Password to validate
 * @return bool True if valid, false otherwise
 */
function validatePassword($password) {
    $length = strlen($password);
    
    // Check length constraints
    if ($length < 8 || $length > 128) {
        return false;
    }
    
    return true;
}

/**
 * Get username validation error message
 * 
 * @param string $username Username that failed validation
 * @return string Error message
 */
function getUsernameError($username) {
    if (strlen($username) < 3) {
        return "Username must be at least 3 characters.";
    }
    if (strlen($username) > 20) {
        return "Username must be no more than 20 characters.";
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        return "Username can only contain letters, numbers, and underscores.";
    }
    $blacklist = ['admin', 'root', 'administrator', 'system', 'guest', 'user', 'test'];
    if (in_array(strtolower($username), $blacklist)) {
        return "This username is reserved and cannot be used.";
    }
    return "Invalid username.";
}

/**
 * Sanitize output for HTML display
 * 
 * Wrapper for htmlspecialchars with safe defaults.
 * Always use this when displaying user input in HTML.
 * 
 * @param string $string String to sanitize
 * @return string Sanitized string
 */
function sanitizeOutput($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
