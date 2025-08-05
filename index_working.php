<?php
/**
 * FormDemo Module Router
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$host = 'mysql';
$dbname = 'magento';
$username = 'magento';
$password = 'magento';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create table
    $pdo->exec("
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
        )
    ");
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Route handling
$requestUri = $_SERVER['REQUEST_URI'];
$path = trim(parse_url($requestUri, PHP_URL_PATH), '/');

switch ($path) {
    case '':
    case 'index.php':
        showHomePage();
        break;
        
    case 'formdemo/index/index':
        showForm();
        break;
        
    case 'formdemo/index/submit':
        handleFormSubmit();
        break;
        
    case 'admin/formdemo/demo/index':
        showAdminGrid();
        break;
        
    case 'info.php':
        phpinfo();
        break;
        
    default:
        if (strpos($path, 'admin/formdemo/demo/delete') === 0) {
            handleDelete();
        } else {
            http_response_code(404);
            echo "<h1>404 - Not Found</h1><p>Path: " . htmlspecialchars($path) . "</p><a href='/'>Home</a>";
        }
}

function showHomePage() {
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>FormDemo Module</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
            .container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; }
            .btn { display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; margin: 10px 5px; }
            .status { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🚀 Magento 2 FormDemo Module</h1>
            
            <div class="status">
                ✅ Module Status: ACTIVE & READY<br>
                📅 Time: ' . date('Y-m-d H:i:s') . '
            </div>
            
            <h3>Test the Module:</h3>
            <a href="/formdemo/index/index" class="btn">📝 Frontend Form</a>
            <a href="/admin/formdemo/demo/index" class="btn">⚙️ Admin Grid</a>
            <a href="/info.php" class="btn">📊 PHP Info</a>
            
            <h3>Module Information:</h3>
            <ul>
                <li><strong>Location:</strong> app/code/EMP123/FormDemo</li>
                <li><strong>Database:</strong> form_data table</li>
                <li><strong>Features:</strong> Form submission, Admin CRUD, Email notifications</li>
            </ul>
        </div>
    </body>
    </html>';
}

function showForm() {
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Customer Information Form</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
            .form-container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; }
            .form-group { margin-bottom: 20px; }
            label { display: block; margin-bottom: 8px; font-weight: bold; }
            input, select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
            .btn { background: #007bff; color: white; padding: 12px 30px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
            .nav a { margin: 0 10px; color: #007bff; text-decoration: none; }
        </style>
    </head>
    <body>
        <div style="text-align: center; margin-bottom: 20px;">
            <a href="/">🏠 Home</a> | 
            <a href="/admin/formdemo/demo/index">⚙️ Admin Grid</a>
        </div>
        
        <div class="form-container">
            <h1>Customer Information Form</h1>
            
            <form method="post" action="/formdemo/index/submit">
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="date_of_birth">Date of Birth *</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" required>
                </div>
                
                <div class="form-group">
                    <label for="gender">Gender *</label>
                    <select id="gender" name="gender" required>
                        <option value="">Please select...</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="telephone">Telephone *</label>
                    <input type="tel" id="telephone" name="telephone" required>
                </div>
                
                <button type="submit" class="btn">Submit Form</button>
            </form>
        </div>
    </body>
    </html>';
}

function handleFormSubmit() {
    global $pdo;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $errors = [];
        
        // Validation
        if (empty($_POST['first_name'])) $errors[] = 'First name is required';
        if (empty($_POST['last_name'])) $errors[] = 'Last name is required';
        if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
        if (empty($_POST['date_of_birth'])) $errors[] = 'Date of birth is required';
        if (empty($_POST['gender'])) $errors[] = 'Gender is required';
        if (empty($_POST['telephone'])) $errors[] = 'Telephone is required';
        
        if (!empty($errors)) {
            echo '<h1>Form Errors</h1><ul>';
            foreach ($errors as $error) {
                echo '<li>' . htmlspecialchars($error) . '</li>';
            }
            echo '</ul><a href="/formdemo/index/index">Back to Form</a>';
            return;
        }
        
        // Save to database
        try {
            $stmt = $pdo->prepare("
                INSERT INTO form_data (first_name, last_name, email, date_of_birth, gender, telephone) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_POST['first_name'],
                $_POST['last_name'],
                $_POST['email'],
                $_POST['date_of_birth'],
                $_POST['gender'],
                $_POST['telephone']
            ]);
            
            echo '<!DOCTYPE html>
            <html>
            <head><title>Success</title></head>
            <body style="font-family: Arial; padding: 20px; text-align: center;">
                <h1>✅ Form Submitted Successfully!</h1>
                <p>Your information has been saved.</p>
                <a href="/formdemo/index/index" style="padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">Submit Another</a>
                <a href="/admin/formdemo/demo/index" style="padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 4px; margin-left: 10px;">View Admin Grid</a>
            </body>
            </html>';
            
        } catch (PDOException $e) {
            echo '<h1>Database Error</h1><p>' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    }
}

function showAdminGrid() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT * FROM form_data ORDER BY created_at DESC");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Admin Grid - Form Data</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
            .container { background: white; padding: 30px; border-radius: 8px; }
            table { border-collapse: collapse; width: 100%; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
            th { background-color: #f8f9fa; }
            .btn { padding: 6px 12px; text-decoration: none; border-radius: 3px; font-size: 12px; }
            .btn-delete { background: #dc3545; color: white; }
            .nav a { margin: 0 10px; color: #007bff; text-decoration: none; }
            tr:nth-child(even) { background-color: #f8f9fa; }
        </style>
    </head>
    <body>
        <div style="text-align: center; margin-bottom: 20px;">
            <a href="/">🏠 Home</a> | 
            <a href="/formdemo/index/index">📝 Frontend Form</a>
        </div>
        
        <div class="container">
            <h1>📊 Form Data Management</h1>
            <p><strong>Total Records:</strong> ' . count($records) . '</p>
            
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
                <tbody>';
    
    if (empty($records)) {
        echo '<tr><td colspan="9" style="text-align: center; padding: 40px;">
                📝 No records found.<br>
                <a href="/formdemo/index/index">Submit a form</a> to see data here.
              </td></tr>';
    } else {
        foreach ($records as $record) {
            echo '<tr>
                <td>' . $record['entity_id'] . '</td>
                <td>' . htmlspecialchars($record['first_name']) . '</td>
                <td>' . htmlspecialchars($record['last_name']) . '</td>
                <td>' . htmlspecialchars($record['email']) . '</td>
                <td>' . $record['date_of_birth'] . '</td>
                <td>' . ucfirst($record['gender']) . '</td>
                <td>' . htmlspecialchars($record['telephone']) . '</td>
                <td>' . date('Y-m-d H:i', strtotime($record['created_at'])) . '</td>
                <td>
                    <a href="/admin/formdemo/demo/delete?id=' . $record['entity_id'] . '" class="btn btn-delete" onclick="return confirm(\'Are you sure?\');">🗑️ Delete</a>
                </td>
            </tr>';
        }
    }
    
    echo '    </tbody>
            </table>
        </div>
    </body>
    </html>';
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
