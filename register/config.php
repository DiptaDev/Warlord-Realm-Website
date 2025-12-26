<?php
// config.php
class Database {
    private $host = "localhost";
    private $db_name = "warlord_realm";
    private $username = "warlord_realm";
    private $password = "warlord_realm";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

// Simple session management for admin
session_start();

// Discord Webhook Configuration
define('DISCORD_WEBHOOK_URL', 'https://discord.com/api/webhooks/1440727358477701151/EF7RQpA9sAkFKeLpF-JAX3qLqV1tMSCqJlNlVqhvRCsvkKGVcR3OnLzUtJOAuewzvCM8'); // not working in infinity free
// Ganti URL di atas dengan webhook URL Discord Anda

function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function adminLogin($username, $password) {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        $query = "SELECT * FROM admin_users WHERE username = :username";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() == 1) {
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $admin['password_hash'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $username;
                $_SESSION['admin_id'] = $admin['id'];
                return true;
            }
        }
        return false;
    } catch(PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

function adminLogout() {
    session_destroy();
    header('Location: admin_login.php');
    exit;
}

// Function to send Discord notification for new registration
function sendDiscordNotification($registrationData) {
    if (!defined('DISCORD_WEBHOOK_URL') || empty(DISCORD_WEBHOOK_URL)) {
        error_log("Discord webhook URL not configured");
        return false;
    }
    
    $timestamp = date('c', strtotime('now'));
    
    // Create embed message
    $embed = [
        "title" => "🎮 Pendaftaran Member Baru!",
        "color" => hexdec("FFA500"), // Warna orange untuk pending
        "fields" => [
            [
                "name" => "👤 Username Minecraft",
                "value" => "`" . $registrationData['username'] . "`",
                "inline" => true
            ],
            [
                "name" => "📧 Email",
                "value" => "`" . $registrationData['email'] . "`",
                "inline" => true
            ],
            [
                "name" => "🎮 Tipe Akun",
                "value" => "`" . ucfirst($registrationData['minecraftType']) . "`",
                "inline" => true
            ],
            [
                "name" => "💬 Discord",
                "value" => "`" . $registrationData['discord'] . "`",
                "inline" => true
            ],
            [
                "name" => "📊 Status",
                "value" => "`🟡 PENDING`",
                "inline" => true
            ],
            [
                "name" => "🆔 Registration ID",
                "value" => "`#" . $registrationData['id'] . "`",
                "inline" => true
            ]
        ],
        "footer" => [
            "text" => "Warlord Realm • " . date('d/m/Y H:i:s')
        ],
        "timestamp" => $timestamp
    ];
    
    $data = [
        "username" => "WarGuard🚨",
        "avatar_url" => "https://i.imgur.com/4sGKTig.jpeg",
        "embeds" => [$embed]
    ];
    
    return sendToDiscord($data);
}

// Function to send status update to Discord
function sendDiscordStatusUpdate($registrationId, $username, $oldStatus, $newStatus, $adminUsername) {
    if (!defined('DISCORD_WEBHOOK_URL') || empty(DISCORD_WEBHOOK_URL)) {
        error_log("Discord webhook URL not configured");
        return false;
    }
    
    $timestamp = date('c', strtotime('now'));
    
    // Tentukan warna berdasarkan status
    $colors = [
        'pending' => hexdec("FFA500"),  // Orange
        'approved' => hexdec("00FF00"), // Green
        'rejected' => hexdec("FF0000")  // Red
    ];
    
    $statusIcons = [
        'pending' => '🟡',
        'approved' => '🟢', 
        'rejected' => '🔴'
    ];
    
    $statusTexts = [
        'pending' => 'PENDING',
        'approved' => 'APPROVED',
        'rejected' => 'REJECTED'
    ];
    
    $color = $colors[$newStatus] ?? hexdec("FFFFFF");
    $statusIcon = $statusIcons[$newStatus] ?? '⚪';
    $statusText = $statusTexts[$newStatus] ?? strtoupper($newStatus);
    
    $embed = [
        "title" => "📝 Status Pendaftaran Diupdate!",
        "color" => $color,
        "fields" => [
            [
                "name" => "👤 Username",
                "value" => "`" . $username . "`",
                "inline" => true
            ],
            [
                "name" => "🆔 Registration ID",
                "value" => "`#" . $registrationId . "`",
                "inline" => true
            ],
            [
                "name" => "📊 Status Sebelumnya",
                "value" => "`" . ($statusIcons[$oldStatus] ?? '⚪') . " " . strtoupper($oldStatus) . "`",
                "inline" => true
            ],
            [
                "name" => "📈 Status Baru",
                "value" => "`" . $statusIcon . " " . $statusText . "`",
                "inline" => true
            ],
            [
                "name" => "👨‍💼 Admin",
                "value" => "`" . $adminUsername . "`",
                "inline" => true
            ]
        ],
        "footer" => [
            "text" => "Warlord Realm • " . date('d/m/Y H:i:s')
        ],
        "timestamp" => $timestamp
    ];
    
    $data = [
        "username" => "Warlord Realm Bot",
        "avatar_url" => "https://your-domain.com/asset/logo.jpg",
        "embeds" => [$embed]
    ];
    
    return sendToDiscord($data);
}

// Generic function to send data to Discord
function sendToDiscord($data) {
    $ch = curl_init(DISCORD_WEBHOOK_URL);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-type: application/json']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode != 204) {
        error_log("Discord webhook failed with HTTP code: " . $httpCode);
        return false;
    }
    
    return true;
}
?>