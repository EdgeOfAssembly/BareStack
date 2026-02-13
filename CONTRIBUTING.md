# Contributing to BareStack

Thank you for your interest in contributing to **BareStack**! This project teaches fullstack web development without framework complexity, and we welcome contributions that enhance this educational mission.

## 🎯 Project Philosophy

**Clarity over cleverness.** BareStack is about understanding fundamentals, not showing off advanced techniques.

### Core Principles

1. **Educational First**: Every line of code should teach something
2. **No Framework Complexity**: Pure PHP, HTML, CSS, JavaScript
3. **Security by Implementation**: Show security features, don't hide them
4. **Beginner-Friendly**: Clear code with detailed comments
5. **Professional Snark**: Memorable but not obnoxious
6. **Real Fullstack**: Frontend, backend, database, security, devops

## 🤝 Types of Contributions

### ✅ Contributions We Love

**Themes** (easiest way to start!)
- New visual themes (cyberpunk, forest, space, etc.)
- Accessibility-focused themes
- Animation and effects
- See "Adding New Themes" section below

**Documentation**
- Clearer explanations of security concepts
- More code comments
- Learning resource links
- Typo fixes and grammar improvements
- Translations to other languages

**Code Improvements**
- Better educational comments
- Security improvements with clear explanations
- Code clarity enhancements
- Bug fixes with test cases

**Educational Content**
- Tutorial blog posts using BareStack
- Video walkthroughs
- Security testing examples
- Challenge exercises for learners

### ❌ Contributions We Won't Accept

**Defeats Project Purpose:**
- Adding npm, webpack, or any build tools
- Integrating React, Vue, Angular, or frameworks
- Adding TypeScript compilation
- Package manager dependencies

**Adds Complexity Without Value:**
- Overcomplicated abstractions
- "Enterprise" patterns that obscure learning
- Features without documentation/comments
- Magic that beginners can't understand

**Security Changes Without Explanation:**
- Security features without educational comments
- Copied code without understanding why
- Trends without proven security benefit

### 🤔 Maybe (Discuss First)

**Could Go Either Way:**
- Additional production features (rate limiting, etc.)
- Database migrations to PostgreSQL/MySQL
- Admin panels or user management
- API authentication methods
- Deployment automation

**How to Propose:**
1. Open an issue explaining the educational value
2. Show how it teaches fundamentals
3. Explain complexity trade-offs
4. Get maintainer feedback before coding

## 🚀 How to Contribute

### Quick Start

```bash
# 1. Fork the repository on GitHub (click "Fork" button)

# 2. Clone YOUR fork (not the original)
git clone https://github.com/YOUR_USERNAME/php-app.git
cd php-app

# 3. Create a feature branch
git checkout -b feature/my-awesome-contribution

# 4. Make your changes (code, docs, themes)

# 5. Test thoroughly
./frankenphp php-server --listen 127.0.0.1:8080
# Test in browser at http://localhost:8080

# 6. Commit with clear message
git add .
git commit -m "Add [feature]: clear description"

# 7. Push to your fork
git push origin feature/my-awesome-contribution

# 8. Open Pull Request on GitHub
# Go to original repo, click "Pull Requests" → "New Pull Request"
```

### Reporting Bugs

**Before reporting:**
1. Update to latest code (might be fixed)
2. Check existing issues
3. Try to reproduce consistently

**When reporting:**
```markdown
**Bug Description**
Clear description of what's wrong

**Steps to Reproduce**
1. Go to login page
2. Enter username 'test'
3. Click submit
4. See error message

**Expected Behavior**
Should redirect to dashboard

**Actual Behavior**
Shows "Database error" message

**Environment**
- OS: Ubuntu 22.04
- PHP: 8.1.2
- Browser: Firefox 122
- Server: FrankenPHP 1.11.2

**Screenshots**
[If applicable]
```

### Suggesting Enhancements

**Enhancement Template:**
```markdown
**Feature Description**
What feature do you want to add?

**Educational Value**
How does this help people learn?

**Implementation Ideas**
Rough plan for how it could be implemented

**Alternatives Considered**
What other approaches did you consider?

**Additional Context**
Links, examples, mockups, etc.
```
├── *.php                   # Main application files
└── *.md                    # Documentation
```

## Code Style Guidelines

### PHP Code Style

#### General Principles

- **Clarity over cleverness**: Code should be easy to understand
- **Comment for education**: Explain WHY, not just WHAT
- **Security first**: Always validate input, escape output

#### Formatting

```php
<?php
// Use spaces, not tabs (4 spaces per indent)
// Opening brace on same line for functions
function myFunction() {
    // Code here
}

// Opening brace on new line for control structures
if ($condition)
{
    // Code here
}

// Use meaningful variable names
$userPassword = 'secret';  // Good
$up = 'secret';            // Bad

// Constants in UPPER_CASE
define('MAX_LOGIN_ATTEMPTS', 5);

// Functions in camelCase
function validateUserInput($input) {
    // ...
}
```

#### Security Patterns

Always follow these security patterns:

```php
// 1. ALWAYS use prepared statements
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);

// 2. ALWAYS escape output
echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');

// 3. ALWAYS validate input
if (!validateUsername($username)) {
    die('Invalid username');
}

// 4. ALWAYS verify CSRF tokens
if (!verifyCSRFToken($_POST['csrf_token'])) {
    die('CSRF attack detected!');
}
```

#### Documentation Comments

```php
/**
 * Brief description of function
 * 
 * Longer description explaining:
 * - What it does
 * - Why it exists
 * - Security considerations
 * 
 * @param string $username Username to validate
 * @return bool True if valid, false otherwise
 */
function validateUsername($username) {
    // Implementation
}
```

### CSS Code Style

```css
/* Use comments to explain theme choices */
body { 
    background: #1a1a2e;  /* Dark blue background */
    color: #e0e0e0;       /* Light gray text */
}

/* Group related styles */
.card {
    /* Layout */
    padding: 1.5em;
    margin: 1em 0;
    
    /* Appearance */
    background: rgba(30, 30, 50, 0.95);
    border: 1px solid #2a2a4a;
    border-radius: 10px;
    
    /* Effects */
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
}
```

### JavaScript Code Style

```javascript
// Use const/let, not var
const username = document.getElementById('username');
let attempts = 0;

// Use meaningful function names
function updateDashboardStats() {
    // Implementation
}

// Add comments for security-critical code
// Verify CSRF token before submission
if (!csrfToken) {
    console.error('Missing CSRF token');
    return false;
}
```

## 🎨 Adding a New Theme (Complete Guide)

Themes are **the best way for beginners to contribute!** No backend knowledge needed - just CSS creativity.

### Step-by-Step Theme Creation

#### 1. Create Your Theme File

Create `themes/yourtheme.css`:

```css
/* themes/yourtheme.css */

/**
 * Theme Name: Your Theme Name
 * Description: Brief description of your theme
 * Author: Your Name
 * Inspired by: [Optional inspiration source]
 */

/* ======================
   BASE STYLES (REQUIRED)
   ====================== */

body {
    background: #your-background-color;
    color: #your-text-color;
    font-family: 'Font Name', sans-serif;
}

/* ======================
   HEADER STYLES (REQUIRED)
   ====================== */

.header h1 {
    color: #your-heading-color;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.header p {
    color: #your-subheading-color;
}

/* ======================
   CARD STYLES (REQUIRED)
   ====================== */

.card {
    background: rgba(your, rgba, values, 0.9);
    border: 1px solid #your-border-color;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(10px);
}

.card h3 {
    color: #your-card-heading-color;
    border-bottom: 2px solid #your-accent-color;
    padding-bottom: 0.5em;
}

/* ======================
   PROGRESS BARS (REQUIRED)
   ====================== */

.progress {
    background: rgba(your, progress, bg, 0.3);
    border: 1px solid #your-progress-border;
}

.bar {
    /* Use gradient for visual appeal */
    background: linear-gradient(
        to right, 
        #color-low,    /* Low usage - green */
        #color-med,    /* Medium - yellow */
        #color-high    /* High - red */
    );
}

/* ======================
   BUTTONS & LINKS (REQUIRED)
   ====================== */

.logout a {
    color: #your-link-text;
    padding: 0.7em 1.5em;
    background: #your-button-bg;
    border: 2px solid #your-button-border;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.logout a:hover {
    background: #your-hover-bg;
    transform: translateY(-2px);
}

.theme-select select {
    background: #your-select-bg;
    color: #your-select-text;
    border: 2px solid #your-select-border;
    padding: 0.6em 1.2em;
    border-radius: 8px;
}

/* ======================
   HIDE OTHER THEMES' ELEMENTS
   ====================== */

.matrix-column,
.snowflake,
.snowman {
    display: none;
}

/* ======================
   OPTIONAL: THEME-SPECIFIC ANIMATIONS
   ====================== */

/* Example: Pulse animation for cards */
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.8; }
}

/* Example: Floating animation */
@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

/* Apply to cards */
.card {
    animation: float 6s ease-in-out infinite;
}

/* ======================
   OPTIONAL: CUSTOM ELEMENTS
   ====================== */

/* If your theme has special visual elements like
   Matrix rain or snow, you can add them here */
```

#### 2. Add Theme to Dashboard Selector

Edit `dashboard.php` around line 136-143:

```php
<select id="theme-select">
    <option value="light">Light Theme</option>
    <option value="dark">Dark Theme</option>
    <option value="matrix">Matrix Theme</option>
    <option value="terminal">Terminal Theme</option>
    <option value="ocean">Ocean Theme</option>
    <option value="snow">Snow Theme</option>
    <option value="yourtheme">Your Theme Name</option> <!-- ADD THIS -->
</select>
```

#### 3. Test Your Theme

```bash
# Start server
./frankenphp php-server --listen 127.0.0.1:8080

# Open browser
# Navigate to http://localhost:8080/dashboard.php
# Select your theme from dropdown
```

**Testing Checklist:**
- [ ] All text is readable (good contrast)
- [ ] Progress bars are visible
- [ ] Buttons/links are clickable and styled
- [ ] Theme persists after page refresh (localStorage)
- [ ] Works on different screen sizes
- [ ] No console errors

#### 4. Design Guidelines

**Accessibility (IMPORTANT!)**
- Contrast ratio ≥ 4.5:1 for normal text ([Check here](https://webaim.org/resources/contrastchecker/))
- Contrast ratio ≥ 3:1 for large text (18px+)
- Test with colorblind simulators

**Performance**
- Avoid heavy animations on elements that update frequently
- Use CSS animations, not JavaScript for effects
- Keep file size reasonable (< 50KB)

**Consistency**
- Style ALL required elements (don't leave any unstyled)
- Match the tone of other themes (professional but fun)
- Consider light and dark monitor settings

**Creativity Encouraged!**
- Add subtle animations
- Use unique color schemes
- Try creative backgrounds
- Add theme-specific decorations

#### 5. Theme Ideas & Examples

**Beginner-Friendly Ideas:**
- **Sunset**: Oranges, pinks, purples gradient
- **Forest**: Greens, browns, nature tones
- **Minimalist**: Pure black and white
- **Cyberpunk**: Neon pinks and blues
- **Retro**: Beige, brown, vintage computer

**Advanced Ideas:**
- **Space**: Stars, cosmic gradients, floating animation
- **Underwater**: Blues, bubbles, wave effects
- **Neon City**: Glowing effects, bright accents
- **Paper**: Texture, ink effects, handwriting font
- **Glitch**: Digital corruption effects

#### 6. Submit Your Theme

```bash
# Create branch
git checkout -b feature/add-mytheme-theme

# Add files
git add themes/yourtheme.css dashboard.php

# Commit
git commit -m "Add [Theme Name] theme

- Color scheme: [describe]
- Inspired by: [inspiration]
- Features: [list special features]"

# Push
git push origin feature/add-mytheme-theme

# Open Pull Request on GitHub
```

**Pull Request Template:**
```markdown
## Theme Name: [Your Theme Name]

**Color Scheme:** Describe the main colors and mood

**Inspiration:** What inspired this theme?

**Special Features:**
- [ ] Custom animations
- [ ] Unique background
- [ ] Special effects
- [ ] Accessibility-focused

**Screenshot:**
[Attach screenshot of dashboard with your theme]

**Testing:**
- [ ] Tested in Chrome
- [ ] Tested in Firefox
- [ ] Tested responsive design
- [ ] Contrast ratios checked
- [ ] No console errors
```

### Example Themes to Study

1. **themes/matrix.css** - Complex: animations, character generation
2. **themes/snow.css** - Medium: snowflake effects, animations
3. **themes/dark.css** - Simple: solid colors, minimal effects
4. **themes/light.css** - Simple: clean, professional

---

## 🔒 Security Contribution Guidelines

Contributing security improvements? **Excellent!** But follow these rules:

### Security Contribution Principles

1. **Explain Your Changes**
   - Don't just fix - teach WHY it was vulnerable
   - Add comments explaining the security benefit
   - Update SECURITY.md if relevant

2. **Follow Established Patterns**
   - Use existing security functions when possible
   - Match the code style of includes/security.php
   - Don't reinvent the wheel

3. **Test Thoroughly**
   - Show the vulnerability (safely)
   - Demonstrate your fix works
   - Include test cases if possible

4. **Cite Your Sources**
   - Link to OWASP documentation
   - Reference CVE if applicable
   - Show research/learning path

### Security Changes We Welcome

✅ **Improvements to Existing Features:**
- Better CSRF implementation
- Enhanced session security
- Additional security headers
- Input validation improvements

✅ **Educational Additions:**
- More detailed security comments
- Example exploits (documented safely)
- Security testing scripts
- Educational security tooling

✅ **Documentation:**
- Security.md improvements
- Vulnerability explanations
- Best practices documentation
- Security testing guides

### Security Changes That Need Discussion

🤔 **Require Maintainer Approval:**
- Changing core security mechanisms
- Adding new security libraries
- Cryptography changes
- Authentication flow changes

**Process:**
1. Open an issue describing the problem
2. Explain your proposed solution
3. Get maintainer feedback
4. Then implement if approved

### Security Testing Checklist

Before submitting security improvements:

```bash
# Test SQL Injection Protection
# Try: admin' OR '1'='1
# Expected: Login fails safely

# Test XSS Protection
# Try: <script>alert('XSS')</script> as username
# Expected: Displayed as text, not executed

# Test CSRF Protection
# Try: Submit form without valid CSRF token
# Expected: "CSRF attack detected!" error

# Test Session Security
# Check: HttpOnly, Secure, SameSite flags in cookies
# Expected: All flags present

# Test File Protection
curl -I http://localhost:8080/data/users.db
# Expected: 404 Not Found (not 403!)

curl -I http://localhost:8080/config.php
# Expected: 404 Not Found (not 403!)

# Test Security Headers
curl -I http://localhost:8080/login.php | grep -i "x-frame\|x-content\|x-xss"
# Expected: All headers present
```

### Example Security Contribution

**Good Security PR:**

```markdown
## Security Improvement: Implement SameSite Cookie Attribute

**Problem:**
Current session cookies don't have SameSite attribute, leaving them 
vulnerable to CSRF attacks via cross-site form submissions.

**Solution:**
Added SameSite=Strict to session cookie configuration in 
includes/session.php

**Educational Value:**
- Explains what SameSite does
- Shows CSRF attack vector it prevents
- Includes link to OWASP documentation
- Tests included

**Changes:**
- includes/session.php: Added SameSite=Strict
- SECURITY.md: Documented the protection
- Added inline comments explaining the security benefit

**References:**
- [OWASP Session Management Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html#samesite-attribute)
- [MDN SameSite Cookies](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie/SameSite)

**Testing:**
Verified session cookie now has SameSite=Strict attribute in browser DevTools.
```

**Bad Security PR:**

```markdown
## Fixed security

Changed some stuff for security. Trust me it's better now.
```

*(No explanation, no education, no context = rejected)*

---

## Adding New Features

### Before You Start

1. Open an issue to discuss the feature
2. Ensure it adds educational value
3. Check it aligns with project philosophy (no frameworks!)
4. Plan documentation alongside code

### Feature Development Process

**1. Design Phase**
```markdown
## Feature: [Name]

**What:** Brief description

**Why:** Educational value - what will users learn?

**How:** Implementation approach

**Examples:** Similar features in other projects

**Documentation:** What docs need updating?
```

**2. Implementation Phase**
- Write code with extensive comments
- Follow existing code style
- Keep it simple (beginners should understand it)
- Add security considerations

**3. Documentation Phase**
- Update README.md if user-facing
- Update SECURITY.md if security-related
- Add inline comments explaining WHY
- Update CONTRIBUTING.md if needed

**4. Testing Phase**
- Manual testing (all user flows)
- Security testing (if applicable)
- Cross-browser testing (if frontend)
- Error handling testing

**5. Review Phase**
- Self-review the code
- Check all comments are clear
- Verify documentation is complete
- Test one more time

---

## 📋 Pull Request Process

### Before Submitting

**Pre-Flight Checklist:**
- [ ] Code follows style guidelines
- [ ] All manual tests passed
- [ ] Security tested (if applicable)
- [ ] Documentation updated
- [ ] Code comments are educational
- [ ] No debug code left in
- [ ] No console.log() statements
- [ ] Git history is clean

### PR Title Format

```
[Type] Brief description (max 50 chars)

Types:
- [Feature] - New functionality
- [Theme] - New theme
- [Fix] - Bug fix
- [Docs] - Documentation only
- [Security] - Security improvement
- [Refactor] - Code cleanup
```

**Examples:**
```
[Theme] Add cyberpunk neon theme
[Feature] Add rate limiting for login
[Fix] Correct CSRF token validation
[Docs] Improve BCrypt explanation
[Security] Add SameSite cookie attribute
```

### PR Description Template

```markdown
## Description
Brief summary of changes

## Type of Change
- [ ] Bug fix (non-breaking change fixing an issue)
- [ ] New feature (non-breaking change adding functionality)
- [ ] Breaking change (fix or feature causing existing functionality to break)
- [ ] Documentation update
- [ ] Security improvement
- [ ] New theme

## Educational Value
How does this help people learn? What concepts does it teach?

## Changes Made
- Bullet list of specific changes
- Include file names
- Explain WHY not just WHAT

## Testing Performed
- [ ] Manual testing (describe what you tested)
- [ ] Security testing (if applicable)
- [ ] Browser testing (Chrome, Firefox, etc.)
- [ ] PHP version testing (if applicable)
- [ ] Mobile/responsive testing (if frontend)

## Screenshots
If UI changes, include before/after screenshots

## Documentation Updated
- [ ] README.md (if needed)
- [ ] SECURITY.md (if security-related)
- [ ] CONTRIBUTING.md (if process changed)
- [ ] Inline code comments added

## References
Links to:
- Related issues
- Documentation you followed
- Resources you learned from

## Checklist
- [ ] Code follows project style guidelines
- [ ] Self-reviewed my code
- [ ] Commented complex code sections
- [ ] Made corresponding documentation changes
- [ ] No new warnings or errors
- [ ] Security implications considered
- [ ] Educational value is clear
```

### Review Process

1. **Automated Checks** - GitHub Actions (if configured)
2. **Maintainer Review** - Core team reviews code
3. **Feedback Round** - Requested changes
4. **Approval** - Maintainer approves
5. **Merge** - Merged into main branch
6. **Recognition** - Added to contributors list!

### After Your PR is Merged

**Celebrate! 🎉** You've helped people learn!

Then:
1. Update your fork
2. Delete your feature branch
3. Look for other issues to contribute to
4. Share your experience (blog, social media, etc.)

---

## 🎓 Learning Path for Contributors

### First Contribution (Easy)

**Theme Contribution:**
1. Fork repository
2. Create new CSS file in themes/
3. Add to theme selector
4. Test thoroughly
5. Submit PR

**Estimated Time:** 1-3 hours  
**Skills Needed:** CSS  
**Learning Value:** CSS, design, Git workflow

### Second Contribution (Medium)

**Documentation Improvement:**
1. Find unclear documentation
2. Research the topic thoroughly
3. Rewrite with more clarity
4. Add examples
5. Submit PR

**Estimated Time:** 2-4 hours  
**Skills Needed:** Writing, research  
**Learning Value:** Technical communication

### Third Contribution (Advanced)

**Feature Addition:**
1. Identify educational feature
2. Design implementation
3. Write code with comments
4. Test security
5. Document thoroughly
6. Submit PR

**Estimated Time:** 5-10 hours  
**Skills Needed:** PHP, security, Git  
**Learning Value:** Fullstack development

---

## 🙏 Recognition & Credits

### How Contributors Are Recognized

- **README.md**: Listed in Acknowledgments section
- **CONTRIBUTING.md**: Featured in success stories (with permission)
- **Release Notes**: Credited in version releases
- **GitHub Profile**: Shows contribution on your profile
- **Community**: Thanked in discussions and issues

### Hall of Fame

Contributors who have made significant educational impact:
- Multiple theme contributions
- Major documentation improvements
- Security enhancements with excellent explanations
- Educational content creation

*(Names added with contributor permission)*

---

## 📞 Getting Help

### Questions About Contributing?

- 💬 **Discussions**: [Ask in GitHub Discussions](https://github.com/EdgeOfAssembly/php-app/discussions)
- 🐛 **Issues**: Comment on related issues
- 📧 **Email**: Contact maintainers (see README)
- 📖 **Docs**: Read this guide thoroughly first

### Common Questions

**Q: I'm a beginner, can I contribute?**  
A: YES! Start with a theme or documentation fix.

**Q: Do I need to know PHP?**  
A: Not for themes or docs. For features, yes.

**Q: How long do PR reviews take?**  
A: Usually 2-7 days. Be patient!

**Q: Can I add npm dependencies?**  
A: No. This project avoids Node.js/npm by design.

**Q: What if my PR is rejected?**  
A: Learn from feedback, try again. Rejection ≠ failure.

**Q: Can I work on multiple PRs at once?**  
A: Yes, but finish one before starting another for best learning.

---

## 📚 Resources for Contributors

### Git & GitHub
- [GitHub Flow Guide](https://guides.github.com/introduction/flow/)
- [Git Cheat Sheet](https://education.github.com/git-cheat-sheet-education.pdf)
- [How to Write Good Commit Messages](https://chris.beams.io/posts/git-commit/)

### PHP
- [PHP Manual](https://www.php.net/manual/en/)
- [PHP The Right Way](https://phptherightway.com/)

### Security
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [OWASP Cheat Sheets](https://cheatsheetseries.owasp.org/)

### CSS & Design
- [CSS Tricks](https://css-tricks.com/)
- [Web Accessibility Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)

---

## 🎯 Final Thoughts

**Remember:**
- **Clarity over cleverness** - Beginners should understand your code
- **Education first** - Teaching is more important than showing off
- **Security matters** - But explain WHY it matters
- **Be patient** - Reviews take time
- **Have fun!** - Contributing should be enjoyable

**Most importantly:** Every contribution, no matter how small, helps someone learn. Thank you for being part of BareStack's educational mission!

---

**Happy Contributing! May your code be clear and your PRs be merged.** 🚀

*Last updated: 2026-02-13*
3. Check if similar features exist
4. Plan how you'll document it

### Feature Development Process

1. **Branch**: Create a feature branch
   ```bash
   git checkout -b feature/rate-limiting
   ```

2. **Implement**: Write the code
   - Follow security best practices
   - Add comprehensive comments
   - Update relevant documentation

3. **Test**: Verify it works
   - Manual testing
   - Security testing
   - Cross-browser testing (if frontend)

4. **Document**: Update docs
   - Add to README if user-facing
   - Update SECURITY.md if security-related
   - Add code comments

5. **Submit**: Create pull request

### Example: Adding Rate Limiting

```php
// includes/rate_limiting.php

/**
 * Rate Limiting Module
 * 
 * Prevents brute force attacks by limiting login attempts.
 * Educational Note: In production, use Redis or similar for
 * distributed rate limiting.
 */

require_once __DIR__ . '/../config.php';

/**
 * Check if IP has exceeded rate limit
 * 
 * @param string $ip IP address to check
 * @param int $maxAttempts Maximum attempts allowed
 * @param int $timeWindow Time window in seconds
 * @return bool True if rate limit exceeded
 */
function isRateLimited($ip, $maxAttempts = 5, $timeWindow = 900) {
    // Implementation here
    // Educational comments explaining each step
}
```

## Adding New Themes

Themes are a great way to contribute! Here's how:

### Theme File Structure

Create a new CSS file in `themes/` directory:

```css
/* themes/mytheme.css */

/* Theme Name: My Theme
 * Description: Brief description
 * Author: Your Name
 */

/* Base Styles */
body { 
    background: /* your colors */;
    color: /* your colors */;
    font-family: /* your font */;
}

/* Required: Style these classes */
.header h1 { /* ... */ }
.header p { /* ... */ }
.card { /* ... */ }
.card h3 { /* ... */ }
.progress { /* ... */ }
.bar { /* ... */ }
.logout a { /* ... */ }
.theme-select select { /* ... */ }

/* Hide unused theme elements */
.matrix-column,
.snowflake,
.snowman {
    display: none;
}

/* Optional: Add theme-specific animations */
@keyframes my-animation {
    /* ... */
}
```

### Update dashboard.php

Add your theme to the dropdown:

```php
<select id="theme-select">
    <option value="light">Light Theme</option>
    <option value="dark">Dark Theme</option>
    <option value="mytheme">My Theme Name</option>
    <!-- ... -->
</select>
```

### Theme Design Guidelines

1. **Accessibility**: Ensure good contrast ratios
2. **Readability**: Text must be easily readable
3. **Consistency**: Follow existing theme patterns
4. **Performance**: Avoid heavy animations
5. **Creativity**: Make it unique and fun!

### Theme Ideas

- Cyberpunk theme (neon colors)
- Retro/Vintage theme (sepia tones)
- Forest theme (greens and browns)
- Space theme (deep purples and stars)
- Minimalist theme (grayscale)
- High contrast theme (accessibility)

## Testing Guidelines

### Before Submitting

Test your changes thoroughly:

#### Functional Testing

```bash
# Test all user flows
1. Register new account
2. Login with correct credentials
3. Login with incorrect credentials
4. View dashboard
5. Switch themes
6. Logout
```

#### Security Testing

```bash
# Test SQL injection
username: admin' OR '1'='1
# Should fail safely

# Test XSS
username: <script>alert('XSS')</script>
# Should be escaped

# Test CSRF
# Modify CSRF token in form
# Should be rejected
```

#### Browser Testing

Test in multiple browsers:
- Chrome/Chromium
- Firefox
- Safari (if available)
- Edge

#### PHP Version Testing

```bash
# Test with different PHP versions
php7.4 -S localhost:8080
php8.0 -S localhost:8080
php8.1 -S localhost:8080
```

### Automated Testing

While we don't have automated tests yet, you can help add them!

```php
// Example: tests/ValidationTest.php
require_once 'includes/validation.php';

// Test username validation
assert(validateUsername('john') === true);
assert(validateUsername('jo') === false);  // Too short
assert(validateUsername('admin') === false);  // Blacklisted
```

## Pull Request Process

### Before Submitting

- [ ] Code follows style guidelines
- [ ] All tests pass
- [ ] Documentation updated
- [ ] Security best practices followed
- [ ] No sensitive data committed
- [ ] Educational value added

### PR Title Format

```
[Type] Brief description

Examples:
[Feature] Add rate limiting for login attempts
[Theme] Add cyberpunk theme
[Fix] Correct CSRF token validation
[Docs] Update installation instructions
[Security] Improve session cookie configuration
```

### PR Description Template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Documentation update
- [ ] Security improvement
- [ ] New theme
- [ ] Code refactoring

## Educational Value
How does this help learners?

## Testing Performed
- [ ] Manual testing
- [ ] Security testing
- [ ] Browser testing
- [ ] PHP version testing

## Screenshots (if applicable)
Add screenshots of UI changes

## Checklist
- [ ] Code follows style guidelines
- [ ] Self-reviewed code
- [ ] Commented complex sections
- [ ] Documentation updated
- [ ] No new warnings/errors
- [ ] Security implications considered
```

### Review Process

1. **Automated Checks**: Code is checked automatically
2. **Maintainer Review**: A maintainer will review your code
3. **Feedback**: You may receive requests for changes
4. **Approval**: Once approved, your PR will be merged
5. **Recognition**: You'll be added to contributors!

## Recognition

Contributors will be:
- Listed in README acknowledgments
- Credited in release notes
- Thanked in the community

## Questions?

- 💬 [Open a Discussion](https://github.com/EdgeOfAssembly/php-app/discussions)
- 📧 Contact maintainers
- 📖 Read the documentation

## Thank You!

Every contribution, no matter how small, helps make this a better learning resource. Thank you for taking the time to contribute! 🎉

---

**Happy Coding! Remember: Good code is educational code.** 📚
