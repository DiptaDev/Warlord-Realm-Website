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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-primary: #0f0f0f;
            --bg-secondary: #1a1a1a;
            --bg-card: #181818;
            --accent-primary: #d32f2f;
            --accent-secondary: #b71c1c;
            --accent-hover: #f44336;
            --text-primary: #ffffff;
            --text-secondary: #b3b3b3;
            --text-muted: #666666;
            --border-light: #333333;
            --border-dark: #222222;
            --shadow-heavy: 0 8px 32px rgba(0, 0, 0, 0.4);
            --shadow-light: 0 4px 16px rgba(0, 0, 0, 0.2);
            --glow-red: rgba(211, 47, 47, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'Segoe UI', sans-serif;
        }

        body {
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
            overflow-y: auto;
        }

        /* Simple background - lebih ringan */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 30%, rgba(211, 47, 47, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 80% 70%, rgba(211, 47, 47, 0.05) 0%, transparent 40%);
            z-index: -1;
        }

        .register-wrapper {
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
        }

        .brand-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .brand-logo {
            width: 70px;
            height: 70px;
            margin: 0 auto 15px;
            border-radius: 14px;
            overflow: hidden;
            position: relative;
            box-shadow: 
                0 8px 25px rgba(0, 0, 0, 0.5),
                0 0 0 1px rgba(255, 255, 255, 0.05);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
        }

        .brand-logo:hover {
            transform: scale(1.05);
            box-shadow: 
                0 12px 35px rgba(211, 47, 47, 0.3),
                0 0 0 1px rgba(211, 47, 47, 0.1);
        }

        .brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .brand-logo:hover img {
            transform: scale(1.05);
        }

        .brand-name {
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 6px;
            background: linear-gradient(to right, #eb5050 0%, #5b0b0b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .brand-subtitle {
            color: var(--text-muted);
            font-size: 0.85rem;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .register-card {
            background: var(--bg-card);
            border-radius: 16px;
            border: 1px solid var(--border-dark);
            padding: 35px;
            box-shadow: var(--shadow-heavy);
            position: relative;
            overflow: visible;
            margin-bottom: 20px;
        }

        .register-card::before {
            content: '';
            position: absolute;
            top: -1px;
            left: -1px;
            right: -1px;
            height: 3px;
            background: linear-gradient(90deg, 
                var(--accent-secondary), 
                var(--accent-primary),
                var(--accent-secondary));
            border-radius: 16px 16px 0 0;
        }

        .form-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 25px;
            text-align: center;
            color: var(--text-primary);
        }

        .form-group {
            margin-bottom: 20px;
            width: 100%;
        }

        .input-wrapper {
            position: relative;
            width: 100%;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.95rem;
            transition: all 0.2s ease;
            z-index: 2;
        }

        .form-input {
            width: 100%;
            padding: 15px 15px 15px 48px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-light);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 0.95rem;
            transition: all 0.2s ease;
            outline: none;
            box-sizing: border-box;
            display: block;
        }

        .form-input:focus {
            border-color: var(--accent-primary);
            background: rgba(26, 26, 26, 0.8);
            box-shadow: 
                0 0 0 3px var(--glow-red);
        }

        .form-input:focus + .input-icon {
            color: var(--accent-primary);
        }

        .form-input::placeholder {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 5px;
            transition: all 0.2s ease;
            z-index: 2;
        }

        .password-toggle:hover {
            color: var(--accent-primary);
        }

        .register-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            color: var(--text-primary);
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }

        .register-btn:hover {
            background: linear-gradient(135deg, var(--accent-hover), var(--accent-primary));
            transform: translateY(-2px);
            box-shadow: 
                0 8px 25px rgba(211, 47, 47, 0.4);
        }

        .register-btn:active {
            transform: translateY(0);
        }

        .login-btn {
            width: 100%;
            padding: 14px;
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-primary);
            border: 1px solid var(--border-light);
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 10px;
            text-decoration: none;
        }

        .login-btn:hover {
            background: rgba(211, 47, 47, 0.1);
            border-color: var(--accent-primary);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border-light);
        }

        .divider span {
            padding: 0 15px;
        }

        .login-link {
            text-align: center;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .login-link a {
            color: var(--accent-primary);
            text-decoration: none;
            font-weight: 600;
            margin-left: 5px;
            transition: color 0.2s ease;
        }

        .login-link a:hover {
            color: var(--accent-hover);
            text-decoration: underline;
        }

        .error-message {
            background: rgba(211, 47, 47, 0.1);
            border: 1px solid rgba(211, 47, 47, 0.2);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .error-message i {
            color: var(--accent-primary);
            font-size: 1.1rem;
            margin-top: 1px;
        }

        .error-message span {
            flex: 1;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .success-message {
            background: rgba(76, 175, 80, 0.1);
            border: 1px solid rgba(76, 175, 80, 0.2);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
        }

        .success-message i {
            color: #4caf50;
            font-size: 2rem;
            margin-bottom: 15px;
            display: block;
        }

        .success-message p {
            color: var(--text-primary);
            font-size: 0.95rem;
            margin-bottom: 20px;
            line-height: 1.4;
        }

        /* BACK BUTTON CONTAINER */
        .back-button-container {
            margin-top: 20px;
            text-align: center;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: rgba(26, 26, 26, 0.8);
            border: 1px solid var(--border-light);
            border-radius: 10px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .back-btn:hover {
            color: var(--text-primary);
            border-color: var(--accent-primary);
            background: rgba(211, 47, 47, 0.1);
            transform: translateY(-1px);
        }

        /* Password strength indicator */
        .password-strength {
            height: 3px;
            background: var(--border-light);
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
        }

        .strength-meter {
            height: 100%;
            width: 0%;
            background: var(--accent-primary);
            transition: width 0.3s ease, background 0.3s ease;
            border-radius: 2px;
        }

        /* Loading animation */
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .loading {
            animation: spin 1s linear infinite;
        }

        /* ========== RESPONSIVE FIXES ========== */
        
        /* Tablet */
        @media (max-width: 768px) {
            .register-wrapper {
                max-width: 400px;
                padding: 10px;
            }
            
            .register-card {
                padding: 30px 25px;
                border-radius: 14px;
            }
            
            .brand-header {
                margin-bottom: 25px;
            }
            
            .brand-logo {
                width: 65px;
                height: 65px;
                margin-bottom: 12px;
            }
            
            .brand-name {
                font-size: 1.5rem;
            }
            
            .form-title {
                font-size: 1.2rem;
                margin-bottom: 20px;
            }
            
            .form-input {
                padding: 14px 14px 14px 45px;
                font-size: 0.9rem;
            }
            
            .register-btn {
                padding: 15px;
                font-size: 0.95rem;
            }
            
            .divider {
                margin: 20px 0;
            }
            
            .error-message,
            .success-message {
                padding: 15px;
            }
            
            .back-button-container {
                margin-top: 15px;
            }
        }

        /* Mobile */
        @media (max-width: 480px) {
            body {
                padding: 15px;
                align-items: flex-start;
                min-height: auto;
                height: auto;
                overflow-y: auto;
            }
            
            .register-wrapper {
                max-width: 100%;
                padding: 0;
                margin: 0;
            }
            
            .register-card {
                padding: 25px 20px;
                border-radius: 12px;
                border: 1px solid var(--border-dark);
                background: var(--bg-card);
                box-shadow: var(--shadow-heavy);
                margin-bottom: 15px;
            }
            
            .brand-header {
                margin-bottom: 20px;
            }
            
            .brand-logo {
                width: 60px;
                height: 60px;
                margin-bottom: 10px;
            }
            
            .brand-name {
                font-size: 1.4rem;
            }
            
            .brand-subtitle {
                font-size: 0.8rem;
            }
            
            .form-title {
                font-size: 1.1rem;
                margin-bottom: 20px;
            }
            
            .form-group {
                margin-bottom: 18px;
            }
            
            .form-input {
                padding: 13px 13px 13px 42px;
                font-size: 0.9rem;
                border-radius: 8px;
            }
            
            .input-icon {
                left: 14px;
                font-size: 0.9rem;
            }
            
            .password-toggle {
                right: 12px;
                padding: 4px;
            }
            
            .register-btn {
                padding: 14px;
                font-size: 0.9rem;
                border-radius: 8px;
            }
            
            .login-btn {
                padding: 13px;
                font-size: 0.9rem;
                border-radius: 8px;
            }
            
            .divider {
                margin: 18px 0;
                font-size: 0.75rem;
            }
            
            .divider span {
                padding: 0 12px;
            }
            
            .login-link {
                font-size: 0.85rem;
            }
            
            .error-message,
            .success-message {
                padding: 12px;
                border-radius: 8px;
                margin-bottom: 18px;
            }
            
            .success-message i {
                font-size: 1.8rem;
                margin-bottom: 12px;
            }
            
            .success-message p {
                font-size: 0.9rem;
                margin-bottom: 15px;
            }
            
            .back-button-container {
                margin-top: 15px;
            }
            
            .back-btn {
                padding: 10px 16px;
                font-size: 0.85rem;
                border-radius: 8px;
            }
            
            .password-strength {
                height: 2px;
                margin-top: 6px;
            }
        }

        /* Small mobile */
        @media (max-width: 360px) {
            body {
                padding: 10px;
            }
            
            .register-card {
                padding: 20px 16px;
            }
            
            .brand-logo {
                width: 55px;
                height: 55px;
            }
            
            .brand-name {
                font-size: 1.3rem;
            }
            
            .form-title {
                font-size: 1rem;
            }
            
            .form-input {
                padding: 12px 12px 12px 40px;
                font-size: 0.85rem;
            }
            
            .input-icon {
                left: 12px;
                font-size: 0.85rem;
            }
            
            .register-btn,
            .login-btn {
                padding: 12px;
                font-size: 0.85rem;
            }
        }

        /* Landscape mode fix */
        @media (max-height: 600px) and (orientation: landscape) {
            body {
                padding: 10px;
                align-items: flex-start;
                min-height: auto;
                height: auto;
            }
            
            .register-wrapper {
                max-width: 100%;
            }
            
            .brand-header {
                margin-bottom: 15px;
            }
            
            .brand-logo {
                width: 50px;
                height: 50px;
                margin-bottom: 8px;
            }
            
            .register-card {
                padding: 20px;
            }
            
            .form-group {
                margin-bottom: 15px;
            }
            
            .form-input {
                padding: 12px 12px 12px 40px;
            }
            
            .register-btn,
            .login-btn {
                padding: 12px;
            }
            
            .divider {
                margin: 15px 0;
            }
        }

        /* Print styles */
        @media print {
            .register-card {
                box-shadow: none;
                border: 1px solid #000;
            }
            
            .back-button-container {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="register-wrapper">
        <div class="brand-header">
            <div class="brand-logo">
                <img src="../asset/logo-min.png" alt="Warlord Realm Logo" 
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const registerForm = document.getElementById('registerForm');
            const togglePassword = document.getElementById('togglePassword');
            const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const backBtn = document.querySelector('.back-btn');
            const brandLogo = document.querySelector('.brand-logo');
            const strengthMeter = document.getElementById('strengthMeter');

            // Toggle password visibility
            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    const icon = this.querySelector('i');
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                });
            }

            // Toggle confirm password visibility
            if (toggleConfirmPassword && confirmPasswordInput) {
                toggleConfirmPassword.addEventListener('click', function() {
                    const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    confirmPasswordInput.setAttribute('type', type);
                    
                    const icon = this.querySelector('i');
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                });
            }

            // Password strength indicator
            if (passwordInput && strengthMeter) {
                passwordInput.addEventListener('input', function() {
                    const password = this.value;
                    let strength = 0;
                    
                    if (password.length >= 6) strength += 25;
                    if (/[A-Z]/.test(password)) strength += 25;
                    if (/[0-9]/.test(password)) strength += 25;
                    if (/[^A-Za-z0-9]/.test(password)) strength += 25;
                    
                    strengthMeter.style.width = strength + '%';
                    
                    if (strength < 50) {
                        strengthMeter.style.background = '#d32f2f';
                    } else if (strength < 75) {
                        strengthMeter.style.background = '#ff9800';
                    } else {
                        strengthMeter.style.background = '#4caf50';
                    }
                });
            }

            // Password confirmation validation
            if (confirmPasswordInput && passwordInput) {
                confirmPasswordInput.addEventListener('input', function() {
                    if (this.value === passwordInput.value && this.value !== '') {
                        this.style.borderColor = '#4caf50';
                    } else {
                        this.style.borderColor = '';
                    }
                });
            }

            // Form submission
            if (registerForm) {
                registerForm.addEventListener('submit', function(e) {
                    // Basic validation
                    if (passwordInput.value.length < 6) {
                        e.preventDefault();
                        alert('Password must be at least 6 characters long!');
                        passwordInput.focus();
                        return;
                    }
                    
                    if (passwordInput.value !== confirmPasswordInput.value) {
                        e.preventDefault();
                        alert('Passwords do not match!');
                        confirmPasswordInput.focus();
                        return;
                    }
                    
                    // Show loading state
                    const submitBtn = this.querySelector('.register-btn');
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="fas fa-spinner loading"></i><span>Creating Account...</span>';
                        submitBtn.disabled = true;
                        
                        setTimeout(() => {
                            if (submitBtn.disabled) {
                                submitBtn.innerHTML = '<i class="fas fa-user-plus"></i><span>Create Account</span>';
                                submitBtn.disabled = false;
                            }
                        }, 5000);
                    }
                });
            }

            // Back button interaction
            if (backBtn) {
                backBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    window.location.href = this.getAttribute('href');
                });
            }

            // Brand logo click effect
            if (brandLogo) {
                brandLogo.addEventListener('click', function() {
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 200);
                });
            }

            // Auto-focus username
            setTimeout(() => {
                const usernameInput = document.getElementById('username');
                if (usernameInput) {
                    usernameInput.focus();
                }
            }, 100);

            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Ctrl/Cmd + Enter to submit
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                    if (registerForm) registerForm.requestSubmit();
                }
                
                // Escape to go back
                if (e.key === 'Escape') {
                    window.location.href = 'index.php';
                }
            });
        });
    </script>
</body>
</html>