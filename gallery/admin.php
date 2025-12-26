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
    <title>Admin - Warlord Realm Gallery</title>
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
            --success-green: #4caf50;
            --warning-orange: #ff9800;
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
            max-width: 1200px;
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

        .admin-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .admin-logo {
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
            color: #ff0000;
            margin-bottom: 5px;
        }

        .logo-text span {
            font-size: 0.9rem;
            color: var(--medium-gray);
        }

        .admin-title {
            font-size: 2rem;
            font-weight: 300;
            color: #ff0000;
            margin-bottom: 10px;
        }

        .admin-subtitle {
            color: var(--medium-gray);
            font-size: 1rem;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(76, 175, 80, 0.1);
            border: 1px solid rgba(76, 175, 80, 0.3);
            color: #81c784;
        }

        .admin-container {
            background: var(--secondary-black);
            border-radius: 12px;
            padding: 30px;
            border: 1px solid var(--border-gray);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: rgba(255, 255, 255, 0.05);
            color: var(--pure-white);
            font-weight: 500;
            font-size: 0.9rem;
            text-align: left;
            padding: 15px;
            border-bottom: 1px solid var(--border-gray);
        }

        td {
            padding: 15px;
            border-bottom: 1px solid var(--border-gray);
            color: var(--light-gray);
            font-size: 0.9rem;
        }

        tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        .image-cell {
            width: 80px;
        }

        .image-cell img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid var(--border-gray);
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .image-cell img:hover {
            transform: scale(1.05);
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-pending {
            background: rgba(255, 152, 0, 0.1);
            color: var(--warning-orange);
            border: 1px solid rgba(255, 152, 0, 0.3);
        }

        .status-approved {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success-green);
            border: 1px solid rgba(76, 175, 80, 0.3);
        }

        .status-rejected {
            background: rgba(255, 0, 51, 0.1);
            color: var(--accent-red);
            border: 1px solid rgba(255, 0, 51, 0.3);
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border: none;
        }

        .btn-approve {
            background: var(--success-green);
            color: var(--pure-white);
        }

        .btn-approve:hover {
            background: #3d8b40;
            transform: translateY(-1px);
        }

        .btn-reject {
            background: var(--accent-red);
            color: var(--pure-white);
        }

        .btn-reject:hover {
            background: #cc0029;
            transform: translateY(-1px);
        }

        .btn-edit {
            background: #2196f3;
            color: var(--pure-white);
        }

        .btn-edit:hover {
            background: #0b7dda;
            transform: translateY(-1px);
        }

        .btn-delete {
            background: transparent;
            color: var(--medium-gray);
            border: 1px solid var(--border-gray);
        }

        .btn-delete:hover {
            border-color: var(--accent-red);
            color: var(--accent-red);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-icon {
            font-size: 3rem;
            color: var(--accent-red);
            margin-bottom: 20px;
            opacity: 0.7;
        }

        .empty-title {
            font-size: 1.3rem;
            color: var(--pure-white);
            margin-bottom: 10px;
        }

        .empty-description {
            color: var(--medium-gray);
            max-width: 400px;
            margin: 0 auto;
        }

        /* Edit Modal Styles */
        .edit-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1000;
            background: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
        }

        .edit-modal-content {
            background: var(--secondary-black);
            border-radius: 12px;
            padding: 30px;
            border: 1px solid var(--border-gray);
            max-width: 500px;
            width: 90%;
        }

        .edit-modal h3 {
            color: var(--pure-white);
            margin-bottom: 20px;
            font-size: 1.3rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--light-gray);
            font-size: 0.9rem;
        }

        .form-input {
            width: 100%;
            padding: 10px 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-gray);
            border-radius: 6px;
            color: var(--pure-white);
            font-size: 0.9rem;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent-red);
        }

        .modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .btn-cancel {
            background: transparent;
            color: var(--medium-gray);
            border: 1px solid var(--border-gray);
        }

        .btn-cancel:hover {
            border-color: var(--accent-red);
            color: var(--accent-red);
        }

        .btn-save {
            background: var(--accent-red);
            color: var(--pure-white);
        }

        .btn-save:hover {
            background: #cc0029;
        }

        /* Preview Modal Styles */
        .preview-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1001;
            background: rgba(0, 0, 0, 0.95);
            justify-content: center;
            align-items: center;
        }

        .preview-content {
            position: relative;
            max-width: 90%;
            max-height: 90%;
        }

        .preview-image {
            max-width: 100%;
            max-height: 80vh;
            border-radius: 8px;
        }

        .preview-close {
            position: absolute;
            top: -40px;
            right: 0;
            background: rgba(255, 0, 51, 0.3);
            border: none;
            color: var(--pure-white);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .preview-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.5);
            border: none;
            color: var(--pure-white);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .preview-prev {
            left: -50px;
        }

        .preview-next {
            right: -50px;
        }

        @media (max-width: 768px) {
            .admin-container {
                padding: 20px 15px;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }

            .preview-nav {
                position: fixed;
                top: auto;
                bottom: 20px;
            }

            .preview-prev {
                left: 20px;
            }

            .preview-next {
                right: 20px;
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

        <div class="admin-header">
            <div class="admin-logo">
                <img src="../asset/logo-min.png" alt="Warlord Realm Logo" class="logo-img" onerror="this.style.display='none'; this.parentElement.querySelector('.logo-icon').style.display='flex';">
                <div class="logo-text">
                    <h1>Warlord Realm</h1>
                    <span>Admin Panel</span>
                </div>
            </div>
            
            <h2 class="admin-title">Manage Uploads</h2>
            <p class="admin-subtitle">Review and approve user screenshots</p>
        </div>

        <?php if(isset($_GET['message'])): ?>
            <div class="alert">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($_GET['message']); ?></span>
            </div>
        <?php endif; ?>

        <div class="admin-container">
            <?php if(count($images) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th class="image-cell">Image</th>
                            <th>Title</th>
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
                                    <strong><?php echo htmlspecialchars($image['title']); ?></strong><br>
                                    <?php if($image['description']): ?>
                                        <small style="color: var(--medium-gray); margin-top: 5px; display: block;">
                                            <?php echo htmlspecialchars(substr($image['description'], 0, 50)); ?>
                                            <?php if(strlen($image['description']) > 50): ?>...<?php endif; ?>
                                        </small>
                                    <?php endif; ?>
                                    <small style="color: var(--medium-gray); margin-top: 5px; display: block;">
                                        <?php echo date('M j, Y', strtotime($image['uploaded_at'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($image['username']); ?><br>
                                    <?php if($image['minecraft_username']): ?>
                                        <small style="color: var(--accent-red);"><?php echo htmlspecialchars($image['minecraft_username']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($image['status'] == 'pending'): ?>
                                        <span class="status-badge status-pending">Pending</span>
                                    <?php elseif($image['status'] == 'approved'): ?>
                                        <span class="status-badge status-approved">Approved</span>
                                    <?php elseif($image['status'] == 'rejected'): ?>
                                        <span class="status-badge status-rejected">Rejected</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if($image['status'] != 'approved'): ?>
                                            <a href="admin.php?action=approve&id=<?php echo $image['id']; ?>" 
                                               class="btn btn-approve"
                                               onclick="return confirm('Approve this screenshot?')">
                                                <i class="fas fa-check"></i> Approve
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if($image['status'] != 'rejected'): ?>
                                            <a href="admin.php?action=reject&id=<?php echo $image['id']; ?>" 
                                               class="btn btn-reject"
                                               onclick="return confirm('Reject this screenshot?')">
                                                <i class="fas fa-times"></i> Reject
                                            </a>
                                        <?php endif; ?>
                                        
                                        <button class="btn btn-edit edit-trigger" 
                                                data-id="<?php echo $image['id']; ?>"
                                                data-title="<?php echo htmlspecialchars($image['title']); ?>"
                                                data-description="<?php echo htmlspecialchars($image['description']); ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        
                                        <a href="admin.php?delete=1&id=<?php echo $image['id']; ?>" 
                                           class="btn btn-delete"
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
                        Users haven't uploaded any screenshots yet.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="edit-modal">
        <div class="edit-modal-content">
            <h3>Edit Screenshot</h3>
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
                    <button type="button" class="btn btn-cancel" id="cancelEdit">Cancel</button>
                    <button type="submit" class="btn btn-save">Save Changes</button>
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
            });
        });

        cancelEdit.addEventListener('click', function() {
            editModal.style.display = 'none';
        });

        // Close edit modal when clicking outside
        editModal.addEventListener('click', function(e) {
            if (e.target === this) {
                editModal.style.display = 'none';
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
            
            // Update navigation buttons
            updatePreviewNavigation();
        }
        
        function closePreviewModal() {
            previewModal.style.display = 'none';
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