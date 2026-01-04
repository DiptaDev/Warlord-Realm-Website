<?php
require_once 'config.php';

// Cek apakah user sudah login
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Ambil gambar yang diupload oleh user yang login
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM images WHERE user_id = $user_id ORDER BY uploaded_at DESC";
$result = mysqli_query($conn, $query);
$my_images = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Hitung statistik
$total_uploads = count($my_images);
$pending_count = count(array_filter($my_images, function($img) { return $img['status'] == 'pending'; }));
$approved_count = count(array_filter($my_images, function($img) { return $img['status'] == 'approved'; }));
$rejected_count = count(array_filter($my_images, function($img) { return $img['status'] == 'rejected'; }));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warlord Realm | My Uploads</title>
    <link rel="shortcut icon" href="../asset/logo-min.png" type="image/x-icon">
    <link rel="stylesheet" href="../asset/style-gallery_myuploads_pange.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body oncontextmenu="return false" ondragstart="return false;" ondrop="return false;">
    <!-- Carbon Fiber Header -->
    <header class="carbon-header">
        <div class="header-content">
            <a href="index.php" class="logo-section">
                <img src="../asset/logo.jpg" alt="Warlord Realm Logo" class="logo-img">
                <div class="logo-text">
                    <h1>WARLORD REALM</h1>
                    <p>My Uploads</p>
                </div>
            </a>

            <div class="user-actions">
                <?php if(isLoggedIn()): ?>
                    <div style="color: var(--medium-gray); font-size: 0.9rem; padding: 8px 16px; background: rgba(33, 33, 33, 0.6); border-radius: 8px; border: 1px solid var(--border-color);">
                        Welcome, <strong style="color: var(--accent-red);"><?php echo $_SESSION['username']; ?></strong>
                    </div>
                    <a href="upload.php" class="btn btn-primary">
                        <i class="fas fa-cloud-upload-alt"></i> Upload New
                    </a>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-images"></i> Gallery
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
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div class="container">
        <?php if(isset($_GET['message'])): ?>
            <div class="alert">
                <i class="fas fa-check-circle"></i>
                <span class="alert-message"><?php echo htmlspecialchars($_GET['message']); ?></span>
            </div>
        <?php endif; ?>

        <div class="myuploads-header">
            <h1 class="myuploads-title">My Screenshots</h1>
            <p class="myuploads-subtitle">Track the status of all your uploaded Warlord Realm adventures</p>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-images"></i>
                </div>
                <div class="stat-number"><?php echo $total_uploads; ?></div>
                <div class="stat-label">Total Uploads</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-number"><?php echo $pending_count; ?></div>
                <div class="stat-label">Pending Review</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon approved">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number"><?php echo $approved_count; ?></div>
                <div class="stat-label">Approved</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon rejected">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-number"><?php echo $rejected_count; ?></div>
                <div class="stat-label">Rejected</div>
            </div>
        </div>

        <!-- Uploads Gallery -->
        <div class="gallery-grid" id="galleryGrid">
            <?php if(count($my_images) > 0): ?>
                <?php foreach($my_images as $index => $image): ?>
                    <div class="upload-card" data-index="<?php echo $index; ?>" data-description="<?php echo htmlspecialchars($image['description']); ?>">
                        <img src="uploads/<?php echo $image['filename']; ?>" 
                             alt="<?php echo htmlspecialchars($image['title']); ?>" 
                             class="upload-image">
                        <div class="upload-content">
                            <h3 class="upload-title"><?php echo htmlspecialchars($image['title']); ?></h3>
                            <?php if(!empty($image['description'])): ?>
                                <p class="upload-description"><?php echo htmlspecialchars($image['description']); ?></p>
                            <?php endif; ?>
                            <div class="upload-meta">
                                <div class="upload-info">
                                    <div class="upload-date">
                                        <i class="fas fa-calendar"></i> 
                                        <?php echo date('M j, Y', strtotime($image['uploaded_at'])); ?>
                                        <?php if($image['approved_at']): ?>
                                            <br><i class="fas fa-check-circle"></i> 
                                            Approved: <?php echo date('M j, Y', strtotime($image['approved_at'])); ?>
                                        <?php elseif($image['rejected_at']): ?>
                                            <br><i class="fas fa-times-circle"></i> 
                                            Rejected: <?php echo date('M j, Y', strtotime($image['rejected_at'])); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="upload-status">
                                        <?php echo getStatusBadge($image['status']); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-uploads">
                    <div class="empty-icon">
                        <i class="fas fa-images"></i>
                    </div>
                    <h3 class="empty-title">No Uploads Yet</h3>
                    <p class="empty-description">
                        You haven't uploaded any screenshots yet. Share your first Warlord Realm adventure!
                    </p>
                    <a href="upload.php" class="btn btn-primary" style="padding: 12px 30px; font-size: 1rem;">
                        <i class="fas fa-cloud-upload-alt"></i> Upload Your First Screenshot
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

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
                        <span id="modalUploader"><?php echo $_SESSION['username']; ?></span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-calendar"></i>
                        <span id="modalDate"></span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-info-circle"></i>
                        <span id="modalStatus"></span>
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

    
</body>
<script src="../asset/java_script-gallery_myuploads.js"></script>
</html>