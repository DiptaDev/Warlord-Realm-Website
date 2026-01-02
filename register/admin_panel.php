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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>

            * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #0f0f0f;
            color: #e0e0e0;
            overflow-x: hidden;
            position: relative;
            line-height: 1.6;
        }

        .ambient-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 50% 50%, rgba(255, 0, 0, 0.03) 0%, transparent 50%);
            z-index: -1;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }

        /* Header Styles */
        .admin-header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }

        .logo-container {
            width: 80px;
            height: 80px;
            margin: 0 auto 15px;
            border-radius: 50%;
            background: linear-gradient(45deg, #990000, #ff0000);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 5px 15px rgba(255, 0, 0, 0.2);
            border: 2px solid #ff3333;
            position: relative;
            overflow: hidden;
        }

        .logo-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .logo-text {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(45deg, #ff0000, #990000, #ff3333);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .admin-info {
            position: absolute;
            top: 0;
            right: 0;
            background: rgba(35, 35, 35, 0.8);
            padding: 10px 15px;
            border-radius: 6px;
            border-left: 3px solid #ff3333;
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(20, 20, 20, 0.95);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border: 1px solid #333;
            box-shadow: 0 5px 15px rgba(255, 0, 0, 0.1);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-total {
            color: #ff3333;
        }

        .stat-pending {
            color: #ffa500;
        }

        .stat-approved {
            color: #00ff00;
        }

        .stat-rejected {
            color: #ff4444;
        }

        /* Table Container Responsive */
        .table-container {
            background: rgba(20, 20, 20, 0.95);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(255, 0, 0, 0.1);
            border: 1px solid #333;
            margin-bottom: 20px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            /* Smooth scrolling on iOS */
        }

        /* Responsive Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            min-width: 800px;
            /* Minimum width for table */
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #333;
            white-space: nowrap;
            /* Prevent text wrapping */
        }

        th {
            background: rgba(255, 51, 51, 0.1);
            color: #ff6666;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        tr:hover {
            background: rgba(255, 51, 51, 0.05);
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending {
            background: rgba(255, 165, 0, 0.2);
            color: #ffa500;
            border: 1px solid #ffa500;
        }

        .status-approved {
            background: rgba(0, 255, 0, 0.2);
            color: #00ff00;
            border: 1px solid #00ff00;
        }

        .status-rejected {
            background: rgba(255, 68, 68, 0.2);
            color: #ff4444;
            border: 1px solid #ff4444;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.8rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-sm {
            padding: 4px 8px;
            font-size: 0.75rem;
        }

        .btn-approve {
            background: rgba(0, 255, 0, 0.2);
            color: #00ff00;
            border: 1px solid #00ff00;
        }

        .btn-reject {
            background: rgba(255, 68, 68, 0.2);
            color: #ff4444;
            border: 1px solid #ff4444;
        }

        .btn-pending {
            background: rgba(255, 165, 0, 0.2);
            color: #ffa500;
            border: 1px solid #ffa500;
        }

        .btn-delete {
            background: rgba(255, 0, 0, 0.2);
            color: #ff0000;
            border: 1px solid #ff0000;
        }

        .btn-view {
            background: rgba(51, 153, 255, 0.2);
            color: #3399ff;
            border: 1px solid #3399ff;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(255, 0, 0, 0.2);
        }

        /* Footer */
        .admin-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #333;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
        }

        .footer-link {
            color: #b0b0b0;
            text-decoration: none;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 15px;
            background: rgba(35, 35, 35, 0.7);
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .footer-link:hover {
            color: #ff3333;
            background: rgba(50, 50, 50, 0.7);
            transform: translateY(-1px);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
        }

        .modal-content {
            background: rgba(20, 20, 20, 0.95);
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            border: 1px solid #ff3333;
            box-shadow: 0 5px 30px rgba(255, 0, 0, 0.3);
        }

        .close {
            color: #ff3333;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #ff6666;
        }

        /* Modal Styles */
        .modal-content {
            background: rgba(20, 20, 20, 0.95);
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow: hidden;
            border: 1px solid #ff3333;
            box-shadow: 0 5px 30px rgba(255, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
        }

        .modal-header {
            padding-bottom: 20px;
            border-bottom: 1px solid #333;
            margin-bottom: 20px;
        }

        .modal-body {
            flex: 1;
            overflow-y: auto;
            max-height: calc(90vh - 150px);
            padding-right: 10px;
        }

        .modal-body::-webkit-scrollbar {
            width: 8px;
        }

        .modal-body::-webkit-scrollbar-track {
            background: rgba(255, 51, 51, 0.1);
            border-radius: 4px;
        }

        .modal-body::-webkit-scrollbar-thumb {
            background: #ff3333;
            border-radius: 4px;
        }

        .modal-body::-webkit-scrollbar-thumb:hover {
            background: #ff6666;
        }

        /* Base Styles */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        
        .stat-card {
            background: rgba(30, 30, 30, 0.7);
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid;
        }
        
        .stat-card:nth-child(1) { border-color: #7289da; }
        .stat-card:nth-child(2) { border-color: #ffaa00; }
        .stat-card:nth-child(3) { border-color: #00ff00; }
        .stat-card:nth-child(4) { border-color: #ff3333; }
        .stat-card:nth-child(5) { border-color: #00ccff; }
        .stat-card:nth-child(6) { border-color: #ffcc00; }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            display: inline-block;
            text-align: center;
            min-width: 80px;
        }
        
        .status-pending { background: #ffaa00; color: #000; }
        .status-approved { background: #00ff00; color: #000; }
        .status-rejected { background: #ff3333; color: #fff; }
        .status-interview { background: #00ccff; color: #000; }
        .status-trial { background: #ffcc00; color: #000; }
        
        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        
        .btn-sm {
            padding: 4px 8px;
            font-size: 0.8rem;
            white-space: nowrap;
        }
        
        .btn-interview { background: #00ccff; color: #000; }
        .btn-trial { background: #ffcc00; color: #000; }
        
        /* Table Styles */
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            min-width: 800px;
        }
        
        /* Modal Responsive */
        .modal-content {
            width: 95%;
            max-width: 900px;
            margin: 5% auto;
            max-height: 90vh;
            overflow: hidden;
        }
        
        #modalContent {
            max-height: 70vh;
            overflow-y: auto;
            padding-right: 10px;
        }
        
        /* Search and Filter Container */
        .filter-container {
            display: flex;
            gap: 10px;
            margin: 20px 0;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .search-input {
            flex-grow: 1;
            min-width: 200px;
            padding: 10px;
            background: rgba(30,30,30,0.7);
            border: 1px solid #444;
            color: #fff;
            border-radius: 5px;
        }
        
        .filter-select {
            padding: 10px;
            background: rgba(30,30,30,0.7);
            border: 1px solid #444;
            color: #fff;
            border-radius: 5px;
            min-width: 150px;
        }
        
        /* Footer Links */
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            padding: 15px;
        }
        
        /* ===== RESPONSIVE STYLES ===== */
        
        /* Large Desktop (1200px and above) */
        @media screen and (min-width: 1200px) {
            .container {
                max-width: 1400px;
                margin: 20px auto;
            }
        }
        
        /* Tablet Landscape (992px to 1199px) */
        @media screen and (max-width: 1199px) {
            .stats-container {
                grid-template-columns: repeat(3, 1fr);
            }
            
            .stat-number {
                font-size: 2rem;
            }
            
            .modal-content {
                width: 95%;
                margin: 10px auto;
            }
        }
        
        /* Tablet Portrait (768px to 991px) */
        @media screen and (max-width: 991px) {
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
            
            .stat-card {
                padding: 15px;
            }
            
            .stat-number {
                font-size: 1.8rem;
            }
            
            .admin-header {
                text-align: center;
                padding: 20px !important;
            }
            
            .logo-container {
                margin: 0 auto 15px !important;
                width: 80px !important;
                height: 80px !important;
            }
            
            .logo-image {
                width: 70px !important;
                height: 70px !important;
            }
            
            .logo-text {
                font-size: 1.8rem !important;
                margin-bottom: 10px !important;
            }
            
            .admin-info {
                margin: 10px auto !important;
                display: inline-block !important;
                padding: 8px 15px !important;
                font-size: 0.9rem !important;
            }
            
            .filter-container {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-input, .filter-select {
                width: 100%;
                min-width: unset;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 5px;
            }
            
            .action-buttons {
                flex-direction: column;
                min-width: 120px;
            }
            
            .btn-sm {
                width: 100%;
                text-align: center;
                justify-content: center;
                margin-bottom: 2px;
            }
            
            table {
                font-size: 0.9rem;
            }
            
            table th, table td {
                padding: 8px 5px !important;
            }
            
            .modal-content {
                padding: 20px;
                width: 98%;
            }
            
            #modalContent div[style*="grid-template-columns"] {
                grid-template-columns: 1fr !important;
                gap: 10px !important;
            }
        }
        
        /* Mobile Landscape (576px to 767px) */
        @media screen and (max-width: 767px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .stat-number {
                font-size: 1.5rem;
            }
            
            .container {
                padding: 10px !important;
                margin: 5px !important;
            }
            
            .logo-text {
                font-size: 1.5rem !important;
            }
            
            .admin-info {
                font-size: 0.8rem !important;
                padding: 6px 12px !important;
            }
            
            h2 {
                font-size: 1.3rem !important;
            }
            
            .footer-links {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            
            .footer-link {
                display: block;
                padding: 10px;
                margin: 5px 0;
            }
            
            /* Table mobile view */
            table thead {
                display: none;
            }
            
            table, table tbody, table tr, table td {
                display: block;
                width: 100%;
            }
            
            table tr {
                margin-bottom: 15px;
                border: 1px solid #444;
                border-radius: 8px;
                padding: 10px;
                background: rgba(30, 30, 30, 0.8);
            }
            
            table td {
                padding: 8px !important;
                border: none;
                position: relative;
                padding-left: 45% !important;
                text-align: left;
                min-height: 35px;
            }
            
            table td:before {
                content: attr(data-label);
                position: absolute;
                left: 10px;
                width: 40%;
                padding-right: 10px;
                font-weight: bold;
                color: #ff6666;
            }
            
            .action-buttons {
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .btn-sm {
                width: auto;
                flex: 1;
                min-width: 60px;
            }
        }
        
        /* Mobile Portrait (up to 575px) */
        @media screen and (max-width: 575px) {
            .stats-container {
                gap: 8px;
            }
            
            .stat-card {
                padding: 12px;
            }
            
            .stat-number {
                font-size: 1.3rem;
            }
            
            .logo-text {
                font-size: 1.3rem !important;
                line-height: 1.2 !important;
            }
            
            .logo-container {
                width: 70px !important;
                height: 70px !important;
            }
            
            .logo-image {
                width: 60px !important;
                height: 60px !important;
            }
            
            table td {
                padding-left: 50% !important;
                font-size: 0.85rem;
            }
            
            table td:before {
                width: 45%;
                font-size: 0.8rem;
            }
            
            .status-badge {
                min-width: 70px;
                padding: 3px 8px;
                font-size: 0.75rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn-sm {
                width: 100%;
                margin-bottom: 3px;
            }
            
            .modal-content {
                padding: 15px;
                width: 99%;
            }
            
            .modal-content h2 {
                font-size: 1.2rem !important;
            }
            
            #modalContent {
                max-height: 60vh;
            }
        }
        
        /* Very Small Devices (up to 375px) */
        @media screen and (max-width: 375px) {
            .stat-number {
                font-size: 1.1rem;
            }
            
            .stat-card {
                padding: 10px 5px;
            }
            
            .logo-text {
                font-size: 1.1rem !important;
            }
            
            table td {
                padding-left: 55% !important;
                font-size: 0.8rem;
            }
            
            table td:before {
                width: 50%;
                font-size: 0.75rem;
            }
            
            .filter-container {
                gap: 8px;
            }
            
            .search-input, .filter-select {
                padding: 8px;
                font-size: 0.9rem;
            }
        }
        
        /* Touch Device Optimizations */
        @media (hover: none) and (pointer: coarse) {
            .btn, .btn-sm {
                padding: 10px 15px;
                min-height: 44px; /* Minimum touch target size */
            }
            
            .action-buttons .btn-sm {
                min-height: 36px;
            }
            
            select, input, button {
                font-size: 16px; /* Prevents iOS zoom on focus */
            }
            
            .modal {
                padding-top: 20px;
            }
        }
        
        /* Print Styles */
        @media print {
            .admin-header, .filter-container, .action-buttons, .admin-footer {
                display: none !important;
            }
            
            table {
                width: 100%;
                font-size: 12pt;
            }
            
            .container {
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
                color: black !important;
            }
            
            .status-badge {
                border: 1px solid #000 !important;
                color: black !important;
                background: none !important;
            }
        }
    </style>
</head>
<body>
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

<script>
    // Responsive JavaScript enhancements
    function initResponsiveFeatures() {
        // Update table cells with data-label attributes for mobile view
        const tableHeaders = document.querySelectorAll('table thead th');
        const tableRows = document.querySelectorAll('table tbody tr');
        
        tableRows.forEach(row => {
            const cells = row.querySelectorAll('td');
            cells.forEach((cell, index) => {
                if (index < tableHeaders.length) {
                    const headerText = tableHeaders[index].textContent;
                    cell.setAttribute('data-label', headerText);
                }
            });
        });
        
        // Make status filter work
        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', function() {
                filterByStatus(this.value);
            });
        }
        
        // Make search input work with Enter key
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    searchApplications();
                }
            });
        }
        
        // Adjust modal for mobile
        window.addEventListener('resize', adjustModalLayout);
        adjustModalLayout();
    }
    
    function adjustModalLayout() {
        const modalContent = document.getElementById('modalContent');
        if (!modalContent) return;
        
        const screenWidth = window.innerWidth;
        const modalDivs = modalContent.querySelectorAll('div[style*="grid-template-columns"]');
        
        modalDivs.forEach(div => {
            if (screenWidth <= 768) {
                div.style.gridTemplateColumns = '1fr !important';
                div.style.gap = '15px !important';
            } else if (screenWidth <= 992) {
                div.style.gridTemplateColumns = 'repeat(2, 1fr) !important';
            }
        });
    }
    
    // Modal functionality
    const modal = document.getElementById('viewModal');
    const closeBtn = document.querySelector('.close');
    const modalContent = document.getElementById('modalContent');
    
    function viewApplication(data) {
        modalContent.innerHTML = `
            <div style="max-height: 70vh; overflow-y: auto; padding-right: 10px;">
                <div style="background: rgba(35,35,35,0.6); padding: 20px; border-radius: 8px; margin: 15px 0;">
                    <h3 style="color: #ff6666; margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 10px;">
                        <i class="fas fa-id-card"></i> Informasi Dasar
                    </h3>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 15px;">
                        <div><strong>Username Minecraft:</strong><br><strong style="color: #ff6666;">${data.mc_username}</strong></div>
                        <div><strong>Umur:</strong><br>${data.age} tahun</div>
                        <div><strong>Discord:</strong><br>${data.discord}</div>
                        <div><strong>Email:</strong><br>${data.email}</div>
                        <div><strong>Durasi Bermain:</strong><br>${data.play_duration}</div>
                        <div><strong>Waktu Bermain/Hari:</strong><br>${data.play_time}</div>
                        <div><strong>Jam Aktif:</strong><br>${data.active_hours}</div>
                        <div><strong>Level Skill:</strong><br>${data.skill_level}</div>
                    </div>
                    <div style="margin-top: 10px;">
                        <strong>Konsistensi:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.consistency}
                        </div>
                    </div>
                </div>

                <div style="background: rgba(35,35,35,0.6); padding: 20px; border-radius: 8px; margin: 15px 0;">
                    <h3 style="color: #ff6666; margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 10px;">
                        <i class="fas fa-graduation-cap"></i> Pengalaman & Keahlian
                    </h3>
                    <div style="margin-bottom: 15px;">
                        <strong>Pengalaman Server:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.server_experience}
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <strong>Keahlian:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.expertise || '<em style="color: #888;">Tidak diisi</em>'}
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <strong>Target Utama:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.main_target}
                        </div>
                    </div>
                    <div>
                        <strong>Tipe Kepribadian:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.personality_type}
                        </div>
                    </div>
                </div>

                <div style="background: rgba(35,35,35,0.6); padding: 20px; border-radius: 8px; margin: 15px 0;">
                    <h3 style="color: #ff6666; margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 10px;">
                        <i class="fas fa-brain"></i> Kepribadian & Sikap
                    </h3>
                    <div style="margin-bottom: 15px;">
                        <strong>Jika base dicuri:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.base_stolen}
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <strong>Sikap ke pemula:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.attitude_newbies}
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <strong>Konflik sebelumnya:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.past_conflict}
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <strong>Reaksi kehilangan:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.reaction_loss}
                        </div>
                    </div>
                    <div>
                        <strong>Definisi fair play:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.fair_play}
                        </div>
                    </div>
                </div>

                <div style="background: rgba(35,35,35,0.6); padding: 20px; border-radius: 8px; margin: 15px 0;">
                    <h3 style="color: #ff6666; margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 10px;">
                        <i class="fas fa-scale-balanced"></i> Kejujuran & Moral
                    </h3>
                    <div style="margin-bottom: 15px;">
                        <strong>Pelanggaran aturan:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.rule_violation}
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <strong>Jika menemukan bug:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.bug_response}
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <strong>Jika tak setuju admin:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.admin_disagreement}
                        </div>
                    </div>
                    <div>
                        <strong>Aturan terpenting:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.important_rule}
                        </div>
                    </div>
                </div>

                <div style="background: rgba(35,35,35,0.6); padding: 20px; border-radius: 8px; margin: 15px 0;">
                    <h3 style="color: #ff6666; margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 10px;">
                        <i class="fas fa-star"></i> Kelebihan & Kekurangan
                    </h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <strong>Kelebihan:</strong><br>
                            <div style="background: rgba(0,255,0,0.1); padding: 12px; border-radius: 6px; margin-top: 5px;">
                                ${data.strength}
                            </div>
                        </div>
                        <div>
                            <strong>Kekurangan:</strong><br>
                            <div style="background: rgba(255,0,0,0.1); padding: 12px; border-radius: 6px; margin-top: 5px;">
                                ${data.weakness}
                            </div>
                        </div>
                    </div>
                </div>

                <div style="background: rgba(35,35,35,0.6); padding: 20px; border-radius: 8px; margin: 15px 0;">
                    <h3 style="color: #ff6666; margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 10px;">
                        <i class="fas fa-file-signature"></i> Komitmen & Kontribusi
                    </h3>
                    <div style="margin-bottom: 15px;">
                        <strong>Alasan komitmen:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.commitment_reason}
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <strong>Kenapa harus diterima:</strong><br>
                        <div style="background: rgba(0,0,0,0.3); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.why_accept}
                        </div>
                    </div>
                    ${data.contribution_willingness ? `
                    <div style="margin-bottom: 15px;">
                        <strong>Kesediaan kontribusi:</strong><br>
                        <div style="background: rgba(255,200,0,0.1); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.contribution_willingness}
                        </div>
                    </div>
                    ` : ''}
                    ${data.contribution_type ? `
                    <div>
                        <strong>Jenis kontribusi:</strong><br>
                        <div style="background: rgba(255,200,0,0.1); padding: 12px; border-radius: 6px; margin-top: 5px;">
                            ${data.contribution_type}
                        </div>
                    </div>
                    ` : ''}
                </div>

                <div style="background: rgba(35,35,35,0.6); padding: 20px; border-radius: 8px; margin: 15px 0;">
                    <h3 style="color: #ff6666; margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 10px;">
                        <i class="fas fa-info-circle"></i> Informasi Admin
                    </h3>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
                        <div><strong>ID Aplikasi:</strong><br><strong>#${data.id}</strong></div>
                        <div><strong>Status:</strong><br><span class="status-badge status-${data.status}">${data.status}</span></div>
                        <div><strong>Tanggal:</strong><br>${new Date(data.application_date).toLocaleString('id-ID')}</div>
                    </div>
                    <div style="margin-top: 15px; display: flex; gap: 10px; flex-wrap: wrap; justify-content: center;">
                        ${data.status !== 'interview' ? `
                        <button type="button" class="btn btn-interview" onclick="updateStatus(${data.id}, 'interview')">
                            <i class="fas fa-comments"></i> Set Interview
                        </button>
                        ` : ''}
                        
                        ${data.status !== 'trial' ? `
                        <button type="button" class="btn btn-trial" onclick="updateStatus(${data.id}, 'trial')">
                            <i class="fas fa-user-clock"></i> Set Trial
                        </button>
                        ` : ''}
                        
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
                    </div>
                </div>
            </div>
        `;
        
        // Adjust modal layout for current screen size
        adjustModalLayout();
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

    // Update status function
    function updateStatus(applicationId, action) {
        const actionTexts = {
            'approve': 'approve',
            'reject': 'reject', 
            'interview': 'set interview',
            'trial': 'set trial',
            'pending': 'set pending'
        };

        if (!confirm(`Are you sure you want to ${actionTexts[action]} this application?`)) {
            return;
        }
        
        const buttons = document.querySelectorAll(`button[onclick*="updateStatus(${applicationId}, '${action}')"]`);
        
        buttons.forEach(button => {
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            button.disabled = true;
        });

        const formData = new FormData();
        formData.append('id', applicationId);
        formData.append('action', action);

        fetch('admin_panel.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat update status.');
            
            buttons.forEach(button => {
                button.innerHTML = button.getAttribute('data-original') || originalText;
                button.disabled = false;
            });
        });
    }

    // Search functionality
    function searchApplications() {
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

    // Initialize responsive features when page loads
    document.addEventListener('DOMContentLoaded', initResponsiveFeatures);
</script>
</body>
</html>