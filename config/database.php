<?php
// ================================================================
// Database Configuration (PDO with prepared statements)
// ================================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'testing');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Create PDO connection with error handling
 * Uses prepared statements for all queries
 */
function getDbConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        die('Database connection failed: ' . $e->getMessage());
    }
}

/**
 * Initialize database with required tables if they don't exist
 * This only adds the remember_token column to users table
 */
function initDatabase() {
    try {
        $pdo = getDbConnection();
        
        // Check if users table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($stmt->rowCount() === 0) {
            // Create users table
            $sql = "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                role ENUM('admin', 'scout', 'user') NOT NULL DEFAULT 'user',
                is_verified TINYINT(1) NOT NULL DEFAULT 0,
                profile_picture VARCHAR(255) DEFAULT NULL,
                remember_token VARCHAR(255) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB";
            $pdo->exec($sql);
        }
        
        // Check if posts table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'posts'");
        if ($stmt->rowCount() === 0) {
            // Create posts table
            $sql = "CREATE TABLE IF NOT EXISTS posts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                scout_id INT NOT NULL,
                title VARCHAR(200) NOT NULL,
                short_history TEXT,
                country VARCHAR(100) NOT NULL,
                genre VARCHAR(50) NOT NULL,
                cost_level ENUM('free', 'low', 'medium', 'high') NOT NULL DEFAULT 'free',
                travel_medium_info TEXT,
                image VARCHAR(255) DEFAULT NULL,
                status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (scout_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB";
            $pdo->exec($sql);
        } else {
            // Check if image column exists in posts table
            $stmt = $pdo->query("SHOW COLUMNS FROM posts LIKE 'image'");
            if ($stmt->rowCount() === 0) {
                $pdo->exec("ALTER TABLE posts ADD COLUMN image VARCHAR(255) DEFAULT NULL AFTER travel_medium_info");
            }
        }
        
        // Check if post_requests table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'post_requests'");
        if ($stmt->rowCount() === 0) {
            // Create post_requests table
            $sql = "CREATE TABLE IF NOT EXISTS post_requests (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                original_post_id INT DEFAULT NULL,
                title VARCHAR(200) NOT NULL,
                short_history TEXT,
                country VARCHAR(100) NOT NULL,
                genre VARCHAR(50) NOT NULL,
                cost_level ENUM('free', 'low', 'medium', 'high') NOT NULL DEFAULT 'free',
                travel_medium_info TEXT,
                image VARCHAR(255) DEFAULT NULL,
                status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (original_post_id) REFERENCES posts(id) ON DELETE SET NULL
            ) ENGINE=InnoDB";
            $pdo->exec($sql);
        }
        
        // Check if wishlist table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'wishlist'");
        if ($stmt->rowCount() === 0) {
            // Create wishlist table
            $sql = "CREATE TABLE IF NOT EXISTS wishlist (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                post_id INT NOT NULL,
                added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
                UNIQUE KEY unique_wishlist (user_id, post_id)
            ) ENGINE=InnoDB";
            $pdo->exec($sql);
        }
        
        // Check if default admin exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
        $stmt->execute();
        if ($stmt->rowCount() === 0) {
            // Create default admin
            $hash = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, is_verified) VALUES (?, ?, ?, 'admin', 1)");
            $stmt->execute(['Admin User', 'admin@travelguide.com', $hash]);
        }
        
    } catch (PDOException $e) {
        // Log error but don't die - let the app handle it gracefully
        error_log('Database initialization error: ' . $e->getMessage());
    }
}

// Initialize database on first load
initDatabase();
?>
