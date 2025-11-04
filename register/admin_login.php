<?php
// admin_login.php
include 'config.php';

if (isAdminLoggedIn()) {
    header('Location: admin_panel.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (adminLogin($username, $password)) {
        header('Location: admin_panel.php');
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Warlord Realm</title>
    <link rel="stylesheet" href="../asset/style-admin_login_register_pange.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="ambient-bg"></div>
    
    <div class="login-container">
        <div class="logo-container">
            <img src="../asset/logo.jpg" alt="Warlord Realm" class="logo-image">
        </div>
        
        <h1 class="logo-text">Warlord Panel</h1>
        <p class="subtitle">Warlord Realm Management</p>
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="input-group">
                <label for="username"><i class="fas fa-user"></i> Username</label>
                <input type="text" id="username" name="username" required placeholder="Masukkan username admin">
            </div>
            
            <div class="input-group">
                <label for="password"><i class="fas fa-lock"></i> Password</label>
                <input type="password" id="password" name="password" required placeholder="Masukkan password">
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
        
        <div class="back-link">
            <a href="../index.html"><i class="fas fa-arrow-left"></i> Kembali ke Website</a>
        </div>
    </div>
</body>
</html>