<?php
require_once __DIR__ . '/auth.php';

if (isLoggedIn()) {
    header('Location: /admin/index.php');
    exit;
}
// ADMIN_USER: 'admin';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');
    if ($user === 'admin' && $pass === 'boutique@nds') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header('Location: /admin/index.php');
        exit;
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
<link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=DM+Serif+Display&display=swap" rel="stylesheet">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    background: #0c0e0d;
    font-family: 'DM Mono', monospace;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}
.login-box {
    background: #141614;
    border: 1px solid #2a2e2a;
    padding: 48px;
    width: 100%;
    max-width: 400px;
}
.login-box h1 {
    font-family: 'DM Serif Display', serif;
    color: #9ebffe;
    font-size: 1.6rem;
    margin-bottom: 6px;
}
.login-box p { color: #556055; font-size: 12px; margin-bottom: 32px; }
label { display: block; font-size: 11px; color: #556055; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; }
input[type=text], input[type=password] {
    width: 100%;
    background: #0c0e0d;
    border: 1px solid #2a2e2a;
    color: #d4d9d4;
    padding: 10px 14px;
    font-family: 'DM Mono', monospace;
    font-size: 13px;
    margin-bottom: 20px;
    outline: none;
    transition: border-color 0.15s;
}
input:focus { border-color: #9ebffe; }
button {
    width: 100%;
    background: #9ebffe;
    color: #0c0e0d;
    border: none;
    padding: 12px;
    font-family: 'DM Mono', monospace;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.15s;
}
button:hover { background: #000; color: #9ebffe; }
.error { background: #2a1414; border: 1px solid #5a2020; color: #e06363; padding: 10px 14px; font-size: 12px; margin-bottom: 20px; }
</style>
</head>
<body>
<div class="login-box">
    <h1>Admin Panel</h1>
    <p>Boutique Hotels in Udaipur</p>
    <?php if ($error): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" autofocus autocomplete="off">
        <label>Password</label>
        <input type="password" name="password">
        <button type="submit">Sign In →</button>
    </form>
</div>
</body>
</html>