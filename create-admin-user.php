<?php
/**
 * Create Admin User Script
 * This script will create an admin user in the database
 * Run this file directly in your browser: http://localhost/2026sjs/create-admin-user.php
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Don't start session for CLI execution
if (php_sapi_name() !== 'cli') {
    // Only start session if running in browser
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Create Admin User</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
        h1 { color: #333; }
    </style>
</head>
<body>
    <h1>🔧 Create Admin User</h1>";

try {
    // Include database configuration
    require_once __DIR__ . '/config/config.php';
    
    // Get database connection
    $db = getDB();
    $pdo = $db->getConnection();
    
    echo "<div class='info'><strong>Step 1:</strong> Database connection successful!</div>";
    echo "<div class='info'>Database: " . DB_NAME . "<br>Host: " . DB_HOST . "<br>User: " . DB_USER . "</div>";
    
    // Check if users table exists
    echo "<div class='info'><strong>Step 2:</strong> Checking if users table exists...</div>";
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($tableCheck->rowCount() == 0) {
        throw new Exception("Users table does not exist! Please run the database schema first.");
    }
    echo "<div class='success'>✓ Users table exists!</div>";
    
    // Admin credentials
    $adminUsername = 'admin';
    $adminEmail = 'admin@excellenceacademy.com';
    $adminPassword = 'admin123'; // Change this to your desired password
    $adminRole = 'admin';
    
    // Hash the password
    $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
    
    echo "<div class='info'><strong>Step 3:</strong> Preparing to create admin user...</div>";
    echo "<div class='info'>
        Username: <strong>{$adminUsername}</strong><br>
        Email: <strong>{$adminEmail}</strong><br>
        Password: <strong>{$adminPassword}</strong><br>
        Role: <strong>{$adminRole}</strong>
    </div>";
    
    // Check if admin user already exists
    echo "<div class='info'><strong>Step 4:</strong> Checking if admin user already exists...</div>";
    $existingUser = $db->fetchOne(
        "SELECT id, username, email, role FROM users WHERE username = ? OR email = ?",
        [$adminUsername, $adminEmail]
    );
    
    if ($existingUser) {
        echo "<div class='info'>Admin user already exists. Updating password...</div>";
        
        // Update existing user
        $updateResult = $db->update(
            "UPDATE users SET password_hash = ?, role = ?, updated_at = CURRENT_TIMESTAMP WHERE username = ? OR email = ?",
            [$passwordHash, $adminRole, $adminUsername, $adminEmail]
        );
        
        if ($updateResult > 0) {
            echo "<div class='success'>✓ Admin user password updated successfully!</div>";
            
            // Verify the update
            $updatedUser = $db->fetchOne(
                "SELECT id, username, email, role FROM users WHERE username = ? OR email = ?",
                [$adminUsername, $adminEmail]
            );
            
            echo "<div class='success'>
                <strong>Updated User Details:</strong><br>
                ID: {$updatedUser['id']}<br>
                Username: {$updatedUser['username']}<br>
                Email: {$updatedUser['email']}<br>
                Role: {$updatedUser['role']}
            </div>";
        } else {
            throw new Exception("Failed to update admin user.");
        }
    } else {
        echo "<div class='info'>Admin user does not exist. Creating new admin user...</div>";
        
        // Insert new admin user
        $userId = $db->insert(
            "INSERT INTO users (username, email, password_hash, role, created_at, updated_at) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
            [$adminUsername, $adminEmail, $passwordHash, $adminRole]
        );
        
        if ($userId) {
            echo "<div class='success'>✓ Admin user created successfully!</div>";
            
            // Verify the creation
            $newUser = $db->fetchOne(
                "SELECT id, username, email, role FROM users WHERE id = ?",
                [$userId]
            );
            
            echo "<div class='success'>
                <strong>Created User Details:</strong><br>
                ID: {$newUser['id']}<br>
                Username: {$newUser['username']}<br>
                Email: {$newUser['email']}<br>
                Role: {$newUser['role']}
            </div>";
        } else {
            throw new Exception("Failed to create admin user.");
        }
    }
    
    // Verify password works
    echo "<div class='info'><strong>Step 5:</strong> Verifying password hash...</div>";
    $verifyUser = $db->fetchOne(
        "SELECT id, username, email, password_hash, role FROM users WHERE username = ? OR email = ?",
        [$adminUsername, $adminEmail]
    );
    
    if ($verifyUser && password_verify($adminPassword, $verifyUser['password_hash'])) {
        echo "<div class='success'>✓ Password verification successful! Login should work now.</div>";
    } else {
        echo "<div class='error'>✗ Password verification failed! There may be an issue with the password hash.</div>";
    }
    
    // Show all users in database
    echo "<div class='info'><strong>Step 6:</strong> Listing all users in database...</div>";
    $allUsers = $db->fetchAll("SELECT id, username, email, role, created_at FROM users ORDER BY id");
    
    if (count($allUsers) > 0) {
        echo "<div class='info'><strong>All Users in Database:</strong></div>";
        echo "<pre>";
        echo str_pad("ID", 5) . str_pad("Username", 20) . str_pad("Email", 35) . str_pad("Role", 10) . "Created\n";
        echo str_repeat("-", 100) . "\n";
        foreach ($allUsers as $user) {
            echo str_pad($user['id'], 5) . 
                 str_pad($user['username'], 20) . 
                 str_pad($user['email'], 35) . 
                 str_pad($user['role'], 10) . 
                 $user['created_at'] . "\n";
        }
        echo "</pre>";
    } else {
        echo "<div class='info'>No users found in database.</div>";
    }
    
    echo "<div class='success' style='margin-top: 30px;'>
        <h2>✅ Setup Complete!</h2>
        <p><strong>You can now login with:</strong></p>
        <ul>
            <li><strong>Username/Email:</strong> {$adminUsername} or {$adminEmail}</li>
            <li><strong>Password:</strong> {$adminPassword}</li>
        </ul>
        <p><a href='login.php' style='background: #800020; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 10px;'>Go to Login Page →</a></p>
    </div>";
    
} catch (Exception $e) {
    echo "<div class='error'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<div class='info'><strong>Debug Information:</strong><br>";
    echo "Error: " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "</div>";
    
    // Try to show database connection details
    echo "<div class='info'><strong>Database Configuration:</strong><br>";
    echo "Host: " . (defined('DB_HOST') ? DB_HOST : 'Not defined') . "<br>";
    echo "User: " . (defined('DB_USER') ? DB_USER : 'Not defined') . "<br>";
    echo "Database: " . (defined('DB_NAME') ? DB_NAME : 'Not defined') . "<br>";
    echo "Password: " . (defined('DB_PASS') ? (empty(DB_PASS) ? '(empty)' : '***') : 'Not defined') . "</div>";
}

echo "</body></html>";
?>
