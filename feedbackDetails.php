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

class FeedbackDetails {
    private $conn;

    public function __construct(){
        $db = new DBConnector();
        $this -> conn = $db -> connect();


    }

    public function getFeedbackDetails() {
        try{
            $stmt = $this -> conn -> prepare("SELECT
                                              feedback.feedback_id,
                                              feedback.feedback,
                                              feedback.date,
                                              user.name AS name,
                                              user.user_type AS user_type,
                                              user.email AS email
                                              FROM feedback
                                              INNER JOIN user ON feedback.user_id = user.user_id");
            $stmt -> execute();
            $feedback = $stmt -> fetchAll(PDO::FETCH_ASSOC);
            return $feedback;

        }catch(PDOException $e)
        {
              http_response_code(500);
              echo json_encode(["message" => "Failed to retrieve feedbacks.".$e->getMessage()]);
              exit;
        }
    }

    public function deleteFeedback($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM feedback WHERE feedback_id = :id");
            $stmt->bindParam(':id', $id);
            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to delete feedback.".$e->getMessage()]);
            exit;
        }
    }
}


$feedbackDetails = new FeedbackDetails();

$requestMethod = $_SERVER["REQUEST_METHOD"];

switch($requestMethod) {
    case 'GET':
        $data = $feedbackDetails->getFeedbackDetails();
        if ($data) {
            http_response_code(200);
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "No feedback found."]);
        }
        break;

    case 'DELETE':
        $input = json_decode(file_get_contents("php://input"), true);
        if (!isset($input['id'])) {
            http_response_code(400);
            echo json_encode(["message" => "Feedback ID is required."]);
            exit;
        }
        $id = $input['id'];
        if ($feedbackDetails->deleteFeedback($id)) {
            http_response_code(200);
            echo json_encode(["message" => "Feedback deleted successfully."]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Failed to delete feedback."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"]);
        break;
}
