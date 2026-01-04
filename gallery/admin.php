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
    <title>Admin Panel | Warlord Realm Gallery</title>
    <link rel="shortcut icon" href="../asset/logo-min.png" type="image/x-icon">
    <link rel="stylesheet" href="../asset/style-gallery_admin_pange.css">
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
</body>
<script src="../asset/java_script-admin_gallery_pange.js"></script>
</html>