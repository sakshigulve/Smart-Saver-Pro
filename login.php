<?php
include 'includes/config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['profile_pic'] = $user['profile_pic'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid Email or Password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Smart Saver Pro</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="auth-container">
    <div class="auth-card card">
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="background: var(--primary); width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                <i class="fa fa-shield-halved" style="color: white; font-size: 1.8rem;"></i>
            </div>
            <h2 style="margin: 0; color: var(--text-main);">Welcome Back</h2>
            <p style="color: var(--text-dim); font-size: 0.9rem;">Secure access to your finances</p>
        </div>

        <?php if($error): ?>
            <div style="background: rgba(255, 75, 43, 0.1); color: var(--danger); padding: 12px; border-radius: 10px; margin-bottom: 20px; font-size: 0.85rem; text-align: center; border: 1px solid var(--danger);">
                <i class="fa fa-circle-exclamation"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label style="display:block; margin-bottom:8px; font-size:0.8rem; color:var(--text-dim);">Email Address</label>
                <input type="email" name="email" placeholder="name@example.com" required>
            </div>
            <div class="form-group">
                <label style="display:block; margin-bottom:8px; font-size:0.8rem; color:var(--text-dim);">Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="btn">Login to Account</button>
        </form>

        <!-- REGISTER BUTTON SECTION -->
        <div style="margin-top: 25px; text-align: center; border-top: 1px solid var(--glass); padding-top: 25px;">
            <p style="color: var(--text-dim); font-size: 0.85rem; margin-bottom: 15px;">New to Smart Saver Pro?</p>
            <a href="register.php" style="text-decoration: none;">
                <button type="button" class="btn" style="background: transparent; border: 1px solid var(--primary); color: var(--primary);">
                    <i class="fa fa-user-plus"></i> Create New Account
                </button>
            </a>
        </div>
    </div>
</body>
</html>