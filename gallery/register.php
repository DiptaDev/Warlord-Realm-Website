<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $minecraft_username = sanitize($_POST['minecraft_username']);
    
    // Validasi
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'All fields are required!';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match!';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters!';
    } else {
        // Cek apakah username sudah ada
        $check_query = "SELECT id FROM users WHERE username = '$username' OR email = '$email'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = 'Username or email already exists!';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user ke database
            $insert_query = "INSERT INTO users (username, email, password, minecraft_username) 
                            VALUES ('$username', '$email', '$hashed_password', '$minecraft_username')";
            
            if (mysqli_query($conn, $insert_query)) {
                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warlord Realm | Gallery Register</title>
    <link rel="shortcut icon" href="../asset/logo-min.png" type="image/x-icon">
    <link rel="stylesheet" href="../asset/style-gallery_register_pange.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="register-wrapper">
        <div class="brand-header">
            <div class="brand-logo">
                <img src="../asset/logo.jpg" alt="Warlord Realm Logo" 
                     onerror="this.onerror=null; this.style.display='none'; this.parentElement.innerHTML='W'; this.parentElement.style.background='linear-gradient(135deg, var(--accent-primary), var(--accent-secondary))'; this.parentElement.style.display='flex'; this.parentElement.style.alignItems='center'; this.parentElement.style.justifyContent='center'; this.parentElement.style.color='white'; this.parentElement.style.fontSize='28px'; this.parentElement.style.fontWeight='bold';">
            </div>
            <h1 class="brand-name">WARLORD REALM</h1>
            <p class="brand-subtitle">Create Account</p>
        </div>

        <div class="register-card">
            <h2 class="form-title">Join the Community <br> <br><p class=brand-subtitle>to share your Warlord Realm adventure</p></h2>

            <?php if($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <p><?php echo $success; ?></p>
                    <a href="login.php" class="login-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Go to Login</span>
                    </a>
                </div>
            <?php else: ?>
                <form method="POST" action="" id="registerForm">
                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" id="username" name="username" class="form-input" required 
                                   placeholder="Username">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" id="email" name="email" class="form-input" required 
                                   placeholder="Email">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="fas fa-gamepad input-icon"></i>
                            <input type="text" id="minecraft_username" name="minecraft_username" class="form-input" 
                                   placeholder="Minecraft username (optional)">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="password" name="password" class="form-input" required 
                                   placeholder="Password (min. 6 chars)">
                            <button type="button" class="password-toggle" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="strength-meter" id="strengthMeter"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-input" required 
                                   placeholder="Confirm password">
                            <button type="button" class="password-toggle" id="toggleConfirmPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="register-btn">
                        <i class="fas fa-user-plus"></i>
                        <span>Create Account</span>
                    </button>
                </form>

                <div class="divider">
                    <span>OR</span>
                </div>

                <div class="login-link">
                    <p>Already have an account? <a href="login.php">Sign in here</a></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- BACK BUTTON CONTAINER -->
        <div class="back-button-container">
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Gallery</span>
            </a>
        </div>
    </div>

    <script src="../asset/java_script-gallery_register_pange.js"></script>
</body>
</html>