<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Database configuration
$host = 'formdemo-mysql-1';
$dbname = 'magento';
$username = 'magento';
$password = 'magento';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['ids']) || !is_array($input['ids']) || empty($input['ids'])) {
    echo json_encode(['success' => false, 'message' => 'No records selected']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Sanitize IDs
    $ids = array_filter($input['ids'], 'is_numeric');
    
    if (empty($ids)) {
        echo json_encode(['success' => false, 'message' => 'Invalid record IDs']);
        exit;
    }
    
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    
    $stmt = $pdo->prepare("DELETE FROM form_data WHERE entity_id IN ($placeholders)");
    $stmt->execute($ids);
    
    $deletedCount = $stmt->rowCount();
    
    echo json_encode([
        'success' => true, 
        'message' => "Successfully deleted $deletedCount record(s)"
    ]);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
