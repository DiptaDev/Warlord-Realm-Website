<?php
require_once 'config.php';

// Cek apakah user sudah login
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Fungsi untuk kompresi gambar yang lebih baik
function compressAndResizeImage($source, $destination, $max_width = 1920, $quality = 75) {
    // Cek apakah file sumber ada
    if (!file_exists($source)) {
        return false;
    }
    
    $info = getimagesize($source);
    
    if (!$info) {
        return false;
    }
    
    $mime = $info['mime'];
    
    // Create image from source based on mime type
    switch ($mime) {
        case 'image/jpeg':
            $image = @imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = @imagecreatefrompng($source);
            break;
        case 'image/gif':
            $image = @imagecreatefromgif($source);
            break;
        case 'image/webp':
            $image = @imagecreatefromwebp($source);
            break;
        default:
            return false;
    }
    
    if (!$image) {
        return false;
    }
    
    // Get original dimensions
    $original_width = imagesx($image);
    $original_height = imagesy($image);
    
    // Calculate new dimensions
    if ($original_width > $max_width) {
        $new_width = $max_width;
        $new_height = intval($original_height * ($max_width / $original_width));
        
        // Create new image with new dimensions
        $new_image = imagecreatetruecolor($new_width, $new_height);
        
        // Preserve transparency for PNG and GIF
        if ($mime == 'image/png' || $mime == 'image/gif') {
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
            $transparent = imagecolorallocatealpha($new_image, 0, 0, 0, 127);
            imagefill($new_image, 0, 0, $transparent);
        }
        
        // Resize image
        imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);
        imagedestroy($image);
        $image = $new_image;
    }
    
    // Save image with compression
    $success = false;
    switch ($mime) {
        case 'image/jpeg':
            $success = imagejpeg($image, $destination, $quality);
            break;
        case 'image/png':
            // PNG compression level (0-9, 9 is highest compression)
            $png_quality = 9 - round(($quality / 100) * 9);
            $success = imagepng($image, $destination, $png_quality);
            break;
        case 'image/gif':
            $success = imagegif($image, $destination);
            break;
        case 'image/webp':
            $success = imagewebp($image, $destination, $quality);
            break;
    }
    
    imagedestroy($image);
    return $success;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $user_id = $_SESSION['user_id'];
    
    // Validasi
    if (empty($title)) {
        $error = 'Title is required!';
    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] != 0) {
        $error = 'Please select an image to upload!';
    } else {
        // Validasi file
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        $file_type = $_FILES['image']['type'];
        $file_size = $_FILES['image']['size'];
        $original_name = $_FILES['image']['name'];
        
        if (!in_array($file_type, $allowed_types)) {
            $error = 'Only JPG, PNG, GIF, and WebP images are allowed!';
        } elseif ($file_size > $max_size) {
            $error = 'Image size must be less than 5MB!';
        } else {
            // Generate unique filename
            $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
            $filename = 'warlord_' . time() . '_' . uniqid() . '.' . $ext;
            $upload_path = 'uploads/' . $filename;
            
            // Temporary file path
            $temp_file = $_FILES['image']['tmp_name'];
            
            // Simpan ukuran asli untuk ditampilkan di pesan sukses
            $original_size = $file_size;
            
            // Kompresi gambar
            $compression_success = compressAndResizeImage($temp_file, $upload_path, 1920, 75);
            
            if (!$compression_success) {
                // Jika kompresi gagal, coba upload langsung
                if (!move_uploaded_file($temp_file, $upload_path)) {
                    $error = 'Failed to process image. Please try again.';
                }
            }
            
            // Jika upload berhasil
            if (empty($error)) {
                // Cek ukuran file setelah kompresi
                $final_size = file_exists($upload_path) ? filesize($upload_path) : 0;
                
                // Simpan ke database TANPA kolom original_size dan compressed_size
                $query = "INSERT INTO images (user_id, title, description, filename, status) 
                         VALUES ('$user_id', '$title', '$description', '$filename', 'pending')";
                
                if (mysqli_query($conn, $query)) {
                    // Hitung pengurangan ukuran (hanya untuk ditampilkan, tidak disimpan di database)
                    $size_reduction = $original_size > 0 ? round((($original_size - $final_size) / $original_size) * 100, 1) : 0;
                    
                    $success = 'Screenshot uploaded successfully! It will be visible after admin approval.';
                    if ($size_reduction > 0) {
                        $success .= " (Image optimized for web viewing)";
                    }
                } else {
                    $error = 'Failed to save image information. Please try again.';
                    // Hapus file yang sudah diupload
                    if (file_exists($upload_path)) {
                        unlink($upload_path);
                    }
                }
            }
        }
    }
}

// Fungsi untuk format bytes (hanya untuk ditampilkan)
function formatBytes($bytes, $precision = 2) {
    if ($bytes <= 0) return '0 Bytes';
    
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warlord Realm | Gallery Upload</title>
    <link rel="shortcut icon" href="../asset/logo-min.png" type="image/x-icon">
    <link rel="stylesheet" href="../asset/style-gallery_upload.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Carbon Fiber Header -->
    <header class="carbon-header">
        <div class="header-content">
            <a href="index.php" class="logo-section">
                <img src="../asset/logo-min.png" alt="Warlord Realm Logo" class="logo-img">
                <div class="logo-text">
                    <h1>WARLORD REALM</h1>
                    <p>Minecraft Gallery</p>
                </div>
            </a>

            <div class="user-actions">
                <?php if(isLoggedIn()): ?>
                    <div style="color: var(--medium-gray); font-size: 0.9rem; padding: 8px 16px; background: rgba(33, 33, 33, 0.6); border-radius: 8px; border: 1px solid var(--border-color);">
                        Welcome, <strong style="color: var(--accent-red);"><?php echo $_SESSION['username']; ?></strong>
                    </div>
                    <a href="myuploads.php" class="btn btn-secondary">
                        <i class="fas fa-user-circle"></i> My Uploads
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
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Gallery
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="upload-header">
            <h1 class="upload-title">Upload Screenshot</h1>
            <p class="upload-subtitle">Share your epic Warlord Realm adventures with the community</p>
            <!-- <div class="compression-info">
                <i class="fas fa-compress-alt"></i>
                <span>All images are automatically optimized for web viewing</span>
            </div> -->
        </div>

        <?php if($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <div class="alert-content">
                    <div class="alert-message"><?php echo $error; ?></div>
                </div>
            </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <div class="alert-content">
                    <div class="alert-message"><?php echo $success; ?></div>
                    <div style="margin-top: 15px;">
                        <a href="index.php" class="btn btn-primary" style="padding: 10px 25px;">
                            <i class="fas fa-images"></i> View Gallery
                        </a>
                        <a href="upload.php" class="btn btn-secondary" style="padding: 10px 25px; margin-left: 10px;">
                            <i class="fas fa-plus"></i> Upload Another
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if(!$success): ?>
            <div class="upload-container">
                <form method="POST" action="" enctype="multipart/form-data" id="uploadForm">
                    <div class="form-group">
                        <label for="title" class="form-label">Screenshot Title *</label>
                        <input type="text" id="title" name="title" class="form-input" 
                               placeholder="Enter a descriptive title for your screenshot..." 
                               value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Description (Optional)</label>
                        <textarea id="description" name="description" class="form-input" 
                                  placeholder="Tell the story behind this screenshot... What makes it special?"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Upload Image *</label>
                        <div class="file-upload-area" onclick="document.getElementById('image').click()" id="uploadArea">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Click or drag to upload your screenshot</p>
                            <p><small>Maximum file size: 5MB | Supported formats: JPG, PNG, GIF, WebP</small></p>
                            <p><small> All images are automatically optimized</small></p>
                            <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(this)" required style="display: none;">
                        </div>
                        <div id="fileInfo" class="file-info">
                            <i class="fas fa-info-circle"></i>
                            <span id="fileDetails"></span>
                        </div>
                        <div id="image-preview">
                            <img id="preview" src="" alt="Preview">
                            <div style="margin-top: 15px;">
                                <button type="button" onclick="document.getElementById('image').click()" class="btn btn-secondary" style="padding: 8px 20px;">
                                    <i class="fas fa-sync-alt"></i> Change Image
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="upload-progress" id="uploadProgress">
                        <div class="progress-bar">
                            <div class="progress-fill" id="progressFill"></div>
                        </div>
                        <div class="progress-text" id="progressText">Processing image...</div>
                    </div>

                    <button type="submit" class="upload-btn" id="uploadButton">
                        <i class="fas fa-cloud-upload-alt"></i> Upload Screenshot
                    </button>
                </form>

                <div class="upload-guidelines">
                    <h3><i class="fas fa-info-circle"></i> Upload Guidelines</h3>
                    <ul>
                        <li>Only upload screenshots from Warlord Realm Minecraft server</li>
                        <li>All content must be appropriate and respectful</li>
                        <li>Screenshots require admin approval before appearing in gallery</li>
                        <li>High-quality screenshots are encouraged</li>
                        <li>You can upload multiple screenshots over time</li>
                        <li>All images are automatically optimized for web viewing</li>
                        <li>Include descriptive titles and stories for better engagement</li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="../asset/java_script-gallery_upload_pange.js"></script>
</body>
</html>