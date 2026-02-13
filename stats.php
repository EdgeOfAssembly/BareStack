<?php
/**
 * Stats API Endpoint
 * 
 * Returns system statistics as JSON for dashboard live updates.
 * 
 * Security Features:
 * - Session validation (must be logged in)
 * - JSON output only
 * - Error handling to prevent information disclosure
 */

require_once 'includes/session.php';
require_once 'includes/security.php';

// Set security headers
setSecurityHeaders();

// Initialize secure session
initSecureSession();

// Require authentication - only logged-in users can access stats
requireLogin();

// Set JSON content type
header('Content-Type: application/json');

try {
    // CPU cores and load
    $cpu_cores = 0;
    if (file_exists('/proc/cpuinfo')) {
        foreach (file('/proc/cpuinfo', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (strpos($line, 'processor') === 0) $cpu_cores++;
        }
    }
    
    $load = sys_getloadavg();
    $load1 = round($load[0], 2);
    $load_percent = $cpu_cores > 0 ? round(($load1 / $cpu_cores) * 100) : 0;

    // CPU temperature
    $cpu_temp = 'N/A';
    $temps = [];
    foreach (glob('/sys/class/thermal/thermal_zone*/temp') as $file) {
        if (is_readable($file) && $val = (int)trim(@file_get_contents($file))) {
            $temps[] = $val / 1000;
        }
    }
    if ($temps) $cpu_temp = round(array_sum($temps) / count($temps), 1);

    // Memory information
    $mem = [];
    if (file_exists('/proc/meminfo')) {
        foreach (file('/proc/meminfo', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (preg_match('/^(Mem|Swap)(Total|Free|Available):\s+(\d+)/', $line, $m)) {
                $mem[$m[1].$m[2]] = round($m[3] / 1024 / 1024, 2);
            }
        }
    }
    
    $mem_total = $mem['MemTotal'] ?? 0;
    $mem_available = $mem['MemAvailable'] ?? $mem['MemFree'] ?? 0;
    $mem_used = round($mem_total - $mem_available, 2);
    $mem_percent = $mem_total > 0 ? round(($mem_used / $mem_total) * 100) : 0;

    $swap_total = $mem['SwapTotal'] ?? 0;
    $swap_free = $mem['SwapFree'] ?? 0;
    $swap_used = round($swap_total - $swap_free, 2);
    $swap_percent = $swap_total > 0 ? round(($swap_used / $swap_total) * 100) : 'N/A';

    // Disk information
    $disk_total = round(@disk_total_space('/') / (1024 ** 3), 2);
    $disk_free = round(@disk_free_space('/') / (1024 ** 3), 2);
    $disk_used = round($disk_total - $disk_free, 2);
    $disk_percent = $disk_total > 0 ? round(($disk_used / $disk_total) * 100) : 0;

    // Backup drive (optional)
    $backup_path = '/run/media/wizard/Backup24TB';
    $backup_exists = is_dir($backup_path);
    $backup_total = $backup_used = $backup_free = $backup_percent = 0;
    if ($backup_exists) {
        $backup_total = round(@disk_total_space($backup_path) / (1024 ** 3), 2);
        $backup_free = round(@disk_free_space($backup_path) / (1024 ** 3), 2);
        $backup_used = round($backup_total - $backup_free, 2);
        $backup_percent = $backup_total > 0 ? round(($backup_used / $backup_total) * 100) : 0;
    }

    // Network I/O
    $net = ['rx' => 0, 'tx' => 0];
    if (file_exists('/proc/net/dev')) {
        $lines = @file('/proc/net/dev', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines) {
            foreach ($lines as $line) {
                if (preg_match('/^\s*(\w+):/', $line, $m) && $m[1] !== 'lo') {
                    $parts = preg_split('/\s+/', trim($line));
                    $net['rx'] += (int)($parts[1] ?? 0);
                    $net['tx'] += (int)($parts[9] ?? 0);
                }
            }
        }
    }
    
    function human_bytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes > 1024 && $i < count($units)-1; $i++) $bytes /= 1024;
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    $net_rx = human_bytes($net['rx']);
    $net_tx = human_bytes($net['tx']);

    // System uptime
    $uptime_sec = 0;
    if (file_exists('/proc/uptime')) {
        $uptime_sec = (int)explode(' ', @file_get_contents('/proc/uptime'))[0];
    }
    $days = floor($uptime_sec / 86400);
    $hours = floor(($uptime_sec % 86400) / 3600);
    $minutes = floor(($uptime_sec % 3600) / 60);
    $uptime = "{$days} days, {$hours} hours, {$minutes} minutes";

    // Output JSON response
    echo json_encode([
        'cpu_cores' => $cpu_cores,
        'cpu_temp' => $cpu_temp,
        'load1' => $load1,
        'load_percent' => $load_percent,
        'mem_used' => $mem_used,
        'mem_total' => $mem_total,
        'mem_available' => $mem_available,
        'mem_percent' => $mem_percent,
        'swap_used' => $swap_used,
        'swap_total' => $swap_total,
        'swap_percent' => $swap_percent,
        'disk_used' => $disk_used,
        'disk_total' => $disk_total,
        'disk_free' => $disk_free,
        'disk_percent' => $disk_percent,
        'backup_exists' => $backup_exists,
        'backup_used' => $backup_used,
        'backup_total' => $backup_total,
        'backup_free' => round($backup_free, 2),
        'backup_percent' => $backup_percent,
        'net_rx' => $net_rx,
        'net_tx' => $net_tx,
        'uptime' => $uptime,
        'server_time' => date('Y-m-d H:i:s')
    ], JSON_THROW_ON_ERROR);
    
} catch (Exception $e) {
    // Log error but don't expose details to user
    error_log("Stats API error: " . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to retrieve system stats'
    ]);
}