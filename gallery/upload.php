<?php
require_once 'config.php';

// Cek apakah user sudah login
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
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
        
        if (!in_array($file_type, $allowed_types)) {
            $error = 'Only JPG, PNG, GIF, and WebP images are allowed!';
        } elseif ($file_size > $max_size) {
            $error = 'Image size must be less than 5MB!';
        } else {
            // Generate unique filename
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = 'warlord_' . time() . '_' . uniqid() . '.' . $ext;
            $upload_path = 'uploads/' . $filename;
            
            // Upload file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Simpan ke database
                $query = "INSERT INTO images (user_id, title, description, filename, status) 
                         VALUES ('$user_id', '$title', '$description', '$filename', 'pending')";
                
                if (mysqli_query($conn, $query)) {
                    $success = 'Screenshot uploaded successfully! It will be visible after admin approval.';
                } else {
                    $error = 'Failed to save image information. Please try again.';
                    // Hapus file yang sudah diupload
                    unlink($upload_path);
                }
            } else {
                $error = 'Failed to upload image. Please try again.';
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
    <title>Upload - Warlord Realm Gallery</title>
    <link rel="shortcut icon" href="../asset/logo-min.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-black: #000000;
            --secondary-black: #0a0a0a;
            --accent-red: #ff0033;
            --accent-light-red: #ff3366;
            --pure-white: #ffffff;
            --light-gray: #e0e0e0;
            --medium-gray: #888888;
            --border-gray: #333333;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
        }

        body {
            background-color: var(--primary-black);
            color: var(--pure-white);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--medium-gray);
            text-decoration: none;
            margin-bottom: 30px;
            transition: color 0.2s ease;
        }

        .back-link:hover {
            color: var(--accent-red);
        }

        .upload-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .upload-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .logo-img {
            width: 50px;
            height: 50px;
            border-radius: 9px;
            object-fit: cover;
            /* border: 2px solid var(--accent-red); */
        }

        .logo-text h1 {
            font-size: 1.4rem;
            color: var(--pure-white);
            margin-bottom: 5px;
        }

        .logo-text span {
            font-size: 0.9rem;
            color: var(--medium-gray);
        }

        .upload-title {
            font-size: 2rem;
            font-weight: 300;
            color: var(--pure-white);
            margin-bottom: 10px;
        }

        .upload-subtitle {
            color: var(--medium-gray);
            font-size: 1rem;
        }

        .upload-container {
            background: var(--secondary-black);
            border-radius: 12px;
            padding: 40px;
            border: 1px solid var(--border-gray);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            color: var(--light-gray);
            font-size: 0.95rem;
            font-weight: 500;
        }

        .form-input {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-gray);
            border-radius: 8px;
            color: var(--pure-white);
            font-size: 1rem;
            transition: all 0.2s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent-red);
            background: rgba(255, 0, 51, 0.05);
        }

        textarea.form-input {
            min-height: 120px;
            resize: vertical;
        }

        .file-upload-area {
            border: 2px dashed var(--border-gray);
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            background: rgba(255, 255, 255, 0.02);
        }

        .file-upload-area:hover {
            border-color: var(--accent-red);
            background: rgba(255, 0, 51, 0.05);
        }

        .file-upload-area i {
            font-size: 2.5rem;
            color: var(--accent-red);
            margin-bottom: 15px;
            opacity: 0.7;
        }

        .file-upload-area:hover i {
            opacity: 1;
        }

        .file-upload-area p {
            color: var(--medium-gray);
            margin-bottom: 10px;
        }

        .file-upload-area small {
            color: var(--medium-gray);
            font-size: 0.85rem;
        }

        #image-preview {
            margin-top: 20px;
            display: none;
        }

        #preview {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            border: 1px solid var(--border-gray);
        }

        .btn {
            padding: 12px 30px;
            background: #c91414;
            color: var(--pure-white);
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn:hover {
            background: #8f0606;
            transform: translateY(-1px);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-error {
            background: rgba(255, 0, 51, 0.1);
            border: 1px solid rgba(255, 0, 51, 0.3);
            color: #ff6b6b;
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.1);
            border: 1px solid rgba(76, 175, 80, 0.3);
            color: #81c784;
        }

        .upload-guidelines {
            background: rgba(255, 0, 51, 0.05);
            border: 1px solid rgba(255, 0, 51, 0.2);
            border-radius: 8px;
            padding: 25px;
            margin-top: 40px;
        }

        .upload-guidelines h3 {
            font-size: 1.1rem;
            color: var(--pure-white);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .upload-guidelines ul {
            list-style: none;
            color: var(--medium-gray);
        }

        .upload-guidelines li {
            margin-bottom: 10px;
            padding-left: 25px;
            position: relative;
        }

        .upload-guidelines li:before {
            content: '✓';
            color: var(--accent-red);
            position: absolute;
            left: 0;
        }

        @media (max-width: 768px) {
            .upload-container {
                padding: 30px 20px;
            }
            
            .upload-title {
                font-size: 1.7rem;
            }
            
            .file-upload-area {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Gallery</span>
        </a>

        <div class="upload-header">
            <div class="upload-logo">
                <img src="../asset/logo-min.png" alt="Warlord Realm Logo" class="logo-img" onerror="this.style.display='none'; this.parentElement.querySelector('.logo-icon').style.display='flex';">
                <div class="logo-text">
                    <h1>Warlord Realm</h1>
                    <span>Upload Screenshot</span>
                </div>
            </div>
            
            <h2 class="upload-title">Share Your Adventure</h2>
            <p class="upload-subtitle">Upload Minecraft screenshots from Warlord Realm server</p>
        </div>

        <?php if($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?php echo $success; ?></span>
                <div style="margin-top: 15px;">
                    <a href="index.php" class="btn" style="padding: 8px 20px; font-size: 0.9rem;">
                        <i class="fas fa-images"></i> Back to Gallery
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <?php if(!$success): ?>
            <div class="upload-container">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title" class="form-label">Screenshot Title *</label>
                        <input type="text" id="title" name="title" class="form-input" 
                               placeholder="e.g., Epic Battle, Our Castle Build, Beautiful Landscape" required>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Description (Optional)</label>
                        <textarea id="description" name="description" class="form-input" 
                                  placeholder="Tell us about this moment..."></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Upload Image *</label>
                        <div class="file-upload-area" onclick="document.getElementById('image').click()">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Click to select your Minecraft screenshot</p>
                            <p><small>Max file size: 5MB | Allowed: JPG, PNG, GIF, WebP</small></p>
                            <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(this)" required style="display: none;">
                        </div>
                        <div id="image-preview">
                            <img id="preview" src="" alt="Preview">
                        </div>
                    </div>

                    <button type="submit" class="btn" style="width: 100%; padding: 15px;">
                        <i class="fas fa-cloud-upload-alt"></i> Upload Screenshot
                    </button>
                </form>

                <div class="upload-guidelines">
                    <h3><i class="fas fa-info-circle"></i> Upload Guidelines</h3>
                    <ul>
                        <li>Only upload screenshots from Warlord Realm server</li>
                        <li>Images must be appropriate for all audiences</li>
                        <li>Screenshots will be reviewed by admin before appearing in gallery</li>
                        <li>You can upload multiple screenshots</li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('preview');
            const previewContainer = document.getElementById('image-preview');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewContainer.style.display = 'block';
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>