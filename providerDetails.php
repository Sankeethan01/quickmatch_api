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


class ProviderDetails {
    private $conn;



    public function __construct(){
        $db = new DBConnector();
        $this -> conn = $db -> connect();
    }

    public function getProviderDetails(){
        try
        {
            $query = "SELECT
                  user.name,
                  user.username,
                  user.email,
                  user.phone,
                  user.address,
                  user.national_id,
                  user.profile_image,
                  provider.provider_id AS provider_id,
                  provider.description AS description,
                  provider.services AS services,
                  provider.charge_per_day AS charge,
                  provider.qualification AS qualification,
                  provider.status AS status,
                  service.service_name AS service_category
                  FROM user
                  INNER JOIN provider ON user.user_id = provider.user_id
                  INNER JOIN service ON provider.service_category_id = service.service_category_id
                  WHERE user.user_type = 'provider' ";
                 
         $stmt = $this -> conn -> prepare($query);
         $stmt -> execute();
          
         $providers =  $stmt->fetchAll(PDO::FETCH_ASSOC);
         
        
         return $providers;
        
        }catch(PDOException $e){
            return json_encode(array('messsage' => 'Error:'.$e->getMessage()));
        }


    }

    public function getProviderCount() {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) AS provider_count FROM user WHERE user_type = 'provider'");
            $stmt->execute();
            $count = $stmt->fetch(PDO::FETCH_ASSOC);
            return $count;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to retrieve provider count.".$e->getMessage()]);
            exit;
        }
    }


    public function getLastFiveProviders() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM user WHERE user_type = 'provider' ORDER BY user_id DESC LIMIT 5");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $users;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to fetch last 5 providers." . $e->getMessage()]);
            exit;
        }
    }

    public function deleteProviderDetails($provider_id){
        try {
            $this->conn->beginTransaction();
    
          
            $stmt = $this->conn->prepare("SELECT user_id FROM provider WHERE provider_id = :provider_id");
            $stmt->bindParam(':provider_id', $provider_id);
            $stmt->execute();
            $user_id = $stmt->fetchColumn();
    
          
            $stmt = $this->conn->prepare("DELETE FROM provider WHERE provider_id = :provider_id");
            $stmt->bindParam(':provider_id', $provider_id);
            $stmt->execute();
    
            
            if ($user_id) {
                $stmt = $this->conn->prepare("DELETE FROM user WHERE user_id = :user_id");
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
            }
    
            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            http_response_code(500);
            echo json_encode(["message" => "Failed to delete provider data. " . $e->getMessage()]);
            return false;
        }
    }
}



//create an object for getting provider details
$providerDetails = new ProviderDetails();

$requestMethod = $_SERVER["REQUEST_METHOD"];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch($requestMethod) {
    case 'GET':
        if ($action == 'count') {
            $data = $providerDetails->getProviderCount();
            if ($data) {
                http_response_code(200);
                echo json_encode($data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "No providers found."]);
            }
         }elseif ($action == 'lastFive') {
            $data = $providerDetails->getLastFiveProviders();
            if ($data) {
                http_response_code(200);
                echo json_encode($data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "No provider found."]);
            }
        }
         else {
            $data = $providerDetails->getProviderDetails();
            if ($data) {
                http_response_code(200);
                echo json_encode($data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "No providers found."]);
            }
        }
        break;


        case 'DELETE':
            $input = json_decode(file_get_contents("php://input"), true);
            if (!isset($input['id'])) {
                http_response_code(400);
                echo json_encode(["message" => "Provider ID is required."]);
                exit;
            }
            $provider_id = $input['id'];
            if ($providerDetails->deleteProviderDetails($provider_id)) {
                http_response_code(200);
                echo json_encode(["message" => "Provider data deleted successfully."]);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Failed to delete provider data."]);
            }
            break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"]);
        break;
}

