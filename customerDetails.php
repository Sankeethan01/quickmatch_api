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

class CustomerDetails {
    private $con;

    public function __construct(){
        $db = new DBConnector();
        $this -> con = $db -> connect();


    }

    public function getCustomers() {
        try{
            $stmt = $this -> con -> prepare("SELECT * FROM user WHERE user_type = 'customer' ");
            $stmt -> execute();
            $customers = $stmt -> fetchAll(PDO::FETCH_ASSOC);
            return $customers;
        }catch(PDOException $e)
        {
              http_response_code(500);
              echo json_encode(["message" => "Failed to retrieve customers.".$e->getMessage()]);
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
            echo json_encode(["message" => "Failed to retrieve customer count.".$e->getMessage()]);
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
            echo json_encode(["message" => "Failed to fetch last 5 users." . $e->getMessage()]);
            exit;
        }
    }

     
    public function deleteCustomer($id) {
        try {
            $stmt = $this->con->prepare("DELETE FROM user WHERE user_id = :id");
            $stmt->bindParam(':id', $id);
            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to delete Customer Data.".$e->getMessage()]);
            exit;
        }
    }

}


$customerDetails = new CustomerDetails();

$requestMethod = $_SERVER["REQUEST_METHOD"];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch($requestMethod) {
    case 'GET':
        if ($action == 'count') {
            $data = $customerDetails->getCustomerCount();
            if ($data) {
                http_response_code(200);
                echo json_encode($data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "No customers found."]);
            }
        }elseif ($action == 'lastFive') {
            $data = $customerDetails->getLastFiveCustomers();
            if ($data) {
                http_response_code(200);
                echo json_encode($data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "No customer found."]);
            }
        }
         
        else {
            $data = $customerDetails->getCustomers();
            if ($data) {
                http_response_code(200);
                echo json_encode($data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "No customer found."]);
            }
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

