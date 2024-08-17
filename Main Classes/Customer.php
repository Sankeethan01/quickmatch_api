<?php
include_once 'Main Classes/User.php';

class Customer extends User{
    public function __construct()
    {
        parent::__construct();
    }

    public function getAllCustomers()
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM user WHERE user_type = 'customer' ORDER BY user_id DESC");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $users;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to fetch cutomers. " . $e->getMessage()]);
            exit;
        }
    }

}