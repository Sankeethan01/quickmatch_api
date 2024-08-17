<?php
require_once 'DbConnector.php';
class CustomerReview
{
    private $id;
    private $provider_id;
    private $reviewer;
    private $comment;
    private $rating;
    private $date;
    private $pdo;

    public function __construct()
    {
        $db = new DBConnector();
        $this->pdo = $db->connect();
    }

    public function addReview($provider_id, $reviewer, $comment, $rating)
    {
        $this->provider_id = $provider_id;
        $this->reviewer = $reviewer;
        $this->comment = $comment;
        $this->rating = $rating;
        try {
            $sql = "INSERT INTO review (provider_id, reviewer, comment, rating) VALUES (:provider_id, :reviewer, :comment, :rating)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':provider_id', $this->provider_id);
            $stmt->bindParam(':reviewer', $this->reviewer);
            $stmt->bindParam(':comment', $this->comment);
            $stmt->bindParam(':rating', $this->rating);

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
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to add customer reviews. " . $e->getMessage()]);
            exit;
        }
    }

    public function getReviewsByProviderId($provider_id)
    {
        $this->provider_id = $provider_id;
        try {
            $sql = "SELECT * FROM review WHERE provider_id = :provider_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':provider_id', $provider_id);
            $stmt->execute();
            $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($reviews) {
                return $reviews;
            } else {
                return [];
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to retrieve customer reviews. " . $e->getMessage()]);
            exit;
        }
    }
}
