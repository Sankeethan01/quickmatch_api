
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

class ProviderDetails
{
    private $conn;

    public function __construct()
    {
        $db = new DBConnector();
        $this->conn = $db->connect();
    }

    public function getProviderDetails($user_id = null)
    {
        try {
            $query = "SELECT
                user.name,
                user.username,
                user.email,
                user.phone,
                user.user_type,
                user.address,
                user.national_id,
                user.profile_image,
                provider.provider_id AS provider_id,
                provider.description AS description,
                provider.services AS services,
                provider.description,
                provider.charge_per_day AS charge,
                provider.qualification AS qualification,
                provider.status AS status,
                service.service_name AS service_category
                FROM user
                INNER JOIN provider ON user.user_id = provider.user_id
                INNER JOIN service ON provider.service_category_id = service.service_category_id
                WHERE user.user_id = ? ";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $providers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $providers;
        } catch (PDOException $e) {
            return json_encode(['message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function getAllProviderDetails()
    {
        try {
            $query = "SELECT
                user.name,
                user.username,
                user.email,
                user.phone,
                user.user_type,
                user.address,
                user.national_id,
                user.profile_image,
                provider.provider_id AS provider_id,
                provider.description AS description,
                provider.services AS services,
                provider.description,
                provider.charge_per_day AS charge,
                provider.qualification AS qualification,
                provider.status AS status,
                service.service_name AS service_category
                FROM user
                INNER JOIN provider ON user.user_id = provider.user_id
                INNER JOIN service ON provider.service_category_id = service.service_category_id
                WHERE user.user_type = 'provider';
                ";

            $stmt = $this->conn->prepare($query);

            $stmt->execute();
            $providers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $providers;
        } catch (PDOException $e) {
            error_log("Error in getAllProviderDetails: " . $e->getMessage());
            return json_encode(['message' => 'Error: ' . $e->getMessage()]);
        }
    }



    public function getProviderCount()
    {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) AS provider_count FROM user WHERE user_type = 'provider'");
            $stmt->execute();
            $count = $stmt->fetch(PDO::FETCH_ASSOC);
            return $count;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to retrieve provider count." . $e->getMessage()]);
            exit;
        }
    }

    public function getLastFiveProviders()
    {
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

    public function deleteProviderDetails($provider_id)
    {
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

    public function getProviderBookingsById($provider_id)
    {
        try {


            $stmt = $this->conn->prepare("SELECT * FROM booking WHERE provider_id = :provider_id AND booking_status = 'Pending'");
            $stmt->bindParam(':provider_id', $provider_id);
            $stmt->execute();
            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $bookings;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to retrieve bookings. " . $e->getMessage()]);
            return false;
        }
    }

    public function updateBookingStatus($provider_id, $booking_id, $status)
    {
        try {
            $stmt = $this->conn->prepare("UPDATE booking SET booking_status = :status WHERE booking_id = :booking_id AND provider_id = :provider_id");
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':booking_id', $booking_id);
            $stmt->bindParam(':provider_id', $provider_id);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to update booking status. " . $e->getMessage()]);
            return false;
        }
    }

    public function customerFeedbackById($provider_id)
    {
        try {
            $query = "SELECT id, reviewer, comment, rating, timestamp 
                  FROM review 
                  WHERE provider_id = :provider_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':provider_id', $provider_id, PDO::PARAM_INT);
            $stmt->execute();
            $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $reviews;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to retrieve reviews. " . $e->getMessage()]);
            exit;
        }
    }
}



$providerDetails = new ProviderDetails();

$requestMethod = $_SERVER["REQUEST_METHOD"];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($requestMethod) {
    case 'GET':
        error_log("GET request received. Action: " . $action);
        if ($action == 'count') {
            $data = $providerDetails->getProviderCount();
            if ($data) {
                http_response_code(200);
                echo json_encode($data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "No providers found."]);
            }
        } elseif ($action == 'lastFive') {
            $data = $providerDetails->getLastFiveProviders();
            if ($data) {
                http_response_code(200);
                echo json_encode($data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "No provider found."]);
            }
        } elseif ($action == 'getProviderBookingsById') {
            if (!isset($_GET['provider_id'])) {
                http_response_code(400);
                echo json_encode(["message" => "Provider ID is required."]);
                exit;
            }
            $provider_id = $_GET['provider_id'];
            $data = $providerDetails->getProviderBookingsById($provider_id);
            if ($data) {
                http_response_code(200);
                echo json_encode($data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "No bookings found."]);
            }
        } elseif ($action == 'customerFeedbackById') {
            $provider_id = isset($_GET['provider_id']) ? $_GET['provider_id'] : '';
            if ($provider_id) {
                $data = $providerDetails->customerFeedbackById($provider_id);
                if ($data) {
                    http_response_code(200);
                    echo json_encode($data);
                } else {
                    http_response_code(404);
                    echo json_encode(["message" => "No reviews found."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Provider ID is required.


"]);
            }
        } elseif ($action == 'getAll'){
            $data = $providerDetails->getAllProviderDetails();
            if ($data) {
                http_response_code(200);
                echo json_encode($data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "No provider details found."]);
            }
        }
          
        else {
            
            $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
            $data = $providerDetails->getProviderDetails($user_id);
            http_response_code(200);
            echo json_encode($data);
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

    case 'POST':
        $input = json_decode(file_get_contents("php://input"), true);
        if (isset($input['action']) && $input['action'] === 'updateStatus') {
            $provider_id = $input['provider_id'];
            $booking_id = $input['booking_id'];
            $status = $input['status'];
            if ($providerDetails->updateBookingStatus($provider_id, $booking_id, $status)) {
                http_response_code(200);
                echo json_encode(["message" => "Booking status updated successfully."]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Failed to update booking status."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Invalid action."]);
        }
        break;


    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"]);
        break;
}
?>