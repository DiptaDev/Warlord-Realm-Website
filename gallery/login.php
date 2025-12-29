<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    // Cek user di database
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin'];
            
            // Redirect ke halaman utama
            header('Location: index.php?message=Login successful!');
            exit();
        } else {
            $error = 'Invalid password!';
        }
    } else {
        $error = 'User not found!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warlord Realm | Gallery Login</title>
    <link rel="shortcut icon" href="../asset/logo-min.png" type="image/x-icon">
    <link rel="stylesheet" href="../asset/style-gallery_login_pange.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="brand-header">
            <div class="brand-logo">
                <img src="../asset/logo-min.png" alt="Warlord Realm Logo" 
                     onerror="this.onerror=null; this.style.display='none'; this.parentElement.innerHTML='W'; this.parentElement.style.background='linear-gradient(135deg, var(--accent-primary), var(--accent-secondary))'; this.parentElement.style.display='flex'; this.parentElement.style.alignItems='center'; this.parentElement.style.justifyContent='center'; this.parentElement.style.color='white'; this.parentElement.style.fontSize='28px'; this.parentElement.style.fontWeight='bold';">
            </div>
            <h1 class="brand-name">WARLORD REALM</h1>
            <p class="brand-subtitle">Gallery</p>
        </div>

        <div class="login-card">
            <h2 class="form-title">Sign In</h2>

            <?php if($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm">
                <div class="form-group">
                    <div class="input-wrapper">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" id="username" name="username" class="form-input" required 
                               placeholder="Username">
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="password" name="password" class="form-input" required 
                               placeholder="Password">
                        <button type="button" class="password-toggle" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Login</span>
                </button>
            </form>

            <div class="divider">
                <span>OR</span>
            </div>

            <p class="signup-link">
                Don't have an account?
                <a href="register.php">Create Account</a>
            </p>
        </div>

        <!-- BACK BUTTON CONTAINER -->
        <div class="back-button-container">
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Gallery</span>
            </a>
        </div>
    </div>

    <script src="../asset/java_script-gallery_login_pange.js"></script>
</body>
</html>