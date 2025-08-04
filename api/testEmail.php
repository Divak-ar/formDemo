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

try {
    // Simulate sending a test email
    $to = 'admin@formdemo.com';
    $subject = 'Form Demo - Test Email';
    $message = "This is a test email from the Form Demo application.\n\n";
    $message .= "Email functionality is working correctly!\n\n";
    $message .= "Sent at: " . date('Y-m-d H:i:s') . "\n\n";
    $message .= "Best regards,\nForm Demo System";
    
    $headers = 'From: noreply@formdemo.com' . "\r\n" .
               'Reply-To: admin@formdemo.com' . "\r\n" .
               'X-Mailer: PHP/' . phpversion();
    
    // In a real environment, uncomment the line below:
    // $success = mail($to, $subject, $message, $headers);
    
    // For demo purposes, we'll simulate successful email sending
    $success = true;
    
    // Log the email (for demo purposes)
    error_log("Demo Test Email sent to: $to, Subject: $subject");
    
    if ($success) {
        echo json_encode([
            'success' => true, 
            'message' => 'Test email sent successfully! Check your logs to see the simulated email.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send test email'
        ]);
    }
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
