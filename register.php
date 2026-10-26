<?php
/**
 * StudentHub Portal - Practical 9: User Registration & Secure Authentication
 * Course: Web Development Frameworks (WDF)
 * 
 * Features Demonstrated:
 * 1. Successful user registration
 * 2. Empty-field validation
 * 3. Invalid email validation
 * 4. Password mismatch validation
 * 5. Duplicate email/username validation
 * 6. Password stored in hashed format (BCRYPT) in database
 * 7. Successful database insertion using PDO Prepared Statements
 */

require_once 'db.php';

$errors = [];
$success_msg = '';

$full_name = '';
$username = '';
$email = '';

// Handle Form Submission
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name        = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $username         = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email            = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password         = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // 1. EMPTY-FIELD VALIDATION
    if ($full_name === '' || $username === '' || $email === '' || $password === '' || $confirm_password === '') {
        $errors[] = "Empty-Field Validation Failed: All fields are mandatory. Please fill in all required inputs.";
    }

    // 2. INVALID EMAIL VALIDATION
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid Email Validation Failed: Please enter a valid email address (e.g., student@charusat.ac.in).";
    }

    // 3. PASSWORD MISMATCH VALIDATION
    if ($password !== '' && $confirm_password !== '' && ($password !== $confirm_password)) {
        $errors[] = "Password Mismatch Validation Failed: Password and Confirm Password do not match.";
    }

    // 4. DUPLICATE EMAIL / USERNAME VALIDATION (Only if other basic checks pass)
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE LOWER(username) = LOWER(:username) OR LOWER(email) = LOWER(:email) LIMIT 1");
            $stmt->execute([
                ':username' => $username,
                ':email'    => $email
            ]);
            $existing = $stmt->fetch();

            if ($existing) {
                if (strtolower($existing['username']) === strtolower($username)) {
                    $errors[] = "Duplicate Username Validation Failed: The username '<strong>" . htmlspecialchars($username) . "</strong>' is already registered. Please choose another username.";
                }
                if (strtolower($existing['email']) === strtolower($email)) {
                    $errors[] = "Duplicate Email Validation Failed: The email '<strong>" . htmlspecialchars($email) . "</strong>' is already in use. Please use a unique email address.";
                }
            }
        } catch (PDOException $e) {
            $errors[] = "Database Query Error: " . htmlspecialchars($e->getMessage());
        }
    }

    // 5. SECURE BCRYPT PASSWORD HASHING & DATABASE INSERTION
    if (empty($errors)) {
        try {
            // Password hashed using PHP's native bcrypt algorithm
            $password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

            // Prepared Statement to prevent SQL Injection
            $insert_query = "INSERT INTO users (full_name, username, email, password_hash, created_at) 
                             VALUES (:full_name, :username, :email, :password_hash, :created_at)";
            
            $insert_stmt = $pdo->prepare($insert_query);
            $inserted = $insert_stmt->execute([
                ':full_name'     => $full_name,
                ':username'      => $username,
                ':email'         => $email,
                ':password_hash' => $password_hash,
                ':created_at'    => date('Y-m-d H:i:s')
            ]);

            if ($inserted) {
                $last_id = $pdo->lastInsertId();
                $success_msg = "User Registration Successful! Account #$last_id for <strong>" . htmlspecialchars($full_name) . "</strong> has been created. Password securely hashed using <strong>BCRYPT</strong> and stored in database.";
                // Clear form fields on successful registration
                $full_name = '';
                $username = '';
                $email = '';
            } else {
                $errors[] = "Database insertion failed. Please try again.";
            }

        } catch (PDOException $e) {
            $errors[] = "Database Insertion Error: " . htmlspecialchars($e->getMessage());
        }
    }
}

// Fetch all registered users to demonstrate successful database insertion & password hashing
$users_list = [];
try {
    $stmt_all = $pdo->prepare("SELECT id, full_name, username, email, password_hash, created_at FROM users ORDER BY id DESC");
    $stmt_all->execute();
    $users_list = $stmt_all->fetchAll();
} catch (PDOException $e) {
    // Fail gracefully
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practical 9: User Registration & Secure Authentication | StudentHub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #eff6ff;
            --success: #10b981;
            --success-bg: #ecfdf5;
            --danger: #ef4444;
            --danger-bg: #fef2f2;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --card-bg: #ffffff;
            --body-bg: #f8fafc;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: var(--body-bg);
            color: var(--text-dark);
            min-height: 100vh;
            padding: 2rem 1.25rem 3rem;
            line-height: 1.5;
        }

        .container {
            max-width: 1050px;
            margin: 0 auto;
        }

        /* Top Bar */
        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 1.25rem 2rem;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .brand-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
        }

        .brand-text h1 {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--text-dark);
            letter-spacing: -0.5px;
        }

        .brand-text p {
            font-size: 0.82rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #dbeafe;
            color: #1e40af;
            border-radius: 9999px;
            font-size: 0.82rem;
            font-weight: 600;
            border: 1px solid #bfdbfe;
        }

        .badge-status .status-dot {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
        }

        /* Validation Alerts */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
            font-size: 0.92rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-error {
            background-color: var(--danger-bg);
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .alert-error i {
            color: var(--danger);
            font-size: 1.25rem;
            margin-top: 2px;
        }

        .alert-success {
            background-color: var(--success-bg);
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .alert-success i {
            color: var(--success);
            font-size: 1.3rem;
            margin-top: 2px;
        }

        .alert-list {
            margin: 0;
            padding-left: 1.2rem;
        }

        /* Layout Grid */
        .grid-layout {
            display: grid;
            grid-template-columns: 460px 1fr;
            gap: 2rem;
            align-items: start;
        }

        @media (max-width: 900px) {
            .grid-layout {
                grid-template-columns: 1fr;
            }
        }

        /* Card Styles */
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
        }

        .card-header {
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1rem;
        }

        .card-header h2 {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .card-header p {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.45rem;
        }

        .form-label span.req {
            color: var(--danger);
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-group i.field-icon {
            position: absolute;
            left: 1rem;
            color: #94a3b8;
            font-size: 0.95rem;
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.65rem;
            border: 1.5px solid var(--border-color);
            border-radius: 10px;
            font-size: 0.9rem;
            color: var(--text-dark);
            background: #fff;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .form-control.input-error {
            border-color: var(--danger);
            background-color: #fffbfa;
        }

        .btn-submit {
            width: 100%;
            padding: 0.85rem;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            margin-top: 0.5rem;
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* Database View Card */
        .table-responsive {
            overflow-x: auto;
            border-radius: 10px;
            border: 1px solid var(--border-color);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.82rem;
            background: white;
        }

        thead {
            background: #f1f5f9;
        }

        th {
            padding: 0.75rem 0.85rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border-color);
        }

        td {
            padding: 0.75rem 0.85rem;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background: #f8fafc;
        }

        .hash-code {
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 0.72rem;
            background: #f1f5f9;
            color: #0f766e;
            padding: 0.2rem 0.45rem;
            border-radius: 4px;
            display: inline-block;
            word-break: break-all;
            max-width: 200px;
        }

        .badge-id {
            background: #e0f2fe;
            color: #0369a1;
            font-weight: 700;
            padding: 0.15rem 0.5rem;
            border-radius: 6px;
            font-size: 0.75rem;
        }

        /* Checkbox & Helper text */
        .helper-card {
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1.5rem;
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .helper-card h4 {
            color: var(--text-dark);
            font-weight: 700;
            margin-bottom: 0.4rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .helper-card ul {
            padding-left: 1.2rem;
            margin-top: 0.25rem;
        }

        .helper-card li {
            margin-bottom: 0.25rem;
        }

        .footer {
            text-align: center;
            color: var(--text-muted);
            font-size: 0.82rem;
            margin-top: 2.5rem;
        }
    </style>
</head>
<body>

    <div class="container">
        
        <!-- Header -->
        <header class="top-header">
            <div class="brand">
                <div class="brand-icon">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <div class="brand-text">
                    <h1>StudentHub Portal - Practical 9</h1>
                    <p>CHARUSAT - CSPIT | Web Development Frameworks (WDF)</p>
                </div>
            </div>
            <div>
                <span class="badge-status">
                    <span class="status-dot"></span>
                    <?php echo htmlspecialchars($db_driver_used); ?> Connected
                </span>
            </div>
        </header>

        <!-- Dynamic Success Banner -->
        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success" id="successAlert">
                <i class="fa-solid fa-circle-check"></i>
                <div>
                    <strong>Registration Completed Successfully!</strong><br>
                    <?php echo $success_msg; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Dynamic Error Validation Banner -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error" id="errorAlert">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <div>
                    <strong>Please resolve the following validation issues:</strong>
                    <ul class="alert-list">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <!-- Grid: Registration Form + Database Records -->
        <div class="grid-layout">
            
            <!-- Left Column: User Registration Form -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fa-solid fa-user-plus" style="color:var(--primary);"></i> User Registration</h2>
                    <p>Enter student details with automatic validation and bcrypt password hashing.</p>
                </div>

                <form id="registrationForm" method="POST" action="register.php" novalidate>
                    
                    <!-- 1. Full Name -->
                    <div class="form-group">
                        <label class="form-label" for="full_name">Full Name <span class="req">*</span></label>
                        <div class="input-group">
                            <i class="fa-solid fa-id-card field-icon"></i>
                            <input type="text" id="full_name" name="full_name" class="form-control" 
                                   placeholder="e.g. Krisha Patel" 
                                   value="<?php echo htmlspecialchars($full_name); ?>">
                        </div>
                    </div>

                    <!-- 2. Username -->
                    <div class="form-group">
                        <label class="form-label" for="username">Username <span class="req">*</span></label>
                        <div class="input-group">
                            <i class="fa-solid fa-user field-icon"></i>
                            <input type="text" id="username" name="username" class="form-control" 
                                   placeholder="e.g. krisha_patel" 
                                   value="<?php echo htmlspecialchars($username); ?>">
                        </div>
                    </div>

                    <!-- 3. Email Address -->
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address <span class="req">*</span></label>
                        <div class="input-group">
                            <i class="fa-solid fa-envelope field-icon"></i>
                            <input type="email" id="email" name="email" class="form-control" 
                                   placeholder="e.g. krisha.patel@charusat.ac.in" 
                                   value="<?php echo htmlspecialchars($email); ?>">
                        </div>
                    </div>

                    <!-- 4. Password -->
                    <div class="form-group">
                        <label class="form-label" for="password">Password <span class="req">*</span></label>
                        <div class="input-group">
                            <i class="fa-solid fa-lock field-icon"></i>
                            <input type="password" id="password" name="password" class="form-control" 
                                   placeholder="Min 6 characters">
                        </div>
                    </div>

                    <!-- 5. Confirm Password -->
                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Confirm Password <span class="req">*</span></label>
                        <div class="input-group">
                            <i class="fa-solid fa-shield-halved field-icon"></i>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                                   placeholder="Re-enter password">
                        </div>
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn">
                        <i class="fa-solid fa-user-check"></i> Register Account
                    </button>
                </form>

                <div class="helper-card">
                    <h4><i class="fa-solid fa-circle-info" style="color:var(--primary);"></i> Practical 9 Validations Covered:</h4>
                    <ul>
                        <li><strong>Empty-field:</strong> Detects if any input field is empty.</li>
                        <li><strong>Email check:</strong> RFC compliant format validation.</li>
                        <li><strong>Password mismatch:</strong> Confirms Password === Confirm Password.</li>
                        <li><strong>Duplicate check:</strong> Prevents duplicate username or email.</li>
                        <li><strong>Bcrypt Hash:</strong> Stored as <code>$2y$10$...</code> hash string.</li>
                    </ul>
                </div>
            </div>

            <!-- Right Column: Live Database Records (Users Table) -->
            <div class="card">
                <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <h2><i class="fa-solid fa-database" style="color:var(--primary);"></i> Database Records (`users` Table)</h2>
                        <p>Real-time view proving successful insertion & hashed passwords.</p>
                    </div>
                    <span style="font-size:0.8rem; background:#f1f5f9; padding:0.35rem 0.75rem; border-radius:8px; font-weight:700; color:#334155;">
                        Total Users: <?php echo count($users_list); ?>
                    </span>
                </div>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>Full Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Password (Hashed Format)</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users_list)): ?>
                                <tr>
                                    <td colspan="6" style="text-align:center; padding:2rem; color:var(--text-muted);">
                                        No users registered yet. Fill the form to insert a record.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users_list as $user): ?>
                                    <tr>
                                        <td><span class="badge-id">#<?php echo htmlspecialchars($user['id']); ?></span></td>
                                        <td><strong><?php echo htmlspecialchars($user['full_name']); ?></strong></td>
                                        <td><code>@<?php echo htmlspecialchars($user['username']); ?></code></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="hash-code" title="<?php echo htmlspecialchars($user['password_hash']); ?>">
                                                <?php 
                                                    // Truncate hash gracefully for display while showing the $2y$ prefix
                                                    $hash = htmlspecialchars($user['password_hash']);
                                                    echo strlen($hash) > 28 ? substr($hash, 0, 24) . '...' : $hash; 
                                                ?>
                                            </span>
                                        </td>
                                        <td style="font-size:0.75rem; color:#64748b;">
                                            <?php echo htmlspecialchars(substr($user['created_at'], 0, 10)); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div style="margin-top:1.5rem; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px; padding:1rem; font-size:0.82rem; color:#166534;">
                    <div style="font-weight:700; margin-bottom:0.25rem; display:flex; align-items:center; gap:0.4rem;">
                        <i class="fa-solid fa-lock"></i> Security Verification Note for Faculty:
                    </div>
                    Passwords are never stored in plain text. Every password undergoes one-way cryptographic hashing using <code>password_hash(..., PASSWORD_BCRYPT)</code> with a random per-user salt and cost factor of 10.
                </div>
            </div>

        </div>

        <footer class="footer">
            StudentHub Portal &bull; Web Development Frameworks (WDF) &bull; CHARUSAT - CSPIT &bull; Student ID: 25CS069
        </footer>

    </div>

</body>
</html>
