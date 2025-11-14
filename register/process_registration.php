<?php
// process_registration.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Fallback to form data if JSON parsing fails
if ($data === null) {
    $data = $_POST;
}

// Validasi dan sanitasi input
$required_fields = ['email', 'username', 'minecraftType', 'discord', 'skills', 'experience', 'reason', 'diamond'];
$clean_data = [];

foreach ($required_fields as $field) {
    if (empty($data[$field])) {
        echo json_encode(['success' => false, 'message' => 'Harap lengkapi semua field yang wajib diisi!']);
        exit;
    }
    $clean_data[$field] = trim(htmlspecialchars($data[$field]));
}

// Validasi email
if (!filter_var($clean_data['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Harap masukkan email yang valid!']);
    exit;
}

// Field opsional
$clean_data['socialMedia'] = isset($data['socialMedia']) ? trim(htmlspecialchars($data['socialMedia'])) : '';
$clean_data['adminMessage'] = isset($data['adminMessage']) ? trim(htmlspecialchars($data['adminMessage'])) : '';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Cek apakah email sudah terdaftar
    $check_query = "SELECT id FROM registrations WHERE email = :email";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':email', $clean_data['email']);
    $check_stmt->execute();

    if ($check_stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Email ini sudah terdaftar!']);
        exit;
    }

    // Insert data ke database
    $query = "INSERT INTO registrations 
              (email, username, minecraft_type, discord_username, social_media, skills, experience, reason, diamond_preference, admin_message) 
              VALUES 
              (:email, :username, :minecraft_type, :discord, :social_media, :skills, :experience, :reason, :diamond, :admin_message)";

    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':email', $clean_data['email']);
    $stmt->bindParam(':username', $clean_data['username']);
    $stmt->bindParam(':minecraft_type', $clean_data['minecraftType']);
    $stmt->bindParam(':discord', $clean_data['discord']);
    $stmt->bindParam(':social_media', $clean_data['socialMedia']);
    $stmt->bindParam(':skills', $clean_data['skills']);
    $stmt->bindParam(':experience', $clean_data['experience']);
    $stmt->bindParam(':reason', $clean_data['reason']);
    $stmt->bindParam(':diamond', $clean_data['diamond']);
    $stmt->bindParam(':admin_message', $clean_data['adminMessage']);

    if ($stmt->execute()) {
        // Dapatkan ID yang baru dibuat
        $newId = $db->lastInsertId();
        
        // Tambahkan ID ke data untuk webhook
        $clean_data['id'] = $newId;
        
        // Kirim notifikasi ke Discord
        $discordSent = sendDiscordNotification($clean_data);
        
        // Kirim email notifikasi (opsional)
        sendNotificationEmail($clean_data);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Pendaftaran berhasil dikirim! Kami akan menghubungi Anda melalui email dan Discord atau Whatsapp.',
            'registration_id' => $newId,
            'discord_notification' => $discordSent ? 'sent' : 'failed'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.']);
    }

} catch(PDOException $exception) {
    error_log("Database error: " . $exception->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem. Silakan coba lagi nanti.']);
}

function sendNotificationEmail($data) {
    // Implement email sending here
    // Currently disabled for demo
    return true;
}
?>