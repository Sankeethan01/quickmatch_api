<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'DbConnector.php';

class VerificationDetails {
    private $conn;

    public function __construct(){
        $db = new DBConnector();
        $this->conn = $db->connect();
    }

    public function getDetails() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM verification");
            $stmt->execute();
            $verification = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $verification;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to retrieve verifications. " . $e->getMessage()]);
            exit;
        }
    }

    public function deleteVerification($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM verification WHERE verify_id = :id");
            $stmt->bindParam(':id', $id);
            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to delete verification data. " . $e->getMessage()]);
            exit;
        }
    }

    public function verificationProcess($data, $file) {
        try {
            $targetDir = "verifypdfs/";
            $fileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

            // Check if file is a valid PDF
            if ($fileType != "pdf") {
                http_response_code(400);
                echo json_encode(["message" => "Only PDF files are allowed."]);
                return false;
            }

            // Check file size (e.g., max 5MB)
            if ($file["size"] > 5000000) {
                http_response_code(400);
                echo json_encode(["message" => "File is too large."]);
                return false;
            }

            // Generate a unique name for the file to avoid conflicts
            $uniqueFileName = uniqid() . "_" . basename($file["name"]);
            $targetFile = $targetDir . $uniqueFileName;

            // Move the file to the target directory
            if (!move_uploaded_file($file["tmp_name"], $targetFile)) {
                http_response_code(500);
                echo json_encode(["message" => "Failed to upload file."]);
                return false;
            }

             // Hash the password
             $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

            // Insert data into the database
            $stmt = $this->conn->prepare("INSERT INTO verification (provider_name,provider_username,provider_password,provider_email,service_category,proof,description,provider_address,mobile_number) 
            VALUES (:provider_name,:provider_username,:provider_password,:provider_email,:service_category,:proof,:description,:provider_address,:mobile_number)");
            $stmt->bindParam(':provider_name', $data['fullName']);
            $stmt->bindParam(':provider_username', $data['username']);
            $stmt->bindParam(':provider_password',  $hashedPassword);
            $stmt->bindParam(':provider_email', $data['email']);
            $stmt->bindParam(':service_category', $data['serviceCategory']);
            $stmt->bindParam(':proof', $uniqueFileName);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':provider_address', $data['address']);
            $stmt->bindParam(':mobile_number', $data['mobile']);
            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode(["message" => "Provider registered successfully!"]);
                return true;
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Failed to save provider data."]);
                return false;
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to process verification. " . $e->getMessage()]);
            return false;
        }
    }

    public function verifyProvider($id) {
        try {
            // Fetch verification details using verify_id
            $stmt = $this->conn->prepare("SELECT * FROM verification WHERE verify_id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $verificationData = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$verificationData) {
                return ['success' => false, 'message' => "Verification data not found"];
            }
    
            $user_type = 'provider'; 
         
            // Insert data into the users table
            $stmt = $this->conn->prepare("INSERT INTO user (name, username, email, password, phone, user_type, address) 
                VALUES (:name, :username, :email, :password, :mobile, :user_type, :address)");
            $stmt->bindParam(':name', $verificationData['provider_name']);
            $stmt->bindParam(':username', $verificationData['provider_username']);
            $stmt->bindParam(':email', $verificationData['provider_email']);
            $stmt->bindParam(':password', $verificationData['provider_password']);
            $stmt->bindParam(':mobile', $verificationData['mobile_number']);
            $stmt->bindParam(':user_type', $user_type);
            $stmt->bindParam(':address', $verificationData['provider_address']);
    
            if ($stmt->execute()) {
                // Get the auto-generated user_id
                $userId = $this->conn->lastInsertId();
    
                // Map service category to service_category_id
                $serviceCategoryMap = [
                    'Electric Service' => 'S01',
                    'Electronic Service' => 'S02',
                    'Construction Service' => 'S03',
                    'Event Management Service' => 'S04'
                ];
                $service_category_id = $serviceCategoryMap[$verificationData['service_category']] ?? null;
    
                // Insert additional data into the provider table
                $stmt = $this->conn->prepare("INSERT INTO provider (verify_id, description, service_category_id, user_id) 
                    VALUES (:verify_id, :description, :service_category_id, :user_id)");
                $stmt->bindParam(':verify_id', $verificationData['verify_id']);
                $stmt->bindParam(':description', $verificationData['description']);
                $stmt->bindParam(':service_category_id', $service_category_id);
                $stmt->bindParam(':user_id', $userId);
    
                if ($stmt->execute()) {
                    return ['success' => true, 'message' => "Provider verified successfully"];
                } else {
                    $errorInfo = $stmt->errorInfo();
                    return ['success' => false, 'message' => "Failed to save provider data: " . $errorInfo[2]];
                }
            } else {
                $errorInfo = $stmt->errorInfo();
                return ['success' => false, 'message' => "Failed to save user data: " . $errorInfo[2]];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => "Failed to process verification: " . $e->getMessage()];
        }
    }
    
}

$verificationDetails = new VerificationDetails();
$requestMethod = $_SERVER["REQUEST_METHOD"];

switch($requestMethod) {
    case 'GET':
        $data = $verificationDetails->getDetails();
        if ($data) {
            http_response_code(200);
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "No verification data found."]);
        }
        break;

    case 'DELETE':
        $input = json_decode(file_get_contents("php://input"), true);
        if (!isset($input['id'])) {
            http_response_code(400);
            echo json_encode(["message" => "Verification ID is required."]);
            exit;
        }
        $id = $input['id'];
        if ($verificationDetails->deleteVerification($id)) {
            http_response_code(200);
            echo json_encode(["message" => "Verification data deleted successfully."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to delete verification data."]);
        }
        break;

        case 'POST':
            if (!empty($_FILES['proof']) && !empty($_POST)) {
                $input = $_POST;
                $file = $_FILES['proof'];
                $verificationDetails->verificationProcess($input, $file);
            } else {
                $input = json_decode(file_get_contents("php://input"), true);
                if (!isset($input['id'])) {
                    http_response_code(400);
                    echo json_encode(["message" => "Verification ID is required."]);
                    exit;
                }
                $id = $input['id'];
                $result = $verificationDetails->verifyProvider($id);
                if ($result['success']) {
                    http_response_code(200);
                    echo json_encode(["message" => "Provider verified successfully!"]);
                } else {
                    http_response_code(500);
                    echo json_encode(["message" => $result['message']]);
                }
            }
            break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method Not Allowed"]);
        break;
}

