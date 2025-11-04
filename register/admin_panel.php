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

// Get registrations data
try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM registrations ORDER BY registration_date DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Statistics
    $total = count($registrations);
    $pending = 0;
    $approved = 0;
    $rejected = 0;
    
    foreach ($registrations as $reg) {
        switch ($reg['status']) {
            case 'pending': $pending++; break;
            case 'approved': $approved++; break;
            case 'rejected': $rejected++; break;
        }
    }
    
} catch(PDOException $e) {
    $registrations = [];
    $total = $pending = $approved = $rejected = 0;
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
                $stmt = $db->prepare("UPDATE registrations SET status = 'approved' WHERE id = ?");
                break;
            case 'reject':
                $stmt = $db->prepare("UPDATE registrations SET status = 'rejected' WHERE id = ?");
                break;
            case 'delete':
                $stmt = $db->prepare("DELETE FROM registrations WHERE id = ?");
                break;
            case 'pending':
                $stmt = $db->prepare("UPDATE registrations SET status = 'pending' WHERE id = ?");
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
    <title>Admin Panel - Warlord Realm</title>
    <link rel="stylesheet" href="../asset/style-admin_panel_register_pange.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="ambient-bg"></div>
    
    <div class="container">
        <div class="admin-header">
            <div class="logo-container">
                <img src="../asset/logo.jpg" alt="Warlord Realm" class="logo-image">
            </div>
            <h1 class="logo-text">Warlord Panel</h1>
            <p>Management Pendaftaran Warlord Realm</p>
            
            <div class="admin-info">
                <i class="fas fa-user-shield"></i> 
                Logged in as: <strong><?php echo $_SESSION['admin_username'] ?? 'Admin'; ?></strong>
            </div>
        </div>

        <!-- Statistics -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-number stat-total"><?php echo $total; ?></div>
                <div>Total Pendaftar</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-pending"><?php echo $pending; ?></div>
                <div>Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-approved"><?php echo $approved; ?></div>
                <div>Approved</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-rejected"><?php echo $rejected; ?></div>
                <div>Rejected</div>
            </div>
        </div>

        <!-- Registrations Table -->
        <div class="table-container">
            <h2><i class="fas fa-list"></i> Data Pendaftaran</h2>
            
            <?php if (empty($registrations)): ?>
                <div style="text-align: center; padding: 40px; color: #b0b0b0;">
                    <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                    <p>Belum ada data pendaftaran.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Username MC</th>
                            <th>Tipe</th>
                            <th>Discord</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registrations as $reg): ?>
                        <tr>
                            <td><?php echo $reg['id']; ?></td>
                            <td><?php echo htmlspecialchars($reg['email']); ?></td>
                            <td><strong><?php echo htmlspecialchars($reg['username']); ?></strong></td>
                            <td><?php echo ucfirst($reg['minecraft_type']); ?></td>
                            <td><?php echo htmlspecialchars($reg['discord_username']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($reg['registration_date'])); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $reg['status']; ?>">
                                    <?php echo $reg['status']; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="id" value="<?php echo $reg['id']; ?>">
                                        <input type="hidden" name="action" value="view">
                                        <button type="button" class="btn btn-view btn-sm" onclick="viewRegistration(<?php echo htmlspecialchars(json_encode($reg)); ?>)">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </form>
                                    
                                    <?php if ($reg['status'] !== 'approved'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="id" value="<?php echo $reg['id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-approve btn-sm" onclick="return confirm('Approve pendaftaran ini?')">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    
                                    <?php if ($reg['status'] !== 'rejected'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="id" value="<?php echo $reg['id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-reject btn-sm" onclick="return confirm('Reject pendaftaran ini?')">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    
                                    <?php if ($reg['status'] !== 'pending'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="id" value="<?php echo $reg['id']; ?>">
                                        <input type="hidden" name="action" value="pending">
                                        <button type="submit" class="btn btn-pending btn-sm">
                                            <i class="fas fa-clock"></i> Pending
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="id" value="<?php echo $reg['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-delete btn-sm" onclick="return confirm('Hapus permanen pendaftaran ini?')">
                                            <i class="fas fa-trash"></i> Delete
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
                <a href="../index.html" class="footer-link">
                    <i class="fas fa-home"></i> Ke Website
                </a>
                <a href="../register/" class="footer-link">
                    <i class="fas fa-user-plus"></i> Form Pendaftaran
                </a>
            </div>
        </div>
    </div>

    <!-- Modal for Viewing Registration Details -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2><i class="fas fa-user"></i> Detail Pendaftaran</h2>
            <div id="modalContent"></div>
        </div>
    </div>

    <script>
        // Modal functionality
        const modal = document.getElementById('viewModal');
        const closeBtn = document.querySelector('.close');
        const modalContent = document.getElementById('modalContent');

        function viewRegistration(data) {
            modalContent.innerHTML = `
                <div style="background: rgba(35,35,35,0.6); padding: 20px; border-radius: 8px; margin: 15px 0;">
                    <h3 style="color: #ff6666; margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 10px;">
                        <i class="fas fa-id-card"></i> Informasi Pribadi
                    </h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div><strong>Email:</strong><br>${data.email}</div>
                        <div><strong>Username MC:</strong><br>${data.username}</div>
                        <div><strong>Tipe Akun:</strong><br>${data.minecraft_type}</div>
                        <div><strong>Discord:</strong><br>${data.discord_username}</div>
                    </div>
                    ${data.social_media ? `<div><strong>Media Sosial Lain:</strong><br>${data.social_media}</div>` : ''}
                </div>

                <div style="background: rgba(35,35,35,0.6); padding: 20px; border-radius: 8px; margin: 15px 0;">
                    <h3 style="color: #ff6666; margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 10px;">
                        <i class="fas fa-star"></i> Keahlian & Pengalaman
                    </h3>
                    <div style="margin-bottom: 15px;">
                        <strong>Keahlian:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 10px; border-radius: 4px; margin-top: 5px;">
                            ${data.skills}
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <strong>Pengalaman SMP:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 10px; border-radius: 4px; margin-top: 5px;">
                            ${data.experience}
                        </div>
                    </div>
                    <div>
                        <strong>Alasan Bergabung:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 10px; border-radius: 4px; margin-top: 5px;">
                            ${data.reason}
                        </div>
                    </div>
                </div>

                <div style="background: rgba(35,35,35,0.6); padding: 20px; border-radius: 8px; margin: 15px 0;">
                    <h3 style="color: #ff6666; margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 10px;">
                        <i class="fas fa-info-circle"></i> Informasi Tambahan
                    </h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div><strong>Preferensi Diamond:</strong><br>${data.diamond_preference}</div>
                        <div><strong>Status:</strong><br><span class="status-badge status-${data.status}">${data.status}</span></div>
                        <div><strong>Tanggal Daftar:</strong><br>${new Date(data.registration_date).toLocaleString('id-ID')}</div>
                        <div><strong>ID Pendaftaran:</strong><br>#${data.id}</div>
                    </div>
                </div>
            `;
            modal.style.display = 'block';
        }

        closeBtn.onclick = function() {
            modal.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>