<?php
/**
 * Includes Directory Protection
 * 
 * This file blocks direct browser access to the includes directory.
 */

// Block direct access
http_response_code(403);
header('HTTP/1.0 403 Forbidden');
die('Access denied');
