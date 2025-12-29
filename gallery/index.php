<?php
require_once 'config.php';

// Ambil gambar yang sudah approved untuk ditampilkan
$query = "SELECT i.*, u.username, u.minecraft_username 
          FROM images i 
          JOIN users u ON i.user_id = u.id 
          WHERE i.status = 'approved' 
          ORDER BY i.uploaded_at DESC";
$result = mysqli_query($conn, $query);
$images = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../asset/logo-min.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Warlord Realm | Gallery</title>
        <!-- SEO Meta Tag not tested yet-->
    <meta name="description" content="Warlord Realm Gallery, Share your epic Warlord Realm adventures with the community">
    <meta name="keywords" content="Minecraft, Warlord Realm, Indonesia Minecraft Server, Survival, Bedrock, Java Edition, Semi Anarchy">
    <meta name="author" content="Warlord Network by dipta14">
    <!-- Open Graph for Social Media not tested yet-->
    <meta property="og:title" content="Warlord Realm - Minecraft Server">
    <meta property="og:description" content="Warlord Realm Gallery, Share your epic Warlord Realm adventures with the community">
    <meta property="og:image" content="../asset/logo-min.png">
    <meta property="og:url" content="https://warlordrealm.ct.ws">
    <meta property="og:type" content="website">
    <!-- Twitter Card not tested yet-->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Warlord Realm - Minecraft Server">
    <meta name="twitter:description" content="Warlord Realm Gallery, Share your epic Warlord Realm adventures with the community">
    <meta name="twitter:image" content="../asset/Twitter_Card_Image.png">
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
            --glow-red: rgba(211, 47, 47, 0.3);
            --glow-dark-red: rgba(183, 28, 28, 0.4);
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
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 10% 20%, var(--glow-red) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, var(--glow-dark-red) 0%, transparent 20%);
            z-index: -1;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        .carbon-header {
            background: linear-gradient(135deg, rgba(10, 10, 10, 0.95) 0%, rgba(17, 17, 17, 0.95) 100%);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
            padding: 20px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
        }

        .logo-img {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            object-fit: cover;
            box-shadow: 0 8px 20px var(--glow-red);
        }

        .logo-text h1 {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(to right, var(--accent-red), var(--accent-light-red));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: 1px;
        }

        .logo-text p {
            font-size: 0.9rem;
            color: var(--medium-gray);
            font-weight: 500;
        }

        /* User Actions */
        .user-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-red), var(--accent-dark-red));
            color: var(--pure-white);
            box-shadow: 0 4px 15px var(--glow-red);
            border: 1px solid rgba(211, 47, 47, 0.4);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--accent-light-red), var(--accent-red));
            transform: translateY(-3px);
            box-shadow: 0 8px 25px var(--glow-dark-red);
        }

        .btn-secondary {
            background: linear-gradient(135deg, rgba(33, 33, 33, 0.8), rgba(51, 51, 51, 0.6));
            color: var(--light-gray);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, rgba(51, 51, 51, 0.9), rgba(33, 33, 33, 0.7));
            transform: translateY(-3px);
            border-color: var(--accent-red);
        }

        .welcome-text {
            color: var(--medium-gray);
            font-size: 0.9rem;
            padding: 8px 16px;
            background: rgba(33, 33, 33, 0.6);
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .welcome-text strong {
            color: var(--accent-red);
        }

        /* Hero Section */
        .hero-section {
            padding: 80px 0 60px;
            text-align: center;
            position: relative;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            background: linear-gradient(to right, var(--accent-red), var(--accent-light-red));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: var(--medium-gray);
            max-width: 600px;
            margin: 0 auto 40px;
            line-height: 1.6;
        }

        /* Gallery Grid */
        .gallery-section {
            padding: 40px 0;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }

        .gallery-card {
            background: linear-gradient(145deg, rgba(33, 33, 33, 0.8), rgba(17, 17, 17, 0.9));
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            transition: all 0.4s ease;
            cursor: pointer;
        }

        .gallery-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6), 0 0 30px var(--glow-red);
            border-color: var(--accent-red);
        }

        .gallery-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            display: block;
            transition: transform 0.6s ease;
        }

        .gallery-card:hover .gallery-image {
            transform: scale(1.05);
        }

        .gallery-content {
            padding: 25px;
        }

        .image-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--pure-white);
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .image-description {
            color: var(--medium-gray);
            font-size: 0.95rem;
            margin-bottom: 20px;
            line-height: 1.6;
            max-height: 60px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }

        .image-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        .uploader-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .uploader-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--accent-red), var(--accent-dark-red));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--pure-white);
            font-weight: 700;
            font-size: 1rem;
            box-shadow: 0 4px 12px var(--glow-red);
        }

        .uploader-details {
            display: flex;
            flex-direction: column;
        }

        .uploader-name {
            font-size: 0.95rem;
            color: var(--pure-white);
            font-weight: 500;
        }

        .minecraft-name {
            font-size: 0.85rem;
            color: var(--accent-red);
            font-weight: 500;
        }

        .upload-date {
            font-size: 0.85rem;
            color: var(--medium-gray);
            background: rgba(33, 33, 33, 0.6);
            padding: 5px 10px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
        }

        /* Empty State */
        .empty-gallery {
            grid-column: 1 / -1;
            text-align: center;
            padding: 80px 40px;
            background: linear-gradient(145deg, rgba(33, 33, 33, 0.8), rgba(17, 17, 17, 0.9));
            border-radius: 20px;
            border: 2px dashed var(--border-color);
            margin: 40px 0;
        }

        .empty-icon {
            font-size: 4rem;
            color: var(--accent-red);
            margin-bottom: 25px;
            opacity: 0.7;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.7; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.05); }
        }

        .empty-title {
            font-size: 2rem;
            color: var(--pure-white);
            margin-bottom: 15px;
            font-weight: 600;
        }

        .empty-description {
            color: var(--medium-gray);
            font-size: 1.1rem;
            max-width: 500px;
            margin: 0 auto 30px;
            line-height: 1.6;
        }

        /* Back Link - Positioned under container */
        .back-link-container {
            text-align: center;
            margin: 60px 0 40px;
            padding-top: 40px;
            border-top: 1px solid var(--border-color);
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

        /* Alert Messages */
        .alert {
            position: fixed;
            top: 100px;
            right: 30px;
            padding: 20px 25px;
            background: linear-gradient(135deg, rgba(33, 33, 33, 0.95), rgba(17, 17, 17, 0.95));
            border-radius: 12px;
            border-left: 5px solid var(--accent-red);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5), 0 0 20px var(--glow-red);
            z-index: 9999;
            max-width: 400px;
            animation: slideInRight 0.5s ease, slideOutRight 0.5s ease 4.5s forwards;
            display: flex;
            align-items: center;
            gap: 15px;
            border: 1px solid var(--border-color);
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        .alert i {
            color: var(--accent-red);
            font-size: 1.3rem;
        }

        .alert-message {
            color: var(--light-gray);
            font-size: 0.95rem;
            font-weight: 500;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 20px;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .gallery-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .gallery-image {
                height: 200px;
            }

            .user-actions {
                flex-wrap: wrap;
                justify-content: center;
            }

            .back-link {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
        }

        /* IMAGE MODAL STYLES - FIXED */
        .image-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
        }

        .modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            backdrop-filter: blur(10px);
        }

        .modal-container {
            position: relative;
            max-width: 90%;
            max-height: 90vh;
            margin: 2% auto;
            background: linear-gradient(145deg, rgba(33, 33, 33, 0.95), rgba(17, 17, 17, 0.98));
            border-radius: 20px;
            border: 1px solid var(--border-color);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.8), 0 0 40px var(--glow-red);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .modal-header {
            padding: 20px 30px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(33, 33, 33, 0.95);
        }

        .modal-header h3 {
            color: var(--pure-white);
            font-size: 1.3rem;
            font-weight: 600;
            margin: 0;
        }

        .modal-close {
            background: rgba(211, 47, 47, 0.2);
            border: 1px solid rgba(211, 47, 47, 0.4);
            color: var(--accent-red);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 1001;
        }

        .modal-close:hover {
            background: rgba(211, 47, 47, 0.4);
            transform: rotate(90deg);
        }

        /* FIXED: Modal body with proper constraints */
        .modal-body {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            min-height: 300px;
            max-height: 70vh;
            overflow: hidden;
        }

        .nav-btn {
            background: rgba(33, 33, 33, 0.7);
            border: 1px solid var(--border-color);
            color: var(--pure-white);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 2;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
        }

        .nav-btn:hover {
            background: var(--accent-red);
            border-color: var(--accent-red);
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 0 20px var(--glow-red);
        }

        .prev-btn {
            left: 20px;
        }

        .next-btn {
            right: 20px;
        }

        /* FIXED: Image container with proper constraints */
        .image-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.3);
            min-height: 200px;
            max-height: 60vh;
            width: 100%;
            max-width: 100%;
        }

        /* FIXED: Modal image with proper constraints */
        #modalImage {
            max-width: 100%;
            max-height: 60vh;
            object-fit: contain;
            border-radius: 8px;
            transition: opacity 0.3s ease;
        }

        .modal-description {
            padding: 20px 30px;
            border-top: 1px solid var(--border-color);
            background: rgba(33, 33, 33, 0.8);
        }

        .modal-description p {
            color: var(--medium-gray);
            font-size: 0.95rem;
            line-height: 1.6;
            margin: 0;
        }

        .image-loader {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: none;
        }

        .image-loader.active {
            display: block;
        }

        .image-loader .loader {
            width: 40px;
            height: 40px;
            border: 3px solid transparent;
            border-top-color: var(--accent-red);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            box-shadow: 0 0 15px var(--glow-red);
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .modal-footer {
            padding: 20px 30px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(33, 33, 33, 0.8);
            flex-wrap: wrap;
            gap: 15px;
        }

        .image-info {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--medium-gray);
            font-size: 0.9rem;
        }

        .info-item i {
            color: var(--accent-red);
        }

        .image-actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            padding: 10px 20px;
            background: linear-gradient(135deg, rgba(33, 33, 33, 0.8), rgba(51, 51, 51, 0.6));
            color: var(--light-gray);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .action-btn:hover {
            background: linear-gradient(135deg, var(--accent-red), var(--accent-dark-red));
            border-color: var(--accent-red);
            color: var(--pure-white);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px var(--glow-red);
        }

        .image-counter {
            position: absolute;
            top: 20px;
            right: 80px;
            background: rgba(0, 0, 0, 0.7);
            color: var(--pure-white);
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            border: 1px solid var(--border-color);
            backdrop-filter: blur(10px);
            z-index: 1000;
        }

        @media (max-width: 768px) {
            .modal-container {
                max-width: 95%;
                margin: 1% auto;
            }

            .modal-body {
                min-height: 200px;
                max-height: 50vh;
                padding: 10px;
            }

            .nav-btn {
                width: 40px;
                height: 40px;
            }

            .prev-btn {
                left: 10px;
            }

            .next-btn {
                right: 10px;
            }

            .modal-footer {
                flex-direction: column;
                align-items: stretch;
                gap: 15px;
            }

            .image-counter {
                right: 70px;
            }

            /* Mobile fixes for image container */
            .image-container {
                min-height: 150px;
                max-height: 40vh;
            }

            #modalImage {
                max-height: 40vh;
            }
        }
    </style>
</head>
<body>
    <!-- Carbon Fiber Header -->
    <header class="carbon-header">
        <div class="container header-content">
            <a href="../" class="logo-section">
                 <img src="../asset/logo-min.png" alt="Warlord Realm Logo" class="logo-img" onerror="this.style.display='none'; this.parentElement.querySelector('.logo-icon').style.display='flex';">
                <div class="logo-text">
                    <h1>WARLORD REALM</h1>
                    <p>Minecraft Gallery</p>
                </div>
            </a>

            <div class="user-actions">
                <?php if(isLoggedIn()): ?>
                    <div class="welcome-text">
                        Welcome, <strong><?php echo $_SESSION['username']; ?></strong>
                    </div>
                    <a href="upload.php" class="btn btn-primary">
                        <i class="fas fa-cloud-upload-alt"></i> Upload
                    </a>
                    <?php if(isAdmin()): ?>
                        <a href="admin.php" class="btn btn-secondary">
                            <i class="fas fa-crown"></i> Admin
                        </a>
                    <?php endif; ?>
                    <a href="logout.php" class="btn btn-secondary">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-secondary">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="register.php" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <main class="container">
        <?php if(isset($_GET['message'])): ?>
            <div class="alert">
                <i class="fas fa-check-circle"></i>
                <span class="alert-message"><?php echo htmlspecialchars($_GET['message']); ?></span>
            </div>
        <?php endif; ?>

        <section class="hero-section">
            <h1 class="hero-title">Community Gallery</h1>
            <p class="hero-subtitle">Showcase your Warlord Realm adventures. Epic battles, amazing builds, and unforgettable moments shared by our community. <br> <small>(register to upload)</small></p>
        </section>

        <!-- Gallery Section -->
        <section class="gallery-section">
            <div class="gallery-grid" id="galleryGrid">
                <?php if(count($images) > 0): ?>
                    <?php foreach($images as $index => $image): ?>
                        <div class="gallery-card" data-index="<?php echo $index; ?>" data-description="<?php echo htmlspecialchars($image['description']); ?>">
                            <img src="uploads/<?php echo $image['filename']; ?>" 
                                 alt="<?php echo htmlspecialchars($image['title']); ?>" 
                                 class="gallery-image">
                            <div class="gallery-content">
                                <h3 class="image-title"><?php echo htmlspecialchars($image['title']); ?></h3>
                                <?php if(!empty($image['description'])): ?>
                                    <p class="image-description"><?php echo htmlspecialchars($image['description']); ?></p>
                                <?php endif; ?>
                                <div class="image-meta">
                                    <div class="uploader-info">
                                        <div class="uploader-avatar">
                                            <?php echo strtoupper(substr($image['username'], 0, 1)); ?>
                                        </div>
                                        <div class="uploader-details">
                                            <span class="uploader-name"><?php echo htmlspecialchars($image['username']); ?></span>
                                            <?php if($image['minecraft_username']): ?>
                                                <span class="minecraft-name"><?php echo htmlspecialchars($image['minecraft_username']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="upload-date">
                                        <?php echo date('M j, Y', strtotime($image['uploaded_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-gallery">
                        <div class="empty-icon">
                            <i class="fas fa-images"></i>
                        </div>
                        <h3 class="empty-title">Gallery is Empty</h3>
                        <p class="empty-description">
                            Be the first to share your Warlord Realm adventures!
                            <?php if(!isLoggedIn()): ?>
                                Register now to upload your screenshots.
                            <?php else: ?>
                                Click upload to share your first screenshot.
                            <?php endif; ?>
                        </p>
                        <?php if(!isLoggedIn()): ?>
                            <a href="register.php" class="btn btn-primary" style="padding: 12px 30px; font-size: 1rem;">
                                <i class="fas fa-user-plus"></i> Register to Upload
                            </a>
                        <?php else: ?>
                            <a href="upload.php" class="btn btn-primary" style="padding: 12px 30px; font-size: 1rem;">
                                <i class="fas fa-cloud-upload-alt"></i> Upload Screenshot
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Back Link Container -->
        <div class="back-link-container">
            <a href="../" class="back-link">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Main Site</span>
            </a>
        </div>
    </main>

    <!-- Image Preview Modal -->
    <div id="imageModal" class="image-modal">
        <div class="modal-overlay" id="modalOverlay"></div>
        <div class="modal-container">
            <div class="modal-header">
                <h3 id="modalTitle"></h3>
                <button class="modal-close" id="modalClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <button class="nav-btn prev-btn" id="prevBtn">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="image-container">
                    <img id="modalImage" src="" alt="">
                    <div class="image-loader" id="imageLoader">
                        <div class="loader"></div>
                    </div>
                </div>
                <button class="nav-btn next-btn" id="nextBtn">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            <div id="modalDescription" class="modal-description" style="display: none;">
                <p></p>
            </div>
            <div class="modal-footer">
                <div class="image-info">
                    <div class="info-item">
                        <i class="fas fa-user"></i>
                        <span id="modalUploader"></span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-calendar"></i>
                        <span id="modalDate"></span>
                    </div>
                </div>
                <div class="image-actions">
                    <button class="action-btn" id="downloadBtn">
                        <i class="fas fa-download"></i> Download
                    </button>
                </div>
            </div>
            <div class="image-counter">
                <span id="currentImage">1</span> / <span id="totalImages">1</span>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Image Modal functionality
            const imageModal = document.getElementById('imageModal');
            const modalOverlay = document.getElementById('modalOverlay');
            const modalClose = document.getElementById('modalClose');
            const modalImage = document.getElementById('modalImage');
            const imageLoader = document.getElementById('imageLoader');
            const modalTitle = document.getElementById('modalTitle');
            const modalUploader = document.getElementById('modalUploader');
            const modalDate = document.getElementById('modalDate');
            const modalDescription = document.getElementById('modalDescription');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const currentImageSpan = document.getElementById('currentImage');
            const totalImagesSpan = document.getElementById('totalImages');
            const downloadBtn = document.getElementById('downloadBtn');
            
            let currentImageIndex = 0;
            let images = [];
            let touchStartX = 0;
            let touchEndX = 0;
            const swipeThreshold = 50;
            
            // Collect all gallery images
            function collectImages() {
                const galleryCards = document.querySelectorAll('.gallery-card');
                images = [];
                
                galleryCards.forEach((card, index) => {
                    const img = card.querySelector('.gallery-image');
                    const title = card.querySelector('.image-title').textContent;
                    const uploader = card.querySelector('.uploader-name').textContent;
                    const date = card.querySelector('.upload-date').textContent;
                    const minecraftName = card.querySelector('.minecraft-name')?.textContent || '';
                    const description = card.getAttribute('data-description') || '';
                    
                    images.push({
                        src: img.src,
                        title: title,
                        description: description,
                        uploader: uploader + (minecraftName ? ` (${minecraftName})` : ''),
                        date: date,
                        index: index
                    });
                });
                
                totalImagesSpan.textContent = images.length;
            }
            
            // Open modal with image
            function openModal(index) {
                if (images.length === 0) return;
                
                currentImageIndex = index;
                const image = images[currentImageIndex];
                
                // Show modal
                imageModal.style.display = 'block';
                document.body.style.overflow = 'hidden';
                
                // Set image info
                modalTitle.textContent = image.title;
                modalUploader.textContent = image.uploader;
                modalDate.textContent = image.date;
                
                // Show/hide description
                if (image.description) {
                    modalDescription.style.display = 'block';
                    modalDescription.querySelector('p').textContent = image.description;
                } else {
                    modalDescription.style.display = 'none';
                }
                
                // Show loader and hide image
                imageLoader.classList.add('active');
                modalImage.style.opacity = '0';
                
                // Load image with size check
                const img = new Image();
                img.onload = function() {
                    // Check if image is very large
                    if (this.naturalWidth > 2000 || this.naturalHeight > 2000) {
                        // For very large images, apply additional constraints
                        modalImage.style.maxWidth = '90%';
                        modalImage.style.maxHeight = '55vh';
                    } else {
                        // Reset to default
                        modalImage.style.maxWidth = '';
                        modalImage.style.maxHeight = '';
                    }
                    
                    modalImage.src = image.src;
                    modalImage.alt = image.title;
                    modalImage.style.opacity = '1';
                    imageLoader.classList.remove('active');
                    
                    // Update counter
                    currentImageSpan.textContent = currentImageIndex + 1;
                };
                
                img.onerror = function() {
                    imageLoader.classList.remove('active');
                    modalImage.style.opacity = '1';
                    modalImage.src = 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300" viewBox="0 0 400 300"><rect width="400" height="300" fill="%23333"/><text x="200" y="150" font-family="Arial" font-size="16" fill="%23fff" text-anchor="middle">Image failed to load</text></svg>';
                };
                
                img.src = image.src;
                
                // Update navigation buttons
                updateNavigation();
            }
            
            // Close modal
            function closeModal() {
                imageModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
            
            // Update navigation buttons state
            function updateNavigation() {
                prevBtn.style.display = currentImageIndex > 0 ? 'flex' : 'none';
                nextBtn.style.display = currentImageIndex < images.length - 1 ? 'flex' : 'none';
            }
            
            // Navigate to previous image
            function prevImage() {
                if (currentImageIndex > 0) {
                    currentImageIndex--;
                    openModal(currentImageIndex);
                }
            }
            
            // Navigate to next image
            function nextImage() {
                if (currentImageIndex < images.length - 1) {
                    currentImageIndex++;
                    openModal(currentImageIndex);
                }
            }
            
            // Keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (imageModal.style.display === 'block') {
                    if (e.key === 'Escape') {
                        closeModal();
                    } else if (e.key === 'ArrowLeft') {
                        prevImage();
                    } else if (e.key === 'ArrowRight') {
                        nextImage();
                    }
                }
            });
            
            // Touch events for swipe
            modalOverlay.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
            });
            
            modalOverlay.addEventListener('touchend', function(e) {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe();
            });
            
            function handleSwipe() {
                const diff = touchStartX - touchEndX;
                
                if (Math.abs(diff) > swipeThreshold) {
                    if (diff > 0) {
                        // Swipe left
                        nextImage();
                    } else {
                        // Swipe right
                        prevImage();
                    }
                }
            }
            
            // Download image
            downloadBtn.addEventListener('click', function() {
                const image = images[currentImageIndex];
                const link = document.createElement('a');
                link.href = image.src;
                link.download = `warlord-realm-${image.title.replace(/\s+/g, '-').toLowerCase()}.jpg`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
            
            // Event listeners
            modalOverlay.addEventListener('click', closeModal);
            modalClose.addEventListener('click', closeModal);
            prevBtn.addEventListener('click', prevImage);
            nextBtn.addEventListener('click', nextImage);
            
            // Initialize
            collectImages();
            
            // Add click events to gallery images
            document.querySelectorAll('.gallery-card').forEach((card, index) => {
                card.addEventListener('click', function() {
                    openModal(index);
                });
            });
            
            // Prevent modal close when clicking inside modal container
            document.querySelector('.modal-container').addEventListener('click', function(e) {
                e.stopPropagation();
            });
            
            // Auto-hide alerts
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    alert.style.animation = 'slideOutRight 0.5s ease forwards';
                    setTimeout(() => {
                        alert.remove();
                    }, 500);
                });
            }, 4500);
            
            // Back link interaction
            const backLink = document.querySelector('.back-link');
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
        });
    </script>
</body>
</html>