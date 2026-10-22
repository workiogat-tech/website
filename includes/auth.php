<?php
/**
 * Authentication System
 * Handles login, logout, and session management
 */

require_once __DIR__ . '/../config/config.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Login user
     */
    public function login($email, $password) {
        try {
            // Normalize email - try both exact match and case-insensitive
            $email = trim($email);
            $emailLower = strtolower($email);
            
            // Find user - try multiple ways
            $user = $this->db->fetchOne(
                "SELECT id, username, email, password_hash, role FROM users WHERE email = ? OR LOWER(TRIM(email)) = ? OR email LIKE ?",
                [$email, $emailLower, $email]
            );
            
            if (!$user) {
                // Try one more time with just the email as-is
                $user = $this->db->fetchOne(
                    "SELECT id, username, email, password_hash, role FROM users WHERE email = ?",
                    [$email]
                );
            }
            
            if (!$user) {
                return ['success' => false, 'error' => 'User not found. Please create your account first.'];
            }
            
            // Verify password
            if (!password_verify($password, $user['password_hash'])) {
                return ['success' => false, 'error' => 'Incorrect password. Please check your password.'];
            }
            
            // Check if using default password
            $defaultPassword = 'user@12345';
            $mustChangePassword = password_verify($defaultPassword, $user['password_hash']);
            
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            
            return [
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ],
                'mustChangePassword' => $mustChangePassword
            ];
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Login failed'];
        }
    }
    
    /**
     * Logout user
     */
    public function logout() {
        $_SESSION = [];
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        session_destroy();
        return ['success' => true];
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Get current user
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['username'] ?? null,
            'email' => $_SESSION['email'] ?? null,
            'role' => $_SESSION['role'] ?? null
        ];
    }
    
    /**
     * Check if user is admin
     */
    public function isAdmin() {
        $role = strtolower(trim((string) ($_SESSION['role'] ?? '')));
        return $this->isLoggedIn() && ($role === 'admin' || $role === 'administrator');
    }
    
    /**
     * Require login (redirect if not logged in)
     */
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
    }
    
    /**
     * Require admin (redirect if not admin)
     */
    public function requireAdmin() {
        $this->requireLogin();
        if (!$this->isAdmin()) {
            header('Location: index.php');
            exit();
        }
    }
    
    /**
     * Change password
     */
    public function changePassword($userId, $oldPassword, $newPassword) {
        try {
            // Get user
            $user = $this->db->fetchOne(
                "SELECT id, password_hash FROM users WHERE id = ?",
                [$userId]
            );
            
            if (!$user) {
                return ['success' => false, 'error' => 'User not found'];
            }
            
            // Verify old password
            if (!password_verify($oldPassword, $user['password_hash'])) {
                return ['success' => false, 'error' => 'Current password is incorrect'];
            }
            
            // Validate new password
            if (strlen($newPassword) < 6) {
                return ['success' => false, 'error' => 'New password must be at least 6 characters long'];
            }
            
            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password
            $this->db->update(
                "UPDATE users SET password_hash = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                [$hashedPassword, $userId]
            );
            
            return ['success' => true, 'message' => 'Password changed successfully'];
        } catch (Exception $e) {
            error_log("Change password error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to change password'];
        }
    }
    
    /**
     * Register new user
     */
    public function register($username, $email, $password) {
        try {
            // Validate input
            if (strlen($username) < 3) {
                return ['success' => false, 'error' => 'Username must be at least 3 characters'];
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'error' => 'Invalid email address'];
            }
            
            if (strlen($password) < 6) {
                return ['success' => false, 'error' => 'Password must be at least 6 characters'];
            }
            
            // Check if user exists
            $existing = $this->db->fetchOne(
                "SELECT id FROM users WHERE username = ? OR email = ?",
                [$username, $email]
            );
            
            if ($existing) {
                return ['success' => false, 'error' => 'User already exists'];
            }
            
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Create user
            $userId = $this->db->insert(
                "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)",
                [$username, $email, $hashedPassword]
            );
            
            // Get created user
            $user = $this->db->fetchOne(
                "SELECT id, username, email, role FROM users WHERE id = ?",
                [$userId]
            );
            
            return [
                'success' => true,
                'message' => 'User created successfully',
                'user' => $user
            ];
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Registration failed'];
        }
    }
}

// Helper function
function getAuth() {
    return new Auth();
}
