<?php
require_once 'config.php';

// Cek apakah user adalah admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: index.php');
    exit();
}

// Handle approve/reject actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $image_id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if (in_array($action, ['approve', 'reject'])) {
        $status = $action == 'approve' ? 'approved' : 'rejected';
        $date_field = $action == 'approve' ? 'approved_at' : 'rejected_at';
        
        $query = "UPDATE images SET status = '$status', $date_field = NOW() WHERE id = $image_id";
        mysqli_query($conn, $query);
        
        header('Location: admin.php?message=Image ' . $action . 'd successfully!');
        exit();
    }
}

// Handle delete action
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $image_id = (int)$_GET['id'];
    
    // Get filename first
    $query = "SELECT filename FROM images WHERE id = $image_id";
    $result = mysqli_query($conn, $query);
    $image = mysqli_fetch_assoc($result);
    
    // Delete from database
    $delete_query = "DELETE FROM images WHERE id = $image_id";
    if (mysqli_query($conn, $delete_query)) {
        // Delete file
        if (file_exists('uploads/' . $image['filename'])) {
            unlink('uploads/' . $image['filename']);
        }
        header('Location: admin.php?message=Image deleted successfully!');
        exit();
    }
}

// Handle edit action
if (isset($_POST['edit_image'])) {
    $image_id = (int)$_POST['image_id'];
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    
    $query = "UPDATE images SET title = '$title', description = '$description' WHERE id = $image_id";
    if (mysqli_query($conn, $query)) {
        header('Location: admin.php?message=Image updated successfully!');
        exit();
    }
}

// Get all images with user info
$query = "SELECT i.*, u.username, u.minecraft_username 
          FROM images i 
          JOIN users u ON i.user_id = u.id 
          ORDER BY i.uploaded_at DESC";
$result = mysqli_query($conn, $query);
$images = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Warlord Realm Gallery</title>
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
            --success-green: #4CAF50;
            --warning-orange: #FF9800;
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

        /* Admin Header */
        .admin-header {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px;
            background: linear-gradient(145deg, rgba(33, 33, 33, 0.8), rgba(17, 17, 17, 0.9));
            border-radius: 20px;
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }

        .admin-title {
            font-size: 2.8rem;
            font-weight: 800;
            margin-bottom: 15px;
            background: linear-gradient(to right, var(--accent-red), var(--accent-light-red));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .admin-subtitle {
            color: var(--medium-gray);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: linear-gradient(145deg, rgba(33, 33, 33, 0.9), rgba(17, 17, 17, 0.95));
            border-radius: 15px;
            padding: 25px;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
            text-align: center;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--accent-red);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.5), 0 0 30px var(--glow-red);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            display: inline-block;
            padding: 15px;
            border-radius: 12px;
        }

        .stat-icon.pending { background: rgba(255, 152, 0, 0.1); color: var(--warning-orange); }
        .stat-icon.approved { background: rgba(76, 175, 80, 0.1); color: var(--success-green); }
        .stat-icon.rejected { background: rgba(211, 47, 47, 0.1); color: var(--accent-red); }
        .stat-icon.total { background: rgba(33, 150, 243, 0.1); color: #2196F3; }

        .stat-number {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--pure-white);
        }

        .stat-label {
            color: var(--medium-gray);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
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

        .alert-message {
            color: var(--light-gray);
            font-size: 1rem;
            font-weight: 500;
        }

        /* Admin Container */
        .admin-container {
            background: linear-gradient(145deg, rgba(33, 33, 33, 0.9), rgba(17, 17, 17, 0.95));
            border-radius: 20px;
            padding: 30px;
            border: 1px solid var(--border-color);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.5);
            overflow-x: auto;
            margin-bottom: 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: rgba(33, 33, 33, 0.95);
            color: var(--pure-white);
            font-weight: 600;
            font-size: 0.95rem;
            text-align: left;
            padding: 20px;
            border-bottom: 2px solid var(--border-color);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: var(--light-gray);
            font-size: 0.9rem;
        }

        tr:hover {
            background: rgba(211, 47, 47, 0.05);
        }

        .image-cell {
            width: 80px;
        }

        .image-cell img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid var(--border-color);
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .image-cell img:hover {
            transform: scale(1.1);
            border-color: var(--accent-red);
            box-shadow: 0 6px 25px var(--glow-red);
        }

        .status-badge {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: linear-gradient(135deg, rgba(255, 152, 0, 0.15), rgba(255, 152, 0, 0.1));
            color: var(--warning-orange);
            border: 1px solid rgba(255, 152, 0, 0.3);
        }

        .status-approved {
            background: linear-gradient(135deg, rgba(76, 175, 80, 0.15), rgba(76, 175, 80, 0.1));
            color: var(--success-green);
            border: 1px solid rgba(76, 175, 80, 0.3);
        }

        .status-rejected {
            background: linear-gradient(135deg, rgba(211, 47, 47, 0.15), rgba(183, 28, 28, 0.1));
            color: var(--accent-red);
            border: 1px solid rgba(211, 47, 47, 0.3);
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 8px 15px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
        }

        .btn-approve {
            background: linear-gradient(135deg, var(--success-green), #388E3C);
            color: var(--pure-white);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
        }

        .btn-approve:hover {
            background: linear-gradient(135deg, #66BB6A, var(--success-green));
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
        }

        .btn-reject {
            background: linear-gradient(135deg, var(--accent-red), var(--accent-dark-red));
            color: var(--pure-white);
            box-shadow: 0 4px 12px var(--glow-red);
        }

        .btn-reject:hover {
            background: linear-gradient(135deg, var(--accent-light-red), var(--accent-red));
            transform: translateY(-2px);
            box-shadow: 0 6px 20px var(--glow-dark-red);
        }

        .btn-edit {
            background: linear-gradient(135deg, rgba(33, 33, 33, 0.8), rgba(51, 51, 51, 0.6));
            color: var(--light-gray);
            border: 1px solid var(--border-color);
        }

        .btn-edit:hover {
            background: linear-gradient(135deg, #2196F3, #1976D2);
            border-color: #2196F3;
            color: var(--pure-white);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(33, 150, 243, 0.4);
        }

        .btn-delete {
            background: transparent;
            color: var(--medium-gray);
            border: 1px solid var(--border-color);
        }

        .btn-delete:hover {
            background: rgba(211, 47, 47, 0.1);
            border-color: var(--accent-red);
            color: var(--accent-red);
            transform: translateY(-2px);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 40px;
            background: linear-gradient(145deg, rgba(33, 33, 33, 0.8), rgba(17, 17, 17, 0.9));
            border-radius: 15px;
            border: 2px dashed var(--border-color);
        }

        .empty-icon {
            font-size: 4rem;
            color: var(--accent-red);
            margin-bottom: 20px;
            opacity: 0.7;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.7; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.05); }
        }

        .empty-title {
            font-size: 1.8rem;
            color: var(--pure-white);
            margin-bottom: 10px;
            font-weight: 600;
        }

        .empty-description {
            color: var(--medium-gray);
            max-width: 400px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Edit Modal */
        .edit-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1000;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(10px);
            justify-content: center;
            align-items: center;
        }

        .edit-modal-content {
            background: linear-gradient(145deg, rgba(33, 33, 33, 0.95), rgba(17, 17, 17, 0.98));
            border-radius: 20px;
            padding: 40px;
            border: 1px solid var(--border-color);
            max-width: 500px;
            width: 90%;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.8), 0 0 40px var(--glow-red);
            animation: modalSlideIn 0.4s ease;
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .edit-modal h3 {
            color: var(--pure-white);
            margin-bottom: 25px;
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
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
            padding: 14px 18px;
            background: rgba(33, 33, 33, 0.8);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            color: var(--pure-white);
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent-red);
            box-shadow: 0 0 0 3px var(--glow-red);
            background: rgba(33, 33, 33, 0.9);
        }

        textarea.form-input {
            min-height: 120px;
            resize: vertical;
            line-height: 1.5;
        }

        .modal-buttons {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }

        .btn-cancel {
            padding: 12px 25px;
            background: linear-gradient(135deg, rgba(33, 33, 33, 0.8), rgba(51, 51, 51, 0.6));
            color: var(--light-gray);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .btn-cancel:hover {
            background: linear-gradient(135deg, rgba(51, 51, 51, 0.9), rgba(33, 33, 33, 0.7));
            border-color: var(--accent-red);
            color: var(--accent-red);
            transform: translateY(-2px);
        }

        .btn-save {
            padding: 12px 30px;
            background: linear-gradient(135deg, var(--accent-red), var(--accent-dark-red));
            color: var(--pure-white);
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            box-shadow: 0 4px 15px var(--glow-red);
        }

        .btn-save:hover {
            background: linear-gradient(135deg, var(--accent-light-red), var(--accent-red));
            transform: translateY(-2px);
            box-shadow: 0 8px 25px var(--glow-dark-red);
        }

        /* Preview Modal */
        .preview-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1001;
            background: rgba(0, 0, 0, 0.95);
            backdrop-filter: blur(20px);
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .preview-content {
            position: relative;
            max-width: 90%;
            max-height: 90%;
        }

        .preview-image {
            max-width: 100%;
            max-height: 80vh;
            border-radius: 15px;
            border: 2px solid var(--border-color);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.8);
        }

        .preview-close {
            position: absolute;
            top: -50px;
            right: 0;
            background: rgba(211, 47, 47, 0.3);
            border: 1px solid rgba(211, 47, 47, 0.5);
            color: var(--pure-white);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .preview-close:hover {
            background: rgba(211, 47, 47, 0.6);
            transform: rotate(90deg);
        }

        .preview-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(33, 33, 33, 0.8);
            border: 1px solid var(--border-color);
            color: var(--pure-white);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .preview-nav:hover {
            background: var(--accent-red);
            border-color: var(--accent-red);
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 0 25px var(--glow-red);
        }

        .preview-prev {
            left: -70px;
        }

        .preview-next {
            right: -70px;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .admin-container {
                padding: 20px;
            }
            
            th, td {
                padding: 15px 10px;
                font-size: 0.85rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .action-btn {
                width: 100%;
                justify-content: center;
                padding: 10px;
            }
            
            .preview-nav {
                position: fixed;
                top: auto;
                bottom: 30px;
                width: 45px;
                height: 45px;
            }
            
            .preview-prev {
                left: 30px;
            }
            
            .preview-next {
                right: 30px;
            }
            
            .preview-close {
                top: 20px;
                right: 20px;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-title {
                font-size: 2rem;
            }
            
            .stat-number {
                font-size: 1.8rem;
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
                    <p>Admin Panel</p>
                </div>
            </a>

            <div class="user-actions">
                <a href="myuploads.php" class="btn btn-secondary">
                    <i class="fas fa-user-circle"></i> My Uploads
                </a>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-images"></i> Gallery
                </a>
                <div style="color: var(--medium-gray); font-size: 0.9rem; padding: 8px 16px; background: rgba(33, 33, 33, 0.6); border-radius: 8px; border: 1px solid var(--border-color);">
                    Admin: <strong style="color: var(--accent-red);"><?php echo $_SESSION['username']; ?></strong>
                </div>
                <a href="logout.php" class="btn btn-secondary">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="admin-header">
            <h1 class="admin-title">Admin Dashboard</h1>
            <p class="admin-subtitle">Manage all uploaded screenshots and user submissions</p>
        </div>

        <?php if(isset($_GET['message'])): ?>
            <div class="alert">
                <i class="fas fa-check-circle"></i>
                <span class="alert-message"><?php echo htmlspecialchars($_GET['message']); ?></span>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <?php
        $total = count($images);
        $pending = count(array_filter($images, function($img) { return $img['status'] == 'pending'; }));
        $approved = count(array_filter($images, function($img) { return $img['status'] == 'approved'; }));
        $rejected = count(array_filter($images, function($img) { return $img['status'] == 'rejected'; }));
        ?>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-images"></i>
                </div>
                <div class="stat-number"><?php echo $total; ?></div>
                <div class="stat-label">Total Images</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-number"><?php echo $pending; ?></div>
                <div class="stat-label">Pending Review</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon approved">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number"><?php echo $approved; ?></div>
                <div class="stat-label">Approved</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon rejected">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-number"><?php echo $rejected; ?></div>
                <div class="stat-label">Rejected</div>
            </div>
        </div>

        <!-- Images Table -->
        <div class="admin-container">
            <?php if(count($images) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th class="image-cell">Preview</th>
                            <th>Details</th>
                            <th>Uploader</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($images as $image): ?>
                            <tr>
                                <td class="image-cell">
                                    <img src="uploads/<?php echo $image['filename']; ?>" 
                                         alt="<?php echo htmlspecialchars($image['title']); ?>"
                                         class="preview-trigger"
                                         data-src="uploads/<?php echo $image['filename']; ?>"
                                         data-title="<?php echo htmlspecialchars($image['title']); ?>"
                                         data-description="<?php echo htmlspecialchars($image['description']); ?>">
                                </td>
                                <td>
                                    <strong style="color: var(--pure-white); font-size: 1rem;"><?php echo htmlspecialchars($image['title']); ?></strong><br>
                                    <?php if($image['description']): ?>
                                        <div style="color: var(--medium-gray); margin-top: 8px; font-size: 0.85rem; line-height: 1.4;">
                                            <?php echo htmlspecialchars(substr($image['description'], 0, 80)); ?>
                                            <?php if(strlen($image['description']) > 80): ?>...<?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div style="color: var(--accent-red); margin-top: 8px; font-size: 0.8rem;">
                                        <i class="fas fa-calendar"></i> <?php echo date('M j, Y g:i A', strtotime($image['uploaded_at'])); ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="color: var(--pure-white); font-weight: 500;"><?php echo htmlspecialchars($image['username']); ?></div>
                                    <?php if($image['minecraft_username']): ?>
                                        <div style="color: var(--accent-red); font-size: 0.85rem; margin-top: 5px;">
                                            <i class="fas fa-gamepad"></i> <?php echo htmlspecialchars($image['minecraft_username']); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($image['status'] == 'pending'): ?>
                                        <span class="status-badge status-pending">
                                            <i class="fas fa-clock"></i> Pending
                                        </span>
                                    <?php elseif($image['status'] == 'approved'): ?>
                                        <span class="status-badge status-approved">
                                            <i class="fas fa-check"></i> Approved
                                        </span>
                                    <?php elseif($image['status'] == 'rejected'): ?>
                                        <span class="status-badge status-rejected">
                                            <i class="fas fa-times"></i> Rejected
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if($image['status'] != 'approved'): ?>
                                            <a href="admin.php?action=approve&id=<?php echo $image['id']; ?>" 
                                               class="action-btn btn-approve"
                                               onclick="return confirm('Approve this screenshot?')">
                                                <i class="fas fa-check"></i> Approve
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if($image['status'] != 'rejected'): ?>
                                            <a href="admin.php?action=reject&id=<?php echo $image['id']; ?>" 
                                               class="action-btn btn-reject"
                                               onclick="return confirm('Reject this screenshot?')">
                                                <i class="fas fa-times"></i> Reject
                                            </a>
                                        <?php endif; ?>
                                        
                                        <button class="action-btn btn-edit edit-trigger" 
                                                data-id="<?php echo $image['id']; ?>"
                                                data-title="<?php echo htmlspecialchars($image['title']); ?>"
                                                data-description="<?php echo htmlspecialchars($image['description']); ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        
                                        <a href="admin.php?delete=1&id=<?php echo $image['id']; ?>" 
                                           class="action-btn btn-delete"
                                           onclick="return confirm('Permanently delete this screenshot?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-images"></i>
                    </div>
                    <h3 class="empty-title">No Screenshots Yet</h3>
                    <p class="empty-description">
                        Users haven't uploaded any screenshots yet. Check back later!
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="edit-modal">
        <div class="edit-modal-content">
            <h3><i class="fas fa-edit"></i> Edit Screenshot</h3>
            <form method="POST" action="">
                <input type="hidden" name="image_id" id="editImageId">
                <input type="hidden" name="edit_image" value="1">
                
                <div class="form-group">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" id="editTitle" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="editDescription" class="form-input" rows="4"></textarea>
                </div>
                
                <div class="modal-buttons">
                    <button type="button" class="btn-cancel" id="cancelEdit">Cancel</button>
                    <button type="submit" class="btn-save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Preview Modal -->
    <div id="previewModal" class="preview-modal">
        <div class="preview-content">
            <img id="previewImage" class="preview-image" src="" alt="">
            <button class="preview-close" id="closePreview">
                <i class="fas fa-times"></i>
            </button>
            <button class="preview-nav preview-prev" id="prevPreview">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="preview-nav preview-next" id="nextPreview">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>

    <script>
        // Auto-hide alert after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.3s ease';
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.remove();
                }, 300);
            });
        }, 5000);

        // Edit Modal Functionality
        const editModal = document.getElementById('editModal');
        const editTriggers = document.querySelectorAll('.edit-trigger');
        const cancelEdit = document.getElementById('cancelEdit');
        const editImageId = document.getElementById('editImageId');
        const editTitle = document.getElementById('editTitle');
        const editDescription = document.getElementById('editDescription');

        editTriggers.forEach(trigger => {
            trigger.addEventListener('click', function() {
                editImageId.value = this.getAttribute('data-id');
                editTitle.value = this.getAttribute('data-title');
                editDescription.value = this.getAttribute('data-description');
                editModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });
        });

        cancelEdit.addEventListener('click', function() {
            editModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });

        // Close edit modal when clicking outside
        editModal.addEventListener('click', function(e) {
            if (e.target === this) {
                editModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });

        // Preview Modal Functionality
        const previewModal = document.getElementById('previewModal');
        const previewImage = document.getElementById('previewImage');
        const closePreview = document.getElementById('closePreview');
        const prevPreview = document.getElementById('prevPreview');
        const nextPreview = document.getElementById('nextPreview');
        const previewTriggers = document.querySelectorAll('.preview-trigger');
        
        let previewImages = [];
        let currentPreviewIndex = 0;
        
        // Collect all images for preview
        function collectPreviewImages() {
            previewImages = [];
            document.querySelectorAll('.preview-trigger').forEach((img, index) => {
                previewImages.push({
                    src: img.getAttribute('data-src'),
                    title: img.getAttribute('data-title'),
                    description: img.getAttribute('data-description'),
                    index: index
                });
            });
        }
        
        // Open preview modal
        previewTriggers.forEach((trigger, index) => {
            trigger.addEventListener('click', function() {
                collectPreviewImages();
                currentPreviewIndex = index;
                openPreview(currentPreviewIndex);
            });
        });
        
        function openPreview(index) {
            const image = previewImages[index];
            previewImage.src = image.src;
            previewImage.alt = image.title;
            previewModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            // Update navigation buttons
            updatePreviewNavigation();
        }
        
        function closePreviewModal() {
            previewModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        function prevPreviewImage() {
            if (currentPreviewIndex > 0) {
                currentPreviewIndex--;
                openPreview(currentPreviewIndex);
            }
        }
        
        function nextPreviewImage() {
            if (currentPreviewIndex < previewImages.length - 1) {
                currentPreviewIndex++;
                openPreview(currentPreviewIndex);
            }
        }
        
        function updatePreviewNavigation() {
            prevPreview.style.display = currentPreviewIndex > 0 ? 'flex' : 'none';
            nextPreview.style.display = currentPreviewIndex < previewImages.length - 1 ? 'flex' : 'none';
        }
        
        // Event listeners for preview modal
        closePreview.addEventListener('click', closePreviewModal);
        prevPreview.addEventListener('click', prevPreviewImage);
        nextPreview.addEventListener('click', nextPreviewImage);
        
        // Close preview modal when clicking outside
        previewModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closePreviewModal();
            }
        });
        
        // Keyboard navigation for preview
        document.addEventListener('keydown', function(e) {
            if (previewModal.style.display === 'flex') {
                if (e.key === 'Escape') {
                    closePreviewModal();
                } else if (e.key === 'ArrowLeft') {
                    prevPreviewImage();
                } else if (e.key === 'ArrowRight') {
                    nextPreviewImage();
                }
            }
        });
        
        // Touch swipe for preview
        let touchStartX = 0;
        let touchEndX = 0;
        
        previewImage.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        });
        
        previewImage.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            handlePreviewSwipe();
        });
        
        function handlePreviewSwipe() {
            const diff = touchStartX - touchEndX;
            const swipeThreshold = 50;
            
            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    nextPreviewImage();
                } else {
                    prevPreviewImage();
                }
            }
        }
    </script>
</body>
</html>