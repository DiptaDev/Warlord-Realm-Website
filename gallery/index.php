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
    <link rel="stylesheet" href="../asset/style-gallery_pange.css">
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
</head>
<body>
    <!-- Carbon Fiber Header -->
    <header class="carbon-header">
        <div class="container header-content">
            <a href="../" class="logo-section">
                 <img src="../asset/logo.jpg" alt="Warlord Realm Logo" class="logo-img" onerror="this.style.display='none'; this.parentElement.querySelector('.logo-icon').style.display='flex';">
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
                    <a href="myuploads.php" class="btn btn-secondary">
                        <i class="fas fa-user-circle"></i> My Uploads
                    </a>
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
<script src="../asset/java_script-gallery_pange.js"></script>
</body>
</html>