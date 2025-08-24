<?php
// Database configuration
$db_host = 'localhost';
$db_name = 'clg_ass';
$db_user = 'root';
$db_pass = '';

try {
    // Create database connection
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create admins table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    
    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
    $stmt->execute(['admin']);
    
    if (!$stmt->fetch()) {
        // Create admin account with proper password hash
        $admin_password = 'admin123';
        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO admins (username, password, email) VALUES (?, ?, ?)");
        $stmt->execute(['admin', $hashed_password, 'admin@college.com']);
        
        echo "<h2>✅ Admin Account Created Successfully!</h2>";
        echo "<p><strong>Username:</strong> admin</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        echo "<p><a href='admin_login.php'>Go to Admin Login</a></p>";
    } else {
        echo "<h2>ℹ️ Admin Account Already Exists</h2>";
        echo "<p>Username: admin</p>";
        echo "<p>Password: admin123</p>";
        echo "<p><a href='admin_login.php'>Go to Admin Login</a></p>";
    }
    
} catch (PDOException $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Setup</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: #f4f6f8; 
            padding: 20px;
        }
        h2 { color: #2c3e50; }
        p { color: #34495e; }
        a { 
            color: #3498db; 
            text-decoration: none; 
            font-weight: bold;
        }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>Admin Account Setup</h1>
    <p>This script will create the admin account for your system.</p>
</body>
</html> 