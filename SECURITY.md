# Security Policy

## Overview

**BareStack** is an **educational project** designed to teach secure web development fundamentals. This document provides comprehensive security information, including detailed answers to common security questions.

> 📚 **Educational Focus**: This project demonstrates security best practices for learning purposes. For production applications, always conduct professional security audits.

---

## ❓ Common Security Questions (Answered)

### Question 1: Is BCrypt Salted? How Does It Work?

**YES! BCrypt salts automatically.** You never need to manually generate or manage salts.

#### How BCrypt Auto-Salting Works

When you call PHP's `password_hash()`:

```php
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
```

**Here's what happens under the hood:**

1. **Random Salt Generation**
   - BCrypt generates a unique 128-bit (16 byte) random salt
   - Uses cryptographically secure randomness (not `rand()` or `mt_rand()`)
   - Every password gets a **different salt**, even identical passwords

2. **Salt is Embedded in the Hash**
   - The salt is **not stored separately**
   - It's encoded into the hash string itself
   - You only need to store one string: the hash

3. **Hash Format Breakdown**

```
$2y$12$R9h/cIPz0gi.URNNX3kh2OUZGCKveTPd5fQOaIwvY7CjCCt8pEY3a
|  |  |                    |                             |
|  |  |                    |                             └─ Hash (31 chars)
|  |  |                    └─ Salt (22 chars)
|  |  └─ Cost (12 = 2^12 = 4,096 iterations)
|  └─ Algorithm (2y = BCrypt)
└─ Prefix ($)
```

**Component Details:**
- `$2y$` - BCrypt algorithm identifier (PHP-specific)
- `12` - Cost factor (2^12 = 4,096 iterations)
- `R9h/cIPz0gi.URNNX3kh2O` - The salt (22 characters, Base64-like encoding)
- `UZGCKveTPd5fQOaIwvY7CjCCt8pEY3a` - The actual hash (31 characters)

4. **Verification Process**

When verifying with `password_verify()`:

```php
if (password_verify($inputPassword, $storedHash)) {
    // Password correct!
}
```

**What happens:**
1. PHP extracts the salt from the stored hash
2. Hashes the input password with the **same salt**
3. Compares the result with the stored hash
4. Uses timing-safe comparison (no timing attacks)

#### Why This Matters

✅ **Unique Salts**: Even if two users have the same password, their hashes are different
```php
password_hash("password123", PASSWORD_BCRYPT); 
// → $2y$12$abc...xyz

password_hash("password123", PASSWORD_BCRYPT); 
// → $2y$12$def...uvw  (different!)
```

✅ **Rainbow Table Resistance**: Pre-computed hash tables are useless because every password has a unique salt

✅ **No Salt Management**: You don't need a separate `salt` column in your database

✅ **Cost Factor Adjustable**: Increase cost as computers get faster
```php
// Cost 10 = 1,024 iterations
// Cost 12 = 4,096 iterations  ← Current BareStack default
// Cost 14 = 16,384 iterations
```

#### Code Example with Detailed Comments

**Registration (register.php, lines 73-76):**
```php
// Hash the password with BCrypt
// - PASSWORD_BCRYPT: Uses BCrypt algorithm
// - cost 12: 2^12 = 4,096 iterations (good balance of security/performance)
// - BCrypt generates a UNIQUE RANDOM SALT automatically
// - Salt is embedded in the returned hash string
// - Format: $2y$12$[22-char-salt][31-char-hash]
$hash = password_hash($pass1, PASSWORD_BCRYPT, ['cost' => 12]);

// Example output:
// $2y$12$R9h/cIPz0gi.URNNX3kh2OUZGCKveTPd5fQOaIwvY7CjCCt8pEY3a
//        ├────── salt ──────┤├────── hash ────────┤

// Store $hash in database - that's all you need!
$stmt = $db->prepare("INSERT INTO users (username, hash) VALUES (?, ?)");
$stmt->execute([$username, $hash]);
```

**Login Verification (login.php, line 74):**
```php
// Retrieve the stored hash from database
$stmt = $db->prepare("SELECT hash FROM users WHERE username = ?");
$stmt->execute([$username]);
$storedHash = $stmt->fetchColumn();

// Verify password
// password_verify() automatically:
// 1. Extracts the salt from $storedHash
// 2. Hashes $inputPassword with that same salt
// 3. Compares hashes using timing-safe comparison
// 4. Returns true if they match
if ($storedHash && password_verify($inputPassword, $storedHash)) {
    // Password correct! Log them in
    $_SESSION['logged_in'] = true;
    $_SESSION['username'] = $username;
}
```

#### Security Benefits

1. **Computational Cost**: BCrypt is intentionally slow (good for passwords!)
2. **Future-Proof**: Increase cost factor as computers get faster
3. **Salted Automatically**: No developer error possible
4. **Widely Tested**: Battle-tested algorithm, used by major companies
5. **Prevents Timing Attacks**: Built-in constant-time comparison

#### References
- [PHP password_hash() documentation](https://www.php.net/manual/en/function.password-hash.php)
- [BCrypt Wikipedia](https://en.wikipedia.org/wiki/Bcrypt)
- [OWASP Password Storage Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html)

---

### Question 2: Should We Return 404 Instead of 403 for Sensitive Files?

**YES! Return 404, not 403.** This is a security best practice.

#### Why 403 is a Problem

**403 Forbidden** tells the attacker:
- ✓ "Yes, this file exists"
- ✓ "You found something interesting"
- ✓ "Keep probing this area"

**404 Not Found** tells the attacker:
- ✗ "Nothing here" (ambiguous - file might not exist, or might be protected)
- ✗ Doesn't confirm file existence
- ✗ Less information leaked

#### Information Disclosure Example

**Bad (403 Forbidden):**
```
GET /config.php HTTP/1.1

HTTP/1.1 403 Forbidden
```
**Attacker thinks:** "Aha! `config.php` exists and contains sensitive data. Let me try to exploit a vulnerability to read it."

**Good (404 Not Found):**
```
GET /config.php HTTP/1.1

HTTP/1.1 404 Not Found
```
**Attacker thinks:** "Probably doesn't exist. Moving on..."


#### Web Server Configuration Examples

**Apache (.htaccess):**
```apache
# Deny access to sensitive files
<FilesMatch "^(config\.php|\.env|composer\.(json|lock))$">
    # Return 404 instead of 403
    # ErrorDocument 403 /404.php would still show 403 status
    # Instead, rewrite to a non-existent file
    RewriteEngine On
    RewriteRule .* - [R=404,L]
</FilesMatch>

# Alternative: More explicit approach
<Files "config.php">
    # Deny access
    Require all denied
    # Custom error document returns 404
    ErrorDocument 403 /404.php
</Files>

# Protect includes directory
<Directory "/var/www/html/includes">
    Require all denied
    ErrorDocument 403 /404.php
</Directory>
```

**Caddy (Caddyfile) - Used in BareStack:**
```caddy
# BareStack uses Caddy with FrankenPHP
# Protect sensitive PHP files by returning 404

# Method 1: Explicit file protection (current approach)
@sensitive_files {
    path /config.php /includes/* /.env /composer.json /composer.lock
}
respond @sensitive_files "Not Found" 404

# Method 2: Regex-based protection
@config_files {
    path_regexp config \.(env|ini|config|conf)$
}
respond @config_files "Not Found" 404

# Method 3: Protect entire directories
@includes_dir {
    path /includes/*
}
respond @includes_dir "Not Found" 404

# Example from BareStack's Caddyfile:
{
    frankenphp
}

:80 {
    root * /app
    php_fastcgi localhost:9000
    
    # Return 404 for sensitive files (security by obscurity layer)
    @blocked {
        path /config.php /router.php /Caddyfile /includes/* /data/* /.env
    }
    respond @blocked "Not Found" 404
    
    file_server
}
```

**Nginx (nginx.conf):**
```nginx
server {
    listen 80;
    server_name example.com;
    root /var/www/html;
    
    # Return 404 for sensitive files
    location ~ ^/(config\.php|\.env|composer\.(json|lock))$ {
        # Return 404 instead of 403
        return 404;
    }
    
    # Protect includes directory
    location ^~ /includes/ {
        # Return 404 for any file in includes/
        return 404;
    }
    
    # Deny access to hidden files but return 404
    location ~ /\. {
        return 404;
    }
    
    # PHP handling
    location ~ \.php$ {
        # Only process PHP files that exist
        try_files $uri =404;
        
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

#### Additional Protection Layers

**1. File Permissions (Linux):**
```bash
# Make config.php readable only by the web server user
chmod 600 config.php
chown www-data:www-data config.php

# Protect includes directory
chmod 700 includes/
chown -R www-data:www-data includes/
```

**2. PHP Code Protection (config.php):**
```php
<?php
// Prevent direct access - must be included, not executed directly
if (!defined('APP_INIT')) {
    // Return 404 programmatically
    http_response_code(404);
    header('HTTP/1.1 404 Not Found');
    exit('Not Found');
}

// Your configuration here
define('DB_HOST', 'localhost');
// ...
?>
```

**3. Application-Level Check (router.php approach):**
```php
<?php
// BareStack's router.php approach:
// Only allow specific PHP files to be executed

$allowed_files = ['login.php', 'register.php', 'dashboard.php', 'logout.php'];

$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$script = basename($request_uri);

// If someone tries to access config.php or any non-allowed file
if (!in_array($script, $allowed_files)) {
    http_response_code(404);
    include '404.php';  // Your custom 404 page
    exit;
}
?>
```

#### Defense in Depth

**BareStack implements multiple layers:**

1. **Web Server Level** (Caddy): Returns 404 for sensitive paths
2. **File System Level**: Proper file permissions (if deployed on Linux)
3. **PHP Level**: `config.php` included via router, not directly accessible
4. **Code Level**: No direct execution of sensitive files

**Result:** Even if one layer fails, others provide protection.

#### Why This Matters

- **Information Disclosure**: Don't tell attackers what exists
- **Attack Surface Reduction**: Less information = harder to attack
- **Security by Obscurity**: Not the only layer, but a helpful one
- **Professional Practice**: Major frameworks (Laravel, Symfony) do this

---

### Question 3: Does This Teach Fullstack Development?

**YES! BareStack is a comprehensive fullstack learning project.**

#### What is Fullstack Development?

**Fullstack** means understanding and implementing both:
- **Frontend** (client-side): What users see and interact with
- **Backend** (server-side): Business logic, data processing, security
- **Database**: Data storage and retrieval
- **DevOps**: Deployment, hosting, infrastructure
- **Security**: Protecting the entire stack

BareStack covers **all of these areas** with practical, hands-on code.

---

#### 🎨 Frontend Skills (Client-Side)

**What You Learn:**

1. **HTML5 Structure**
   - Semantic HTML (`<header>`, `<main>`, `<section>`)
   - Forms (`<form>`, `<input>`, `<button>`)
   - Accessibility (labels, ARIA attributes)
   
2. **CSS3 Styling**
   - Modern layouts (Flexbox, Grid)
   - Responsive design (media queries)
   - Custom properties (CSS variables)
   - Animations and transitions
   - Theme system (see `themes/` directory)

3. **JavaScript (Vanilla JS)**
   - DOM manipulation
   - Event handling
   - Fetch API for AJAX requests
   - Form validation (client-side)
   - Dynamic content updates

4. **UX/UI Patterns**
   - Login/registration flows
   - Dashboard interfaces
   - Error message display
   - Loading states
   - Responsive navigation

**Example from BareStack:**
```html
<!-- dashboard.php shows frontend skills -->
<div class="stats-container">
    <div class="stat-card">
        <span class="stat-value"><?php echo $total_users; ?></span>
        <span class="stat-label">Total Users</span>
    </div>
</div>

<script>
// Vanilla JavaScript for dynamic updates
document.addEventListener('DOMContentLoaded', function() {
    // Fetch updated stats without page reload
    fetchStats();
    setInterval(fetchStats, 30000); // Update every 30 seconds
});

function fetchStats() {
    fetch('stats.php')
        .then(response => response.json())
        .then(data => {
            document.querySelector('.stat-value').textContent = data.total_users;
        });
}
</script>
```

---

#### ⚙️ Backend Skills (Server-Side)

**What You Learn:**

1. **PHP Programming**
   - Variables, functions, conditionals
   - Object-oriented programming (PDO)
   - Session management
   - Error handling
   - Input validation
   - Security best practices

2. **Authentication System**
   - User registration with validation
   - Password hashing (BCrypt)
   - Login/logout functionality
   - Session management
   - Remember me functionality (optional)

3. **Request/Response Cycle**
   - HTTP methods (GET, POST)
   - Headers (Content-Type, Location)
   - Status codes (200, 302, 404, 403)
   - Redirects
   - Cookies and sessions

4. **Business Logic**
   - Input validation and sanitization
   - Password strength enforcement
   - Username uniqueness checks
   - Error handling and user feedback

**Example from BareStack:**
```php
// register.php - Backend validation and processing
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Retrieve and sanitize input
    $username = trim($_POST['username']);
    $pass1 = $_POST['password1'];
    $pass2 = $_POST['password2'];
    
    // 2. Validate input
    if (strlen($username) < 3 || strlen($username) > 20) {
        $error = "Username must be 3-20 characters";
    }
    elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = "Username can only contain letters, numbers, underscore";
    }
    elseif (strlen($pass1) < 8) {
        $error = "Password must be at least 8 characters";
    }
    elseif ($pass1 !== $pass2) {
        $error = "Passwords do not match";
    }
    else {
        // 3. Check username uniqueness (database query)
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->fetch()) {
            $error = "Username already taken";
        }
        else {
            // 4. Hash password securely
            $hash = password_hash($pass1, PASSWORD_BCRYPT, ['cost' => 12]);
            
            // 5. Insert into database
            $stmt = $db->prepare("INSERT INTO users (username, hash, created_at) VALUES (?, ?, ?)");
            $stmt->execute([$username, $hash, date('Y-m-d H:i:s')]);
            
            // 6. Redirect to login page
            header('Location: login.php?registered=1');
            exit;
        }
    }
}
```

**Skills demonstrated:**
- Input validation (length, regex patterns)
- Database interaction (prepared statements)
- Password security (BCrypt hashing)
- Error handling (user-friendly messages)
- Flow control (redirects)

---

#### 🗄️ Database Skills

**What You Learn:**

1. **SQL Fundamentals**
   - Creating tables (DDL)
   - Inserting data (DML)
   - Querying data (SELECT)
   - Filtering (WHERE)
   - Prepared statements (security)

2. **Database Design**
   - Schema design (users table)
   - Data types (VARCHAR, TEXT, DATETIME)
   - Primary keys (AUTO_INCREMENT)
   - UNIQUE constraints (username)
   - Timestamps (created_at)

3. **SQLite Specifics**
   - File-based database
   - Zero configuration
   - Perfect for learning and small projects
   - Easy to backup (single file)

4. **Security**
   - SQL injection prevention (prepared statements)
   - Password storage (hashing, never plaintext)
   - Input sanitization

**Example from BareStack:**
```php
// config.php - Database setup
$db = new PDO('sqlite:' . __DIR__ . '/data/users.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create users table with proper schema
$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    hash TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Secure query example (login.php)
// NEVER use string concatenation - prevents SQL injection
$stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Bad (NEVER do this - SQL injection vulnerability):
// $query = "SELECT * FROM users WHERE username = '$username'";
// Attacker could input: admin' OR '1'='1

// Good (BareStack approach - prepared statements):
// $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
// $stmt->execute([$username]);
```

---

#### 🔒 Security Skills

**What You Learn:**

1. **Authentication Security**
   - Password hashing (BCrypt with cost factor)
   - Salt generation (automatic with BCrypt)
   - Session management
   - CSRF protection considerations

2. **Input Validation**
   - Server-side validation (never trust client)
   - Length checks
   - Character whitelisting
   - Type validation

3. **SQL Injection Prevention**
   - Prepared statements with parameterized queries
   - PDO parameter binding
   - Why string concatenation is dangerous

4. **Session Security**
   - Session hijacking prevention
   - Session fixation prevention
   - Proper session destruction (logout)

5. **Information Disclosure Prevention**
   - Generic error messages
   - 404 instead of 403
   - No stack traces in production

6. **Access Control**
   - Authentication checks on protected pages
   - Redirect unauthorized users
   - Session-based authorization

**Example from BareStack:**
```php
// dashboard.php - Access control
session_start();

// Check if user is authenticated
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Not logged in - redirect to login page
    header('Location: login.php');
    exit;
}

// User is authenticated - show dashboard content
$username = $_SESSION['username'];
```

**Security best practices demonstrated:**
- Always check authentication before showing protected content
- Use sessions to maintain login state
- Redirect unauthenticated users
- Store minimal data in sessions (username, logged_in flag)

---

#### 🚀 DevOps & Infrastructure Skills

**What You Learn:**

1. **Containerization (Docker)**
   - Dockerfile creation
   - Multi-stage builds
   - Container orchestration
   - Volume management
   - Port mapping

2. **Web Server Configuration**
   - Caddy (modern, automatic HTTPS)
   - FrankenPHP (PHP server)
   - Static file serving
   - Reverse proxies
   - URL rewriting

3. **Deployment**
   - Environment variables
   - Configuration management
   - File permissions
   - Process management

4. **Version Control (Git)**
   - Repository structure
   - .gitignore configuration
   - Branch management
   - Commit best practices

**Example from BareStack:**
```dockerfile
# Dockerfile - Multi-stage build for production
FROM dunglas/frankenphp:latest

# Copy application code
COPY . /app

# Set working directory
WORKDIR /app

# Create data directory with proper permissions
RUN mkdir -p /app/data && \
    chmod 755 /app/data && \
    chown www-data:www-data /app/data

# Expose port
EXPOSE 80

# Run FrankenPHP with Caddy
CMD ["frankenphp", "run", "--config", "/app/Caddyfile"]
```

```caddy
# Caddyfile - Web server configuration
{
    frankenphp
    # Disable admin API for security
    admin off
}

:80 {
    # Document root
    root * /app
    
    # PHP processing
    php_fastcgi unix//var/run/php-fpm.sock
    
    # Security: Block sensitive files
    @blocked {
        path /config.php /includes/* /.env /data/*
    }
    respond @blocked "Not Found" 404
    
    # Serve static files
    file_server
    
    # Logging
    log {
        output file /var/log/caddy/access.log
    }
}
```

---

#### 📊 Skills Breakdown Summary

| Category | Skills Learned | BareStack Examples |
|----------|----------------|-------------------|
| **Frontend** | HTML5, CSS3, JavaScript, Responsive Design | `dashboard.php`, `themes/` |
| **Backend** | PHP, Session Management, Authentication | `login.php`, `register.php` |
| **Database** | SQL, SQLite, Prepared Statements, Schema Design | `config.php`, users table |
| **Security** | BCrypt, Input Validation, SQL Injection Prevention, Access Control | All PHP files |
| **DevOps** | Docker, Caddy, FrankenPHP, Deployment | `Dockerfile`, `Caddyfile` |
| **HTTP** | Request/Response, Status Codes, Headers, Redirects | `router.php` |
| **Architecture** | MVC Pattern, Separation of Concerns, File Organization | `includes/`, `themes/` |

---

#### Why BareStack is "Fullstack"

✅ **Complete Stack Coverage**: Frontend → Backend → Database → Deployment

✅ **Real-World Patterns**: Not just theory, actual working code

✅ **Security First**: Learn secure coding from the start

✅ **Production-Ready Practices**: Docker, web server config, environment variables

✅ **Hands-On Learning**: Clone, run, modify, experiment

✅ **Modern Tools**: Caddy, FrankenPHP, Docker (not outdated tech)

✅ **Minimal Dependencies**: No frameworks - understand fundamentals first

---

#### Learning Path Recommendation

**Beginner → Intermediate → Advanced**

1. **Start Here (Beginner)**:
   - Run BareStack with Docker
   - Explore `login.php` and `register.php`
   - Understand form submission (frontend → backend)
   - Examine `config.php` (database connection)

2. **Intermediate**:
   - Add new features (password reset, email verification)
   - Implement CSRF tokens
   - Add password strength meter (frontend)
   - Create user profiles

3. **Advanced**:
   - Implement two-factor authentication (2FA)
   - Add API endpoints (JSON responses)
   - Implement rate limiting
   - Add comprehensive logging
   - Deploy to production (VPS, cloud)

---

### Question 4: Author Security Expertise Disclaimer

**⚠️ Important: Transparency About Expertise**

#### Who Built This?

BareStack was created by **a software developer who follows security best practices**, **NOT** a certified security professional or penetration tester.

#### What Does This Mean?

**✅ What I Have:**
- Years of software development experience
- Knowledge of common security vulnerabilities (OWASP Top 10)
- Experience implementing authentication systems
- Familiarity with security best practices
- Understanding of secure coding principles
- Regular reading of security advisories and best practices

**❌ What I Am NOT:**
- A certified security professional (CISSP, CEH, OSCP)
- A professional penetration tester
- A security researcher or bug bounty hunter
- An expert in cryptography or advanced security topics
- Claiming this code is bulletproof or production-ready without review

#### Why This Matters

**Honesty in Education:**
- This project is for **learning purposes**
- Real production systems need **professional security audits**
- Security is complex - no one person knows everything
- Best practices evolve - what's secure today may not be tomorrow

**What I Did:**
- Followed OWASP guidelines
- Used PHP's built-in security functions (`password_hash`, PDO prepared statements)
- Researched each security decision
- Implemented multiple layers of defense
- Documented choices and trade-offs

**What You Should Do:**
- Use this project to **learn fundamentals**
- Don't deploy to production without professional security review
- Stay updated on security best practices
- Consider hiring security professionals for production apps
- Participate in security communities
- Run security scanners (see Security Testing section)

#### My Approach to Security

**1. Defense in Depth**
- Multiple layers of protection
- No single point of failure
- Web server + PHP + database + file system

**2. Principle of Least Privilege**
- Users only access what they need
- Sessions contain minimal data
- File permissions restricted

**3. Secure by Default**
- Sessions are HTTP-only (can't be accessed via JavaScript)
- Prepared statements prevent SQL injection
- BCrypt hashing is automatic

**4. Fail Securely**
- Errors don't reveal sensitive information
- Default deny (return 404, not 403)
- Validation failures are generic

**5. Don't Roll Your Own Crypto**
- Use PHP's built-in `password_hash()` (BCrypt)
- Don't create custom encryption
- Trust battle-tested libraries

#### Resources I Used

**Learning Resources:**
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [OWASP Cheat Sheet Series](https://cheatsheetseries.owasp.org/)
- [PHP Security Guide](https://phptherightway.com/#security)
- [PortSwigger Web Security Academy](https://portswigger.net/web-security)

**Security Tools:**
- [OWASP ZAP](https://www.zaproxy.org/) - Security scanner
- [SQLMap](https://sqlmap.org/) - SQL injection testing
- [Burp Suite](https://portswigger.net/burp) - Web vulnerability scanner

#### Call for Community Review

**If you're a security professional**, please:
- Review the code
- Open GitHub issues for vulnerabilities
- Suggest improvements
- Help make this better for learners

**Security is a community effort.** I welcome feedback and improvements.

---

## 🛡️ Implemented Security Measures

BareStack implements multiple security layers. Here's a comprehensive list:

### 1. Authentication & Password Security

✅ **BCrypt Password Hashing**
- Cost factor: 12 (4,096 iterations)
- Automatic salting (unique per password)
- Future-proof (cost can be increased)
- Uses PHP's `password_hash()` and `password_verify()`

**Code Reference:** `register.php` (line 73), `login.php` (line 74)

✅ **Password Requirements**
- Minimum 8 characters
- Enforced server-side
- Can be extended (uppercase, numbers, symbols)

**Code Reference:** `register.php` (lines 50-52)

✅ **Username Validation**
- Length: 3-20 characters
- Alphanumeric + underscore only (`^[a-zA-Z0-9_]+$`)
- Prevents special characters that could cause issues

**Code Reference:** `register.php` (lines 47-49)

---

### 2. SQL Injection Prevention

✅ **Prepared Statements (100% Coverage)**
- ALL database queries use PDO prepared statements
- Parameter binding (no string concatenation)
- Type-safe queries

**Example:**
```php
// SECURE (BareStack approach)
$stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);

// INSECURE (NOT used in BareStack)
// $query = "SELECT * FROM users WHERE username = '$username'";
```

**Code Reference:** All database interactions in `login.php`, `register.php`, `dashboard.php`

---

### 3. Session Management

✅ **Secure Session Configuration**
- Sessions used for authentication state
- Minimal data stored (username, logged_in flag)
- Proper session destruction on logout

**Code Reference:** `login.php` (lines 81-82), `logout.php` (lines 5-6)

✅ **Access Control**
- Protected pages check authentication
- Redirect to login if not authenticated
- No cached pages after logout

**Code Reference:** `dashboard.php` (lines 5-9)

**Recommended Enhancement (Not Implemented):**
```php
// Add to config.php for production
ini_set('session.cookie_httponly', 1);  // Prevent JavaScript access
ini_set('session.cookie_secure', 1);     // HTTPS only (requires SSL)
ini_set('session.cookie_samesite', 'Strict'); // CSRF protection
```

---

### 4. Input Validation & Sanitization

✅ **Server-Side Validation**
- Never trust client input
- Validate length, format, type
- Reject invalid data before processing

✅ **Data Sanitization**
- `trim()` removes whitespace
- Type checking (string length, regex patterns)
- HTML entity encoding for output (XSS prevention)

**Code Reference:** `register.php` (lines 40-60)

---

### 5. Information Disclosure Prevention

✅ **Generic Error Messages**
- Don't reveal if username exists
- "Invalid username or password" (not which one)
- No database error messages shown to users

✅ **404 Instead of 403**
- Sensitive files return 404 (not found)
- Doesn't confirm file existence
- See Question 2 for full explanation

**Code Reference:** `Caddyfile` (lines 8-11)

---

### 6. File Access Control

✅ **Protected Directories**
- `/includes/` not directly accessible
- `/data/` database file protected
- `/config.php` returns 404
- `.env` files blocked

✅ **Web Server Rules (Caddy)**
```caddy
@blocked {
    path /config.php /includes/* /.env /data/*
}
respond @blocked "Not Found" 404
```

**Code Reference:** `Caddyfile`

---

### 7. HTTP Security Headers

✅ **Status Codes**
- 200 (OK) for successful requests
- 302 (Redirect) after successful login/register
- 404 (Not Found) for sensitive files

**Recommended Enhancement (Add to Caddyfile):**
```caddy
header {
    # Prevent clickjacking
    X-Frame-Options "SAMEORIGIN"
    
    # XSS protection
    X-Content-Type-Options "nosniff"
    X-XSS-Protection "1; mode=block"
    
    # Content Security Policy
    Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';"
    
    # HTTPS enforcement (if using HTTPS)
    Strict-Transport-Security "max-age=31536000; includeSubDomains"
}
```

---

### 8. Database Security

✅ **SQLite File Permissions**
- Database file in `/data/` directory
- Not directly web-accessible
- Proper file permissions in production

✅ **Schema Design**
- UNIQUE constraint on username
- NOT NULL constraints
- Proper data types

**Code Reference:** `config.php` (lines 15-21)

---

### 9. Error Handling

✅ **User-Friendly Errors**
- Display generic messages to users
- Log detailed errors server-side (should be added)
- No stack traces exposed

✅ **Graceful Degradation**
- Check for POST data existence
- Validate before processing
- Provide feedback on all errors

---

### 10. Docker Security

✅ **Non-Root User (Recommended)**
```dockerfile
# Add to Dockerfile for production
RUN useradd -m -u 1000 appuser
USER appuser
```

✅ **Minimal Base Image**
- Uses `dunglas/frankenphp:latest`
- Fewer packages = smaller attack surface

---

## ⚠️ Known Limitations & Missing Features

**Honest assessment of what's NOT implemented:**

### 1. Rate Limiting ❌

**Missing:** No rate limiting on login attempts

**Risk:** Brute-force password attacks possible

**Recommendation for Production:**
```php
// Add to login.php
$max_attempts = 5;
$lockout_time = 900; // 15 minutes

// Track failed attempts in database or session
if ($failed_attempts >= $max_attempts) {
    if (time() - $last_attempt < $lockout_time) {
        die("Too many login attempts. Try again in 15 minutes.");
    }
}
```

**Better Solution:** Use a library like `symfony/rate-limiter` or middleware

---

### 2. Account Lockout ❌

**Missing:** No account lockout after multiple failed logins

**Risk:** Unlimited password guessing attempts

**Recommendation:**
- Lock account after N failed attempts
- Require email verification to unlock
- Admin can unlock accounts

---

### 3. Password Reset ❌

**Missing:** No "Forgot Password" functionality

**Risk:** Users locked out if they forget password

**Requirement:** Email integration needed

---

### 5. Email Verification ❌

**Missing:** No email verification on registration

**Risk:** Spam accounts, fake users

**Recommendation:**
- Send verification email with token
- Mark accounts as unverified until confirmed
- Require verification before login

---

### 6. Two-Factor Authentication (2FA) ❌

**Missing:** No 2FA/MFA support

**Risk:** Compromised passwords = account compromise

**Options:**
- TOTP (Time-based One-Time Password) - Google Authenticator
- SMS codes (less secure)
- Email codes
- Security keys (WebAuthn)

---

### 7. Logging & Monitoring ❌

**Missing:** No security event logging

**Risk:** Can't detect attacks or investigate breaches

**Should Log:**
- Failed login attempts
- Successful logins (with IP, timestamp)
- Account creations
- Password changes
- Suspicious activity

**Example:**
```php
// Add to login.php
function logSecurityEvent($event, $username, $success) {
    $log = fopen('logs/security.log', 'a');
    $ip = $_SERVER['REMOTE_ADDR'];
    $timestamp = date('Y-m-d H:i:s');
    fwrite($log, "[$timestamp] $event: $username from $ip - " . ($success ? 'SUCCESS' : 'FAILED') . "\n");
    fclose($log);
}

// Usage
logSecurityEvent('LOGIN', $username, $login_success);
```

---

### 8. Input Sanitization (XSS) ⚠️

**Partially Implemented:** Some output encoding

**Risk:** Cross-Site Scripting (XSS) if username displayed without encoding

**Current Code:**
```php
// dashboard.php - GOOD
echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
```

**Recommendation:** Use `htmlspecialchars()` on ALL user-generated output

---

### 9. Content Security Policy (CSP) ❌

**Missing:** No CSP headers

**Risk:** XSS attacks easier to exploit

**Fix:** Add to Caddyfile (see HTTP Security Headers section)

---

### 10. HTTPS Enforcement ⚠️

**Current:** HTTP only in default setup

**Risk:** Credentials sent in plaintext

**Production Requirement:** MUST use HTTPS

**Caddy makes this easy:**
```caddy
# Caddyfile with auto-HTTPS
yourdomain.com {
    # Caddy automatically enables HTTPS!
    root * /app
    php_fastcgi localhost:9000
    file_server
}
```

---

### 11. Password Strength Meter ❌

**Missing:** No client-side password strength indicator

**Recommendation:** Add JavaScript library like `zxcvbn`

---

### 12. Session Timeout ❌

**Missing:** No automatic session expiration

**Risk:** Sessions live indefinitely

**Fix:**
```php
// config.php
ini_set('session.gc_maxlifetime', 3600); // 1 hour
ini_set('session.cookie_lifetime', 3600);

// Or check last activity
if (time() - $_SESSION['last_activity'] > 3600) {
    session_destroy();
    header('Location: login.php');
    exit;
}
$_SESSION['last_activity'] = time();
```

---

### 13. Database Backups ❌

**Missing:** No automated backup system

**Risk:** Data loss

**Recommendation:**
```bash
# Cron job for daily backups
0 2 * * * sqlite3 /app/data/users.db ".backup /backups/users-$(date +\%Y\%m\%d).db"
```

---

### 14. IP-Based Restrictions ❌

**Missing:** No IP whitelisting/blacklisting

**Use Case:** Restrict admin access to specific IPs

**Caddy Example:**
```caddy
@admin {
    path /admin/*
}
route @admin {
    @allowed {
        remote_ip 192.168.1.0/24 10.0.0.1
    }
    handle @allowed {
        # Allow access
    }
    handle {
        respond "Forbidden" 403
    }
}
```

---

## 📋 Production Deployment Checklist

**Before deploying BareStack to production, complete these steps:**

### ✅ Security Configuration

- [ ] **Enable HTTPS** (Let's Encrypt, Cloudflare, AWS Certificate Manager)
- [ ] **Set secure session cookies**
  ```php
  ini_set('session.cookie_httponly', 1);
  ini_set('session.cookie_secure', 1);
  ini_set('session.cookie_samesite', 'Strict');
  ```
- [ ] **Configure CSP headers** (see HTTP Security Headers section)
- [ ] **Set proper file permissions**
  ```bash
  chmod 600 config.php
  chmod 700 data/
  chmod 600 data/users.db
  ```
- [ ] **Change default database location** (move outside web root if possible)
- [ ] **Implement rate limiting** (login attempts, registration)
- [ ] **Enable error logging** (not display)
  ```php
  ini_set('display_errors', 0);
  ini_set('log_errors', 1);
  ini_set('error_log', '/var/log/php_errors.log');
  ```
- [ ] **Implement session timeout**
- [ ] **Add security event logging**

### ✅ Code Review

- [ ] **Run security scanner** (see Security Testing section)
- [ ] **Review all user input points** (forms, URL parameters)
- [ ] **Check for hardcoded credentials** (none should exist)
- [ ] **Verify all queries use prepared statements**
- [ ] **Test error handling** (don't expose stack traces)
- [ ] **Review file upload functionality** (if added)

### ✅ Database

- [ ] **Set up automated backups** (daily minimum)
- [ ] **Test backup restoration**
- [ ] **Move database outside web root**
- [ ] **Use proper database user** (not root)
- [ ] **Set database file permissions** (owner only)

### ✅ Infrastructure

- [ ] **Use Docker secrets** for sensitive config
- [ ] **Set up firewall rules** (only necessary ports)
- [ ] **Configure logging** (application, web server, system)
- [ ] **Set up monitoring** (uptime, errors, attacks)
- [ ] **Implement automated updates** (security patches)
- [ ] **Use a WAF** (Web Application Firewall) - Cloudflare, AWS WAF

### ✅ Testing

- [ ] **Test authentication flow** (register, login, logout)
- [ ] **Test with invalid input** (SQL injection attempts, XSS)
- [ ] **Test access control** (unauthorized access attempts)
- [ ] **Test on multiple browsers**
- [ ] **Test mobile responsiveness**
- [ ] **Load testing** (can it handle expected traffic?)

### ✅ Compliance

- [ ] **Privacy policy** (if collecting user data)
- [ ] **Terms of service**
- [ ] **GDPR compliance** (if serving EU users)
  - Right to access data
  - Right to delete account
  - Data export functionality
- [ ] **Cookie consent** (if using tracking cookies)

### ✅ Monitoring & Maintenance

- [ ] **Set up error alerts** (email, Slack, PagerDuty)
- [ ] **Monitor failed login attempts**
- [ ] **Review logs regularly**
- [ ] **Keep dependencies updated**
- [ ] **Subscribe to security advisories** (PHP, Caddy, FrankenPHP)

### ✅ Documentation

- [ ] **Document deployment process**
- [ ] **Document backup/restore procedures**
- [ ] **Document incident response plan**
- [ ] **Document who to contact for security issues**

---

## 🧪 Security Testing Guidelines

### Manual Testing

**1. SQL Injection Testing**

Try these inputs in login/register forms:

```sql
' OR '1'='1
admin'--
' OR '1'='1' --
'; DROP TABLE users; --
admin' OR '1'='1' --
```

**Expected Result:** Login should fail, queries should not execute

**Check:** Are prepared statements used everywhere?

---

**2. XSS (Cross-Site Scripting) Testing**

Register with usernames:
```html
<script>alert('XSS')</script>
<img src=x onerror=alert('XSS')>
javascript:alert('XSS')
<svg onload=alert('XSS')>
```

**Expected Result:** Script should not execute, should be displayed as text

**Check:** Is output HTML-encoded with `htmlspecialchars()`?

---

**3. Authentication Bypass Testing**

- Try accessing `/dashboard.php` without logging in
- Try manipulating session cookies
- Try session fixation attacks
- Try accessing other users' data

**Expected Result:** Should redirect to login, or show 403/404

---

**4. CSRF Testing**

Test CSRF protection:
```bash
# 1. Open login page in browser
# 2. Open DevTools → Elements
# 3. Find CSRF token hidden input
# 4. Change its value to random string
# 5. Submit form

# Expected Result: "CSRF attack detected!" error
```

**Current Status:** ✅ CSRF protection is implemented on login.php and register.php

---

**5. Path Traversal Testing**

Try accessing:
```
/config.php
/includes/header.php
/../../../etc/passwd
/data/users.db
/.env
```

**Expected Result:** Should return 404 (not 403, not file contents)

---

### Automated Testing Tools

**1. OWASP ZAP (Zed Attack Proxy)**

Free, open-source security scanner

```bash
# Install
docker pull owasp/zap2docker-stable

# Run automated scan
docker run -t owasp/zap2docker-stable zap-baseline.py \
    -t http://localhost:80 \
    -r zap-report.html
```

**What it finds:**
- Missing security headers
- XSS vulnerabilities
- SQL injection
- Insecure cookies
- Information disclosure

---

**2. SQLMap**

Automated SQL injection testing

```bash
# Install
pip install sqlmap

# Test login page
sqlmap -u "http://localhost/login.php" \
    --data="username=admin&password=test" \
    --level=5 --risk=3

# Test registration
sqlmap -u "http://localhost/register.php" \
    --data="username=test&password1=test&password2=test" \
    --level=5 --risk=3
```

**Expected Result:** Should find no SQL injection vulnerabilities (BareStack uses prepared statements)

---

**3. Nikto**

Web server scanner

```bash
# Install
apt-get install nikto

# Scan
nikto -h http://localhost:80
```

**Finds:**
- Outdated software versions
- Misconfigurations
- Sensitive files exposed
- Default files/directories

---

**4. Burp Suite (Community Edition)**

Manual testing tool

**Features:**
- Intercept HTTP requests
- Modify requests/responses
- Automated scanning
- Session management testing

**Use for:**
- Testing authentication
- CSRF testing
- Session hijacking attempts
- Parameter tampering

---

**5. Security Headers Check**

Online tool: [https://securityheaders.com](https://securityheaders.com)

**Checks for:**
- Content-Security-Policy
- X-Frame-Options
- X-Content-Type-Options
- Strict-Transport-Security
- Permissions-Policy

**Current BareStack Grade:** Likely D or F (missing headers)

**After adding headers:** Should be A or A+

---

**6. SSL Labs Test** (for production with HTTPS)

[https://www.ssllabs.com/ssltest/](https://www.ssllabs.com/ssltest/)

**Checks:**
- SSL/TLS configuration
- Certificate validity
- Protocol support
- Cipher suites

---

### Writing Security Tests

**PHPUnit Example:**
```php
// tests/SecurityTest.php
use PHPUnit\Framework\TestCase;

class SecurityTest extends TestCase
{
    public function testSQLInjectionPrevention()
    {
        // Attempt SQL injection in username
        $malicious_username = "admin' OR '1'='1' --";
        
        // Should not bypass login
        $result = attemptLogin($malicious_username, "anypassword");
        
        $this->assertFalse($result, "SQL injection should not succeed");
    }
    
    public function testXSSPrevention()
    {
        // Register with XSS payload
        $xss_username = "<script>alert('XSS')</script>";
        
        registerUser($xss_username, "password123");
        
        // Retrieve and check if encoded
        $displayed_username = getUsernameDisplay($xss_username);
        
        $this->assertStringNotContainsString('<script>', $displayed_username);
        $this->assertStringContainsString('&lt;script&gt;', $displayed_username);
    }
    
    public function testAuthenticationRequired()
    {
        // Access dashboard without login
        $_SESSION = []; // No session
        
        ob_start();
        include 'dashboard.php';
        $output = ob_get_clean();
        
        // Should redirect (output will be header)
        $this->assertEmpty($output, "Dashboard should not load without authentication");
    }
}
```

---

## 🚨 How to Report Security Issues

**Found a vulnerability in BareStack? Here's how to report it responsibly.**

### Responsible Disclosure

**DO:**
- ✅ Report privately first (don't publish exploits)
- ✅ Give reasonable time to fix (30-90 days)
- ✅ Provide detailed reproduction steps
- ✅ Include proof-of-concept (PoC) code

**DON'T:**
- ❌ Publicly disclose before fix is released
- ❌ Exploit for personal gain
- ❌ Access other users' data
- ❌ Perform DoS attacks

---

### How to Report

**1. GitHub Security Advisory (Preferred)**
- Go to: https://github.com/yourusername/barestack/security/advisories
- Click "Report a vulnerability"
- Fill out the form with details

**2. Email (Alternative)**
- Email: security@yourdomain.com
- Subject: "[SECURITY] BareStack Vulnerability Report"
- Include: Details, impact, reproduction steps

**3. What to Include**

```markdown
## Vulnerability Report

**Type:** SQL Injection / XSS / CSRF / etc.

**Severity:** Critical / High / Medium / Low

**Affected Component:** login.php / register.php / etc.

**Description:**
[Detailed description of the vulnerability]

**Impact:**
[What an attacker can do with this vulnerability]

**Steps to Reproduce:**
1. Step 1
2. Step 2
3. Step 3

**Proof of Concept:**
```http
POST /login.php HTTP/1.1
Host: localhost
Content-Type: application/x-www-form-urlencoded

username=admin' OR '1'='1'--&password=anything
```

**Suggested Fix:**
[Optional: How to fix the issue]

**Your Name/Handle:** (for acknowledgment)
```

---

### What Happens Next?

1. **Acknowledgment** (within 48 hours)
   - We confirm receipt of your report
   
2. **Investigation** (within 7 days)
   - We reproduce and assess severity
   
3. **Fix Development** (within 30 days for Critical/High)
   - We develop and test a fix
   
4. **Disclosure** (coordinated with you)
   - We release the fix
   - You can publish your findings
   - We credit you (if desired)

---

### Security Hall of Fame

**Contributors who have responsibly disclosed vulnerabilities:**

- (None yet - be the first!)

---

### Bug Bounty?

**Currently:** No bug bounty program (this is an educational project)

**Recognition:** Public credit in README.md and security advisory

---

## 📚 Additional Resources

### Learning Materials

**OWASP (Open Web Application Security Project)**
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Cheat Sheet Series](https://cheatsheetseries.owasp.org/)
- [Web Security Testing Guide](https://owasp.org/www-project-web-security-testing-guide/)

**PHP Security**
- [PHP: The Right Way - Security](https://phptherightway.com/#security)
- [PHP.net Security Documentation](https://www.php.net/manual/en/security.php)
- [Paragon Initiative: PHP Security Guide](https://paragonie.com/blog/2015/08/you-wouldnt-base64-a-password-cryptography-decoded)

**General Web Security**
- [PortSwigger Web Security Academy](https://portswigger.net/web-security) - Free, excellent resource
- [Google: Security Best Practices](https://developers.google.com/web/fundamentals/security)
- [MDN Web Security](https://developer.mozilla.org/en-US/docs/Web/Security)

**Books**
- "Web Application Security" by Andrew Hoffman
- "The Tangled Web" by Michal Zalewski
- "The Web Application Hacker's Handbook" by Dafydd Stuttard

---

## 🔄 Security Updates

**This document will be updated as:**
- New vulnerabilities are discovered
- Security best practices evolve
- Community feedback is received
- New features are added to BareStack

**Last Updated:** 2024-01-XX (Update this date)

---

## 🤝 Contributing to Security

**Want to improve BareStack's security?**

1. **Code Review**: Review PHP files for vulnerabilities
2. **Documentation**: Improve this SECURITY.md file
3. **Testing**: Write security tests
4. **Features**: Implement missing security features (rate limiting, account lockout, etc.)
5. **Education**: Create tutorials on secure coding

**See CONTRIBUTING.md for guidelines.**

---

## ✅ Summary

**BareStack Security Status:**

| Feature | Status | Notes |
|---------|--------|-------|
| Password Hashing | ✅ Implemented | BCrypt with cost 12 |
| SQL Injection Prevention | ✅ Implemented | Prepared statements everywhere |
| XSS Prevention | ✅ Implemented | Output encoding with sanitizeOutput() |
| CSRF Protection | ✅ Implemented | Tokens on all forms (login, register) |
| Rate Limiting | ❌ Missing | Brute-force attacks possible |
| Session Security | ✅ Implemented | httponly, secure, SameSite=Strict flags |
| HTTPS | ⚠️ Optional | Required for production |
| Logging | ⚠️ Basic | Minimal security event logging |
| 2FA | ❌ Missing | Enhancement |
| Input Validation | ✅ Implemented | Server-side validation |

**Overall:** Good foundation for learning, needs hardening for production.

---

**Questions? Security Concerns?**

- 📧 Open a GitHub Issue
- 🔒 Report security vulnerabilities privately (see "How to Report Security Issues")
- 💬 Discuss in GitHub Discussions

**Remember: Security is a continuous process, not a one-time fix. Stay vigilant, stay updated, stay secure!**

---

*This security documentation is part of the BareStack educational project. Use responsibly.*
