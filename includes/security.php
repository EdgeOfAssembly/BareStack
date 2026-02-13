<?php
/**
 * Security Headers Module
 * 
 * This file provides security-related functions including
 * HTTP security headers that protect against common web vulnerabilities.
 */

/**
 * Set HTTP security headers
 * 
 * These headers protect against various attacks:
 * - X-Frame-Options: Prevents clickjacking attacks
 * - X-Content-Type-Options: Prevents MIME-type sniffing
 * - X-XSS-Protection: Enables browser XSS filter (legacy browsers)
 * - Content-Security-Policy: Controls resource loading
 * - Referrer-Policy: Controls referrer information leakage
 * 
 * @return void
 */
function setSecurityHeaders() {
    // Prevent page from being displayed in iframe (clickjacking protection)
    header("X-Frame-Options: DENY");
    
    // Prevent MIME-type sniffing
    header("X-Content-Type-Options: nosniff");
    
    // Enable XSS filter in older browsers
    header("X-XSS-Protection: 1; mode=block");
    
    // Content Security Policy - allows inline styles/scripts for themes
    // In production, consider moving inline scripts to separate files
    header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; img-src 'self' data:");
    
    // Control referrer information sent to other sites
    header("Referrer-Policy: strict-origin-when-cross-origin");
    
    // Prevent caching of sensitive pages
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
}

/**
 * Generate a CSRF token
 * 
 * Creates a cryptographically secure random token for CSRF protection.
 * Tokens should be regenerated after successful form submissions.
 * 
 * @return string 64-character hexadecimal token
 */
function generateCSRFToken() {
    return bin2hex(random_bytes(32));
}

/**
 * Verify CSRF token
 * 
 * Compares submitted token with session token using timing-safe comparison
 * to prevent timing attacks.
 * 
 * @param string $token Token to verify
 * @return bool True if token is valid, false otherwise
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
