<?php
require_once 'DbConnector.php';
class Messages{
    private $message_id;
    private $name;
    private $email;
    private $message;
    private $date;
    private $pdo;

    public function __construct()
    {
        $db = new DBConnector();
        $this->pdo = $db->connect();
    }

    public function getAllMessages()
    {
        try{
            $stmt = $this -> pdo-> prepare("SELECT * FROM message");
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
            $stmt = $this->pdo->prepare("DELETE FROM message WHERE message_id = :id");
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