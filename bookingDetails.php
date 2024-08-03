<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");



if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}


require_once 'dbconnector.php';

class BookingDetails
{
    private $conn;


    public function __construct()
    {
        $db = new DBConnector();
        $this->conn = $db->connect();
    }

    public function getAllBookings()
    {
        try {
            $query = "SELECT 
                  booking.booking_id,
                  service.service_name AS service_type, 
                  booking.booking_date,
                  booking.customer_name,
                  user.name AS provider_name, 
                  booking.booking_status
                  FROM 
                  booking
                  INNER JOIN 
                  service ON booking.service_category_id = service.service_category_id
                  INNER JOIN 
                  provider ON booking.provider_id = provider.provider_id
                  INNER JOIN 
                  user  ON provider.user_id = user.user_id";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $booking = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $booking;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to retrieve bookings." . $e->getMessage()]);
            exit;
        }
    }

    public function createBooking($data) {
        try {
            $query = "INSERT INTO booking (service_category_id, customer_name, provider_id, booking_status, phone, booking_date, booking_time, customer_address, additional_notes) 
                      VALUES (:service_category_id, :customer_name, :provider_id, :booking_status, :phone, :booking_date, :booking_time, :customer_address, :additional_notes)";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':service_category_id', $data['service_category_id']);
            $stmt->bindParam(':customer_name', $data['customer_name']);
            $stmt->bindParam(':provider_id', $data['provider_id']);
            $stmt->bindParam(':booking_status', $data['booking_status']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':booking_date', $data['booking_date']);
            $stmt->bindParam(':booking_time', $data['booking_time']);
            $stmt->bindParam(':customer_address', $data['customer_address']);
            $stmt->bindParam(':additional_notes', $data['additional_notes']);

            if ($stmt->execute()) {
                return ['success' => true, 'booking_id' => $this->conn->lastInsertId()];
            } else {
                return ['success' => false, 'error' => $stmt->errorInfo()];
            }
        } catch (Exception $e) {
            return ['error' => 'An error occurred while inserting data: ' . $e->getMessage()];
        }
    }

    public function getSuccessfulServiceCount()
    {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) AS successful_service_count FROM booking WHERE booking_status = 'Completed'");
            $stmt->execute();
            $count = $stmt->fetch(PDO::FETCH_ASSOC);
            return $count;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to retrieve successful service count." . $e->getMessage()]);
            exit;
        }
    }

    public function getTotalIncome()
    {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) AS total_income_count FROM booking WHERE booking_status != 'Declined'");
            $stmt->execute();
            $count = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalIncome = $count['total_income_count'] * 500; // Assuming each booking contributes 500 LKR
            return ["total_income" => $totalIncome];
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to calculate total income." . $e->getMessage()]);
            exit;
        }
    }

    public function getLastFourBooking() {
        try {
            $stmt = $this->conn->prepare("SELECT 
                                          booking.customer_name,
                                          booking.booking_date,
                                          booking.booking_status,
                                          user.profile_image AS profile_image,
                                          service.service_name AS service_category
                                          FROM 
                                          booking
                                          INNER JOIN 
                                          user ON booking.customer_name = user.name
                                          INNER JOIN 
                                          service ON booking.service_category_id = service.service_category_id
                                          ORDER BY 
                                          booking.booking_id DESC LIMIT 4;");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $users;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to fetch last 4 booking." . $e->getMessage()]);
            exit;
        }
    }


    public function deleteBookings($id)
    {
        try {
            $stmt = $this->conn->prepare("DELETE FROM booking WHERE booking_id = :id");
            $stmt->bindParam(':id', $id);
            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to delete booking data." . $e->getMessage()]);
            exit;
        }
    }
}


$bookingDetails = new BookingDetails();

$requestMethod = $_SERVER["REQUEST_METHOD"];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($requestMethod) {
    case 'GET':
        if ($action == 'count') {
            $data = $bookingDetails->getSuccessfulServiceCount();
            if ($data) {
                http_response_code(200);
                echo json_encode($data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "No successful services found."]);
            }
        } elseif ($action == 'totalIncome') {
            $data = $bookingDetails->getTotalIncome();
            if ($data) {
                http_response_code(200);
                echo json_encode($data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Failed to retrieve total income."]);
            }
        }
        elseif ($action == 'lastFour') {
            $data = $bookingDetails->getLastFourBooking();
            if ($data) {
                http_response_code(200);
                echo json_encode($data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "No booking found."]);
            }
        }
         
          
        else {
            $data = $bookingDetails->getAllBookings();
            if ($data) {
                http_response_code(200);
                echo json_encode($data);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "No booking data found."]);
            }
        }
        break;

        case 'POST':
            $data = json_decode(file_get_contents("php://input"), true);
            $booking = new BookingDetails();
            $result = $booking->createBooking($data);
            echo json_encode($result);
            break;


    case 'DELETE':
        $input = json_decode(file_get_contents("php://input"), true);
        if (!isset($input['id'])) {
            http_response_code(400);
            echo json_encode(["message" => "Booking ID is required."]);
            exit;
        }
        $id = $input['id'];
        if ($bookingDetails->deleteBookings($id)) {
            http_response_code(200);
            echo json_encode(["message" => "Booking data deleted successfully."]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Failed to delete Booking data."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"]);
        break;
}
