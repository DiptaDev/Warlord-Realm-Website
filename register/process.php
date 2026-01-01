<?php
// process_whitelist.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include config
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if ($data === null) {
    $data = $_POST;
}

// Validate required fields
$required_fields = [
    'mc_username', 'age', 'discord', 'play_duration', 'play_time', 
    'active_hours', 'consistency', 'server_experience', 'skill_level',
    'main_target', 'base_stolen', 'attitude_newbies', 'past_conflict',
    'reaction_loss', 'fair_play', 'rule_violation', 'bug_response',
    'admin_disagreement', 'important_rule', 'personality_type',
    'strength', 'weakness', 'commitment_reason', 'why_accept', 'email'
];

$clean_data = [];

foreach ($required_fields as $field) {
    if (empty($data[$field])) {
        echo json_encode(['success' => false, 'message' => 'Harap lengkapi semua field yang wajib diisi!']);
        exit;
    }
    $clean_data[$field] = trim(htmlspecialchars($data[$field]));
}

// Validate email
if (!filter_var($clean_data['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email tidak valid!']);
    exit;
}

// Validate age
if (!is_numeric($clean_data['age']) || $clean_data['age'] < 10 || $clean_data['age'] > 99) {
    echo json_encode(['success' => false, 'message' => 'Umur harus antara 13-99 tahun!']);
    exit;
}

// Optional fields
$clean_data['expertise'] = isset($data['expertise']) ? $data['expertise'] : '';
$clean_data['contribution_willingness'] = isset($data['contribution_willingness']) ? trim(htmlspecialchars($data['contribution_willingness'])) : '';
$clean_data['contribution_type'] = isset($data['contribution_type']) ? trim(htmlspecialchars($data['contribution_type'])) : '';
$clean_data['agree_rules'] = isset($data['agree_rules']) ? 1 : 0;
$clean_data['agree_sanctions'] = isset($data['agree_sanctions']) ? 1 : 0;
$clean_data['agree_trial'] = isset($data['agree_trial']) ? 1 : 0;

try {
    $database = new Database();
    $db = $database->getConnection();

    // Check if Minecraft username already exists
    $check_query = "SELECT id FROM whitelist_applications WHERE mc_username = :username";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':username', $clean_data['mc_username']);
    $check_stmt->execute();

    if ($check_stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Username Minecraft ini sudah terdaftar!']);
        exit;
    }

    // Check if Discord already exists
    $check_query = "SELECT id FROM whitelist_applications WHERE discord = :discord";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':discord', $clean_data['discord']);
    $check_stmt->execute();

    if ($check_stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Discord username ini sudah terdaftar!']);
        exit;
    }

    // Insert into database
    $query = "INSERT INTO whitelist_applications (
                mc_username, age, discord, email, play_duration, play_time, 
                active_hours, consistency, server_experience, expertise, 
                skill_level, main_target, base_stolen, attitude_newbies, 
                past_conflict, reaction_loss, fair_play, rule_violation, 
                bug_response, admin_disagreement, important_rule, 
                personality_type, strength, weakness, contribution_willingness, 
                contribution_type, agree_rules, agree_sanctions, agree_trial, 
                commitment_reason, why_accept, status, application_date
              ) VALUES (
                :mc_username, :age, :discord, :email, :play_duration, :play_time, 
                :active_hours, :consistency, :server_experience, :expertise, 
                :skill_level, :main_target, :base_stolen, :attitude_newbies, 
                :past_conflict, :reaction_loss, :fair_play, :rule_violation, 
                :bug_response, :admin_disagreement, :important_rule, 
                :personality_type, :strength, :weakness, :contribution_willingness, 
                :contribution_type, :agree_rules, :agree_sanctions, :agree_trial, 
                :commitment_reason, :why_accept, 'pending', NOW()
              )";

    $stmt = $db->prepare($query);
    
    // Bind parameters
    $stmt->bindParam(':mc_username', $clean_data['mc_username']);
    $stmt->bindParam(':age', $clean_data['age']);
    $stmt->bindParam(':discord', $clean_data['discord']);
    $stmt->bindParam(':email', $clean_data['email']);
    $stmt->bindParam(':play_duration', $clean_data['play_duration']);
    $stmt->bindParam(':play_time', $clean_data['play_time']);
    $stmt->bindParam(':active_hours', $clean_data['active_hours']);
    $stmt->bindParam(':consistency', $clean_data['consistency']);
    $stmt->bindParam(':server_experience', $clean_data['server_experience']);
    $stmt->bindParam(':expertise', $clean_data['expertise']);
    $stmt->bindParam(':skill_level', $clean_data['skill_level']);
    $stmt->bindParam(':main_target', $clean_data['main_target']);
    $stmt->bindParam(':base_stolen', $clean_data['base_stolen']);
    $stmt->bindParam(':attitude_newbies', $clean_data['attitude_newbies']);
    $stmt->bindParam(':past_conflict', $clean_data['past_conflict']);
    $stmt->bindParam(':reaction_loss', $clean_data['reaction_loss']);
    $stmt->bindParam(':fair_play', $clean_data['fair_play']);
    $stmt->bindParam(':rule_violation', $clean_data['rule_violation']);
    $stmt->bindParam(':bug_response', $clean_data['bug_response']);
    $stmt->bindParam(':admin_disagreement', $clean_data['admin_disagreement']);
    $stmt->bindParam(':important_rule', $clean_data['important_rule']);
    $stmt->bindParam(':personality_type', $clean_data['personality_type']);
    $stmt->bindParam(':strength', $clean_data['strength']);
    $stmt->bindParam(':weakness', $clean_data['weakness']);
    $stmt->bindParam(':contribution_willingness', $clean_data['contribution_willingness']);
    $stmt->bindParam(':contribution_type', $clean_data['contribution_type']);
    $stmt->bindParam(':agree_rules', $clean_data['agree_rules']);
    $stmt->bindParam(':agree_sanctions', $clean_data['agree_sanctions']);
    $stmt->bindParam(':agree_trial', $clean_data['agree_trial']);
    $stmt->bindParam(':commitment_reason', $clean_data['commitment_reason']);
    $stmt->bindParam(':why_accept', $clean_data['why_accept']);

    if ($stmt->execute()) {
        $application_id = $db->lastInsertId();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Aplikasi whitelist berhasil dikirim! Admin akan meninjau aplikasi Anda dalam 1-3 hari.',
            'application_id' => $application_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.']);
    }

} catch(PDOException $exception) {
    error_log("Database error: " . $exception->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem. Silakan coba lagi nanti.']);
}
?>