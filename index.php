<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

$host = "mysql";
$dbname = "magento";
$username = "magento";
$password = "magento";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $createTable = "
    CREATE TABLE IF NOT EXISTS form_data (
        entity_id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(255) NOT NULL,
        last_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        date_of_birth DATE NOT NULL,
        gender VARCHAR(10) NOT NULL,
        telephone VARCHAR(20) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($createTable);
    
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
    exit;
}

$requestUri = $_SERVER["REQUEST_URI"];
$path = trim(parse_url($requestUri, PHP_URL_PATH), "/");

if (empty($path) || $path === "index.php") {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Magento 2 FormDemo Module</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #333; text-align: center; margin-bottom: 30px; }
            .links { text-align: center; }
            .links a { display: inline-block; margin: 10px; padding: 15px 25px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; }
            .links a:hover { background: #2980b9; }
            .status { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        </style>
    </head>
    <body>
        <div class=\"container\">
            <div class=\"status\">✅ Docker containers are running successfully!</div>
            <h1>🚀 Magento 2 FormDemo Module</h1>
            <div class=\"links\">
                <a href=\"/formdemo/index/index\">📝 Frontend Form</a>
                <a href=\"/admin/formdemo/demo/index\">🛠️ Admin Grid</a>
                <a href=\"/info.php\">ℹ️ PHP Info</a>
            </div>
        </div>
    </body>
    </html>";
    exit;
}

if ($path === "info.php") {
    phpinfo();
    exit;
}

if (strpos($path, "formdemo/index/index") === 0) {
    include __DIR__ . "/app/code/EMP123/FormDemo/view/frontend/templates/form.phtml";
    exit;
}

if (strpos($path, "formdemo/index/submit") === 0) {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $data = json_decode(file_get_contents("php://input"), true) ?: $_POST;
        
        $errors = [];
        if (empty($data["first_name"])) $errors[] = "First name is required";
        if (empty($data["last_name"])) $errors[] = "Last name is required";
        if (empty($data["email"]) || !filter_var($data["email"], FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
        if (empty($data["date_of_birth"])) $errors[] = "Date of birth is required";
        if (empty($data["gender"])) $errors[] = "Gender is required";
        if (empty($data["telephone"])) $errors[] = "Telephone is required";
        
        if (!empty($errors)) {
            header("Content-Type: application/json");
            echo json_encode(["success" => false, "errors" => $errors]);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO form_data (first_name, last_name, email, date_of_birth, gender, telephone) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data["first_name"],
                $data["last_name"], 
                $data["email"],
                $data["date_of_birth"],
                $data["gender"],
                $data["telephone"]
            ]);
            
            header("Content-Type: application/json");
            echo json_encode(["success" => true, "message" => "✅ Form submitted successfully!"]);
            exit;
            
        } catch (PDOException $e) {
            header("Content-Type: application/json");
            echo json_encode(["success" => false, "errors" => ["Database error: " . $e->getMessage()]]);
            exit;
        }
    }
}

if (strpos($path, "admin/formdemo/demo/index") === 0) {
    $stmt = $pdo->query("SELECT * FROM form_data ORDER BY created_at DESC");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Admin Grid - Form Data</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
            .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #333; margin-bottom: 20px; }
            .stats { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
            table { border-collapse: collapse; width: 100%; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
            th { background-color: #f2f2f2; font-weight: bold; }
            .btn { padding: 8px 15px; margin: 2px; text-decoration: none; border-radius: 4px; font-size: 12px; }
            .btn-delete { background: #dc3545; color: white; }
            .nav { text-align: center; margin-bottom: 20px; }
            .nav a { margin: 0 10px; color: #3498db; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class=\"nav\">
            <a href=\"/\">🏠 Home</a> | 
            <a href=\"/formdemo/index/index\">📝 Frontend Form</a> | 
            <a href=\"/admin/formdemo/demo/index\">🛠️ Admin Grid</a> | 
            <a href=\"/info.php\">ℹ️ PHP Info</a>
        </div>
        <div class=\"container\">
            <h1>📊 Form Data Admin Grid</h1>
            <div class=\"stats\">📈 Total Records: " . count($records) . "</div>
            <table>
                <tr>
                    <th>ID</th><th>First Name</th><th>Last Name</th><th>Email</th>
                    <th>Date of Birth</th><th>Gender</th><th>Telephone</th><th>Created</th><th>Actions</th>
                </tr>";
    
    if (empty($records)) {
        echo "<tr><td colspan=\"9\" style=\"text-align: center; padding: 40px; color: #666;\">
                📝 No records found. <a href=\"/formdemo/index/index\">Submit a form</a> to see data here.
              </td></tr>";
    } else {
        foreach ($records as $record) {
            echo "<tr>
                <td>" . $record["entity_id"] . "</td>
                <td>" . $record["first_name"] . "</td>
                <td>" . $record["last_name"] . "</td>
                <td>" . $record["email"] . "</td>
                <td>" . $record["date_of_birth"] . "</td>
                <td>" . ucfirst($record["gender"]) . "</td>
                <td>" . $record["telephone"] . "</td>
                <td>" . date("Y-m-d H:i", strtotime($record["created_at"])) . "</td>
                <td>
                    <a href=\"/admin/formdemo/demo/delete?id=" . $record["entity_id"] . "\" class=\"btn btn-delete\" onclick=\"return confirm(\\\"Are you sure?\\\")\">🗑️ Delete</a>
                </td>
            </tr>";
        }
    }
    
    echo "</table>
        </div>
    </body>
    </html>";
    exit;
}

if (strpos($path, "admin/formdemo/demo/delete") === 0 && isset($_GET["id"])) {
    $stmt = $pdo->prepare("DELETE FROM form_data WHERE entity_id = ?");
    $stmt->execute([$_GET["id"]]);
    header("Location: /admin/formdemo/demo/index");
    exit;
}

http_response_code(404);
echo "<!DOCTYPE html>
<html><head><title>404 - Not Found</title></head><body>
<h1>404 - Page Not Found</h1>
<p><a href=\"/\">🏠 Go to Home</a></p>
</body></html>";
?>
