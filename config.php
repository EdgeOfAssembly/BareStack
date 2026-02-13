<?php
/**
 * Configuration file for the PHP Dashboard application
 * 
 * This file contains all application-wide configuration constants.
 * Modify these values to customize the application behavior.
 */

// Database Configuration
// Path to SQLite database file (in data directory for security)
// The data directory should be outside web root in production
define('DB_PATH', __DIR__ . '/data/users.db');

// Security Configuration
// BCRYPT cost factor (10-12 recommended, higher = more secure but slower)
// Cost of 12 means 2^12 iterations (~4096 iterations)
define('BCRYPT_COST', 12);

// Session Configuration
// Session lifetime in seconds (0 = until browser closes)
define('SESSION_LIFETIME', 0);

// Maximum number of failed login attempts before temporary lockout
define('MAX_LOGIN_ATTEMPTS', 5);

// Lockout duration in seconds after max failed attempts
define('LOCKOUT_DURATION', 900); // 15 minutes

// Application Configuration
define('APP_NAME', 'SecureLearn Dashboard');
define('APP_VERSION', '1.0.0');

// Enable/disable features
define('ENABLE_RATE_LIMITING', false); // Set to true to enable rate limiting
