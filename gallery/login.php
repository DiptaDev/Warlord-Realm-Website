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
    <title>Login - Warlord Realm Gallery</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-black: #0a0a0a;
            --secondary-black: #111111;
            --accent-red: #d32f2f;
            --accent-dark-red: #b71c1c;
            --accent-light-red: #f44336;
            --pure-white: #ffffff;
            --light-gray: #f5f5f5;
            --medium-gray: #9e9e9e;
            --dark-gray: #212121;
            --border-color: #333333;
            --input-bg: rgba(255, 255, 255, 0.08);
            --glow-red: rgba(211, 47, 47, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--primary-black) 0%, var(--secondary-black) 100%);
            color: var(--pure-white);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 30%, var(--glow-red) 0%, transparent 40%),
                radial-gradient(circle at 80% 70%, var(--glow-red) 0%, transparent 40%);
            z-index: -1;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            background: linear-gradient(145deg, rgba(33, 33, 33, 0.95), rgba(17, 17, 17, 0.98));
            border-radius: 16px;
            padding: 40px;
            border: 1px solid var(--border-color);
            box-shadow: 
                0 20px 50px rgba(0, 0, 0, 0.6),
                0 0 30px var(--glow-red);
            position: relative;
            animation: slideUp 0.4s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-red), var(--accent-light-red));
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .logo-img {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            object-fit: cover;
            /* border: 2px solid var(--accent-red); */
            box-shadow: 
                0 6px 20px var(--glow-red),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }

        .logo-text h1 {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(to right, var(--accent-red), var(--accent-light-red));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .logo-text p {
            font-size: 0.9rem;
            color: var(--medium-gray);
            margin-top: 2px;
        }

        .login-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--pure-white);
            margin-bottom: 8px;
        }

        .login-subtitle {
            color: var(--medium-gray);
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--light-gray);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px;
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--pure-white);
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent-red);
            box-shadow: 0 0 0 3px var(--glow-red);
        }

        .form-input::placeholder {
            color: var(--medium-gray);
        }

        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--accent-red), var(--accent-dark-red));
            color: var(--pure-white);
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 10px;
        }

        .btn:hover {
            background: linear-gradient(135deg, var(--accent-light-red), var(--accent-red));
            transform: translateY(-2px);
            box-shadow: 0 8px 25px var(--glow-red);
        }

        .btn:active {
            transform: translateY(0);
        }

        .alert {
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .alert-error {
            background: rgba(211, 47, 47, 0.15);
            border: 1px solid rgba(211, 47, 47, 0.3);
            color: var(--accent-light-red);
        }

        .alert i {
            font-size: 1.1rem;
        }

        .links {
            text-align: center;
            margin-top: 25px;
            color: var(--medium-gray);
            font-size: 0.9rem;
        }

        .links a {
            color: var(--accent-red);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .links a:hover {
            color: var(--accent-light-red);
        }

        /* Back Link Container */
        .back-link-container {
            margin-top: 30px;
            text-align: center;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--light-gray);
            text-decoration: none;
            font-weight: 600;
            padding: 12px 24px;
            background: linear-gradient(135deg, rgba(33, 33, 33, 0.8), rgba(51, 51, 51, 0.6));
            border-radius: 10px;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: var(--accent-red);
            border-color: var(--accent-red);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        @media (max-width: 768px) {
            .login-container {
                padding: 30px 24px;
                max-width: 90%;
            }
            
            .login-title {
                font-size: 1.5rem;
            }
            
            .logo-text h1 {
                font-size: 1.3rem;
            }
            
            .back-link {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 25px 20px;
            }
            
            body {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-logo">
                <img src="../asset/logo-min.png" alt="Warlord Realm Logo" class="logo-img" onerror="this.style.display='none'; this.parentElement.querySelector('.logo-icon').style.display='flex';">
                
                <div class="logo-text">
                    <h1>WARLORD REALM</h1>
                    <p>Gallery Login</p>
                </div>
            </div>
            
            <h2 class="login-title">Welcome Back</h2>
            <p class="login-subtitle">Sign in to your account</p>
        </div>

        <?php if($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-input" required 
                       placeholder="Enter your username">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-input" required 
                       placeholder="Enter your password">
            </div>

            <button type="submit" class="btn">
                <i class="fas fa-sign-in-alt"></i>
                <span>Sign In</span>
            </button>
        </form>

        <div class="links">
            <p>Don't have an account? <a href="register.php">Create one now</a></p>
        </div>
    </div>

    <!-- Back Link Container -->
    <div class="back-link-container">
        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Gallery</span>
        </a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const backLink = document.querySelector('.back-link');
            
            // Form submission
            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    // Show loading state
                    const submitBtn = this.querySelector('.btn');
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Signing In...</span>';
                        submitBtn.disabled = true;
                    }
                });
            }
            
            // Back link interaction
            if (backLink) {
                backLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Add click animation
                    this.style.transform = 'translateY(-3px)';
                    this.style.opacity = '0.8';
                    
                    setTimeout(() => {
                        window.location.href = this.getAttribute('href');
                    }, 300);
                });
                
                // Reset animation on mouse leave
                backLink.addEventListener('mouseleave', function() {
                    setTimeout(() => {
                        this.style.transform = '';
                        this.style.opacity = '';
                    }, 300);
                });
            }
            
            // Auto-focus first input
            setTimeout(() => {
                const firstInput = document.querySelector('.form-input');
                if (firstInput) {
                    firstInput.focus();
                }
            }, 300);
        });
    </script>
</body>
</html>