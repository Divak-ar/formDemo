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

// Validation
$errors = [];

if (empty($input['first_name'])) {
    $errors[] = 'First name is required';
} elseif (!preg_match('/^[a-zA-Z\s]+$/', $input['first_name'])) {
    $errors[] = 'First name can only contain letters and spaces';
}

if (empty($input['last_name'])) {
    $errors[] = 'Last name is required';
} elseif (!preg_match('/^[a-zA-Z\s]+$/', $input['last_name'])) {
    $errors[] = 'Last name can only contain letters and spaces';
}

if (empty($input['email'])) {
    $errors[] = 'Email is required';
} elseif (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}

if (empty($input['telephone'])) {
    $errors[] = 'Telephone is required';
} elseif (!preg_match('/^[0-9+\-\s()]{10,15}$/', $input['telephone'])) {
    $errors[] = 'Invalid telephone format';
}

if (empty($input['date_of_birth'])) {
    $errors[] = 'Date of birth is required';
} else {
    $dob = DateTime::createFromFormat('Y-m-d', $input['date_of_birth']);
    if (!$dob) {
        $errors[] = 'Invalid date format';
    } else {
        $age = $dob->diff(new DateTime())->y;
        if ($age < 13 || $age > 120) {
            $errors[] = 'Age must be between 13 and 120 years';
        }
    }
}

if (empty($input['gender']) || !in_array($input['gender'], ['male', 'female', 'other'])) {
    $errors[] = 'Valid gender selection is required';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if (isset($input['id']) && !empty($input['id'])) {
        // Update existing record
        $stmt = $pdo->prepare("
            UPDATE form_data 
            SET first_name = ?, last_name = ?, email = ?, telephone = ?, date_of_birth = ?, gender = ?
            WHERE entity_id = ?
        ");
        $stmt->execute([
            $input['first_name'],
            $input['last_name'],
            $input['email'],
            $input['telephone'],
            $input['date_of_birth'],
            $input['gender'],
            $input['id']
        ]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Record updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes made or record not found']);
        }
    } else {
        // Insert new record
        $stmt = $pdo->prepare("
            INSERT INTO form_data (first_name, last_name, email, telephone, date_of_birth, gender, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $input['first_name'],
            $input['last_name'],
            $input['email'],
            $input['telephone'],
            $input['date_of_birth'],
            $input['gender']
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Record created successfully']);
    }
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
