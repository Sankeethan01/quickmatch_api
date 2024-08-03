<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'DbConnector.php';

class Services {
    private $conn;

    public function __construct() {
        $db = new DBConnector();
        $this->conn = $db->connect();
    }

    private function fetchServiceProviders($category_id) {
        try {
            $query = "SELECT u.name, u.user_id, u.address, u.email, u.phone, u.username, u.user_type, 
                             p.description, p.status, p.service_category_id,p.services, p.provider_id, 
                             u.profile_image, s.service_name 
                      FROM user u
                      INNER JOIN provider p ON u.user_id = p.user_id
                      INNER JOIN service s ON s.service_category_id = p.service_category_id
                      WHERE p.service_category_id = :category_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':category_id', $category_id);
            $stmt->execute();
            $providers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $providers;
        } catch (PDOException $e) {
            return json_encode(array('message' => 'Error: ' . $e->getMessage()));
        }
    }

    public function getElectricServiceProviders() {
        return $this->fetchServiceProviders('S01');
    }

    public function getElectronicServiceProviders() {
        return $this->fetchServiceProviders('S02');
    }

    public function getConstructionServiceProviders() {
        return $this->fetchServiceProviders('S03');
    }

    public function getEventManagementServiceProviders() {
        return $this->fetchServiceProviders('S04');
    }
}



$service = new Services();
$requestMethod = $_SERVER["REQUEST_METHOD"];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($requestMethod) {
    case 'GET':
        if ($action == 'electric') {
            $data = $service->getElectricServiceProviders();
        } elseif ($action == 'electronic') {
            $data = $service->getElectronicServiceProviders();
        } elseif ($action == 'construction') {
            $data = $service->getConstructionServiceProviders();
        } elseif ($action == 'event') {
            $data = $service->getEventManagementServiceProviders();
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Invalid action."]);
            exit;
        }

        if ($data) {
            http_response_code(200);
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "No service providers found."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"]);
        break;
}