<?php
/**
 * Login Page - Enhanced with Colorful Design
 */
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

$auth = getAuth();

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header('Location: admin.php');
    exit();
}

$error = '';
$success = '';

// Get database connection
$db = getDB();

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required';
    } else {
        // Normalize email
        $email = strtolower(trim($email));
        
        // Find user
        $user = $db->fetchOne("SELECT * FROM users WHERE LOWER(email) = ?", [$email]);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            header('Location: admin.php');
            exit();
        } else {
            $error = 'Invalid email or password';
        }
    }
}
$branding = null;
try {
    $branding = $db->fetchOne("SELECT * FROM branding WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
} catch (Exception $e) {
    // Continue with defaults
}

// Auto-create user if doesn't exist (for testing)
try {
    $testUser = $db->fetchOne("SELECT * FROM users WHERE email = ?", ['hub@school.com']);
    if (!$testUser) {
        $hash = password_hash('hub h@11', PASSWORD_DEFAULT);
        $db->insert("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'admin')", ['hub', 'hub@school.com', $hash]);
    }
} catch (Exception $e) {
    // Ignore
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo htmlspecialchars($branding['school_name'] ?? 'Excellence Academy'); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        * {
            transition: all 0.3s ease;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 450px;
        }
        
        .login-box {
            background: white;
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            position: relative;
            overflow: hidden;
        }
        
        .login-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #4facfe);
            background-size: 200% 100%;
            animation: gradient 3s ease infinite;
        }
        
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .login-box h1 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 10px;
            font-size: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .login-subtitle {
            text-align: center;
            color: var(--text-light);
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(128,0,32,0.1);
            transform: translateY(-2px);
        }
        
        .btn-primary {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary-color) 0%, #a00030 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(128,0,32,0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(128,0,32,0.4);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .message-alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-weight: 500;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .message-error {
            background: #fee;
            color: #c33;
            border-left: 4px solid #c33;
        }
        
        .message-success {
            background: #efe;
            color: #3c3;
            border-left: 4px solid #3c3;
        }
        
        .back-link {
            text-align: center;
            margin-top: 25px;
        }
        
        .back-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>🔐 Admin Login</h1>
            <p class="login-subtitle">Access the admin panel</p>
            
            <?php if ($error): ?>
                <div class="message-alert message-error">
                    ⚠️ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="message-alert message-success">
                    ✅ <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label class="form-label" for="email">
                        📧 Email
                    </label>
                    <input type="email" id="email" name="email" class="form-input" required placeholder="Enter your email">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">
                        🔒 Password
                    </label>
                    <div style="position: relative;">
                        <input type="password" id="password" name="password" class="form-input" required placeholder="Enter your password" style="padding-right: 50px;">
                        <button type="button" id="togglePassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 20px; color: #666; padding: 5px;" aria-label="Toggle password visibility">
                            👁️
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    🚀 Login
                </button>
            </form>
            
            <div class="back-link">
                <a href="index.php">← Back to Home</a>
            </div>
        </div>
    </div>
    <script>
        // Password visibility toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Change icon
                if (type === 'text') {
                    togglePassword.textContent = '🙈';
                    togglePassword.setAttribute('aria-label', 'Hide password');
                } else {
                    togglePassword.textContent = '👁️';
                    togglePassword.setAttribute('aria-label', 'Show password');
                }
            });
        }
    </script>
</body>
</html>
