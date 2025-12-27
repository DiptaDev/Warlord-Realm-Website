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
    <title>Upload - Warlord Realm Gallery</title>
    <link rel="shortcut icon" href="../asset/logo-min.png" type="image/x-icon">
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
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        .carbon-header {
            background: linear-gradient(135deg, rgba(10, 10, 10, 0.95) 0%, rgba(17, 17, 17, 0.95) 100%);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
            padding: 20px 0;
            margin-bottom: 40px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5);
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
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

        /* Back Link */
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
            margin-bottom: 30px;
        }

        .back-link:hover {
            color: var(--accent-red);
            border-color: var(--accent-red);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        /* Upload Header */
        .upload-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .upload-title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 15px;
            background: linear-gradient(to right, var(--accent-red), var(--accent-light-red));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .upload-subtitle {
            color: var(--medium-gray);
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .compression-info {
            background: linear-gradient(135deg, rgba(33, 150, 243, 0.1), rgba(33, 150, 243, 0.05));
            border: 1px solid rgba(33, 150, 243, 0.2);
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .compression-info i {
            color: #2196F3;
            margin-right: 10px;
        }

        /* Upload Container */
        .upload-container {
            background: linear-gradient(145deg, rgba(33, 33, 33, 0.9), rgba(17, 17, 17, 0.95));
            border-radius: 20px;
            padding: 40px;
            border: 1px solid var(--border-color);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.5);
            margin-bottom: 40px;
        }

        .form-group {
            margin-bottom: 30px;
        }

        .form-label {
            display: block;
            margin-bottom: 12px;
            color: var(--light-gray);
            font-size: 1rem;
            font-weight: 600;
        }

        .form-input {
            width: 100%;
            padding: 15px 20px;
            background: rgba(33, 33, 33, 0.8);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            color: var(--pure-white);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent-red);
            box-shadow: 0 0 0 3px var(--glow-red);
            background: rgba(33, 33, 33, 0.9);
        }

        textarea.form-input {
            min-height: 150px;
            resize: vertical;
            line-height: 1.5;
        }

        /* File Upload Area */
        .file-upload-area {
            border: 3px dashed var(--border-color);
            border-radius: 15px;
            padding: 60px 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: rgba(33, 33, 33, 0.5);
            position: relative;
            overflow: hidden;
        }

        .file-upload-area:hover {
            border-color: var(--accent-red);
            background: rgba(211, 47, 47, 0.1);
            transform: translateY(-3px);
            box-shadow: 0 10px 30px var(--glow-red);
        }

        .file-upload-area i {
            font-size: 4rem;
            color: var(--accent-red);
            margin-bottom: 20px;
            opacity: 0.8;
        }

        .file-upload-area:hover i {
            opacity: 1;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .file-upload-area p {
            color: var(--pure-white);
            font-size: 1.1rem;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .file-upload-area small {
            color: var(--medium-gray);
            font-size: 0.9rem;
        }

        /* File Info */
        .file-info {
            display: none;
            margin-top: 15px;
            padding: 15px;
            background: rgba(76, 175, 80, 0.1);
            border: 1px solid rgba(76, 175, 80, 0.3);
            border-radius: 10px;
            color: #4CAF50;
            font-size: 0.9rem;
        }

        .file-info.warning {
            background: rgba(255, 152, 0, 0.1);
            border-color: rgba(255, 152, 0, 0.3);
            color: #FF9800;
        }

        .file-info i {
            margin-right: 8px;
        }

        /* Image Preview */
        #image-preview {
            margin-top: 25px;
            display: none;
            text-align: center;
        }

        #preview {
            max-width: 100%;
            max-height: 300px;
            border-radius: 12px;
            border: 2px solid var(--border-color);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
            transition: all 0.3s ease;
        }

        #preview:hover {
            border-color: var(--accent-red);
            transform: scale(1.02);
        }

        /* Upload Button */
        .upload-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, var(--accent-red), var(--accent-dark-red));
            color: var(--pure-white);
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-top: 20px;
            box-shadow: 0 6px 20px var(--glow-red);
        }

        .upload-btn:hover {
            background: linear-gradient(135deg, var(--accent-light-red), var(--accent-red));
            transform: translateY(-3px);
            box-shadow: 0 10px 30px var(--glow-dark-red);
        }

        .upload-btn:active {
            transform: translateY(-1px);
        }

        .upload-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        /* Upload Progress */
        .upload-progress {
            display: none;
            margin-top: 20px;
            padding: 20px;
            background: linear-gradient(135deg, rgba(33, 33, 33, 0.9), rgba(17, 17, 17, 0.95));
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }

        .progress-bar {
            width: 100%;
            height: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(to right, var(--accent-red), var(--accent-light-red));
            width: 0%;
            transition: width 0.3s ease;
            border-radius: 5px;
        }

        .progress-text {
            color: var(--medium-gray);
            font-size: 0.9rem;
            text-align: center;
        }

        /* Alert Messages */
        .alert {
            padding: 20px 25px;
            background: linear-gradient(135deg, rgba(33, 33, 33, 0.95), rgba(17, 17, 17, 0.95));
            border-radius: 12px;
            margin-bottom: 30px;
            border-left: 5px solid var(--accent-red);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5), 0 0 20px var(--glow-red);
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 15px;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .alert i {
            font-size: 1.5rem;
        }

        .alert-error {
            border-left-color: #f44336;
            background: linear-gradient(135deg, rgba(244, 67, 54, 0.1), rgba(183, 28, 28, 0.1));
        }

        .alert-error i {
            color: #f44336;
        }

        .alert-success {
            border-left-color: #4caf50;
            background: linear-gradient(135deg, rgba(76, 175, 80, 0.1), rgba(27, 94, 32, 0.1));
        }

        .alert-success i {
            color: #4caf50;
        }

        .alert-content {
            flex: 1;
        }

        .alert-message {
            color: var(--light-gray);
            font-size: 1rem;
            font-weight: 500;
            margin-bottom: 10px;
        }

        /* Guidelines */
        .upload-guidelines {
            background: linear-gradient(145deg, rgba(33, 33, 33, 0.8), rgba(17, 17, 17, 0.9));
            border-radius: 15px;
            padding: 30px;
            margin-top: 40px;
            border: 1px solid var(--border-color);
            border-left: 5px solid var(--accent-red);
        }

        .upload-guidelines h3 {
            font-size: 1.2rem;
            color: var(--pure-white);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .upload-guidelines h3 i {
            color: var(--accent-red);
        }

        .upload-guidelines ul {
            list-style: none;
        }

        .upload-guidelines li {
            margin-bottom: 12px;
            padding-left: 28px;
            position: relative;
            color: var(--medium-gray);
            line-height: 1.5;
        }

        .upload-guidelines li:before {
            content: '▶';
            color: var(--accent-red);
            position: absolute;
            left: 0;
            font-size: 0.8rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 20px;
                padding: 0 15px;
            }

            .upload-title {
                font-size: 2.2rem;
            }

            .upload-subtitle {
                font-size: 1rem;
            }

            .upload-container {
                padding: 30px 20px;
            }

            .file-upload-area {
                padding: 40px 20px;
            }

            .file-upload-area i {
                font-size: 3rem;
            }

            .upload-btn {
                padding: 15px;
                font-size: 1rem;
            }

            .user-actions {
                flex-wrap: wrap;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 15px;
            }

            .upload-title {
                font-size: 1.8rem;
            }

            .form-input {
                padding: 12px 15px;
            }

            .file-upload-area p {
                font-size: 1rem;
            }
        }
    </style>
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
            <div class="compression-info">
                <i class="fas fa-compress-alt"></i>
                <span>All images are automatically optimized for web viewing</span>
            </div>
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
                            <p><small><i class="fas fa-compress-alt"></i> All images are automatically optimized</small></p>
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
                        <li>Large images are resized to 1920px maximum width</li>
                        <li>Include descriptive titles and stories for better engagement</li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function previewImage(input) {
            const preview = document.getElementById('preview');
            const previewContainer = document.getElementById('image-preview');
            const uploadArea = document.getElementById('uploadArea');
            const fileInfo = document.getElementById('fileInfo');
            const fileDetails = document.getElementById('fileDetails');
            const uploadButton = document.getElementById('uploadButton');
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewContainer.style.display = 'block';
                    uploadArea.style.display = 'none';
                    
                    // Show file info
                    const fileSize = file.size;
                    const maxSize = 5 * 1024 * 1024; // 5MB
                    
                    fileDetails.textContent = `Selected: ${file.name} (${formatBytes(fileSize)})`;
                    fileInfo.className = 'file-info';
                    fileInfo.style.display = 'block';
                    
                    // Check if image needs compression
                    if (fileSize > 1024 * 1024) { // 1MB
                        const originalSize = formatBytes(fileSize);
                        fileDetails.innerHTML += `<br><i class="fas fa-compress-alt" style="color: #2196F3;"></i> Will be optimized for web`;
                    }
                    
                    if (fileSize > maxSize) {
                        fileInfo.innerHTML = `<i class="fas fa-exclamation-triangle"></i> 
                            File size (${formatBytes(fileSize)}) exceeds 5MB limit!`;
                        fileInfo.className = 'file-info warning';
                        uploadButton.disabled = true;
                        uploadButton.style.opacity = '0.7';
                    } else {
                        uploadButton.disabled = false;
                        uploadButton.style.opacity = '1';
                    }
                };
                
                reader.readAsDataURL(file);
            }
        }

        // Drag and drop functionality
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('image');

        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = 'var(--accent-red)';
            this.style.backgroundColor = 'rgba(211, 47, 47, 0.2)';
        });

        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = 'var(--border-color)';
            this.style.backgroundColor = 'rgba(33, 33, 33, 0.5)';
        });

        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = 'var(--border-color)';
            this.style.backgroundColor = 'rgba(33, 33, 33, 0.5)';
            
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                previewImage(fileInput);
            }
        });

        // Show progress bar on form submit
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('image');
            const maxSize = 5 * 1024 * 1024; // 5MB
            
            if (fileInput.files.length > 0) {
                const fileSize = fileInput.files[0].size;
                
                if (fileSize > maxSize) {
                    e.preventDefault();
                    alert('File size exceeds 5MB limit. Please choose a smaller image.');
                    return false;
                }
                
                // Show progress bar
                const uploadProgress = document.getElementById('uploadProgress');
                const progressFill = document.getElementById('progressFill');
                const progressText = document.getElementById('progressText');
                const uploadButton = document.getElementById('uploadButton');
                
                uploadProgress.style.display = 'block';
                uploadButton.disabled = true;
                uploadButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                
                // Simulate progress animation
                let progress = 0;
                const interval = setInterval(() => {
                    progress += 5;
                    progressFill.style.width = progress + '%';
                    
                    if (progress < 30) {
                        progressText.textContent = 'Validating image...';
                    } else if (progress < 60) {
                        progressText.textContent = 'Optimizing image...';
                    } else if (progress < 90) {
                        progressText.textContent = 'Uploading to server...';
                    } else {
                        progressText.textContent = 'Finalizing upload...';
                    }
                    
                    if (progress >= 95) {
                        clearInterval(interval);
                    }
                }, 100);
            }
        });

        // File size validation
        fileInput.addEventListener('change', function() {
            const uploadButton = document.getElementById('uploadButton');
            const maxSize = 5 * 1024 * 1024; // 5MB
            
            if (this.files.length > 0) {
                const fileSize = this.files[0].size;
                
                if (fileSize > maxSize) {
                    uploadButton.disabled = true;
                    uploadButton.style.opacity = '0.7';
                } else {
                    uploadButton.disabled = false;
                    uploadButton.style.opacity = '1';
                }
            }
        });
    </script>
</body>
</html>