<?php
// Konfigurasi server
$server_ip = "basic-7.alstore.space:22046";
$api_urls = [
    'mcsrvstat' => "https://api.mcsrvstat.us/3/" . $server_ip,
    'minecraftpinger' => "https://api.minecraftpinger.com/ping/" . $server_ip,
    'mcapi' => "https://api.mcsrvstat.us/2/" . $server_ip, // Versi 2 kadang lebih baik
];

// Ambil data dari multiple API untuk hasil yang lebih baik
$server_data = fetchServerDataMultiAPI($api_urls, $server_ip);
$from_cache = false;
$cache_age = 0;

// Fungsi untuk mengambil data dari multiple API
function fetchServerDataMultiAPI($api_urls, $server_ip) {
    $results = [];
    
    // Try mcsrvstat v3 first (utama)
    $data1 = fetchAPI($api_urls['mcsrvstat']);
    if ($data1) {
        $results['mcsrvstat'] = $data1;
    }
    
    // Try minecraftpinger sebagai fallback
    $data2 = fetchAPI($api_urls['minecraftpinger']);
    if ($data2) {
        $results['minecraftpinger'] = $data2;
    }
    
    // Try mcsrvstat v2 sebagai fallback ketiga
    $data3 = fetchAPI($api_urls['mcapi']);
    if ($data3) {
        $results['mcapi'] = $data3;
    }
    
    // Pilih data terbaik
    return selectBestData($results, $server_ip);
}

// Fungsi untuk mengambil data dari API tunggal
function fetchAPI($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        // error_log("CURL Error for {$url}: " . curl_error($ch));
        return null;
    }
    
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($http_code === 200 && $response) {
        return json_decode($response, true);
    }
    
    return null;
}

// Fungsi untuk memilih data terbaik dari multiple API
function selectBestData($results, $server_ip) {
    if (empty($results)) {
        return null;
    }
    
    // Prioritaskan data yang memiliki player list
    foreach ($results as $source => $data) {
        if (isset($data['online']) && $data['online'] === true) {
            $has_player_list = false;
            
            // Cek berbagai format player list
            if (isset($data['players']['list']) && is_array($data['players']['list']) && count($data['players']['list']) > 0) {
                $has_player_list = true;
            } elseif (isset($data['players']['sample']) && is_array($data['players']['sample']) && count($data['players']['sample']) > 0) {
                $has_player_list = true;
                // Konversi sample ke format list
                $data['players']['list'] = $data['players']['sample'];
            } elseif (isset($data['players']) && is_array($data['players'])) {
                // Minecraftpinger format
                $players = [];
                foreach ($data['players'] as $player) {
                    if (is_array($player) && isset($player['name'])) {
                        $players[] = $player;
                    } elseif (is_string($player)) {
                        $players[] = ['name' => $player];
                    }
                }
                if (count($players) > 0) {
                    $data['players']['list'] = $players;
                    $has_player_list = true;
                }
            }
            
            if ($has_player_list) {
                error_log("Using {$source} API - Has player list with " . count($data['players']['list']) . " players");
                return $data;
            }
        }
    }
    
    // Jika tidak ada yang punya player list, gunakan yang pertama yang online
    foreach ($results as $source => $data) {
        if (isset($data['online']) && $data['online'] === true) {
            error_log("Using {$source} API - No player list available");
            return $data;
        }
    }
    
    // Return data pertama yang ada
    return reset($results);
}

// Fungsi untuk mendapatkan player head dari UUID atau nama
function getPlayerHead($player_name, $player_uuid = null) {
    // Prioritaskan UUID jika ada
    if ($player_uuid) {
        // Format UUID tanpa dash
        $uuid = str_replace('-', '', $player_uuid);
        return "https://mc-heads.net/avatar/{$uuid}/36";
    }
    
    // Gunakan nama sebagai fallback
    return "https://mc-heads.net/avatar/{$player_name}/36";
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

// Handle MOTD dari berbagai format API
$motd_raw = 'Warlord Realm Minecraft Server';
if ($server_data && $server_data['online']) {
    if (isset($server_data['motd']['raw'])) {
        $motd_raw = is_array($server_data['motd']['raw']) ? implode(' ', $server_data['motd']['raw']) : $server_data['motd']['raw'];
    } elseif (isset($server_data['motd']['clean'])) {
        $motd_raw = is_array($server_data['motd']['clean']) ? implode(' ', $server_data['motd']['clean']) : $server_data['motd']['clean'];
    } elseif (isset($server_data['description'])) {
        $motd_raw = is_array($server_data['description']) ? implode(' ', $server_data['description']) : $server_data['description'];
    }
}

$server_icon = $server_data && $server_data['online'] && isset($server_data['icon']) ? $server_data['icon'] : null;

// Handle players list dengan lebih baik - gunakan multiple source
$players_list = [];
$has_player_list = false;
$player_list_source = 'none';

if ($server_data && $server_data['online'] && isset($server_data['players'])) {
    // Cek berbagai format player list dari berbagai API
    if (isset($server_data['players']['list']) && is_array($server_data['players']['list']) && count($server_data['players']['list']) > 0) {
        $players_list = $server_data['players']['list'];
        $has_player_list = true;
        $player_list_source = 'list';
    } 
    // Cek untuk format sample
    elseif (isset($server_data['players']['sample']) && is_array($server_data['players']['sample']) && count($server_data['players']['sample']) > 0) {
        $players_list = $server_data['players']['sample'];
        $has_player_list = true;
        $player_list_source = 'sample';
    }
    // Format minecraftpinger
    elseif (isset($server_data['players']) && is_array($server_data['players']) && !isset($server_data['players']['online'])) {
        // Ini kemungkinan format minecraftpinger langsung
        foreach ($server_data['players'] as $player) {
            if (is_array($player) && isset($player['name'])) {
                $players_list[] = $player;
            } elseif (is_string($player)) {
                $players_list[] = ['name' => $player];
            }
        }
        if (count($players_list) > 0) {
            $has_player_list = true;
            $player_list_source = 'direct';
        }
    }
    
    // Log untuk debugging
    error_log("Players online: {$players_online}, Has list: " . ($has_player_list ? 'Yes' : 'No') . ", Source: {$player_list_source}, Count: " . count($players_list));
}

// Timestamp untuk last updated
$last_updated = date('Y-m-d H:i:s');

// Tentukan cache status
$cache_status = 'live';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warlord Realm | Server Status</title>
    <link rel="shortcut icon" href="/asset/logo-min.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Semua CSS sebelumnya tetap sama, hanya tambahkan sedikit untuk API indicator */
        .api-indicator {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(0, 0, 0, 0.3);
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            color: #666;
            border: 1px solid rgba(255, 0, 0, 0.1);
            display: none;
        }
        
        .api-indicator.show {
            display: block;
        }
        
        .api-indicator.multi {
            color: #00cc00;
        }
        
        .api-indicator.single {
            color: #ff9933;
        }

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
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
            border: 2px solid rgba(255, 51, 51, 0.3);
            background: linear-gradient(135deg, #990000, #ff3333);
        }

        .player-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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

        .no-player-list {
            text-align: center;
            color: #ff9933;
            font-style: italic;
            padding: 20px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            border: 1px dashed rgba(255, 153, 51, 0.3);
        }

        .no-player-list i {
            color: #ff9933;
            font-size: 1.5rem;
            margin-bottom: 10px;
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
            <div class="loading-subtext" id="loadingSubtext">Checking server status</div>
        </div>
    </div>

    <!-- Cache Indicator -->
    <div class="cache-indicator" id="cacheIndicator">
        Live Data
    </div>
    
    <!-- API Indicator -->
    <div class="api-indicator" id="apiIndicator">
        Multi-API Mode
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
                        <p class="server-subtitle">Minecraft Vanilla Survival Server</p>
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
                        <?php if($status === 'online'): ?>
                            <?php if($players_online > 0): ?>
                                <?php if($has_player_list && count($players_list) > 0): ?>
                                    <!-- Tampilkan player list yang berhasil didapat -->
                                    <?php foreach($players_list as $player): ?>
                                        <?php 
                                        // Handle berbagai format player data
                                        if (is_array($player) && isset($player['name'])) {
                                            $player_name = htmlspecialchars($player['name']);
                                            $player_uuid = $player['uuid'] ?? $player['id'] ?? null;
                                        } elseif (is_string($player)) {
                                            $player_name = htmlspecialchars($player);
                                            $player_uuid = null;
                                        } else {
                                            continue; // Skip jika format tidak valid
                                        }
                                        
                                        $player_head = getPlayerHead($player_name, $player_uuid);
                                        ?>
                                        <div class="player-item" onclick="copyPlayerName('<?php echo addslashes($player_name); ?>')">
                                            <div class="player-avatar">
                                                <img src="<?php echo $player_head; ?>" 
                                                     alt="<?php echo $player_name; ?>" 
                                                     onerror="this.onerror=null; this.parentElement.style.background='linear-gradient(135deg, #990000, #ff3333)'; this.parentElement.innerHTML='<?php echo strtoupper(substr($player_name, 0, 1)); ?>'; this.remove();">
                                            </div>
                                            <div class="player-info">
                                                <div class="player-name"><?php echo $player_name; ?></div>
                                            </div>
                                            <i class="fas fa-copy" style="color: #666; font-size: 0.9rem;"></i>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <!-- Player list tidak tersedia dari semua API -->
                                    <div class="no-player-list">
                                        <i class="fas fa-user-shield"></i><br>
                                        <strong><?php echo $players_online; ?> player(s) online</strong><br>
                                        <small>Player list not available from server</small><br>
                                        <small style="font-size: 0.8rem; color: #888;">
                                            This happens with premium-only servers
                                        </small>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="no-players">
                                    <i class="fas fa-user-slash"></i><br>
                                    No players currently online
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="no-players">
                                <i class="fas fa-server"></i><br>
                                Server is offline
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- API Status Indicator -->
                    <div style="margin-top: 15px; text-align: center;">
                        <small style="color: #666; font-size: 0.7rem;">
                            <?php if($has_player_list): ?>
                                <i class="fas fa-check-circle" style="color: #00cc00;"></i> Player list available
                            <?php else: ?>
                                <i class="fas fa-exclamation-triangle" style="color: #ff9933;"></i> Player list not available
                            <?php endif; ?>
                        </small>
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
        let refreshInterval = 240;
        let countdown = refreshInterval;
        let refreshBtn = null;
        let originalBtnHTML = null;
        
        // Elements
        const countdownElement = document.getElementById('countdown');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const loadingText = document.getElementById('loadingText');
        const loadingSubtext = document.getElementById('loadingSubtext');
        const lastUpdatedText = document.getElementById('lastUpdatedText');
        const playersCount = document.getElementById('playersCount');
        const serverVersion = document.getElementById('serverVersion');
        const motdContent = document.getElementById('motdContent');
        const onlineCount = document.getElementById('onlineCount');
        const playersList = document.getElementById('playersList');
        const apiIndicator = document.getElementById('apiIndicator');

        // Show indicators
        cacheIndicator.style.display = 'block';
        cacheIndicator.textContent = 'Live Data';
        
        apiIndicator.style.display = 'block';
        apiIndicator.textContent = 'Multi-API Mode';
        apiIndicator.classList.add('multi');

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

        // Refresh data function (AJAX) - menggunakan multiple API
        function refreshData() {
            if (isRefreshing) return;
            
            isRefreshing = true;
            countdown = refreshInterval + 10; // Reset countdown dengan buffer lebih besar untuk multiple API
            
            // Show loading overlay with fade in animation
            loadingText.textContent = 'Updating server status...';
            loadingSubtext.textContent = 'Checking server status';

            // Fade in loading overlay
            loadingOverlay.style.display = 'block';
            setTimeout(() => {
                loadingOverlay.style.opacity = '1';
            }, 10);

            // Disable refresh button
            refreshBtn = document.querySelector('.btn-secondary');
            originalBtnHTML = refreshBtn.innerHTML;
            refreshBtn.disabled = true;
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking APIs...';

            // AJAX request to update data - gunakan multiple API
            const startTime = Date.now();

            fetch('?refresh=true&multi=1&_=' + Date.now(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache'
                },
                cache: 'no-store'
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update loading text with success message
                    loadingText.textContent = 'Status updated!';
                    loadingSubtext.textContent = `Found ${data.data.players_list?.length || 0} players from API`;

                    // Small delay to show success message
                    setTimeout(() => {
                        updateUI(data.data);
                        hideLoadingOverlay();
                        resetButtonState();
                        showNotification('✓ Status updated');
                    }, 500);
                } else {
                    throw new Error(data.error || 'Failed to update');
                }
            })
            .catch(error => {
                console.error('Refresh error:', error);
                loadingText.textContent = 'Update failed';
                loadingSubtext.textContent = 'Trying single API fallback...';

                // Coba fallback ke API tunggal
                setTimeout(() => {
                    refreshSingleAPI();
                }, 1000);
            });
        }

        // Fallback ke API tunggal jika multi-API gagal
        function refreshSingleAPI() {
            fetch('?refresh=true&single=1&_=' + Date.now(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    loadingText.textContent = 'Status updated (fallback)!';
                    loadingSubtext.textContent = 'Using single API';
                    
                    setTimeout(() => {
                        updateUI(data.data);
                        hideLoadingOverlay();
                        apiIndicator.textContent = 'Single API Mode';
                        apiIndicator.classList.remove('multi');
                        apiIndicator.classList.add('single');
                        resetButtonState();
                        showNotification('⚠ Using single API fallback');
                    }, 500);
                } else {
                    throw new Error('Fallback also failed');
                }
            })
            .catch(error => {
                console.error('Fallback error:', error);
                loadingText.textContent = 'Update failed';
                loadingSubtext.textContent = 'All APIs unavailable';

                setTimeout(() => {
                    hideLoadingOverlay();
                    resetButtonState();
                    showNotification('✗ All API attempts failed', 'error');
                }, 1500);
            });
        }

        // Function to reset button state
        function resetButtonState() {
            if (refreshBtn) {
                refreshBtn.disabled = false;
                refreshBtn.innerHTML = originalBtnHTML;
            }
            isRefreshing = false;
        }

        // Function to hide loading overlay
        function hideLoadingOverlay() {
            // Fade out animation
            loadingOverlay.style.opacity = '0';
            setTimeout(() => {
                loadingOverlay.style.display = 'none';
            }, 300);
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
            updatePlayersList(data.players_list, data.status, data.players_online);
            
            // Update last updated time
            const now = new Date();
            lastUpdatedText.textContent = now.toLocaleString();
            
            // Update indicators
            cacheIndicator.textContent = 'Live Data';
            cacheIndicator.style.display = 'block';
        }

        // Update players list
        function updatePlayersList(players, status, playersOnline) {
            if (status === 'online') {
                if (playersOnline > 0) {
                    if (players && players.length > 0) {
                        let playersHTML = '';
                        players.forEach(player => {
                            // Handle berbagai format player data dari API
                            let playerName = '';
                            let playerUuid = null;
                            
                            if (typeof player === 'object' && player !== null) {
                                if (player.name) {
                                    playerName = player.name;
                                    playerUuid = player.uuid || player.id || null;
                                } else if (player.username) {
                                    playerName = player.username;
                                    playerUuid = player.id || null;
                                }
                            } else if (typeof player === 'string') {
                                playerName = player;
                            }
                            
                            if (!playerName) {
                                console.warn('Invalid player data:', player);
                                return; // Skip invalid data
                            }
                            
                            const escapedName = playerName.replace(/'/g, "\\'");
                            // Generate player head URL
                            const playerHead = playerUuid 
                                ? `https://mc-heads.net/avatar/${playerUuid.replace(/-/g, '')}/36`
                                : `https://mc-heads.net/avatar/${encodeURIComponent(playerName)}/36`;
                            
                            playersHTML += `
                                <div class="player-item" onclick="copyPlayerName('${escapedName}')">
                                    <div class="player-avatar">
                                        <img src="${playerHead}" 
                                             alt="${escapeHtml(playerName)}"
                                             onerror="this.onerror=null; this.parentElement.style.background='linear-gradient(135deg, #990000, #ff3333)'; this.parentElement.innerHTML='${playerName.charAt(0).toUpperCase()}'; this.remove();">
                                    </div>
                                    <div class="player-info">
                                        <div class="player-name">${escapeHtml(playerName)}</div>
                                    </div>
                                    <i class="fas fa-copy" style="color: #666; font-size: 0.9rem;"></i>
                                </div>
                            `;
                        });
                        playersList.innerHTML = playersHTML;
                    } else {
                        // Jika ada player online tapi list kosong
                        playersList.innerHTML = `
                            <div class="no-player-list">
                                <i class="fas fa-user-shield"></i><br>
                                <strong>${playersOnline} player(s) online</strong><br>
                                <small>Player list not available from server</small><br>
                                <small style="font-size: 0.8rem; color: #888;">
                                    This happens with premium-only servers<br>
                                    Tried multiple APIs
                                </small>
                            </div>
                        `;
                    }
                } else {
                    playersList.innerHTML = `
                        <div class="no-players">
                            <i class="fas fa-user-slash"></i><br>
                            No players currently online
                        </div>
                    `;
                }
            } else {
                playersList.innerHTML = `
                    <div class="no-players">
                        <i class="fas fa-server"></i><br>
                        Server is offline
                    </div>
                `;
            }
        }

        // Escape HTML untuk security
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
    
    // Cek apakah menggunakan multi API atau single API
    $use_multi_api = isset($_GET['multi']) && $_GET['multi'] == '1';
    
    if ($use_multi_api) {
        // Gunakan multiple API
        $fresh_data = fetchServerDataMultiAPI($api_urls, $server_ip);
    } else {
        // Gunakan API tunggal sebagai fallback
        $fresh_data = fetchAPI($api_urls['mcsrvstat']);
    }
    
    // Format players list
    $formatted_players = [];
    
    if ($fresh_data && $fresh_data['online'] && isset($fresh_data['players'])) {
        // Cek berbagai format
        if (isset($fresh_data['players']['list']) && is_array($fresh_data['players']['list'])) {
            $formatted_players = $fresh_data['players']['list'];
        } 
        elseif (isset($fresh_data['players']['sample']) && is_array($fresh_data['players']['sample'])) {
            $formatted_players = $fresh_data['players']['sample'];
        }
        elseif (isset($fresh_data['players']) && is_array($fresh_data['players']) && !isset($fresh_data['players']['online'])) {
            foreach ($fresh_data['players'] as $player) {
                if (is_array($player) && isset($player['name'])) {
                    $formatted_players[] = $player;
                } elseif (is_string($player)) {
                    $formatted_players[] = ['name' => $player];
                }
            }
        }
        
        error_log("Refresh - Players online: " . ($fresh_data['players']['online'] ?? 0) . 
                 ", Player list count: " . count($formatted_players) .
                 ", API mode: " . ($use_multi_api ? 'Multi' : 'Single'));
    }
    
    // Prepare response data
    $response_data = [
        'success' => true,
        'data' => [
            'status' => $fresh_data ? ($fresh_data['online'] ? 'online' : 'offline') : 'error',
            'players_online' => $fresh_data && $fresh_data['online'] ? ($fresh_data['players']['online'] ?? 0) : 0,
            'players_max' => $fresh_data && $fresh_data['online'] ? ($fresh_data['players']['max'] ?? 0) : 0,
            'version' => $fresh_data && $fresh_data['online'] ? ($fresh_data['version'] ?? 'Unknown') : 'Offline',
            'motd_html' => $fresh_data && $fresh_data['online'] ? 
                          parseMinecraftColors(
                              isset($fresh_data['motd']['raw']) ? 
                              (is_array($fresh_data['motd']['raw']) ? implode(' ', $fresh_data['motd']['raw']) : $fresh_data['motd']['raw']) : 
                              'Warlord Realm Minecraft Server'
                          ) : 
                          '<span style="color: #FFFFFF">Warlord Realm Minecraft Server</span>',
            'players_list' => $formatted_players,
            'cache_status' => 'live',
            'cache_age' => 0,
            'timestamp' => time()
        ]
    ];
    
    // Output JSON response
    ob_clean();
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    echo json_encode($response_data);
    exit;
}

ob_end_flush();