<?php
/**
 * Auto Setup Admin User
 * This script automatically creates an admin user with default credentials
 * Run once: http://localhost/2026sjs/auto-setup-admin.php
 */
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/config/config.php';

$db = getDB();
$adminEmail = 'admin@excellence.academy';
$adminUsername = 'admin';
$adminPassword = 'admin123'; // Default password - CHANGE THIS AFTER FIRST LOGIN!

$message = '';
$error = '';

try {
    // Check if admin already exists
    $existing = $db->fetchOne("SELECT id FROM users WHERE email = ? OR username = ?", [$adminEmail, $adminUsername]);
    
    if ($existing) {
        $message = "Admin user already exists!<br><br><strong>Login Credentials:</strong><br>Email: <code>$adminEmail</code><br>Username: <code>$adminUsername</code><br>Password: <code>$adminPassword</code>";
    } else {
        // Create users table if it doesn't exist
        try {
            $db->query("CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(100) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                role VARCHAR(20) DEFAULT 'user',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_email (email),
                INDEX idx_username (username)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (Exception $e) {
            // Table might already exist, continue
        }
        
        // Hash password
        $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
        
        // Create admin user
        $userId = $db->insert(
            "INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'admin')",
            [$adminUsername, $adminEmail, $hashedPassword]
        );
        
        if ($userId) {
            $message = "✅ <strong>Admin user created successfully!</strong><br><br>
            <div style='background: #e8f5e9; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #4caf50;'>
                <h3 style='color: #2e7d32; margin-top: 0;'>🔐 Login Credentials:</h3>
                <p style='font-size: 18px; margin: 10px 0;'><strong>Email:</strong> <code style='background: white; padding: 5px 10px; border-radius: 5px;'>$adminEmail</code></p>
                <p style='font-size: 18px; margin: 10px 0;'><strong>Username:</strong> <code style='background: white; padding: 5px 10px; border-radius: 5px;'>$adminUsername</code></p>
                <p style='font-size: 18px; margin: 10px 0;'><strong>Password:</strong> <code style='background: white; padding: 5px 10px; border-radius: 5px;'>$adminPassword</code></p>
                <p style='color: #d32f2f; margin-top: 15px;'><strong>⚠️ Important:</strong> Please change this password after your first login!</p>
            </div>";
        } else {
            $error = 'Failed to create admin user';
        }
    }
} catch (Exception $e) {
    $error = 'Error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto Setup Admin</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .setup-box {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
        }
        .setup-box h1 {
            color: var(--primary-color);
            margin-bottom: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="setup-box">
        <h1>🔧 Auto Setup Admin</h1>
        
        <?php if ($error): ?>
            <div style="background: #fee; color: #c33; padding: 20px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #c33;">
                ⚠️ <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($message): ?>
            <div style="padding: 20px;">
                <?php echo $message; ?>
            </div>
            <div style="text-align: center; margin-top: 30px;">
                <a href="login.php" class="btn btn-primary" style="padding: 15px 30px; font-size: 18px;">🚀 Go to Login</a>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: var(--text-light);">Click the button below to create admin user automatically.</p>
            <form method="POST" style="text-align: center; margin-top: 30px;">
                <button type="submit" class="btn btn-primary" style="padding: 15px 30px; font-size: 18px;">Create Admin User</button>
            </form>
        <?php endif; ?>
        
        <p style="text-align: center; margin-top: 30px;">
            <a href="index.php" style="color: var(--primary-color);">← Back to Home</a>
        </p>
    </div>
</body>
</html>
