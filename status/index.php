<?php
// Konfigurasi server
$server_ip = "warlordrealm.elytra.top";
$api_url = "https://api.mcsrvstat.us/3/" . $server_ip;

// Cache system untuk mengurangi API calls
session_start();
$cache_key = 'server_status_data';
$cache_time = 30; // Cache selama 30 detik

// Cek apakah data sudah di-cache dan masih fresh
if (isset($_SESSION[$cache_key]) && 
    isset($_SESSION[$cache_key]['timestamp']) && 
    (time() - $_SESSION[$cache_key]['timestamp']) < $cache_time) {
    
    // Gunakan data dari cache
    $server_data = $_SESSION[$cache_key]['data'];
    $from_cache = true;
} else {
    // Ambil data baru dari API
    $server_data = fetchServerData($api_url);
    $from_cache = false;
    
    // Simpan ke cache
    $_SESSION[$cache_key] = [
        'data' => $server_data,
        'timestamp' => time()
    ];
}

// Fungsi untuk mengambil data dari API
function fetchServerData($api_url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        error_log("CURL Error: " . curl_error($ch));
        if (PHP_VERSION_ID < 80000) {
            curl_close($ch);
        }
        return false;
    }
    
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (PHP_VERSION_ID < 80000) {
        curl_close($ch);
    }
    
    if ($http_code === 200 && $response) {
        return json_decode($response, true);
    }
    
    return false;
}

// Fungsi untuk parsing kode warna Minecraft ke HTML
function parseMinecraftColors($text) {
    if (empty($text)) {
        return '<span style="color: #FFFFFF">Warlord Realm Minecraft Server</span>';
    }
    
    $colors = [
        '§0' => '<span style="color: #000000">', '§1' => '<span style="color: #0000AA">',
        '§2' => '<span style="color: #00AA00">', '§3' => '<span style="color: #00AAAA">',
        '§4' => '<span style="color: #AA0000">', '§5' => '<span style="color: #AA00AA">',
        '§6' => '<span style="color: #FFAA00">', '§7' => '<span style="color: #AAAAAA">',
        '§8' => '<span style="color: #555555">', '§9' => '<span style="color: #5555FF">',
        '§a' => '<span style="color: #55FF55">', '§b' => '<span style="color: #55FFFF">',
        '§c' => '<span style="color: #FF5555">', '§d' => '<span style="color: #FF55FF">',
        '§e' => '<span style="color: #FFFF55">', '§f' => '<span style="color: #FFFFFF">',
        '§k' => '<span class="minecraft-obfuscated">',
        '§l' => '<span style="font-weight: bold">',
        '§m' => '<span style="text-decoration: line-through">',
        '§n' => '<span style="text-decoration: underline">',
        '§o' => '<span style="font-style: italic">',
        '§r' => '</span><span style="color: #FFFFFF">',
    ];
    
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    
    foreach ($colors as $code => $html) {
        $text = str_replace($code, $html, $text);
    }
    
    $text = preg_replace('/§([0-9a-fk-or])/', '', $text);
    $text .= '</span>';
    
    return $text;
}

// Mulai output buffering
ob_start();

// Data untuk ditampilkan
$status = $server_data ? ($server_data['online'] ? 'online' : 'offline') : 'error';
$players_online = $server_data && $server_data['online'] ? ($server_data['players']['online'] ?? 0) : 0;
$players_max = $server_data && $server_data['online'] ? ($server_data['players']['max'] ?? 0) : 0;
$version = $server_data && $server_data['online'] ? ($server_data['version'] ?? 'Unknown') : 'Offline';
$motd_raw = $server_data && $server_data['online'] && isset($server_data['motd']['raw']) ? implode(' ', $server_data['motd']['raw']) : 'Warlord Realm Minecraft Server';
$players_list = $server_data && $server_data['online'] ? ($server_data['players']['list'] ?? []) : [];
$server_icon = $server_data && $server_data['online'] && isset($server_data['icon']) ? $server_data['icon'] : null;

// Timestamp untuk last updated
$last_updated = date('Y-m-d H:i:s');

// Tentukan cache status
$cache_status = $from_cache ? 'cached' : 'fresh';
$cache_age = $from_cache ? (time() - $_SESSION[$cache_key]['timestamp']) : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warlord Realm | Server Status</title>
    <link rel="shortcut icon" href="/asset/logo-min.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SEO Meta Tag not tested yet-->
    <meta name="description"
        content="Check Warlord Realm Minecraft server status">
    <meta name="keywords"
        content="Minecraft, Warlord Realm, Indonesia Minecraft Server, Survival, Bedrock, Java Edition, Semi Anarchy">
    <meta name="author" content="Warlord Network by dipta14">
    <!-- Open Graph for Social Media not tested yet-->
    <meta property="og:title" content="Warlord Realm - Status">
    <meta property="og:description"
        content="Check Warlord Realm Minecraft server status">
    <meta property="og:image" content="/asset/logo-min.png">
    <meta property="og:url" content="https://warlordrealm.ct.ws">
    <meta property="og:type" content="website">
    <!-- Twitter Card not tested yet-->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Warlord Realm - Status">
    <meta name="twitter:description"
        content="Check Warlord Realm Minecraft server status">
    <meta name="twitter:image" content="/asset/Twitter_Card_Image.png">
    <style>
        /* Reset dan Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        body {
            background-color: #0a0a0a;
            color: #f0f0f0;
            overflow-x: hidden;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            background:
                radial-gradient(circle at 20% 30%, rgba(120, 0, 0, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(120, 0, 0, 0.1) 0%, transparent 50%),
                linear-gradient(135deg, #0a0a0a 0%, #111111 100%);
        }

        /* Container Utama */
        .status-container {
            max-width: 1000px;
            width: 100%;
            background: rgba(10, 10, 10, 0.95);
            border-radius: 12px;
            padding: 40px;
            box-shadow:
                0 0 0 1px rgba(255, 0, 0, 0.1),
                0 10px 40px rgba(0, 0, 0, 0.5),
                inset 0 0 0 1px rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 0, 0, 0.15);
            animation: fadeIn 0.6s ease-out;
            position: relative;
            overflow: hidden;
        }

        .status-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg,
                    transparent 0%,
                    rgba(255, 0, 0, 0.3) 50%,
                    transparent 100%);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Layout Grid */
        .layout-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            .layout-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Left Panel */
        .left-panel {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        /* Server Header */
        .server-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 10px;
        }

        .server-icon-container {
            width: 70px;
            height: 70px;
            border-radius: 10px;
            border: 2px solid rgba(255, 0, 0, 0.3);
            background: rgba(20, 20, 20, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .server-icon-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .default-icon {
            font-size: 2rem;
            color: #ff3333;
        }

        .server-title-section h1 {
            font-size: 2.2rem;
            font-weight: 800;
            background: linear-gradient(45deg, #ff3333, #990000);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            line-height: 1.2;
            letter-spacing: -0.5px;
        }

        .server-subtitle {
            color: #888;
            font-size: 0.95rem;
            margin-top: 5px;
        }

        /* Server Address */
        .server-address {
            background: rgba(20, 20, 20, 0.6);
            border-radius: 8px;
            padding: 15px;
            border: 1px solid rgba(255, 0, 0, 0.1);
        }

        .address-label {
            color: #aaa;
            font-size: 0.9rem;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .address-value {
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 1.2rem;
            color: #ff3333;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 5px;
            border: 1px solid rgba(255, 0, 0, 0.2);
        }

        .copy-btn {
            margin-left: auto;
            background: rgba(255, 0, 0, 0.1);
            border: 1px solid rgba(255, 0, 0, 0.3);
            color: #ff6666;
            padding: 5px 12px;
            border-radius: 4px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .copy-btn:hover {
            background: rgba(255, 0, 0, 0.2);
            transform: translateY(-1px);
        }

        /* Status Card */
        .status-card {
            background: rgba(20, 20, 20, 0.6);
            border-radius: 10px;
            padding: 20px;
            border: 1px solid rgba(255, 0, 0, 0.1);
            transition: all 0.3s;
        }

        .status-card:hover {
            border-color: rgba(255, 0, 0, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .status-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .status-header h3 {
            color: #f0f0f0;
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-indicator {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            transition: all 0.3s;
        }

        .status-dot.online {
            background: #00cc00;
            box-shadow: 0 0 10px rgba(0, 204, 0, 0.5);
            animation: pulseOnline 2s infinite;
        }

        .status-dot.offline {
            background: #cc0000;
            box-shadow: 0 0 10px rgba(204, 0, 0, 0.5);
        }

        .status-dot.error {
            background: #cc6600;
            box-shadow: 0 0 10px rgba(204, 102, 0, 0.5);
            animation: pulseError 1.5s infinite;
        }

        @keyframes pulseOnline {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        @keyframes pulseError {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .status-text {
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .status-text.online { color: #00cc00; }
        .status-text.offline { color: #cc0000; }
        .status-text.error { color: #cc6600; }

        .status-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .detail-label {
            color: #888;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-value {
            color: #f0f0f0;
            font-size: 1.1rem;
            font-weight: 600;
            font-family: 'Consolas', monospace;
        }

        /* Right Panel */
        .right-panel {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        /* MOTD Section */
        .motd-section {
            background: rgba(20, 20, 20, 0.6);
            border-radius: 10px;
            padding: 25px;
            border: 1px solid rgba(255, 0, 0, 0.1);
            transition: all 0.3s;
        }

        .motd-section:hover {
            border-color: rgba(255, 0, 0, 0.3);
            transform: translateY(-2px);
        }

        .motd-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            color: #ff3333;
        }

        .motd-header h3 {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .motd-content {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 8px;
            padding: 20px;
            min-height: 80px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 1.1rem;
            line-height: 1.5;
        }

        .minecraft-obfuscated {
            background: linear-gradient(90deg, transparent, #fff, transparent);
            background-size: 200% 100%;
            animation: obfuscate 1s infinite linear;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        @keyframes obfuscate {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Players Section */
        .players-section {
            background: rgba(20, 20, 20, 0.6);
            border-radius: 10px;
            padding: 25px;
            border: 1px solid rgba(255, 0, 0, 0.1);
            transition: all 0.3s;
        }

        .players-section:hover {
            border-color: rgba(255, 0, 0, 0.3);
            transform: translateY(-2px);
        }

        .players-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .players-header h3 {
            color: #f0f0f0;
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .players-count {
            background: rgba(255, 0, 0, 0.1);
            color: #ff6666;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            border: 1px solid rgba(255, 0, 0, 0.2);
        }

        .players-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-height: 300px;
            overflow-y: auto;
            padding-right: 5px;
        }

        /* Custom scrollbar */
        .players-list::-webkit-scrollbar {
            width: 6px;
        }

        .players-list::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 3px;
        }

        .players-list::-webkit-scrollbar-thumb {
            background: rgba(255, 0, 0, 0.3);
            border-radius: 3px;
        }

        .players-list::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 0, 0, 0.5);
        }

        .player-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.2s;
            cursor: pointer;
        }

        .player-item:hover {
            background: rgba(255, 0, 0, 0.05);
            border-color: rgba(255, 0, 0, 0.2);
            transform: translateX(5px);
        }

        .player-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #990000, #ff3333);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .player-info {
            flex-grow: 1;
        }

        .player-name {
            color: #f0f0f0;
            font-weight: 500;
            font-size: 1rem;
        }

        .no-players {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            border: 1px dashed rgba(255, 255, 255, 0.1);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .btn {
            flex: 1;
            padding: 14px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(45deg, #990000, #ff0000);
            color: white;
            box-shadow: 0 5px 15px rgba(255, 0, 0, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(255, 0, 0, 0.4);
        }

        .btn-secondary {
            background: transparent;
            color: #ff3333;
            border: 2px solid #ff3333;
        }

        .btn-secondary:hover {
            background: rgba(255, 51, 51, 0.1);
            transform: translateY(-5px);
        }

        /* Footer */
        .status-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .last-updated {
            color: #666;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .refresh-timer {
            background: rgba(255, 0, 0, 0.1);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            border: 1px solid rgba(255, 0, 0, 0.2);
            color: #ff6666;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .footer-link {
            color: #888;
            text-decoration: none;
            font-size: 0.9rem;
            padding: 8px 16px;
            border-radius: 6px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .footer-link:hover {
            color: #ff3333;
            background: rgba(255, 0, 0, 0.05);
            border-color: rgba(255, 0, 0, 0.2);
            transform: translateY(-1px);
        }

        /* Loading Overlay Animation */
        .loading-overlay {
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        .loading-overlay[style*="display: block"] {
            opacity: 1;
        }

        /* Loading Overlay (Hidden by default) */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(10, 10, 10, 0.98);
            display: none;
            z-index: 9999;
            backdrop-filter: blur(15px);
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        .loading-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            width: 100%;
            max-width: 300px;
            padding: 30px;
        }

        .loading-spinner {
            width: 80px;
            height: 80px;
            margin: 0 auto 25px;
            border: 4px solid rgba(255, 0, 0, 0.1);
            border-radius: 50%;
            border-top-color: #ff3333;
            border-right-color: #990000;
            border-bottom-color: #ff6666;
            border-left-color: #cc0000;
            animation: spin 1.5s ease-in-out infinite;
            position: relative;
        }

        .loading-spinner::after {
            content: '';
            position: absolute;
            top: -8px;
            left: -8px;
            right: -8px;
            bottom: -8px;
            border-radius: 50%;
            border: 4px solid transparent;
            border-top-color: rgba(255, 51, 51, 0.3);
            animation: spinReverse 2s ease-in-out infinite;
        }

        .loading-text {
            color: #ff3333;
            font-size: 1.2rem;
            font-weight: 600;
            text-align: center;
            margin-top: 20px;
            letter-spacing: 0.5px;
            text-shadow: 0 0 10px rgba(255, 51, 51, 0.3);
        }

        .loading-subtext {
            color: #888;
            font-size: 0.9rem;
            margin-top: 10px;
            text-align: center;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes spinReverse {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(-360deg); }
        }

        .loading-overlay[style*="display: block"] {
            opacity: 1;
        }

        /* Cache Indicator (Hidden by default) */
        .cache-indicator {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.3);
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            color: #666;
            border: 1px solid rgba(255, 0, 0, 0.1);
            display: none;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .status-container {
                padding: 25px;
            }
            
            .server-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .footer-links {
                flex-direction: column;
                align-items: center;
            }
            
            .footer-link {
                width: 100%;
                max-width: 200px;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .status-container {
                padding: 20px;
            }
            
            .status-details {
                grid-template-columns: 1fr;
            }
            
            .server-title-section h1 {
                font-size: 1.8rem;
            }
            
            .address-value {
                font-size: 1rem;
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .copy-btn {
                align-self: flex-end;
            }
        }

        /* Interactive Effects */
        .interactive-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .interactive-card:hover {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        /* Smooth transitions */
        * {
            transition: background-color 0.3s, border-color 0.3s, transform 0.3s, box-shadow 0.3s;
        }
    </style>
</head>

<body oncontextmenu="return false" ondragstart="return false;" ondrop="return false;">
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <div class="loading-text" id="loadingText">Updating server status...</div>
            <div class="loading-subtext" id="loadingSubtext">Please wait a moment</div>
        </div>
    </div>

    <!-- Cache Indicator -->
    <div class="cache-indicator" id="cacheIndicator">
        <?php echo $cache_status === 'cached' ? "Cached (" . $cache_age . "s ago)" : "Live"; ?>
    </div>

    <div class="status-container">
        <!-- Layout Grid -->
        <div class="layout-grid">
            <!-- Left Panel -->
            <div class="left-panel">
                <!-- Server Header -->
                <div class="server-header">
                    <div class="server-icon-container">
                        <?php if($server_icon): ?>
                            <?php 
                            $icon_data = $server_icon;
                            if (strpos($icon_data, 'data:image/png;base64,') === 0) {
                                echo '<img src="' . htmlspecialchars($icon_data) . '" alt="Server Icon">';
                            } else {
                                echo '<div class="default-icon"><i class="fas fa-server"></i></div>';
                            }
                            ?>
                        <?php else: ?>
                            <div class="default-icon">
                                <i class="fas fa-crown"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="server-title-section">
                        <h1>WARLORD REALM</h1>
                        <p class="server-subtitle">Minecraft Vanilla Survial Server</p>
                    </div>
                </div>

                <!-- Server Address -->
                <div class="server-address interactive-card">
                    <div class="address-label">Server Address</div>
                    <div class="address-value">
                        <span id="serverAddress"><?php echo htmlspecialchars($server_ip); ?></span>
                        <button class="copy-btn" onclick="copyAddress()">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                </div>

                <!-- Status Card -->
                <div class="status-card interactive-card">
                    <div class="status-header">
                        <h3><i class="fas fa-chart-bar"></i> Server Status</h3>
                        <div class="status-indicator">
                            <div class="status-dot <?php echo $status; ?>"></div>
                            <div class="status-text <?php echo $status; ?>">
                                <?php 
                                if($status === 'online') echo 'Online';
                                elseif($status === 'offline') echo 'Offline';
                                else echo 'Error';
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="status-details">
                        <div class="detail-item">
                            <div class="detail-label">Players</div>
                            <div class="detail-value" id="playersCount"><?php echo $players_online; ?> / <?php echo $players_max; ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Version</div>
                            <div class="detail-value" id="serverVersion"><?php echo htmlspecialchars($version); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Platform</div>
                            <div class="detail-value">Java & Bedrock</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Uptime</div>
                            <div class="detail-value"><?php echo $status === 'online' ? 'Active' : 'Offline'; ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel -->
            <div class="right-panel">
                <!-- MOTD Section -->
                <div class="motd-section interactive-card">
                    <div class="motd-header">
                        <i class="fas fa-quote-left"></i>
                        <h3>Server MOTD</h3>
                    </div>
                    <div class="motd-content" id="motdContent">
                        <?php if($status === 'online'): ?>
                            <?php echo parseMinecraftColors($motd_raw); ?>
                        <?php else: ?>
                            <div style="color: #666; text-align: center; padding: 20px;">
                                <i class="fas fa-exclamation-circle"></i><br>
                                Server is currently offline
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Players Section -->
                <div class="players-section interactive-card">
                    <div class="players-header">
                        <h3><i class="fas fa-users"></i> Online Players</h3>
                        <div class="players-count" id="onlineCount"><?php echo $players_online; ?> online</div>
                    </div>
                    
                    <div class="players-list" id="playersList">
                        <?php if($status === 'online' && !empty($players_list)): ?>
                            <?php foreach($players_list as $player): ?>
                                <?php if(isset($player['name'])): ?>
                                <div class="player-item" onclick="copyPlayerName('<?php echo addslashes($player['name']); ?>')">
                                    <div class="player-avatar">
                                        <?php echo strtoupper(substr($player['name'], 0, 1)); ?>
                                    </div>
                                    <div class="player-info">
                                        <div class="player-name"><?php echo htmlspecialchars($player['name']); ?></div>
                                    </div>
                                    <i class="fas fa-copy" style="color: #666; font-size: 0.9rem;"></i>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-players">
                                <?php if($status === 'online'): ?>
                                    <i class="fas fa-user-slash"></i><br>
                                    No players currently online
                                <?php else: ?>
                                    <i class="fas fa-server"></i><br>
                                    Server is offline
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="/" class="btn btn-primary">
                <i class="fas fa-home"></i> Back to Home
            </a>
            <button class="btn btn-secondary" onclick="refreshData()">
                <i class="fas fa-sync-alt"></i> Refresh Now
            </button>
        </div>

        <!-- Footer -->
        <div class="status-footer">
            <div class="last-updated">
                <i class="fas fa-clock"></i>
                Last updated: <span id="lastUpdatedText"><?php echo $last_updated; ?></span>
                <span class="refresh-timer">
                    <i class="fas fa-sync"></i> Auto-refresh in <span id="countdown">30</span>s
                </span>
            </div>
            
            <div class="footer-links">
                <a href="../" class="footer-link">
                    <i class="fas fa-home"></i> Home
                </a>
                
                <a href="../register" class="footer-link">
                    <i class="fas fa-file-signature"></i> Register
                </a>

                <a href="../gallery" class="footer-link">
                    <i class="fa-solid fa-camera"></i> Gallery
                </a>

                <a target="_blank" href="../discord" class="footer-link">
                    <i class="fab fa-discord"></i> Discord
                </a>

                <a href="../help" class="footer-link">
                    <i class="fas fa-question-circle"></i> Help
                </a>
            </div>
        </div>
    </div>

    <script>
        // Initial state
        let isRefreshing = false;
        let refreshInterval = 30;
        let countdown = refreshInterval;
        let cacheAge = <?php echo $cache_age; ?>;
        let isCached = <?php echo $cache_status === 'cached' ? 'true' : 'false'; ?>;
        
        // Elements
        const countdownElement = document.getElementById('countdown');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const loadingText = document.getElementById('loadingText');
        const loadingSubtext = document.getElementById('loadingSubtext');
        const cacheIndicator = document.getElementById('cacheIndicator');
        const lastUpdatedText = document.getElementById('lastUpdatedText');
        const playersCount = document.getElementById('playersCount');
        const serverVersion = document.getElementById('serverVersion');
        const motdContent = document.getElementById('motdContent');
        const onlineCount = document.getElementById('onlineCount');
        const playersList = document.getElementById('playersList');

        // Show cache indicator (debug only)
        if (isCached) {
            cacheIndicator.style.display = 'block';
            cacheIndicator.textContent = 'Cached (' + cacheAge + 's ago)';
        }

        // Countdown timer
        function updateCountdown() {
            countdown--;
            if (countdown <= 0) {
                countdown = refreshInterval;
                refreshData(); // Auto-refresh
            }
            countdownElement.textContent = countdown;
        }

        setInterval(updateCountdown, 1000);

        // Refresh data function (AJAX)
        function refreshData() {
            if (isRefreshing) return;
            
            isRefreshing = true;
            countdown = refreshInterval + 5; // Reset countdown with buffer
            
            // Show loading overlay with fade in animation
            loadingText.textContent = 'Updating server status...';
            loadingSubtext.textContent = 'Fetching latest data from server';

            // Fade in loading overlay
            loadingOverlay.style.display = 'block';
            setTimeout(() => {
                loadingOverlay.style.opacity = '1';
            }, 10);

            // Disable refresh button
            const refreshBtn = document.querySelector('.btn-secondary');
            const originalBtnHTML = refreshBtn.innerHTML;
            refreshBtn.disabled = true;
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';

            // AJAX request to update data
            const startTime = Date.now();

            fetch('?refresh=true', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                cache: 'no-cache'
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                const elapsedTime = Date.now() - startTime;

                if (data.success) {
                    // Update loading text with success message
                    loadingText.textContent = 'Status updated!';
                    loadingSubtext.textContent = 'Applying changes...';

                    // Small delay to show success message
                    setTimeout(() => {
                        updateUI(data.data);
                        hideLoadingOverlay();
                        showNotification('✓ Status updated successfully!');
                    }, 500);
                } else {
                    throw new Error(data.error || 'Failed to update');
                }
            })
            .catch(error => {
                console.error('Refresh error:', error);
                loadingText.textContent = 'Update failed';
                loadingSubtext.textContent = 'Check your connection';

                setTimeout(() => {
                    hideLoadingOverlay();
                    showNotification('✗ Failed to update status', 'error');
                }, 1500);
            })
            .finally(() => {
                // Re-enable refresh button
                setTimeout(() => {
                    refreshBtn.disabled = false;
                    refreshBtn.innerHTML = originalBtnHTML;
                    isRefreshing = false;
                }, 1000);
            });
        }

        // Function to hide loading overlay
        function hideLoadingOverlay() {
            // Fade out animation
            loadingOverlay.style.opacity = '0';
            setTimeout(() => {
                loadingOverlay.style.display = 'none';
            }, 300);
        }

        // Function to show loading overlay
        function showLoadingOverlay(message = 'Loading...', submessage = 'Please wait') {
            loadingText.textContent = message;
            loadingSubtext.textContent = submessage;

            loadingOverlay.style.display = 'block';
            setTimeout(() => {
                loadingOverlay.style.opacity = '1';
            }, 10);
        }

        // Update UI with new data
        function updateUI(data) {
            // Update status indicator
            const statusDot = document.querySelector('.status-dot');
            const statusText = document.querySelector('.status-text');
            
            statusDot.className = 'status-dot ' + data.status;
            statusText.className = 'status-text ' + data.status;
            statusText.textContent = data.status === 'online' ? 'Online' : 
                                   data.status === 'offline' ? 'Offline' : 'Error';
            
            // Update players count
            playersCount.textContent = data.players_online + ' / ' + data.players_max;
            onlineCount.textContent = data.players_online + ' online';
            
            // Update version
            serverVersion.textContent = data.version;
            
            // Update MOTD
            motdContent.innerHTML = data.motd_html;
            
            // Update players list
            updatePlayersList(data.players_list, data.status);
            
            // Update last updated time
            const now = new Date();
            lastUpdatedText.textContent = now.toLocaleString();
            
            // Update cache indicator
            if (data.cache_status === 'cached') {
                cacheIndicator.style.display = 'block';
                cacheIndicator.textContent = 'Cached (' + data.cache_age + 's ago)';
            } else {
                cacheIndicator.style.display = 'none';
            }
        }

        // Update players list
        function updatePlayersList(players, status) {
            if (status === 'online' && players && players.length > 0) {
                let playersHTML = '';
                players.forEach(player => {
                    const escapedName = player.name.replace(/'/g, "\\'");
                    playersHTML += `
                        <div class="player-item" onclick="copyPlayerName('${escapedName}')">
                            <div class="player-avatar">
                                ${player.name.charAt(0).toUpperCase()}
                            </div>
                            <div class="player-info">
                                <div class="player-name">${escapeHtml(player.name)}</div>
                            </div>
                            <i class="fas fa-copy" style="color: #666; font-size: 0.9rem;"></i>
                        </div>
                    `;
                });
                playersList.innerHTML = playersHTML;
            } else {
                playersList.innerHTML = `
                    <div class="no-players">
                        ${status === 'online' ? 
                            '<i class="fas fa-user-slash"></i><br>No players currently online' : 
                            '<i class="fas fa-server"></i><br>Server is offline'}
                    </div>
                `;
            }
        }

        // Escape HTML for security
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Copy functions
        function copyAddress() {
            const address = "<?php echo addslashes($server_ip); ?>";
            copyToClipboard(address, "Server address copied!");
        }

        function copyPlayerName(name) {
            copyToClipboard(name, "Player name copied!");
        }

        function copyToClipboard(text, message) {
            if (!navigator.clipboard) {
                fallbackCopyToClipboard(text, message);
                return;
            }
            
            navigator.clipboard.writeText(text).then(() => {
                showNotification(message || 'Copied to clipboard!');
            }).catch(err => {
                console.error('Failed to copy:', err);
                fallbackCopyToClipboard(text, message);
            });
        }

        function fallbackCopyToClipboard(text, message) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            textArea.style.left = "-999999px";
            textArea.style.top = "-999999px";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                showNotification(message || 'Copied to clipboard!');
            } catch (err) {
                showNotification('Failed to copy text');
            }
            
            document.body.removeChild(textArea);
        }

        // Notification system
        function showNotification(message, type = 'success') {
            const existing = document.querySelector('.status-notification');
            if (existing) {
                existing.remove();
            }
            
            const notification = document.createElement('div');
            notification.className = 'status-notification';
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: rgba(10, 10, 10, 0.95);
                color: ${type === 'error' ? '#ff6666' : '#00cc00'};
                padding: 15px 25px;
                border-radius: 8px;
                z-index: 10000;
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.5);
                animation: slideIn 0.3s ease-out;
                display: flex;
                align-items: center;
                gap: 10px;
                border: 1px solid ${type === 'error' ? 'rgba(204, 0, 0, 0.3)' : 'rgba(0, 204, 0, 0.3)'};
                backdrop-filter: blur(10px);
                max-width: 300px;
            `;
            notification.innerHTML = '<i class="fas fa-' + (type === 'error' ? 'exclamation-circle' : 'check-circle') + '"></i> ' + message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => notification.remove(), 300);
            }, 2000);
        }

        // Add animation styles
        if (!document.getElementById('notification-styles')) {
            const style = document.createElement('style');
            style.id = 'notification-styles';
            style.textContent = `
                @keyframes slideIn {
                    from { 
                        transform: translateX(100%) translateY(-20px); 
                        opacity: 0; 
                    }
                    to { 
                        transform: translateX(0) translateY(0); 
                        opacity: 1; 
                    }
                }
                @keyframes slideOut {
                    from { 
                        transform: translateX(0) translateY(0); 
                        opacity: 1; 
                    }
                    to { 
                        transform: translateX(100%) translateY(-20px); 
                        opacity: 0; 
                    }
                }
            `;
            document.head.appendChild(style);
        }

        // Interactive hover effects
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.interactive-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.style.transform = 'translateY(-5px) rotateX(2deg)';
                });
                
                card.addEventListener('mouseleave', () => {
                    card.style.transform = 'translateY(0) rotateX(0)';
                });
            });
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key === 'r' && !e.ctrlKey && !e.metaKey) {
                e.preventDefault();
                refreshData();
            }
            
            if (e.key === 'Escape') {
                window.location.href = '/';
            }
        });

        // Initial page load complete
        document.addEventListener('DOMContentLoaded', function() {
            // Page is fully loaded, hide any initial loading
            setTimeout(() => {
                hideLoadingOverlay();
            }, 300);

            // Set initial opacity for loading overlay
            loadingOverlay.style.opacity = '0';
        });
    </script>
</body>

</html>
<?php
// Handle AJAX refresh requests
if (isset($_GET['refresh']) && $_GET['refresh'] === 'true') {
    // Clear cache and fetch fresh data
    if (isset($_SESSION['server_status_data'])) {
        unset($_SESSION['server_status_data']);
    }
    
    $fresh_data = fetchServerData($api_url);
    
    // Prepare response data
    $response_data = [
        'success' => true,
        'data' => [
            'status' => $fresh_data ? ($fresh_data['online'] ? 'online' : 'offline') : 'error',
            'players_online' => $fresh_data && $fresh_data['online'] ? ($fresh_data['players']['online'] ?? 0) : 0,
            'players_max' => $fresh_data && $fresh_data['online'] ? ($fresh_data['players']['max'] ?? 0) : 0,
            'version' => $fresh_data && $fresh_data['online'] ? ($fresh_data['version'] ?? 'Unknown') : 'Offline',
            'motd_html' => $fresh_data && $fresh_data['online'] && isset($fresh_data['motd']['raw']) ? 
                          parseMinecraftColors(implode(' ', $fresh_data['motd']['raw'])) : 
                          '<span style="color: #FFFFFF">Warlord Realm Minecraft Server</span>',
            'players_list' => $fresh_data && $fresh_data['online'] ? ($fresh_data['players']['list'] ?? []) : [],
            'cache_status' => 'fresh',
            'cache_age' => 0,
            'timestamp' => time()
        ]
    ];
    
    // Save to cache
    $_SESSION['server_status_data'] = [
        'data' => $fresh_data,
        'timestamp' => time()
    ];
    
    // Output JSON response
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode($response_data);
    exit;
}

ob_end_flush();
?>