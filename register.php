<?php
/**
 * Registration Page
 * 
 * Handles new user registration with security features:
 * - CSRF protection
 * - Password strength validation
 * - BCRYPT password hashing (cost 12)
 * - Input validation and sanitization
 * - Username blacklisting
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

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generateCSRFToken();
}

// Get database connection
try {
    $db = getDatabase();
} catch (Exception $e) {
    error_log("Database error in register.php: " . $e->getMessage());
    die("Database error. Please try again later.");
}

$message = '';

// Registration handling
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // CSRF protection - use timing-safe comparison
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $message = "<p style='color: red;'>CSRF attack detected!</p>";
        error_log("CSRF attack attempt on registration from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    } else {
        $username = trim($_POST['username'] ?? '');
        $pass1 = $_POST['password1'] ?? '';
        $pass2 = $_POST['password2'] ?? '';

        // Validate username
        if (!validateUsername($username)) {
            $message = "<p style='color: red;'>" . sanitizeOutput(getUsernameError($username)) . "</p>";
        } 
        // Validate password length
        elseif (!validatePassword($pass1)) {
            $message = "<p style='color: red;'>Password must be between 8 and 128 characters.</p>";
        } 
        // Check password match
        elseif ($pass1 !== $pass2) {
            $message = "<p style='color: red;'>Passwords do not match.</p>";
        } else {
            try {
                // Check if username already exists
                $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
                $stmt->execute([$username]);
                
                if ($stmt->fetchColumn() > 0) {
                    $message = "<p style='color: red;'>Username already taken.</p>";
                } else {
                    // ============================================================
                    // BCRYPT PASSWORD HASHING WITH AUTOMATIC SALTING
                    // ============================================================
                    // 
                    // When you hash a password with BCrypt, PHP's password_hash()
                    // function does ALL of these steps automatically:
                    // 
                    // 1. GENERATES A RANDOM SALT (128-bit / 16 bytes)
                    //    - Cryptographically secure randomness
                    //    - Unique salt for EVERY password
                    //    - Even identical passwords get different salts
                    // 
                    // 2. EMBEDS THE SALT IN THE HASH
                    //    - Salt is NOT stored separately
                    //    - Format: $2y$12$[salt][hash]
                    //    - One string contains both salt and hash
                    // 
                    // 3. APPLIES THE HASHING ALGORITHM
                    //    - Runs BCrypt with cost factor 12
                    //    - Cost 12 = 2^12 = 4,096 iterations
                    //    - Intentionally slow (good for passwords!)
                    // 
                    // Result: $2y$12$R9h/cIPz0gi.URNNX3kh2OUZGCKveTPd5fQOaIwvY7CjCCt8pEY3a
                    //                ├──salt (22 chars)──┤├──hash (31 chars)───┤
                    //
                    // You NEVER need to:
                    // - Generate salts manually
                    // - Store salts separately
                    // - Manage salt/hash relationships
                    //
                    // PHP does it ALL automatically!
                    // ============================================================
                    
                    $hash = password_hash($pass1, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
                    
                    if ($hash === false) {
                        error_log("Password hashing failed for user: $username");
                        $message = "<p style='color: red;'>An error occurred during registration.</p>";
                    } else {
                        // Insert new user
                        $stmt = $db->prepare("INSERT INTO users (username, hash) VALUES (?, ?)");
                        $stmt->execute([$username, $hash]);
                        
                        // Log successful registration
                        error_log("New user registered: $username from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
                        
                        // Redirect to login with success indicator
                        header("Location: login.php?registered=1");
                        exit;
                    }
                }
            } catch (PDOException $e) {
                error_log("Database error during registration: " . $e->getMessage());
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
    <title>Register</title>
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
        <h2>Register</h2>
        <div class="message"><?php echo $message; ?></div>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo sanitizeOutput($_SESSION['csrf_token']); ?>">
            <input type="text" name="username" placeholder="Username" required autofocus value="<?php echo sanitizeOutput($_POST['username'] ?? ''); ?>" autocomplete="username">
            <input type="password" name="password1" placeholder="Password" required autocomplete="new-password">
            <input type="password" name="password2" placeholder="Confirm Password" required autocomplete="new-password">
            <button type="submit">Register</button>
        </form>
        <div class="links">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>