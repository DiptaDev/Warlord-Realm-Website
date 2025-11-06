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
                                    <button type="button" class="btn btn-view btn-sm" onclick="viewRegistration(<?php echo htmlspecialchars(json_encode($reg)); ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    
                                    <?php if ($reg['status'] !== 'approved'): ?>
                                    <button type="button" class="btn btn-approve btn-sm" onclick="updateStatus(<?php echo $reg['id']; ?>, 'approve')">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($reg['status'] !== 'rejected'): ?>
                                    <button type="button" class="btn btn-reject btn-sm" onclick="updateStatus(<?php echo $reg['id']; ?>, 'reject')">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($reg['status'] !== 'pending'): ?>
                                    <button type="button" class="btn btn-pending btn-sm" onclick="updateStatus(<?php echo $reg['id']; ?>, 'pending')">
                                        <i class="fas fa-clock"></i> Pending
                                    </button>
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
        <div style="max-height: 70vh; overflow-y: auto; padding-right: 10px;">
            <div style="background: rgba(35,35,35,0.6); padding: 20px; border-radius: 8px; margin: 15px 0;">
                <h3 style="color: #ff6666; margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 10px;">
                    <i class="fas fa-id-card"></i> Informasi Pribadi
                </h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div><strong>Email:</strong><br><span style="word-break: break-all;">${data.email}</span></div>
                    <div><strong>Username MC:</strong><br><strong style="color: #ff6666;">${data.username}</strong></div>
                    <div><strong>Tipe Akun:</strong><br>${data.minecraft_type}</div>
                    <div><strong>Discord:</strong><br>${data.discord_username}</div>
                </div>
                ${data.social_media ? `
                <div style="margin-top: 10px;">
                    <strong>Media Sosial Lain:</strong><br>
                    <div style="background: rgba(0,0,0,0.3); padding: 8px; border-radius: 4px; margin-top: 5px;">
                        ${data.social_media}
                    </div>
                </div>
                ` : ''}
            </div>

            <div style="background: rgba(35,35,35,0.6); padding: 20px; border-radius: 8px; margin: 15px 0;">
                <h3 style="color: #ff6666; margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 10px;">
                    <i class="fas fa-star"></i> Keahlian & Pengalaman
                </h3>
                <div style="margin-bottom: 15px;">
                    <strong>Keahlian:</strong><br>
                    <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 8px; max-height: 100px; overflow-y: auto;">
                        ${data.skills || '<em style="color: #888;">Tidak diisi</em>'}
                    </div>
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Pengalaman SMP:</strong><br>
                    <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 8px; max-height: 100px; overflow-y: auto;">
                        ${data.experience || '<em style="color: #888;">Tidak diisi</em>'}
                    </div>
                </div>
                <div>
                    <strong>Alasan Bergabung:</strong><br>
                    <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 8px; max-height: 100px; overflow-y: auto;">
                        ${data.reason || '<em style="color: #888;">Tidak diisi</em>'}
                    </div>
                </div>
            </div>

            <div style="background: rgba(35,35,35,0.6); padding: 20px; border-radius: 8px; margin: 15px 0;">
                <h3 style="color: #ff6666; margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 10px;">
                    <i class="fas fa-info-circle"></i> Informasi Tambahan
                </h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><strong>Preferensi Diamond:</strong><br>${data.diamond_preference || '<em style="color: #888;">Tidak diisi</em>'}</div>
                    <div><strong>Status:</strong><br><span class="status-badge status-${data.status}">${data.status}</span></div>
                    <div><strong>Tanggal Daftar:</strong><br>${new Date(data.registration_date).toLocaleString('id-ID')}</div>
                    <div><strong>ID Pendaftaran:</strong><br><strong>#${data.id}</strong></div>
                </div>
            </div>

            <div style="background: rgba(35,35,35,0.6); padding: 20px; border-radius: 8px; margin: 15px 0; text-align: center;">
                <h3 style="color: #ff6666; margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 10px;">
                    <i class="fas fa-cog"></i> Aksi Admin
                </h3>
                <div class="action-buttons" style="justify-content: center; gap: 10px; flex-wrap: wrap;">
                    ${data.status !== 'approved' ? `
                    <button type="button" class="btn btn-approve" onclick="updateStatus(${data.id}, 'approve')">
                        <i class="fas fa-check"></i> Approve
                    </button>
                    ` : ''}
                    
                    ${data.status !== 'rejected' ? `
                    <button type="button" class="btn btn-reject" onclick="updateStatus(${data.id}, 'reject')">
                        <i class="fas fa-times"></i> Reject
                    </button>
                    ` : ''}
                    
                    ${data.status !== 'pending' ? `
                    <button type="button" class="btn btn-pending" onclick="updateStatus(${data.id}, 'pending')">
                        <i class="fas fa-clock"></i> Set Pending
                    </button>
                    ` : ''}
                </div>
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

    // Function to update status via AJAX
    function updateStatus(registrationId, action) {
        const actionTexts = {
            'approve': 'approve',
            'reject': 'reject', 
            'pending': 'set pending'
        };

        if (!confirm(`Are you sure you want to ${actionTexts[action]} this registration?`)) {
            return;
        }
        
        // Find all buttons for this registration to show loading state
        const buttons = document.querySelectorAll(`button[onclick*="updateStatus(${registrationId}, '${action}')"]`);
        
        buttons.forEach(button => {
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            button.disabled = true;
        });

        fetch('update_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: registrationId,
                action: action
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showNotification(`Status berhasil diupdate ke ${data.new_status}`, 'success');
                
                // Close modal if open
                modal.style.display = 'none';
                
                // Reload page after short delay to show updated status
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showNotification('Error: ' + data.message, 'error');
                
                // Reset buttons
                buttons.forEach(button => {
                    button.innerHTML = button.getAttribute('data-original') || originalText;
                    button.disabled = false;
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan saat update status.', 'error');
            
            // Reset buttons
            buttons.forEach(button => {
                button.innerHTML = button.getAttribute('data-original') || originalText;
                button.disabled = false;
            });
        });
    }

    // Function to show notification
    function showNotification(message, type) {
        // Remove existing notification
        const existingNotification = document.querySelector('.notification');
        if (existingNotification) {
            existingNotification.remove();
        }

        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div style="position: fixed; top: 20px; right: 20px; background: ${type === 'success' ? 'rgba(0, 255, 0, 0.9)' : 'rgba(255, 0, 0, 0.9)'}; color: white; padding: 15px 20px; border-radius: 5px; z-index: 10000; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'}"></i> ${message}
            </div>
        `;
        
        document.body.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    // Update all form buttons to use AJAX (for delete actions)
    document.addEventListener('DOMContentLoaded', function() {
        // Store original button text for all action buttons
        document.querySelectorAll('.action-buttons .btn').forEach(button => {
            button.setAttribute('data-original', button.innerHTML);
        });

        // Handle delete forms with AJAX
        document.querySelectorAll('form[action=""]').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const action = formData.get('action');
                const id = formData.get('id');
                
                if (action === 'delete' && id) {
                    deleteRegistration(id);
                }
            });
        });
    });

    // Function to handle delete with AJAX
    function deleteRegistration(registrationId) {
        if (!confirm('Are you sure you want to permanently delete this registration? This action cannot be undone!')) {
            return;
        }

        const form = document.querySelector(`form input[value="${registrationId}"]`).closest('form');
        const button = form.querySelector('button');
        const originalText = button.innerHTML;

        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        button.disabled = true;

        fetch('update_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: registrationId,
                action: 'delete'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Registration deleted successfully', 'success');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showNotification('Error: ' + data.message, 'error');
                button.innerHTML = originalText;
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan saat menghapus data.', 'error');
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }

    // Search functionality
    function searchRegistrations() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('table tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Filter by status
    function filterByStatus(status) {
        const rows = document.querySelectorAll('table tbody tr');
        
        rows.forEach(row => {
            if (status === 'all') {
                row.style.display = '';
            } else {
                const statusBadge = row.querySelector('.status-badge');
                if (statusBadge && statusBadge.classList.contains(`status-${status}`)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    }

    // Export to CSV (basic implementation)
    function exportToCSV() {
        const rows = document.querySelectorAll('table tbody tr');
        let csvContent = "data:text/csv;charset=utf-8,";
        
        // Add headers
        const headers = ["ID", "Email", "Username", "Minecraft Type", "Discord", "Status", "Registration Date"];
        csvContent += headers.join(",") + "\n";
        
        // Add data
        rows.forEach(row => {
            if (row.style.display !== 'none') {
                const cells = row.querySelectorAll('td');
                const rowData = [];
                
                cells.forEach((cell, index) => {
                    if (index < 7) { // Only first 7 columns
                        let text = cell.textContent.trim();
                        // Remove status badge text and get clean status
                        if (index === 6) {
                            const statusBadge = cell.querySelector('.status-badge');
                            if (statusBadge) {
                                text = statusBadge.textContent.trim();
                            }
                        }
                        // Escape commas and quotes
                        text = text.replace(/"/g, '""');
                        rowData.push(`"${text}"`);
                    }
                });
                
                csvContent += rowData.join(",") + "\n";
            }
        });
        
        // Create download link
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "warlord_registrations.csv");
        document.body.appendChild(link);
        
        link.click();
        document.body.removeChild(link);
    }
</script>
</body>
</html>