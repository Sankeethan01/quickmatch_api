<?php
require_once 'DbConnector.php';
class Booking
{
    private $booking_id;
    private $service_category_id;
    private $customer_name;
    private $provider_Name;
    private $provider_id;
    private $customer_id;
    private $booking_status;
    private $booking_date;
    private $service;
    private $customer_address;
    private $additional_notes;
    private $pdo;

    public function __construct()
    {
        $db = new DBConnector();
        $this->pdo = $db->connect();
    }

    public function createBooking($service_category_id, $customer_name, $provider_Name,$provider_id, $customer_id, $booking_status, $booking_date, $service, $customer_address, $additional_notes)
    {
        $this->service_category_id = $service_category_id;
        $this->customer_name = $customer_name;
        $this->provider_Name = $provider_Name;
        $this->provider_id = $provider_id;
        $this->customer_id = $customer_id;
        $this->booking_status = $booking_status;
        $this->booking_date = $booking_date;
        $this->service = $service;
        $this->customer_address = $customer_address;
        $this->additional_notes = $additional_notes;
        try {
            $query = "INSERT INTO booking (service_category_id, customer_name, provider_Name, provider_id, customer_id, booking_status, booking_date, service, customer_address, additional_notes) 
                      VALUES (:service_category_id, :customer_name, :provider_Name, :provider_id, :customer_id, :booking_status, :booking_date, :service, :customer_address, :additional_notes)";

            $stmt = $this->pdo->prepare($query);

            $stmt->bindParam(':service_category_id', $this->service_category_id);
            $stmt->bindParam(':customer_name',  $this->customer_name);
            $stmt->bindParam(':provider_Name',  $this->provider_Name);
            $stmt->bindParam(':provider_id', $this->provider_id);
            $stmt->bindParam(':customer_id', $this->customer_id);
            $stmt->bindParam(':booking_status', $this->booking_status);
            $stmt->bindParam(':booking_date', $this->booking_date);
            $stmt->bindParam(':service',  $this->service);
            $stmt->bindParam(':customer_address',  $this->customer_address);
            $stmt->bindParam(':additional_notes', $this->additional_notes);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'booking successful'];
            } else {
                return ['success' => false, 'error' => $stmt->errorInfo()];
            }
        } catch (Exception $e) {
            return ['error' => 'An error occurred while inserting data: ' . $e->getMessage()];
        }
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

            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            $booking = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $booking;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to retrieve bookings." . $e->getMessage()]);
            exit;
        }
    }

    public function getBookingForCustomer($user_id)
    {
        $this->customer_id = $user_id;
        try {
            $sql = "SELECT 
                  b.booking_id,
                  b.service_category_id,
                  s.service_name,
                  b.customer_address,
                  b.customer_name,
                  b.provider_name,
                  b.additional_notes,
                  b.provider_id,
                  b.booking_date,
                  b.booking_status,
                  b.service
                FROM 
                  booking b
                JOIN 
                service s ON b.service_category_id = s.service_category_id
                WHERE 
                b.customer_id = :user_id
                ORDER BY 
                     b.booking_id DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':user_id', $this->customer_id);
            $stmt->execute();
            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $bookings;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to retrieve booking details. " . $e->getMessage()]);
            exit;
        }
    }

    public function getBookingForProvider($provider_id)
    {
        $this->provider_id = $provider_id;
        try {
            $sql = "SELECT 
                  b.booking_id,
                  b.service_category_id,
                  s.service_name,
                  b.customer_address,
                  b.customer_name,
                  b.provider_name,
                  b.additional_notes,
                  b.provider_id,
                  b.booking_date,
                  b.booking_status,
                  b.service
                FROM 
                  booking b
                JOIN 
                service s ON b.service_category_id = s.service_category_id
                WHERE 
                b.provider_id = :provider_id
                ORDER BY 
                     b.booking_id DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':provider_id', $this->provider_id);
            $stmt->execute();
            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $bookings;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to retrieve booking details. " . $e->getMessage()]);
            exit;
        }
    }


    public function changeBookingStatus($booking_id, $booking_status)
    {
        $this->booking_id = $booking_id;
        $this->booking_status = $booking_status;
        try {
            $sql = "UPDATE booking SET booking_status = :booking_status WHERE booking_id = :booking_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':booking_status', $this->booking_status);
            $stmt->bindParam(':booking_id', $this->booking_id);
            $result = $stmt->execute();

            return $result;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to retrieve booking details. " . $e->getMessage()]);
            exit;
        }
    }

    public function getSuccessfulServiceCount($booking_status)
   {
       $this->booking_status = $booking_status;

    try {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) AS success_count FROM booking WHERE booking_status = :booking_status");
        $stmt->bindParam(':booking_status',$this->booking_status);
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        return $count;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Failed to retrieve successful service count. " . $e->getMessage()]);
        exit;
    }
   }

   public function getTotalIncome()
    {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) AS total_income_count FROM booking WHERE booking_status != 'Declined-provider'");
            $stmt->execute();
            $count = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalIncome = $count['total_income_count'] * 500; 
            return ["total_income" => $totalIncome];
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to calculate total income." . $e->getMessage()]);
            exit;
        }
    }

    public function getLastFiveBookings()
    {
        try {
            $stmt = $this->pdo->prepare("SELECT 
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
                                          booking.booking_id DESC LIMIT 5;");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $users;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to fetch last 4 booking." . $e->getMessage()]);
            exit;
        }
    }
}
