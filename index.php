<?php
/**
 * FormDemo Module Demo - Standalone Frontend
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$host = 'mysql';
$dbname = 'magento';
$username = 'magento';
$password = 'magento';

$pdo = null;
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Create table if not exists
$createTable = "
CREATE TABLE IF NOT EXISTS form_data (
    entity_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    dob DATE NOT NULL,
    gender VARCHAR(10) NOT NULL,
    telephone VARCHAR(20) NOT NULL,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

try {
    $pdo->exec($createTable);
} catch (PDOException $e) {
    // Table might already exist
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_form'])) {
    $errors = [];
    
    // Validation
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validate required fields
    if (empty($firstName)) $errors[] = 'First Name is required';
    if (empty($lastName)) $errors[] = 'Last Name is required';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid Email is required';
    if (empty($dob)) $errors[] = 'Date of Birth is required';
    if (empty($gender)) $errors[] = 'Gender is required';
    if (empty($telephone)) $errors[] = 'Telephone is required';
    
    // Validate name fields contain only letters
    if (!preg_match('/^[a-zA-Z\s]+$/', $firstName)) $errors[] = 'First Name should contain only letters';
    if (!preg_match('/^[a-zA-Z\s]+$/', $lastName)) $errors[] = 'Last Name should contain only letters';
    
    // Validate telephone contains only numbers
    if (!preg_match('/^[0-9+\-\s()]+$/', $telephone)) $errors[] = 'Telephone should contain only numbers';
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO form_data (first_name, last_name, email, dob, gender, telephone, message)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$firstName, $lastName, $email, $dob, $gender, $telephone, $message]);
            
            // Send email (simplified)
            $to = $email;
            $subject = "Form Submission Confirmation";
            $emailMessage = "
                <h2>Thank you for your submission!</h2>
                <p>Dear $firstName $lastName,</p>
                <p>We have received your form submission with the following details:</p>
                <ul>
                    <li>Name: $firstName $lastName</li>
                    <li>Email: $email</li>
                    <li>Date of Birth: $dob</li>
                    <li>Gender: $gender</li>
                    <li>Telephone: $telephone</li>
                    <li>Message: $message</li>
                </ul>
                <p>Thank you!</p>
            ";
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8\r\n";
            $headers .= "From: noreply@formdemo.com\r\n";
            
            // Note: In production, use proper email service
            @mail($to, $subject, $emailMessage, $headers);
            
            $success = "Form submitted successfully! A confirmation email has been sent.";
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        debug_log("Database connected successfully");
        
        // Create form_data table from db_schema.xml structure
        $createTable = "
        CREATE TABLE IF NOT EXISTS form_data (
            entity_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(255) NOT NULL,
            last_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            date_of_birth DATE NOT NULL,
            gender VARCHAR(10) NOT NULL,
            telephone VARCHAR(20) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email)
        )";
        $pdo->exec($createTable);
        debug_log("Table created/verified");
        
    } catch (PDOException $e) {
        debug_log("Database connection failed: " . $e->getMessage());
        die("Database connection failed: " . $e->getMessage());
    }
    
    // Simple router
    $requestUri = $_SERVER['REQUEST_URI'];
    $path = trim(parse_url($requestUri, PHP_URL_PATH), '/');
    
    debug_log("Request URI: " . $requestUri);
    debug_log("Parsed path: " . $path);
    
    // Handle routes based on Magento module structure
    switch ($path) {
        case '':
        case 'index.php':
            debug_log("Showing home page");
            showHomePage();
            break;
            
        case 'formdemo/index/index':
            debug_log("Showing form");
            showForm();
            break;
            
        case 'formdemo/index/submit':
            debug_log("Handling form submit");
            handleFormSubmit();
            break;
            
        case 'admin/formdemo/demo/index':
            debug_log("Showing admin grid");
            showAdminGrid();
            break;
            
        case strpos($path, 'admin/formdemo/demo/delete') === 0:
            debug_log("Handling delete");
            handleDelete();
            break;
            
        case 'info.php':
            debug_log("Showing PHP info");
            phpinfo();
            break;
            
        default:
            debug_log("404 - Path not found: " . $path);
            http_response_code(404);
            echo "<!DOCTYPE html><html><head><title>404</title></head><body><h1>404 - Not Found</h1><p>Path: " . htmlspecialchars($path) . "</p><p><a href='/'>Go Home</a></p></body></html>";
    }
}

function showHomePage() {
    debug_log("Entering showHomePage function");
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Magento 2 FormDemo Module - DEBUG VERSION</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
            .container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #e26703; text-align: center; margin-bottom: 30px; }
            .status { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 30px; text-align: center; }
            .debug { background: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-family: monospace; font-size: 12px; }
            .links { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 30px 0; }
            .card { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; padding: 20px; text-align: center; }
            .card h3 { color: #495057; margin-bottom: 15px; }
            .btn { display: inline-block; padding: 12px 24px; background: #e26703; color: white; text-decoration: none; border-radius: 4px; margin: 10px 5px; }
            .btn:hover { background: #cc5801; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>Magento 2 FormDemo Module - DEBUG</h1>
            
            <div class='debug'>
                Debug Mode Active<br>
                Request URI: " . htmlspecialchars($_SERVER['REQUEST_URI']) . "<br>
                Script Path: " . htmlspecialchars($_SERVER['SCRIPT_NAME']) . "<br>
                Current Time: " . date('Y-m-d H:i:s') . "
            </div>
            
            <div class='status'>
                ✅ Module Status: ACTIVE & READY
            </div>
            
            <div class='links'>
                <div class='card'>
                    <h3>Frontend Form</h3>
                    <p>Customer-facing form with validation and submission.</p>
                    <a href='/formdemo/index/index' class='btn'>📝 View Form</a>
                </div>
                
                <div class='card'>
                    <h3>Admin Grid</h3>
                    <p>Manage submitted data with CRUD operations.</p>
                    <a href='/admin/formdemo/demo/index' class='btn'>⚙️ Admin Panel</a>
                </div>
            </div>
            
            <div style='text-align: center; margin-top: 30px;'>
                <a href='/info.php' class='btn' style='background: #6c757d;'>📊 PHP Info</a>
            </div>
        </div>
    </body>
    </html>";
    debug_log("Homepage content sent");
}

function showForm() {
    $templatePath = __DIR__ . '/app/code/EMP123/FormDemo/view/frontend/templates/form.phtml';
    
    if (file_exists($templatePath)) {
        include $templatePath;
    } else {
        // Fallback form
        echo "<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Form Demo</title>
            <style>
                body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
                .form-container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                h1 { color: #e26703; text-align: center; margin-bottom: 30px; }
                .form-group { margin-bottom: 20px; }
                label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
                label.required::after { content: ' *'; color: #dc3545; }
                input, select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
                input:focus, select:focus { outline: none; border-color: #e26703; }
                .btn { background: #e26703; color: white; padding: 12px 30px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; }
                .btn:hover { background: #cc5801; }
                .nav { text-align: center; margin-bottom: 20px; }
                .nav a { margin: 0 10px; color: #e26703; text-decoration: none; }
                .nav a:hover { text-decoration: underline; }
                .message { margin-top: 20px; padding: 15px; border-radius: 4px; }
                .success { background: #d4edda; color: #155724; }
                .error { background: #f8d7da; color: #721c24; }
            </style>
        </head>
        <body>
            <div class='nav'>
                <a href='/'>🏠 Home</a> | 
                <a href='/admin/formdemo/demo/index'>⚙️ Admin Grid</a> | 
                <a href='/info.php'>📊 PHP Info</a>
            </div>
            
            <div class='form-container'>
                <h1>Customer Information Form</h1>
                
                <form id='customer-form' method='post'>
                    <div class='form-group'>
                        <label for='first_name' class='required'>First Name</label>
                        <input type='text' id='first_name' name='first_name' required>
                    </div>
                    
                    <div class='form-group'>
                        <label for='last_name' class='required'>Last Name</label>
                        <input type='text' id='last_name' name='last_name' required>
                    </div>
                    
                    <div class='form-group'>
                        <label for='email' class='required'>Email</label>
                        <input type='email' id='email' name='email' required>
                    </div>
                    
                    <div class='form-group'>
                        <label for='date_of_birth' class='required'>Date of Birth</label>
                        <input type='date' id='date_of_birth' name='date_of_birth' required>
                    </div>
                    
                    <div class='form-group'>
                        <label for='gender' class='required'>Gender</label>
                        <select id='gender' name='gender' required>
                            <option value=''>Please select...</option>
                            <option value='male'>Male</option>
                            <option value='female'>Female</option>
                            <option value='other'>Other</option>
                        </select>
                    </div>
                    
                    <div class='form-group'>
                        <label for='telephone' class='required'>Telephone</label>
                        <input type='tel' id='telephone' name='telephone' required>
                    </div>
                    
                    <button type='submit' class='btn'>Submit</button>
                </form>
                
                <div id='message' style='display: none;'></div>
            </div>
            
            <script>
                document.getElementById('customer-form').addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const data = Object.fromEntries(formData);
                    
                    fetch('/formdemo/index/submit', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    })
                    .then(response => response.json())
                    .then(result => {
                        const messageDiv = document.getElementById('message');
                        messageDiv.style.display = 'block';
                        
                        if (result.success) {
                            messageDiv.innerHTML = '<div class=\"success\">' + result.message + '</div>';
                            this.reset();
                        } else {
                            messageDiv.innerHTML = '<div class=\"error\">' + (result.errors ? result.errors.join('<br>') : result.message) + '</div>';
                        }
                    })
                    .catch(error => {
                        const messageDiv = document.getElementById('message');
                        messageDiv.style.display = 'block';
                        messageDiv.innerHTML = '<div class=\"error\">Error: ' + error.message + '</div>';
                    });
                });
            </script>
        </body>
        </html>";
    }
}

function handleFormSubmit() {
    global $pdo;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true) ?: $_POST;
        
        // Validation
        $errors = [];
        if (empty($data['first_name'])) $errors[] = 'First name is required';
        if (empty($data['last_name'])) $errors[] = 'Last name is required';
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
        if (empty($data['date_of_birth'])) $errors[] = 'Date of birth is required';
        if (empty($data['gender'])) $errors[] = 'Gender is required';
        if (empty($data['telephone'])) $errors[] = 'Telephone is required';
        
        header('Content-Type: application/json');
        
        if (!empty($errors)) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }
        
        // Save to database
        try {
            $stmt = $pdo->prepare("
                INSERT INTO form_data (first_name, last_name, email, date_of_birth, gender, telephone) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['date_of_birth'],
                $data['gender'],
                $data['telephone']
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Form submitted successfully! Data has been saved.']);
            
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }
}

function showAdminGrid() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT * FROM form_data ORDER BY created_at DESC");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Admin Grid - Form Data</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
            .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #e26703; margin-bottom: 20px; }
            .stats { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
            table { border-collapse: collapse; width: 100%; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
            th { background-color: #f8f9fa; font-weight: bold; }
            .btn { padding: 6px 12px; text-decoration: none; border-radius: 3px; font-size: 12px; margin: 2px; }
            .btn-delete { background: #dc3545; color: white; }
            .btn-edit { background: #007bff; color: white; }
            .btn-add { background: #28a745; color: white; padding: 12px 20px; margin-bottom: 20px; }
            .nav { text-align: center; margin-bottom: 20px; }
            .nav a { margin: 0 10px; color: #e26703; text-decoration: none; }
            .nav a:hover { text-decoration: underline; }
            tr:nth-child(even) { background-color: #f8f9fa; }
            .empty-state { text-align: center; padding: 40px; color: #6c757d; }
        </style>
    </head>
    <body>
        <div class='nav'>
            <a href='/'>🏠 Home</a> | 
            <a href='/formdemo/index/index'>📝 Frontend Form</a> | 
            <a href='/admin/formdemo/demo/index'>⚙️ Admin Grid</a> | 
            <a href='/info.php'>📊 PHP Info</a>
        </div>
        
        <div class='container'>
            <h1>Form Data Management</h1>
            <div class='stats'>Total Records: " . count($records) . "</div>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Date of Birth</th>
                        <th>Gender</th>
                        <th>Telephone</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>";
    
    if (empty($records)) {
        echo "<tr><td colspan='9' class='empty-state'>
                📝 No records found.<br>
                <a href='/formdemo/index/index'>Submit a form</a> to see data here.
              </td></tr>";
    } else {
        foreach ($records as $record) {
            echo "<tr>
                <td>{$record['entity_id']}</td>
                <td>" . htmlspecialchars($record['first_name']) . "</td>
                <td>" . htmlspecialchars($record['last_name']) . "</td>
                <td>" . htmlspecialchars($record['email']) . "</td>
                <td>{$record['date_of_birth']}</td>
                <td>" . ucfirst($record['gender']) . "</td>
                <td>" . htmlspecialchars($record['telephone']) . "</td>
                <td>" . date('Y-m-d H:i', strtotime($record['created_at'])) . "</td>
                <td>
                    <a href='/admin/formdemo/demo/delete?id={$record['entity_id']}' class='btn btn-delete' onclick='return confirm(\"Are you sure you want to delete this record?\")'>🗑️ Delete</a>
                </td>
            </tr>";
        }
    }
    
    echo "    </tbody>
            </table>
        </div>
    </body>
    </html>";
}

function handleDelete() {
    global $pdo;
    
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("DELETE FROM form_data WHERE entity_id = ?");
        $stmt->execute([$_GET['id']]);
        header('Location: /admin/formdemo/demo/index');
        exit;
    }
}
?>
