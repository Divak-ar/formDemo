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

if (!isset($input['id']) || !is_numeric($input['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid record ID']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get record details
    $stmt = $pdo->prepare("SELECT email, first_name, last_name FROM form_data WHERE entity_id = ?");
    $stmt->execute([$input['id']]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$record) {
        echo json_encode(['success' => false, 'message' => 'Record not found']);
        exit;
    }
    
    // Simulate sending email
    $to = $record['email'];
    $subject = 'Individual Message from Form Demo Admin';
    $message = "Dear {$record['first_name']} {$record['last_name']},\n\n";
    $message .= "This is a personalized email sent from the Form Demo Admin Panel.\n\n";
    $message .= "We wanted to reach out to you individually.\n\n";
    $message .= "Best regards,\nForm Demo Team";
    
    $headers = 'From: admin@formdemo.com' . "\r\n" .
               'Reply-To: admin@formdemo.com' . "\r\n" .
               'X-Mailer: PHP/' . phpversion();
    
    // In a real environment, uncomment the line below:
    // $success = mail($to, $subject, $message, $headers);
    
    // For demo purposes, we'll simulate successful email sending
    $success = true;
    
    // Log the email (for demo purposes)
    error_log("Demo Individual Email sent to: $to, Subject: $subject");
    
    if ($success) {
        echo json_encode([
            'success' => true, 
            'message' => "Email sent successfully to {$record['first_name']} {$record['last_name']} ({$to})"
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send email'
        ]);
    }
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
