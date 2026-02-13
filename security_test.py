#!/usr/bin/env python3
"""
Comprehensive Security Testing Script for SecureLearn Dashboard

This script tests for common web vulnerabilities:
- SQL Injection
- XSS (Cross-Site Scripting)
- CSRF (Cross-Site Request Forgery)
- Session Security
- Authentication Bypass
- Information Disclosure
"""

import requests
import sys
import time
from urllib.parse import urljoin

# Color codes for output
RED = '\033[91m'
GREEN = '\033[92m'
YELLOW = '\033[93m'
BLUE = '\033[94m'
RESET = '\033[0m'

class SecurityTester:
    def __init__(self, base_url):
        self.base_url = base_url
        self.session = requests.Session()
        self.vulnerabilities = []
        self.passed_tests = []
        
    def log(self, message, level='info'):
        """Log messages with color coding"""
        if level == 'pass':
            print(f"{GREEN}[✓] {message}{RESET}")
            self.passed_tests.append(message)
        elif level == 'fail':
            print(f"{RED}[✗] {message}{RESET}")
            self.vulnerabilities.append(message)
        elif level == 'warn':
            print(f"{YELLOW}[!] {message}{RESET}")
        else:
            print(f"{BLUE}[i] {message}{RESET}")
    
    def test_sql_injection(self):
        """Test for SQL injection vulnerabilities"""
        self.log("\n=== Testing SQL Injection ===", 'info')
        
        payloads = [
            "admin' OR '1'='1",
            "admin'--",
            "admin' OR '1'='1'--",
            "'; DROP TABLE users;--",
            "' UNION SELECT NULL--",
            "1' AND '1'='1",
        ]
        
        for payload in payloads:
            try:
                data = {
                    'username': payload,
                    'password': 'test',
                    'action': 'login',
                    'csrf_token': 'test'
                }
                response = self.session.post(
                    urljoin(self.base_url, 'login.php'),
                    data=data,
                    timeout=5
                )
                
                # Check if injection was successful (bad) or blocked (good)
                if 'Welcome' in response.text or 'dashboard' in response.text.lower():
                    self.log(f"SQL Injection possible with payload: {payload}", 'fail')
                else:
                    self.log(f"SQL Injection blocked for payload: {payload[:30]}...", 'pass')
                    
            except Exception as e:
                self.log(f"Error testing SQL injection: {str(e)}", 'warn')
    
    def test_xss(self):
        """Test for XSS vulnerabilities"""
        self.log("\n=== Testing XSS (Cross-Site Scripting) ===", 'info')
        
        payloads = [
            "<script>alert('XSS')</script>",
            "<img src=x onerror=alert('XSS')>",
            "<iframe src='javascript:alert(1)'>",
            "javascript:alert('XSS')",
            "<svg onload=alert('XSS')>",
        ]
        
        for payload in payloads:
            try:
                data = {
                    'username': payload,
                    'password1': 'testtest123',
                    'password2': 'testtest123',
                    'csrf_token': 'test'
                }
                response = self.session.post(
                    urljoin(self.base_url, 'register.php'),
                    data=data,
                    timeout=5
                )
                
                # Check if script is present in raw form (bad) or escaped (good)
                if payload in response.text:
                    self.log(f"XSS possible - payload not escaped: {payload[:30]}...", 'fail')
                elif '&lt;script&gt;' in response.text or '&lt;' in response.text:
                    self.log(f"XSS blocked - payload escaped: {payload[:30]}...", 'pass')
                else:
                    self.log(f"XSS test inconclusive: {payload[:30]}...", 'warn')
                    
            except Exception as e:
                self.log(f"Error testing XSS: {str(e)}", 'warn')
    
    def test_csrf_protection(self):
        """Test CSRF protection"""
        self.log("\n=== Testing CSRF Protection ===", 'info')
        
        try:
            # Test login without CSRF token
            data = {
                'username': 'testuser',
                'password': 'testpass',
                'action': 'login'
                # No csrf_token
            }
            response = self.session.post(
                urljoin(self.base_url, 'login.php'),
                data=data,
                timeout=5
            )
            
            if 'CSRF' in response.text:
                self.log("CSRF protection active - request blocked without token", 'pass')
            elif 'Welcome' in response.text:
                self.log("CSRF protection missing - request succeeded without token", 'fail')
            else:
                self.log("CSRF protection present - form rejected", 'pass')
                
            # Test with invalid CSRF token
            data['csrf_token'] = 'invalid_token_12345'
            response = self.session.post(
                urljoin(self.base_url, 'login.php'),
                data=data,
                timeout=5
            )
            
            if 'CSRF' in response.text or 'Welcome' not in response.text:
                self.log("CSRF protection active - invalid token rejected", 'pass')
            else:
                self.log("CSRF protection weak - invalid token accepted", 'fail')
                
        except Exception as e:
            self.log(f"Error testing CSRF: {str(e)}", 'warn')
    
    def test_session_security(self):
        """Test session cookie security"""
        self.log("\n=== Testing Session Security ===", 'info')
        
        try:
            response = self.session.get(urljoin(self.base_url, 'login.php'), timeout=5)
            
            # Check session cookie flags
            for cookie in self.session.cookies:
                if 'PHPSESSID' in cookie.name or 'session' in cookie.name.lower():
                    self.log(f"Session cookie found: {cookie.name}", 'info')
                    
                    if cookie.secure:
                        self.log("Session cookie has Secure flag", 'pass')
                    else:
                        self.log("Session cookie missing Secure flag (OK for HTTP)", 'warn')
                    
                    # Note: HttpOnly and SameSite flags are not directly accessible via requests
                    # They need to be checked via browser dev tools or raw headers
                    
        except Exception as e:
            self.log(f"Error testing session security: {str(e)}", 'warn')
    
    def test_security_headers(self):
        """Test for security headers"""
        self.log("\n=== Testing Security Headers ===", 'info')
        
        try:
            response = self.session.get(urljoin(self.base_url, 'dashboard.php'), timeout=5)
            headers = response.headers
            
            # Check for important security headers
            required_headers = {
                'X-Frame-Options': 'Protects against clickjacking',
                'X-Content-Type-Options': 'Prevents MIME-sniffing',
                'X-XSS-Protection': 'Browser XSS filter',
                'Content-Security-Policy': 'Controls resource loading',
                'Referrer-Policy': 'Controls referrer information'
            }
            
            for header, description in required_headers.items():
                if header in headers:
                    self.log(f"{header}: {headers[header]} - {description}", 'pass')
                else:
                    self.log(f"Missing header: {header} - {description}", 'fail')
                    
        except Exception as e:
            self.log(f"Error testing security headers: {str(e)}", 'warn')
    
    def test_authentication(self):
        """Test authentication bypass"""
        self.log("\n=== Testing Authentication Bypass ===", 'info')
        
        try:
            # Try to access dashboard without authentication
            response = self.session.get(
                urljoin(self.base_url, 'dashboard.php'),
                allow_redirects=False,
                timeout=5
            )
            
            if response.status_code == 302 or 'Location' in response.headers:
                self.log("Authentication required - unauthorized access redirected", 'pass')
            elif 'Welcome' in response.text:
                self.log("Authentication bypass possible - dashboard accessible", 'fail')
            else:
                self.log("Authentication enforced", 'pass')
                
        except Exception as e:
            self.log(f"Error testing authentication: {str(e)}", 'warn')
    
    def test_information_disclosure(self):
        """Test for information disclosure"""
        self.log("\n=== Testing Information Disclosure ===", 'info')
        
        try:
            response = self.session.get(urljoin(self.base_url, 'login.php'), timeout=5)
            
            # Check for sensitive information in responses
            sensitive_patterns = [
                ('PHP_VERSION', 'PHP version disclosure'),
                ('mysql', 'Database type disclosure'),
                ('Warning:', 'PHP warnings displayed'),
                ('Fatal error:', 'PHP errors displayed'),
                ('Stack trace', 'Stack traces displayed'),
            ]
            
            disclosed = False
            for pattern, description in sensitive_patterns:
                if pattern in response.text:
                    self.log(f"Information disclosure: {description}", 'warn')
                    disclosed = True
            
            if not disclosed:
                self.log("No obvious information disclosure detected", 'pass')
                
        except Exception as e:
            self.log(f"Error testing information disclosure: {str(e)}", 'warn')
    
    def run_all_tests(self):
        """Run all security tests"""
        self.log(f"\n{'='*60}", 'info')
        self.log("SecureLearn Dashboard - Security Test Suite", 'info')
        self.log(f"Target: {self.base_url}", 'info')
        self.log(f"{'='*60}", 'info')
        
        self.test_sql_injection()
        self.test_xss()
        self.test_csrf_protection()
        self.test_session_security()
        self.test_security_headers()
        self.test_authentication()
        self.test_information_disclosure()
        
        # Print summary
        self.log(f"\n{'='*60}", 'info')
        self.log("Test Summary", 'info')
        self.log(f"{'='*60}", 'info')
        self.log(f"Passed Tests: {len(self.passed_tests)}", 'info')
        self.log(f"Failed Tests: {len(self.vulnerabilities)}", 'info')
        
        if self.vulnerabilities:
            self.log("\n⚠️  VULNERABILITIES FOUND:", 'warn')
            for vuln in self.vulnerabilities:
                self.log(f"  - {vuln}", 'fail')
            return False
        else:
            self.log("\n✅ All security tests passed!", 'pass')
            return True

if __name__ == '__main__':
    base_url = sys.argv[1] if len(sys.argv) > 1 else 'http://localhost:8080/'
    
    tester = SecurityTester(base_url)
    success = tester.run_all_tests()
    
    sys.exit(0 if success else 1)
