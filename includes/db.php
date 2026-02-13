<?php
/**
 * Database Connection Module
 * 
 * This file provides a centralized database connection function
 * using PDO with SQLite. This prevents code duplication across
 * multiple files and ensures consistent database setup.
 */

require_once __DIR__ . '/../config.php';

/**
 * Get a PDO database connection with proper configuration
 * 
 * Creates or connects to the SQLite database and ensures the
 * users table exists with the correct schema.
 * 
 * @return PDO Database connection instance
 * @throws PDOException if connection fails
 */
function getDatabase() {
    try {
        // Create PDO connection to SQLite database
        $db = new PDO('sqlite:' . DB_PATH);
        
        // Set error mode to exceptions for better error handling
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create users table if it doesn't exist
        // Using UNIQUE constraint on username to prevent duplicates
        $db->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            hash TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        return $db;
    } catch (PDOException $e) {
        // Log error to PHP error log (don't expose to user)
        error_log("Database connection error: " . $e->getMessage());
        
        // Throw a generic error to avoid information disclosure
        throw new Exception("Database connection failed. Please try again later.");
    }
}
