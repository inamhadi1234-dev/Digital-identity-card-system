<?php
require __DIR__ . '/../config.php';

if (isAdminLoggedIn()) {
    redirect('index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $db->prepare('SELECT id, username, password_hash, full_name FROM admin_users WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_name'] = $admin['full_name'];
        redirect('index.php');
    }

    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #eff6ff, #f8fafc);
            min-height: 100vh;
            display: grid;
            place-items: center;
        }
        .login-box {
            width: min(420px, 92vw);
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 32px;
            box-shadow: 0 18px 38px rgba(15, 23, 42, 0.08);
        }
        h1 { margin-top: 0; }
        .field { margin-bottom: 18px; }
        label { display: block; font-weight: 700; margin-bottom: 8px; }
        input {
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            font-size: 1rem;
        }
        .btn {
            width: 100%;
            background: #1d4ed8;
            color: white;
            border: 0;
            padding: 12px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
        }
        .error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 12px 14px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-weight: 600;
        }
        .meta {
            text-align: center;
            margin-top: 18px;
            color: #475569;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>Admin Login</h1>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="field">
                <label for="username">Username</label>
                <input id="username" name="username" type="text" required value="admin">
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required value="admin123">
            </div>
            <button class="btn" type="submit">Login to Admin Panel</button>
        </form>
        <div class="meta">Default login: admin / admin123</div>
    </div>
</body>
</html>
