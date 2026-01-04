<?php
// admin_panel.php
include 'config.php';

if (!isAdminLoggedIn()) {
    header('Location: admin_login.php');
    exit;
}

// Handle actions
if (isset($_POST['action'])) {
    handleAdminAction($_POST);
}

// Get applications data
try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM whitelist_applications ORDER BY application_date DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Statistics
    $total = count($applications);
    $pending = 0;
    $approved = 0;
    $rejected = 0;
    $interview = 0;
    $trial = 0;
    
    foreach ($applications as $app) {
        switch ($app['status']) {
            case 'pending': $pending++; break;
            case 'approved': $approved++; break;
            case 'rejected': $rejected++; break;
            case 'interview': $interview++; break;
            case 'trial': $trial++; break;
        }
    }
    
} catch(PDOException $e) {
    $applications = [];
    $total = $pending = $approved = $rejected = $interview = $trial = 0;
}

function handleAdminAction($data) {
    if (!isset($data['id']) || !isset($data['action'])) return;
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $id = $data['id'];
        $action = $data['action'];
        
        switch ($action) {
            case 'approve':
                $stmt = $db->prepare("UPDATE whitelist_applications SET status = 'approved' WHERE id = ?");
                break;
            case 'reject':
                $stmt = $db->prepare("UPDATE whitelist_applications SET status = 'rejected' WHERE id = ?");
                break;
            case 'interview':
                $stmt = $db->prepare("UPDATE whitelist_applications SET status = 'interview' WHERE id = ?");
                break;
            case 'trial':
                $stmt = $db->prepare("UPDATE whitelist_applications SET status = 'trial' WHERE id = ?");
                break;
            case 'pending':
                $stmt = $db->prepare("UPDATE whitelist_applications SET status = 'pending' WHERE id = ?");
                break;
            case 'delete':
                $stmt = $db->prepare("DELETE FROM whitelist_applications WHERE id = ?");
                break;
            default:
                return;
        }
        
        $stmt->execute([$id]);
        header('Location: admin_panel.php');
        exit;
        
    } catch(PDOException $e) {
        error_log("Admin action error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warlord Realm | Registeration Admin Panel</title>
    <link rel="shortcut icon" href="../asset/logo-min.png" type="image/x-icon">
    <link rel="stylesheet" href="../asset/style-registeration_admin_panel_pange.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body oncontextmenu="return false" ondragstart="return false;" ondrop="return false;">
    <div class="ambient-bg"></div>
    
    <div class="container">
        <div class="admin-header">
            <div class="logo-container">
                <img src="../asset/logo.jpg" alt="Warlord Realm" class="logo-image">
            </div>
            <h1 class="logo-text">Warlord Realm Registeration Panel</h1>
            <p>Management Whitelist Applications</p>
            
            <div class="admin-info">
                <i class="fas fa-user-shield"></i> 
                Logged in as: <strong><?php echo $_SESSION['admin_username'] ?? 'Admin'; ?></strong>
            </div>
        </div>

        <!-- Statistics -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total; ?></div>
                <div>Total</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $pending; ?></div>
                <div>Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $interview; ?></div>
                <div>Interview</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $trial; ?></div>
                <div>Trial</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $approved; ?></div>
                <div>Approved</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $rejected; ?></div>
                <div>Rejected</div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="filter-container">
            <input type="text" id="searchInput" placeholder="Cari username, discord, atau email..." class="search-input">
            <select id="statusFilter" class="filter-select">
                <option value="all">Semua Status</option>
                <option value="pending">Pending</option>
                <option value="interview">Interview</option>
                <option value="trial">Trial</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
            <button onclick="searchApplications()" class="btn btn-primary">
                <i class="fas fa-search"></i> Cari
            </button>
        </div>

        <!-- Applications Table -->
        <div class="table-container">
            <h2><i class="fas fa-list"></i> Whitelist Applications</h2>
            
            <?php if (empty($applications)): ?>
                <div style="text-align: center; padding: 40px; color: #b0b0b0;">
                    <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                    <p>Belum ada aplikasi whitelist.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Minecraft User</th>
                            <th>Umur</th>
                            <th>Discord</th>
                            <th>Email</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $app): ?>
                        <tr>
                            <td data-label="ID"><?php echo $app['id']; ?></td>
                            <td data-label="Minecraft User"><strong><?php echo htmlspecialchars($app['mc_username']); ?></strong></td>
                            <td data-label="Umur"><?php echo $app['age']; ?></td>
                            <td data-label="Discord"><?php echo htmlspecialchars($app['discord']); ?></td>
                            <td data-label="Email"><?php echo htmlspecialchars($app['email']); ?></td>
                            <td data-label="Tanggal"><?php echo date('d/m/Y', strtotime($app['application_date'])); ?></td>
                            <td data-label="Status">
                                <span class="status-badge status-<?php echo $app['status']; ?>">
                                    <?php echo ucfirst($app['status']); ?>
                                </span>
                            </td>
                            <td data-label="Aksi">
                                <div class="action-buttons">
                                    <button type="button" class="btn btn-view btn-sm" onclick="viewApplication(<?php echo htmlspecialchars(json_encode($app)); ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    
                                    <?php if ($app['status'] !== 'pending'): ?>
                                    <button type="button" class="btn btn-pending btn-sm" onclick="updateStatus(<?php echo $app['id']; ?>, 'pending')">
                                        <i class="fas fa-clock"></i> Pending
                                    </button>
                                    <?php endif; ?>

                                    <?php if ($app['status'] !== 'interview'): ?>
                                    <button type="button" class="btn btn-interview btn-sm" onclick="updateStatus(<?php echo $app['id']; ?>, 'interview')">
                                        <i class="fas fa-comments"></i> Intv
                                    </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($app['status'] !== 'trial'): ?>
                                    <button type="button" class="btn btn-trial btn-sm" onclick="updateStatus(<?php echo $app['id']; ?>, 'trial')">
                                        <i class="fas fa-user-clock"></i> Trial
                                    </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($app['status'] !== 'approved'): ?>
                                    <button type="button" class="btn btn-approve btn-sm" onclick="updateStatus(<?php echo $app['id']; ?>, 'approve')">
                                        <i class="fas fa-check"></i> Apprv
                                    </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($app['status'] !== 'rejected'): ?>
                                    <button type="button" class="btn btn-reject btn-sm" onclick="updateStatus(<?php echo $app['id']; ?>, 'reject')">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                    <?php endif; ?>
                                    
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="id" value="<?php echo $app['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-delete btn-sm" onclick="return confirm('Hapus permanen aplikasi ini?')">
                                            <i class="fas fa-trash"></i> Del
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="admin-footer">
            <div class="footer-links">
                <a href="admin_logout.php" class="footer-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
                <a href="index.html" class="footer-link">
                    <i class="fas fa-file-alt"></i> Form Whitelist
                </a>
                <a href="../index.html" class="footer-link">
                    <i class="fas fa-home"></i> Website Utama
                </a>
            </div>
        </div>
    </div>

    <!-- Modal for Viewing Application Details -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2><i class="fas fa-user"></i> Detail Aplikasi Whitelist</h2>
            <div id="modalContent"></div>
        </div>
    </div>
</body>
<script src="../asset/java_script-registeration_admin_panel_pange.js"></script>
</html>