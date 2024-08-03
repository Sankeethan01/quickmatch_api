<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

require 'DbConnector.php';

class Review {
    private $conn;

    public function __construct() {
        $db = new DbConnector();
        $this->conn = $db->connect();
    }

    public function getReviews($provider_id) {
        $sql = "SELECT * FROM review WHERE provider_id = :provider_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':provider_id', $provider_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function submitReview($data) {
        $provider_id = $data['provider_id'];
        $reviewer = $data['reviewer'];
        $comment = $data['comment'];
        $rating = $data['rating'];

        $sql = "INSERT INTO review (provider_id, reviewer, comment, rating) VALUES (:provider_id, :reviewer, :comment, :rating)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':provider_id', $provider_id, PDO::PARAM_INT);
        $stmt->bindParam(':reviewer', $reviewer, PDO::PARAM_STR);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);

        $response = [];

        if ($stmt->execute()) {
            $response['provider_id'] = $provider_id;
            $response['reviewer'] = $reviewer;
            $response['comment'] = $comment;
            $response['rating'] = $rating;
            $response['timestamp'] = date('Y-m-d H:i:s');
        } else {
            $response['error'] = $stmt->errorInfo();
        }

        return $response;
    }
}

$review = new Review();
$method = $_SERVER['REQUEST_METHOD'];
$response = [];

if ($method === 'GET') {
    $provider_id = isset($_GET['provider_id']) ? intval($_GET['provider_id']) : 0;
    $response = $review->getReviews($provider_id);
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $response = $review->submitReview($data);
}

echo json_encode($response);
?>
