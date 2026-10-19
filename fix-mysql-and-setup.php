<?php
/**
 * Fix MySQL Connection and Setup Database
 * This script will:
 * 1. Test MySQL connection
 * 2. Create database 'sjs' if it doesn't exist
 * 3. Import all tables
 * 
 * Run: php fix-mysql-and-setup.php
 */

echo "========================================\n";
echo "MySQL Setup and Database Creation\n";
echo "========================================\n\n";

// Database configuration
$host = 'localhost';
$user = 'root';
$pass = 'root';
$dbName = 'sjs';

// Step 1: Test MySQL Connection
echo "Step 1: Testing MySQL connection...\n";
try {
    $conn = new mysqli($host, $user, $pass);
    
    if ($conn->connect_error) {
        die("❌ Connection failed: " . $conn->connect_error . "\n");
    }
    
    echo "✅ MySQL connection successful!\n\n";
} catch (Exception $e) {
    die("❌ Cannot connect to MySQL: " . $e->getMessage() . "\n\n" .
        "💡 Troubleshooting:\n" .
        "1. Make sure MySQL is running in XAMPP\n" .
        "2. Check if port 3306 is not blocked\n" .
        "3. Try restarting MySQL in XAMPP Control Panel\n" .
        "4. Check MySQL error logs in XAMPP\n");
}

// Step 2: Create Database
echo "Step 2: Creating database '$dbName'...\n";
$sql = "CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql)) {
    echo "✅ Database '$dbName' created successfully!\n\n";
} else {
    echo "⚠️  Database might already exist: " . $conn->error . "\n\n";
}

// Step 3: Select Database
echo "Step 3: Selecting database '$dbName'...\n";
if ($conn->select_db($dbName)) {
    echo "✅ Database selected!\n\n";
} else {
    die("❌ Cannot select database: " . $conn->error . "\n");
}

// Step 4: Read and Execute Schema
echo "Step 4: Importing database schema...\n";
$schemaFile = __DIR__ . '/database/schema.sql';

if (!file_exists($schemaFile)) {
    die("❌ Schema file not found: $schemaFile\n");
}

$schema = file_get_contents($schemaFile);

// Remove CREATE DATABASE and USE statements (we already did that)
$schema = preg_replace('/CREATE DATABASE.*?;/is', '', $schema);
$schema = preg_replace('/USE.*?;/is', '', $schema);

// Split by semicolons
$statements = array_filter(
    array_map('trim', explode(';', $schema)),
    function($stmt) {
        return !empty($stmt) && 
               !preg_match('/^--/', $stmt) && 
               !preg_match('/^\/\*/', $stmt) &&
               strlen($stmt) > 10; // Ignore very short statements
    }
);

$successCount = 0;
$errorCount = 0;

foreach ($statements as $statement) {
    $statement = trim($statement);
    if (empty($statement)) continue;
    
    // Skip comments
    if (preg_match('/^--/', $statement)) continue;
    if (preg_match('/^\/\*/', $statement)) continue;
    
    try {
        if ($conn->query($statement)) {
            $successCount++;
            // Show progress for CREATE TABLE statements
            if (preg_match('/CREATE TABLE/i', $statement)) {
                if (preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches)) {
                    echo "  ✓ Created table: " . $matches[1] . "\n";
                }
            }
        } else {
            // Ignore "table already exists" errors
            if (strpos($conn->error, 'already exists') === false) {
                $errorCount++;
                echo "  ⚠ Warning: " . $conn->error . "\n";
            }
        }
    } catch (Exception $e) {
        // Ignore duplicate errors
        if (strpos($e->getMessage(), 'already exists') === false) {
            $errorCount++;
            echo "  ⚠ Error: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n✅ Schema import completed!\n";
echo "   Successful: $successCount statements\n";
if ($errorCount > 0) {
    echo "   Warnings: $errorCount statements\n";
}

// Step 5: Verify Tables
echo "\nStep 5: Verifying tables...\n";
$result = $conn->query("SHOW TABLES");
$tableCount = $result->num_rows;
echo "✅ Found $tableCount tables in database '$dbName'\n";

if ($tableCount > 0) {
    echo "\nTables created:\n";
    while ($row = $result->fetch_array()) {
        echo "  - " . $row[0] . "\n";
    }
}

// Step 6: Check Admin User
echo "\nStep 6: Checking admin user...\n";
$result = $conn->query("SELECT id, username, email FROM users WHERE username = 'admin'");
if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    echo "✅ Admin user exists:\n";
    echo "   Username: " . $admin['username'] . "\n";
    echo "   Email: " . $admin['email'] . "\n";
    echo "   Password: admin123\n";
} else {
    echo "⚠️  Admin user not found. Creating...\n";
    $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, email, password_hash, role) 
            VALUES ('admin', 'admin@excellenceacademy.com', '$passwordHash', 'admin')";
    if ($conn->query($sql)) {
        echo "✅ Admin user created!\n";
    } else {
        echo "❌ Failed to create admin user: " . $conn->error . "\n";
    }
}

$conn->close();

echo "\n========================================\n";
echo "✅ Setup Complete!\n";
echo "========================================\n";
echo "\nYou can now:\n";
echo "1. Access your website: http://localhost/2026sjs/\n";
echo "2. Login at: http://localhost/2026sjs/login.php\n";
echo "   Email: admin\n";
echo "   Password: admin123\n";
echo "\n";
