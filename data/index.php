<?php
/**
 * Database File Protection
 * 
 * This file blocks direct browser access to the database.
 * Place this file as index.php in the data directory.
 */

// Block direct access
http_response_code(403);
header('HTTP/1.0 403 Forbidden');
die('Access denied');
