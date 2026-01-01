<?php
// update_status.php
include 'config.php';

if (!isAdminLoggedIn()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['id']) || !isset($data['action'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$applicationId = intval($data['id']);
$action = $data['action'];

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $statusMap = [
        'approve' => 'approved',
        'reject' => 'rejected',
        'interview' => 'interview',
        'trial' => 'trial',
        'pending' => 'pending'
    ];
    
    if (!isset($statusMap[$action])) {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
    }
    
    $newStatus = $statusMap[$action];
    
    $updateQuery = "UPDATE whitelist_applications SET status = :status WHERE id = :id";
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->bindParam(':status', $newStatus);
    $updateStmt->bindParam(':id', $applicationId);
    
    if ($updateStmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Status updated successfully',
            'new_status' => $newStatus
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
    }
    
} catch(PDOException $e) {
    error_log("Status update error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>