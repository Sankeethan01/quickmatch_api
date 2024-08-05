<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'DbConnector.php';

class CustomerDetails {
    private $con;

    public function __construct() {
        $db = new DBConnector();
        $this->con = $db->connect();
    }
    public function getCustomers($user_id = null) {
        try {
            if ($user_id) {
                $stmt = $this->con->prepare("SELECT * FROM user WHERE user_id = ?");
                $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
               
            } else {
                $stmt = $this->con->prepare("SELECT * FROM user");
            }
            $stmt->execute();
            $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $customers;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to retrieve customers. " . $e->getMessage()]);
            exit;
        }
    }

    public function getCustomerCount() {
        try {
            $stmt = $this->con->prepare("SELECT COUNT(*) AS customer_count FROM user WHERE user_type = 'customer'");
            $stmt->execute();
            $count = $stmt->fetch(PDO::FETCH_ASSOC);
            return $count;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to retrieve customer count. " . $e->getMessage()]);
            exit;
        }
    }

    public function getLastFiveCustomers() {
        try {
            $stmt = $this->con->prepare("SELECT * FROM user WHERE user_type = 'customer' ORDER BY user_id DESC LIMIT 5");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $users;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to fetch last 5 users. " . $e->getMessage()]);
            exit;
        }
    }

    public function updateCustomerDetails($id, $name, $username, $phone, $address, $national_id, $profile_image = null) {
        try {
            if ($profile_image) {
                $stmt = $this->con->prepare("UPDATE user SET name = :name, username = :username, phone = :phone, address = :address, profile_image = :profile_image, national_id = :national_id WHERE user_id = :id");
                $stmt->execute([
                    'name' => $name,
                    'username' => $username,
                    'phone' => $phone,
                    'address' => $address,
                    'national_id' => $national_id,
                    'profile_image' => $profile_image,
                    'id' => $id
                ]);
            } else {
                $stmt = $this->con->prepare("UPDATE user SET name = :name, username = :username, phone = :phone, address = :address, national_id = :national_id WHERE user_id = :id");
                $stmt->execute([
                    'name' => $name,
                    'username' => $username,
                    'phone' => $phone,
                    'address' => $address,
                    'national_id' => $national_id,
                    'id' => $id
                ]);
            }
            return ['success' => true];
        } catch (PDOException $e) {
            http_response_code(500);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function deleteCustomer($id) {
        try {
            $stmt = $this->con->prepare("DELETE FROM user WHERE user_id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to delete Customer Data. " . $e->getMessage()]);
            exit;
        }
    }
}

$customerDetails = new CustomerDetails();

$requestMethod = $_SERVER["REQUEST_METHOD"];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($requestMethod) {
    case 'GET':
        if ($action == 'count') {
            $data = $customerDetails->getCustomerCount();
            http_response_code(200);
            echo json_encode($data);
        } elseif ($action == 'lastFive') {
            $data = $customerDetails->getLastFiveCustomers();
            http_response_code(200);
            echo json_encode($data);
        } else {
            $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
            $data = $customerDetails->getCustomers($user_id);
            http_response_code(200);
            echo json_encode($data);
        }
        break;

    case 'POST':
        $id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT) : null;
        $name = isset($_POST['name']) ? filter_var($_POST['name'], FILTER_SANITIZE_STRING) : null;
        $username = isset($_POST['username']) ? filter_var($_POST['username'], FILTER_SANITIZE_STRING) : null;
        $phone = isset($_POST['phone']) ? filter_var($_POST['phone'], FILTER_SANITIZE_STRING) : null;
        $address = isset($_POST['address']) ? filter_var($_POST['address'], FILTER_SANITIZE_STRING) : null;
        $national_id = isset($_POST['national_id']) ? filter_var($_POST['national_id'], FILTER_SANITIZE_STRING) : null;
        $profile_image = null;

        if ($id && $name && $username && $phone && $address && $national_id) {
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
                $uploadDir = 'profile_images/';
                $uploadFile = $uploadDir . basename($_FILES['profile_image']['name']);
                $imageFileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));

                $check = getimagesize($_FILES['profile_image']['tmp_name']);
                if ($check !== false && $_FILES['profile_image']['size'] <= 500000) {
                    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadFile)) {
                        $profile_image = basename($uploadFile);
                    } else {
                        http_response_code(500);
                        echo json_encode(["message" => "Failed to upload profile image."]);
                        exit;
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(["message" => "Invalid profile image."]);
                    exit;
                }
            }
            $result = $customerDetails->updateCustomerDetails($id, $name, $username, $phone, $address, $national_id, $profile_image);
            if ($result['success']) {
                http_response_code(200);
                echo json_encode(["message" => "Customer details updated successfully."]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => $result['message']]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "All fields are required."]);
        }
        break;

    case 'DELETE':
        $input = json_decode(file_get_contents("php://input"), true);
        if (!isset($input['id'])) {
            http_response_code(400);
            echo json_encode(["message" => "User ID is required."]);
            exit;
        }
        $id = $input['id'];
        if ($customerDetails->deleteCustomer($id)) {
            http_response_code(200);
            echo json_encode(["message" => "Customer data deleted successfully."]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Failed to delete customer data."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"]);
        break;
}

