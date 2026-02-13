<?php
/**
 * Dashboard Page - System Monitoring Interface
 * 
 * This is the main application page shown after successful login.
 * Displays real-time system statistics and allows theme customization.
 * 
 * SECURITY FEATURES DEMONSTRATED:
 * 
 * 1. SESSION AUTHENTICATION
 *    - Requires valid logged-in session to access
 *    - Uses requireLogin() to enforce authentication
 *    - Redirects to login.php if not authenticated
 * 
 * 2. XSS (Cross-Site Scripting) PREVENTION
 *    - All user-controlled data is escaped before display
 *    - Uses sanitizeOutput() wrapper around htmlspecialchars()
 *    - Prevents malicious JavaScript from executing
 *    - Example: Username "<script>alert('XSS')</script>" displays as text
 * 
 * 3. SECURITY HEADERS
 *    - setSecurityHeaders() adds protective HTTP headers
 *    - X-Frame-Options: Prevents clickjacking
 *    - Content-Security-Policy: Restricts resource loading
 *    - See includes/security.php for full list
 * 
 * FULLSTACK LEARNING OPPORTUNITIES:
 * 
 * Frontend:
 * - Vanilla JavaScript (no frameworks)
 * - Fetch API for AJAX requests
 * - DOM manipulation
 * - LocalStorage for theme persistence
 * - CSS animations (themes)
 * 
 * Backend:
 * - PHP session management
 * - System information gathering
 * - Real-time data updates
 * - JSON API responses
 * 
 * This page demonstrates that you don't need React/Vue/Angular
 * to build interactive, real-time web applications!
 */

require_once 'includes/session.php';
require_once 'includes/security.php';
require_once 'includes/validation.php';

// Set security headers
setSecurityHeaders();

// Initialize secure session
initSecureSession();

// Require authentication
requireLogin();

$username = sanitizeOutput($_SESSION['username'] ?? 'User');
$hostname = gethostname();
$os = php_uname('s') . ' ' . php_uname('r');

// Stats calculation (initial render)
$cpu_cores = 0;
foreach (file('/proc/cpuinfo', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (strpos($line, 'processor') === 0) $cpu_cores++;
}
$load = sys_getloadavg();
$load1 = round($load[0], 2);
$load_percent = $cpu_cores > 0 ? round(($load1 / $cpu_cores) * 100) : 0;

$cpu_temp = 'N/A';
$temps = [];
foreach (glob('/sys/class/thermal/thermal_zone*/temp') as $file) {
    if ($val = (int)trim(file_get_contents($file))) $temps[] = $val / 1000;
}
if ($temps) $cpu_temp = round(array_sum($temps) / count($temps), 1) . ' °C';

$mem = [];
foreach (file('/proc/meminfo', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (preg_match('/^(Mem|Swap)(Total|Free|Available):\s+(\d+)/', $line, $m)) {
        $mem[$m[1].$m[2]] = round($m[3] / 1024 / 1024, 2);
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

$disk_total = round(disk_total_space('/') / (1024 ** 3), 2);
$disk_free = round(disk_free_space('/') / (1024 ** 3), 2);
$disk_used = round($disk_total - $disk_free, 2);
$disk_percent = $disk_total > 0 ? round(($disk_used / $disk_total) * 100) : 0;

$backup_path = '/run/media/wizard/Backup24TB';
$backup_exists = is_dir($backup_path);
$backup_total = $backup_used = $backup_free = $backup_percent = 0;
if ($backup_exists) {
    $backup_total = round(disk_total_space($backup_path) / (1024 ** 3), 2);
    $backup_free = round(disk_free_space($backup_path) / (1024 ** 3), 2);
    $backup_used = round($backup_total - $backup_free, 2);
    $backup_percent = $backup_total > 0 ? round(($backup_used / $backup_total) * 100) : 0;
}

$net = ['rx' => 0, 'tx' => 0];
if ($lines = file('/proc/net/dev', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)) {
    foreach ($lines as $line) {
        if (preg_match('/^\s*(\w+):/', $line, $m) && $m[1] !== 'lo') {
            $parts = preg_split('/\s+/', trim($line));
            $net['rx'] += (int)$parts[1];
            $net['tx'] += (int)$parts[9];
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

$uptime_sec = (int)explode(' ', file_get_contents('/proc/uptime'))[0];
$days = floor($uptime_sec / 86400);
$hours = floor(($uptime_sec % 86400) / 3600);
$minutes = floor(($uptime_sec % 3600) / 60);
$uptime = "{$days} days, {$hours} hours, {$minutes} minutes";

$server_time = date('Y-m-d H:i:s');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <!-- Common base styles -->
    <style>
        body { margin: 0; padding: 2em; min-height: 100vh; position: relative; }
        .header { text-align: center; margin-bottom: 2em; }
        .header h1 { margin: 0; font-size: 2em; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5em; max-width: 1400px; margin: 0 auto; }
        .card { padding: 1.5em; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); position: relative; z-index: 1; }
        .card h3 { margin-top: 0; }
        .progress { height: 32px; border-radius: 16px; overflow: hidden; margin: 1em 0; }
        .bar { height: 100%; background: linear-gradient(to right, #4caf50, #ffeb3b, #f44336); transition: width 1.5s ease-out; }
        .stats { line-height: 1.8; }
        .logout { text-align: center; margin-top: 3em; z-index: 1; position: relative; }
        .logout a { text-decoration: none; font-size: 1.1em; }
        .theme-select { text-align: center; margin-bottom: 1em; z-index: 2; position: relative; }
        .theme-select select { padding: 0.6em 1.2em; font-size: 1em; border-radius: 8px; cursor: pointer; }
        .matrix-rain { position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 0; }
        .matrix-column { position: absolute; top: -100%; font-size: 18px; font-weight: bold; animation: fall linear infinite; white-space: nowrap; }
        @keyframes fall { to { transform: translateY(100vh); } }
        .snowman { display: none; }
    </style>
    <!-- Theme CSS -->
    <link id="theme-css" rel="stylesheet" href="themes/light.css">
</head>
<body>
    <div class="matrix-rain" id="matrix"></div>

    <div class="theme-select">
        <select id="theme-select">
            <option value="light" selected>Light Theme</option>
            <option value="dark">Dark Theme</option>
            <option value="matrix">Matrix Theme</option>
            <option value="terminal">Terminal Theme</option>
            <option value="ocean">Ocean Theme</option>
            <option value="snow">Snow Theme</option>
        </select>
    </div>

    <div class="header">
        <h1>Welcome, <?php echo $username; ?>!</h1>
        <p>System Monitor • <?php echo sanitizeOutput($hostname); ?> • <span id="time"><?php echo $server_time; ?></span> (live updates every 15s)</p>
    </div>
    <div class="grid">
        <div class="card">
            <h3>CPU</h3>
            <p class="stats">
                Cores: <span id="cpu-cores"><?php echo $cpu_cores; ?></span><br>
                Temperature: <span id="cpu-temp"><?php echo $cpu_temp; ?></span><br>
                Load (1 min): <span id="load1"><?php echo $load1; ?></span><br>
                Current load: <span id="load-percent"><?php echo $load_percent; ?></span>%
            </p>
            <div class="progress"><div class="bar" id="cpu-bar" style="width: <?php echo $load_percent; ?>%;"></div></div>
        </div>
        <div class="card">
            <h3>Memory</h3>
            <p class="stats">
                Used: <span id="mem-used"><?php echo $mem_used; ?></span> / <span id="mem-total"><?php echo $mem_total; ?></span> GB<br>
                Available: <span id="mem-available"><?php echo $mem_available; ?></span> GB<br>
                Swap: <span id="swap-used"><?php echo $swap_used; ?></span> / <span id="swap-total"><?php echo $swap_total; ?></span> GB (<span id="swap-percent"><?php echo $swap_percent; ?></span>)<br>
                Usage: <span id="mem-percent"><?php echo $mem_percent; ?></span>%
            </p>
            <div class="progress"><div class="bar" id="mem-bar" style="width: <?php echo $mem_percent; ?>%;"></div></div>
        </div>
        <div class="card">
            <h3>Disk (Root /)</h3>
            <p class="stats">
                Used: <span id="disk-used"><?php echo $disk_used; ?></span> / <span id="disk-total"><?php echo $disk_total; ?></span> GB<br>
                Free: <span id="disk-free"><?php echo $disk_free; ?></span> GB<br>
                Usage: <span id="disk-percent"><?php echo $disk_percent; ?></span>%
            </p>
            <div class="progress"><div class="bar" id="disk-bar" style="width: <?php echo $disk_percent; ?>%;"></div></div>
        </div>
        <?php if ($backup_exists): ?>
        <div class="card" id="backup-card">
            <h3>Disk (Backup 24TB)</h3>
            <p class="stats">
                Used: <span id="backup-used"><?php echo $backup_used; ?></span> / <span id="backup-total"><?php echo $backup_total; ?></span> TB<br>
                Free: <span id="backup-free"><?php echo round($backup_free, 2); ?></span> TB<br>
                Usage: <span id="backup-percent"><?php echo $backup_percent; ?></span>%
            </p>
            <div class="progress"><div class="bar" id="backup-bar" style="width: <?php echo $backup_percent; ?>%;"></div></div>
        </div>
        <?php endif; ?>
        <div class="card">
            <h3>Network (Total I/O)</h3>
            <p class="stats">
                Received: <span id="net-rx"><?php echo $net_rx; ?></span><br>
                Transmitted: <span id="net-tx"><?php echo $net_tx; ?></span><br>
                (cumulative since boot)
            </p>
        </div>
        <div class="card">
            <h3>System Info</h3>
            <p class="stats">
                OS: <?php echo sanitizeOutput($os); ?><br>
                Uptime: <span id="uptime"><?php echo $uptime; ?></span><br>
                PHP: <?php echo phpversion(); ?>
            </p>
        </div>
    </div>
    <div class="logout">
        <a href="logout.php">Logout</a>
    </div>

    <div class="snowman">☃</div>

    <script>
        const themeSelect = document.getElementById('theme-select');
        const themeLink = document.getElementById('theme-css');
        const matrix = document.getElementById('matrix');

        const savedTheme = localStorage.getItem('theme') || 'light';
        themeSelect.value = savedTheme;
        themeLink.href = `themes/${savedTheme}.css`;
        matrix.innerHTML = '';
        if (savedTheme === 'matrix') generateRain();
        else if (savedTheme === 'snow') generateSnow();

        themeSelect.addEventListener('change', (e) => {
            const theme = e.target.value;
            localStorage.setItem('theme', theme);
            themeLink.href = `themes/${theme}.css`;
            matrix.innerHTML = '';
            if (theme === 'matrix') generateRain();
            else if (theme === 'snow') generateSnow();
        });

        function generateRain() {
            const chars = '01アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤユヨラリルレロワヲン';
            const columns = Math.floor(window.innerWidth / 20);
            for (let i = 0; i < columns; i++) {
                const col = document.createElement('div');
                col.className = 'matrix-column';
                col.style.left = `${i * 20}px`;
                col.style.animationDuration = `${10 + Math.random() * 20}s`;
                col.style.animationDelay = `${Math.random() * 10}s`;
                col.style.opacity = 0.5 + Math.random() * 0.3;
                let text = '';
                const length = 20 + Math.floor(Math.random() * 30);
                for (let j = 0; j < length; j++) text += chars[Math.floor(Math.random() * chars.length)] + '<br>';
                col.innerHTML = text;
                matrix.appendChild(col);
            }
        }

        function generateSnow() {
    const flakes = ['❄', '❅', '❆'];
    const numFlakes = 150; // nice density, still super light

    for (let i = 0; i < numFlakes; i++) {
        const flake = document.createElement('div');
        flake.className = 'snowflake';
        flake.textContent = flakes[Math.floor(Math.random() * flakes.length)];

        // Random horizontal start
        flake.style.left = `${Math.random() * 100}vw`;

        // Size + opacity
        const size = 0.7 + Math.random() * 1.0;
        flake.style.fontSize = `${size}em`;
        flake.style.opacity = `${0.6 + Math.random() * 0.4}`;

        // Fall speed (bit slower for larger flakes)
        const fallDuration = 20 + Math.random() * 20 + (size * 8);

        // Sway speed
        const swayDuration = 4 + Math.random() * 6;

        // Random starting rotation
        const rotation = Math.random() * 360;
        flake.style.transform = `rotate(${rotation}deg)`;

        // Animations
        flake.style.animation = `fall ${fallDuration}s linear infinite, sway ${swayDuration}s ease-in-out infinite`;

        // Random delay so they don't all start together
        flake.style.animationDelay = `${Math.random() * fallDuration}s, ${Math.random() * swayDuration}s`;

        matrix.appendChild(flake);
    }
        }

        function updateStats() {
            fetch('stats.php')
                .then(res => res.json())
                .then(data => {
                    document.getElementById('cpu-cores').textContent = data.cpu_cores;
                    document.getElementById('cpu-temp').textContent = data.cpu_temp;
                    document.getElementById('load1').textContent = data.load1;
                    document.getElementById('load-percent').textContent = data.load_percent;
                    document.getElementById('cpu-bar').style.width = data.load_percent + '%';

                    document.getElementById('mem-used').textContent = data.mem_used;
                    document.getElementById('mem-total').textContent = data.mem_total;
                    document.getElementById('mem-available').textContent = data.mem_available;
                    document.getElementById('swap-used').textContent = data.swap_used;
                    document.getElementById('swap-total').textContent = data.swap_total;
                    document.getElementById('swap-percent').textContent = data.swap_percent;
                    document.getElementById('mem-percent').textContent = data.mem_percent;
                    document.getElementById('mem-bar').style.width = data.mem_percent + '%';

                    document.getElementById('disk-used').textContent = data.disk_used;
                    document.getElementById('disk-total').textContent = data.disk_total;
                    document.getElementById('disk-free').textContent = data.disk_free;
                    document.getElementById('disk-percent').textContent = data.disk_percent;
                    document.getElementById('disk-bar').style.width = data.disk_percent + '%';

                    const backupCard = document.getElementById('backup-card');
                    if (data.backup_exists && backupCard) {
                        backupCard.style.display = 'block';
                        document.getElementById('backup-used').textContent = data.backup_used;
                        document.getElementById('backup-total').textContent = data.backup_total;
                        document.getElementById('backup-free').textContent = data.backup_free;
                        document.getElementById('backup-percent').textContent = data.backup_percent;
                        document.getElementById('backup-bar').style.width = data.backup_percent + '%';
                    } else if (backupCard) {
                        backupCard.style.display = 'none';
                    }

                    document.getElementById('net-rx').textContent = data.net_rx;
                    document.getElementById('net-tx').textContent = data.net_tx;

                    document.getElementById('uptime').textContent = data.uptime;
                    document.getElementById('time').textContent = data.server_time;
                })
                .catch(err => console.error('Update failed:', err));
        }

        setInterval(updateStats, 15000);
        updateStats(); // initial call
    </script>
</body>
</html>