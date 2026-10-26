# Practical 9: User Registration, Server-Side Form Validation & Bcrypt Password Hashing

**Course:** Web Development Frameworks (WDF)  
**Project Base:** StudentHub Portal  
**Student Name:** Krisha Patel  
**Student ID:** 25CS069  
**Institution:** CHARUSAT - CSPIT (Computer Engineering)  
**Due Date:** 14 October 2026, 23:59 PM  

---

## 📋 Submission Checklist & Requirements Compliance

This practical satisfies all **7 instructions** specified in the assignment submission portal:

| # | Practical Instruction Requirement | Implementation Method & Technique Used | Verification Status |
| :-: | :--- | :--- | :-: |
| 1 | **Empty-field validation** | Validates `trim($field) === ''` on `full_name`, `username`, `email`, `password`, and `confirm_password`. | ✅ Fully Verified |
| 2 | **Invalid email validation** | Validates email syntax using PHP `filter_var($email, FILTER_VALIDATE_EMAIL)` compliant with RFC 5322. | ✅ Fully Verified |
| 3 | **Password mismatch validation** | Validates `$password !== $confirm_password` before database submission. | ✅ Fully Verified |
| 4 | **Duplicate email/username validation** | Executes SQL Prepared Statement `SELECT id FROM users WHERE username = :u OR email = :e` to ensure unique credentials. | ✅ Fully Verified |
| 5 | **Password stored in hashed format** | Cryptographically hashes passwords using `password_hash($password, PASSWORD_BCRYPT)` with random salt and cost factor 10 (`$2y$10$...`). | ✅ Fully Verified |
| 6 | **Successful database insertion** | Executes parameterized `INSERT INTO users` query via **PHP Data Objects (PDO)** prepared statements. | ✅ Fully Verified |
| 7 | **Successful user registration** | Displays dynamic Bootstrap/CSS success notification with newly created User ID and re-renders updated live records table. | ✅ Fully Verified |

---

## 🗄️ Database Architecture (`schema.sql`)

### Table: `users`
Designed for MySQL / MariaDB (and portable SQLite fallback):

```sql
CREATE DATABASE IF NOT EXISTS `studenthub_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `studenthub_db`;

CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 💻 Source Code Overview

### 1. Database Connection (`db.php`)
Connects to MySQL database via PDO with exception handling. If MySQL service is stopped, seamlessly falls back to portable local SQLite database so the application runs anywhere without configuration errors:

```php
<?php
$host     = 'localhost';
$db_name  = 'studenthub_db';
$username = 'root';
$password = '';
$charset  = 'utf8mb4';

try {
    $dsn = "mysql:host=$host;dbname=$db_name;charset=$charset";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // Portable SQLite Fallback
    $sqlite_file = __DIR__ . '/studenthub.db';
    $pdo = new PDO("sqlite:" . $sqlite_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
}
?>
```

---

### 2. Validation & Registration Logic (`register.php`)

```php
// 1. Empty-Field Validation
if ($full_name === '' || $username === '' || $email === '' || $password === '' || $confirm_password === '') {
    $errors[] = "Empty-Field Validation Failed: All fields are mandatory.";
}

// 2. Invalid Email Validation
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid Email Validation Failed: Please enter a valid email address.";
}

// 3. Password Mismatch Validation
if ($password !== '' && $confirm_password !== '' && ($password !== $confirm_password)) {
    $errors[] = "Password Mismatch Validation Failed: Password and Confirm Password do not match.";
}

// 4. Duplicate Email / Username Validation
if (empty($errors)) {
    $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE LOWER(username) = LOWER(:username) OR LOWER(email) = LOWER(:email) LIMIT 1");
    $stmt->execute([':username' => $username, ':email' => $email]);
    $existing = $stmt->fetch();
    if ($existing) {
        if (strtolower($existing['username']) === strtolower($username)) {
            $errors[] = "Duplicate Username Validation Failed: Username already exists.";
        }
        if (strtolower($existing['email']) === strtolower($email)) {
            $errors[] = "Duplicate Email Validation Failed: Email already registered.";
        }
    }
}

// 5. Bcrypt Password Hashing & 6. Database Insertion
if (empty($errors)) {
    $password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

    $insert_stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, password_hash, created_at) 
                                 VALUES (:full_name, :username, :email, :password_hash, :created_at)");
    $insert_stmt->execute([
        ':full_name'     => $full_name,
        ':username'      => $username,
        ':email'         => $email,
        ':password_hash' => $password_hash,
        ':created_at'    => date('Y-m-d H:i:s')
    ]);
    $success_msg = "User registered successfully! Password securely hashed using BCRYPT.";
}
```

---

## 🚀 How to Run the Project for Faculty Review

### Method 1: Using XAMPP (Apache & MySQL)
1. Copy or keep the `Practical_9` folder in `C:\xampp\htdocs\StudentHub\Practical_9` (or create a virtual host/symlink).
2. Open **XAMPP Control Panel** and start **Apache** and **MySQL**.
3. Open `http://localhost/phpmyadmin` and import `schema.sql` (creates `studenthub_db` and `users` table).
4. Open `http://localhost/StudentHub/Practical_9/register.php` in your browser.

### Method 2: Instant Built-in PHP Server (Zero-Configuration)
1. Open PowerShell or Command Prompt in `D:\WDF____\StudentHub\Practical_9`.
2. Run:
   ```powershell
   & "C:\xampp\php\php.exe" -S localhost:8000
   ```
3. Open `http://localhost:8000/register.php` in Chrome or Edge.
4. To run the automated verification test suite:
   ```powershell
   & "C:\xampp\php\php.exe" test_validations.php
   ```

---

## 🎓 Faculty Review / Viva Questions & Answers

### Q1: Why do we use `password_hash()` instead of MD5 or SHA-256?
**Answer:** MD5 and SHA-256 are fast cryptographic hash functions originally designed for file integrity, making them vulnerable to brute-force attacks and rainbow table lookups. `password_hash()` uses **Bcrypt** (`PASSWORD_BCRYPT`), which is a key derivation function with an adjustable computational cost factor and an automatically generated cryptographic salt per password. This makes offline brute-force attacks computationally infeasible.

### Q2: What does `$2y$10$...` in the stored hash signify?
**Answer:** 
- `$2y$`: The identifier for the Bcrypt hashing algorithm.
- `$10$`: The cost factor ($2^{10} = 1024$ key expansion rounds).
- The remaining 53 characters contain the 22-character salt and the 31-character cipher text.

### Q3: How do PDO Prepared Statements protect against SQL Injection?
**Answer:** Prepared statements separate the SQL query logic from the user-supplied data. The database compiles the SQL structure first, and then user parameters are transmitted separately using parameter binding (`:username`, `:email`). Any malicious SQL fragments (e.g. `' OR '1'='1`) are treated strictly as literal string values rather than executable code.

### Q4: Why perform both server-side validation and database UNIQUE constraints?
**Answer:** Defense-in-depth security. Client-side checks can be bypassed by disabling JavaScript or using tools like Postman/cURL. Server-side validation catches mistakes and provides user-friendly error messages before hitting the database. The database UNIQUE constraints (`UNIQUE(username)`, `UNIQUE(email)`) guarantee absolute data integrity at the storage layer even in high-concurrency race conditions.

---
*Created for StudentHub Practical Submission & Faculty Review 2026*
