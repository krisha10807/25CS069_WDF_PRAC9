<?php
/**
 * StudentHub Portal - Practical 9: Database Connection File (db.php)
 * Course: Web Development Frameworks (WDF)
 * 
 * Uses PHP Data Objects (PDO) with try-catch error handling.
 * Supports MySQL PDO with seamless portable SQLite PDO fallback.
 */

$host     = 'localhost';
$db_name  = 'studenthub_db';
$username = 'root';
$password = ''; // Default XAMPP MySQL password is empty
$charset  = 'utf8mb4';

$pdo = null;
$db_driver_used = 'MySQL PDO';

try {
    // 1. Attempt MySQL PDO Connection
    $dsn = "mysql:host=$host;dbname=$db_name;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, $username, $password, $options);

} catch (PDOException $e) {
    // 2. Portable SQLite PDO Connection Fallback (Ensures the app runs reliably even if MySQL service is stopped)
    try {
        $db_driver_used = 'SQLite PDO (Portable Server Database)';
        $sqlite_file = __DIR__ . '/studenthub.db';
        $sqlite_exists = file_exists($sqlite_file);

        $pdo = new PDO("sqlite:" . $sqlite_file);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Auto-create users table if new or empty
        if (!$sqlite_exists || filesize($sqlite_file) == 0) {
            $pdo->exec('
                CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    full_name TEXT NOT NULL,
                    username TEXT NOT NULL UNIQUE,
                    email TEXT NOT NULL UNIQUE,
                    password_hash TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
            ');

            // Generate genuine bcrypt hash for seed records
            $seed_hash = password_hash('Password@123', PASSWORD_BCRYPT);
            $seed_stmt = $pdo->prepare('
                INSERT INTO users (full_name, username, email, password_hash, created_at) VALUES 
                (:name1, :user1, :mail1, :hash1, :time1),
                (:name2, :user2, :mail2, :hash2, :time2)
            ');
            $seed_stmt->execute([
                ':name1' => 'Krisha Patel (25CS069)',
                ':user1' => 'krisha_patel',
                ':mail1' => 'krisha.patel@charusat.ac.in',
                ':hash1' => $seed_hash,
                ':time1' => '2026-10-10 10:30:00',
                ':name2' => 'Rohan Shah',
                ':user2' => 'rohan_s',
                ':mail2' => 'rohan.shah@charusat.ac.in',
                ':hash2' => $seed_hash,
                ':time2' => '2026-10-11 11:15:00',
            ]);
        }

    } catch (PDOException $sqlite_error) {
        die("<div style='color:red; padding:15px; border:1px solid red; background:#fee2e2; font-family:sans-serif;'>
                <h3>Database Connection Failed</h3>
                <p><strong>MySQL Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
                <p><strong>SQLite Error:</strong> " . htmlspecialchars($sqlite_error->getMessage()) . "</p>
             </div>");
    }
}
?>
