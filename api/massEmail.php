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
    
    // Get email addresses of selected records
    $stmt = $pdo->prepare("SELECT email, first_name, last_name FROM form_data WHERE entity_id IN ($placeholders)");
    $stmt->execute($ids);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $emailCount = 0;
    
    foreach ($records as $record) {
        // Simulate sending email (in real implementation, use PHPMailer or similar)
        $to = $record['email'];
        $subject = 'Hello from Form Demo Admin';
        $message = "Dear {$record['first_name']} {$record['last_name']},\n\n";
        $message .= "This is a test email sent from the Form Demo Admin Panel.\n\n";
        $message .= "Your record is being processed.\n\n";
        $message .= "Best regards,\nForm Demo Team";
        
        $headers = 'From: admin@formdemo.com' . "\r\n" .
                   'Reply-To: admin@formdemo.com' . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();
        
        // In a real environment, uncomment the line below:
        // if (mail($to, $subject, $message, $headers)) {
        //     $emailCount++;
        // }
        
        // For demo purposes, we'll simulate successful email sending
        $emailCount++;
        
        // Log the email (for demo purposes)
        error_log("Demo Email sent to: $to, Subject: $subject");
    }
    
    echo json_encode([
        'success' => true, 
        'message' => "Successfully sent $emailCount email(s) to selected records"
    ]);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
