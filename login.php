<?php
/**
 * Login Page
 * 
 * Handles user authentication with security features:
 * - CSRF protection
 * - Session fixation prevention (regenerate ID on login)
 * - Password verification using BCRYPT
 * - Input validation
 * - SQL injection prevention (prepared statements)
 */

// Load required modules
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/security.php';
require_once 'includes/session.php';
require_once 'includes/validation.php';

// Set security headers
setSecurityHeaders();

// Initialize secure session
initSecureSession();

// Auto-redirect if already logged in
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generateCSRFToken();
}

// Get database connection
try {
    $db = getDatabase();
} catch (Exception $e) {
    error_log("Database error in login.php: " . $e->getMessage());
    die("Database error. Please try again later.");
}

$message = '';

// Optional: Show success message if coming from registration
if (isset($_GET['registered']) && $_GET['registered'] === '1') {
    $message = "<p style='color: green;'>Account created successfully! Please log in.</p>";
}

// Login handling
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'login') {
    // ============================================================
    // CSRF (Cross-Site Request Forgery) PROTECTION
    // ============================================================
    // 
    // CSRF Attack Example (without protection):
    // 1. User logs into BareStack (victim)
    // 2. Attacker sends malicious email with hidden form
    // 3. Form auto-submits to BareStack with victim's cookies
    // 4. BareStack can't tell if request is legitimate
    // 5. Unwanted action performed on victim's behalf
    // 
    // How CSRF tokens prevent this:
    // 1. Token generated server-side (stored in session)
    // 2. Token embedded in form (hidden field)
    // 3. Form submission includes token
    // 4. Server verifies: submitted token === session token
    // 5. Attacker can't access victim's session/token
    // 6. Attack fails - request rejected
    // 
    // Our implementation:
    // - Token: 32 bytes of random data = 64 hex characters
    // - Generation: random_bytes(32) - cryptographically secure
    // - Verification: hash_equals() - timing attack safe
    // - Regeneration: New token after each form submission
    // ============================================================
    
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $message = "<p style='color: red;'>CSRF attack detected!</p>";
        error_log("CSRF attack attempt on login from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    } else {
        $username = trim($_POST['username'] ?? '');
        $pass = $_POST['password'] ?? '';

        // Validate input
        if (!validateUsername($username)) {
            $message = "<p style='color: red;'>" . sanitizeOutput(getUsernameError($username)) . "</p>";
        } elseif (!validatePassword($pass)) {
            $message = "<p style='color: red;'>Password must be between 8 and 128 characters.</p>";
        } else {
            try {
                // ============================================================
                // SQL INJECTION PREVENTION WITH PREPARED STATEMENTS
                // ============================================================
                // 
                // Using PDO prepared statements ensures user input can NEVER
                // alter the SQL query structure. Here's how it works:
                // 
                // 1. PREPARE: SQL query structure sent to database first
                //    - Uses ? placeholders for values
                //    - Database compiles the query structure
                // 
                // 2. EXECUTE: User input sent separately as parameters
                //    - Values cannot change query structure
                //    - Database treats input as DATA, not CODE
                // 
                // What if attacker tries: admin' OR '1'='1
                // - Query looks for username EXACTLY matching "admin' OR '1'='1"
                // - No SQL injection possible - input is just a string
                // ============================================================
                
                $stmt = $db->prepare("SELECT hash FROM users WHERE username = ?");
                $stmt->execute([$username]);
                $storedHash = $stmt->fetchColumn();
                
                // ============================================================
                // BCRYPT PASSWORD VERIFICATION
                // ============================================================
                // 
                // password_verify() automatically:
                // 1. Extracts the salt from the stored hash
                // 2. Hashes the input password using that same salt
                // 3. Compares the two hashes using timing-safe comparison
                // 4. Returns true only if they match exactly
                // 
                // The stored hash format is:
                // $2y$12$R9h/cIPz0gi.URNNX3kh2OUZGCKveTPd5fQOaIwvY7CjCCt8pEY3a
                //        ├──salt (22 chars)──┤├──hash (31 chars)───┤
                // 
                // PHP automatically extracts the salt portion and uses it
                // to hash the input password for comparison.
                // 
                // Security benefits:
                // - Timing-safe comparison (prevents timing attacks)
                // - Automatic salt extraction (no manual salt management)
                // - Works even if cost factor changes (upgradeable)
                // ============================================================
                
                if ($storedHash && password_verify($pass, $storedHash)) {
                    // ========================================================
                    // SUCCESSFUL LOGIN - SESSION FIXATION PREVENTION
                    // ========================================================
                    // 
                    // Regenerate session ID on successful login to prevent
                    // session fixation attacks.
                    // 
                    // Session Fixation Attack (without regeneration):
                    // 1. Attacker creates session, gets session ID: ABC123
                    // 2. Attacker tricks victim into using session ID: ABC123
                    // 3. Victim logs in using attacker's session ID
                    // 4. Attacker uses ABC123 to access victim's account
                    // 
                    // With session regeneration:
                    // 1. Attacker creates session: ABC123
                    // 2. Victim uses ABC123 to login
                    // 3. Session ID regenerates: XYZ789
                    // 4. Attacker's ABC123 is now invalid - attack fails!
                    // ========================================================
                    
                    session_regenerate_id(true);
                    
                    // Set session variables
                    $_SESSION['logged_in'] = true;
                    $_SESSION['username'] = $username;
                    $_SESSION['login_time'] = time();
                    
                    // Redirect to dashboard
                    header("Location: dashboard.php");
                    exit;
                } else {
                    // Invalid credentials
                    $message = "<p style='color: red;'>Invalid credentials.</p>";
                    error_log("Failed login attempt for user: $username from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
                }
            } catch (PDOException $e) {
                error_log("Database error during login: " . $e->getMessage());
                $message = "<p style='color: red;'>An error occurred. Please try again.</p>";
            }
        }
    }
    
    // Regenerate CSRF token after form submission
    $_SESSION['csrf_token'] = generateCSRFToken();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Login</title>
    <style>
        body { 
            background: linear-gradient(to right, #f0f0f0, #e0e0e0); 
            font-family: Arial, sans-serif; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
        }
        .container { 
            background: white; 
            padding: 2em; 
            border-radius: 10px; 
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); 
            width: 300px; 
            text-align: center;
        }
        input { 
            display: block; 
            width: 100%; 
            margin: 1em 0; 
            padding: 0.5em; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            box-sizing: border-box;
        }
        button { 
            width: 100%; 
            padding: 0.7em; 
            background: linear-gradient(to right, #a0a0a0, #808080); 
            color: white; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
        }
        button:hover { opacity: 0.9; }
        .message { margin-bottom: 1em; font-weight: bold; }
        .links { margin-top: 1em; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <div class="message"><?php echo $message; ?></div>
        <form method="post">
            <input type="hidden" name="action" value="login">
            <input type="hidden" name="csrf_token" value="<?php echo sanitizeOutput($_SESSION['csrf_token']); ?>">
            <input type="text" name="username" placeholder="Username" required autofocus autocomplete="username">
            <input type="password" name="password" placeholder="Password" required autocomplete="current-password">
            <button type="submit">Login</button>
        </form>
        <div class="links">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
</body>
</html>