<?php
/**
 * Create Admin User
 * Run this once to create an admin account
 * Access: http://localhost/2026sjs/create-admin.php
 */
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/config/config.php';

$db = getDB();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'All fields are required';
    } else {
        try {
            // Check if user exists
            $existing = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
            if ($existing) {
                $error = 'User with this email already exists';
            } else {
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Create admin user
                $userId = $db->insert(
                    "INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'admin')",
                    [$username, $email, $hashedPassword]
                );
                
                if ($userId) {
                    $message = "Admin user created successfully! Username: $username, Email: $email";
                } else {
                    $error = 'Failed to create user';
                }
            }
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin User</title>
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
        .create-admin-box {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
        }
        .create-admin-box h1 {
            color: var(--primary-color);
            margin-bottom: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="create-admin-box">
        <h1>🔧 Create Admin User</h1>
        
        <?php if ($error): ?>
            <div style="background: #fee; color: #c33; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                ⚠️ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($message): ?>
            <div style="background: #efe; color: #3c3; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                ✅ <?php echo htmlspecialchars($message); ?>
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <a href="login.php" class="btn btn-primary">Go to Login</a>
            </div>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label" for="username">👤 Username</label>
                    <input type="text" id="username" name="username" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="email">📧 Email</label>
                    <input type="email" id="email" name="email" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">🔒 Password</label>
                    <input type="password" id="password" name="password" class="form-input" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Create Admin</button>
            </form>
            
            <p style="text-align: center; margin-top: 20px;">
                <a href="index.php" style="color: var(--primary-color);">← Back to Home</a>
            </p>
        <?php endif; ?>
    </div>
</body>
</html>
