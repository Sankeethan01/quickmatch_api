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

class Messages {
    private $conn;

    public function __construct(){
        $db = new DBConnector();
        $this -> conn = $db -> connect();


    }

    public function getMessages() {
        try{
            $stmt = $this -> conn -> prepare("SELECT * FROM message");
            $stmt -> execute();
            $message = $stmt -> fetchAll(PDO::FETCH_ASSOC);
            return $message;

        }catch(PDOException $e)
        {
              http_response_code(500);
              echo json_encode(["message" => "Failed to retrieve messages.".$e->getMessage()]);
              exit;
        }
    }

    public function deleteMessage($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM message WHERE message_id = :id");
            $stmt->bindParam(':id', $id);
            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to delete message.".$e->getMessage()]);
            exit;
        }
    }
}


$messages = new Messages();

$requestMethod = $_SERVER["REQUEST_METHOD"];

switch($requestMethod) {
    case 'GET':
        $data = $messages->getMessages();
        if ($data) {
            http_response_code(200);
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "No messages found."]);
        }
        break;

    case 'DELETE':
        $input = json_decode(file_get_contents("php://input"), true);
        if (!isset($input['id'])) {
            http_response_code(400);
            echo json_encode(["message" => "Message ID is required."]);
            exit;
        }
        $id = $input['id'];
        if ($messages->deleteMessage($id)) {
            http_response_code(200);
            echo json_encode(["message" => "Message deleted successfully."]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Failed to delete message."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"]);
        break;
}