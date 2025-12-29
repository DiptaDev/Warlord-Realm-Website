<?php
session_start();

// Konfigurasi database
define('DB_HOST', 'localhost');
define('DB_USER', 'warlord_realm_gallery');
define('DB_PASS', 'warlord_realm_gallery');
define('DB_NAME', 'warlord_realm_gallery');

// Koneksi database
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set timezone
date_default_timezone_set('Asia/Singapore');

// Fungsi untuk keamanan input
function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}

// Cek login status
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

// Fungsi untuk mendapatkan status badge
function getStatusBadge($status) {
    switch ($status) {
        case 'approved':
            return '<span class="status-badge status-approved"><i class="fas fa-check"></i> Approved</span>';
        case 'pending':
            return '<span class="status-badge status-pending"><i class="fas fa-clock"></i> Pending</span>';
        case 'rejected':
            return '<span class="status-badge status-rejected"><i class="fas fa-times"></i> Rejected</span>';
        default:
            return '<span class="status-badge status-pending"><i class="fas fa-clock"></i> Pending</span>';
    }
}
?>