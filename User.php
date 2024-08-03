<?php

class User {
    private $conn;
    private $table_name = "user";

    public $id;
    public $username;
    public $email;
    public $password;

    public $user_type;
   

    public function __construct($db) {
        $this->conn = $db;
    }

    public function signup() {
        if($this->isAlreadyExist()) {
            return false;
        }

        $query = "INSERT INTO " . $this->table_name . " (username, email, password) VALUES (:username, :email, :password)";
        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = htmlspecialchars(strip_tags($this->password));
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function login($rememberMe = false) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if($user && password_verify($this->password, $user['password'])) {
            $this->id = $user['user_id'];
            $this->username = $user['username'];
            $this->user_type = $user['user_type'];
            
            if ($rememberMe) {
                $this->generateRememberMeToken();
            }

            return true;
        }
        return false;
    }

    private function isAlreadyExist() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    private function generateRememberMeToken() {
        $token = bin2hex(random_bytes(16));
        $expiryTime = time() + (86400 * 30); // 30 days

        $query = "INSERT INTO user_tokens (user_id, token, expiry) VALUES (:user_id, :token, :expiry)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $this->id);
        $stmt->bindParam(":token", $token);
        $stmt->bindParam(":expiry", date('Y-m-d H:i:s', $expiryTime));

        if($stmt->execute()) {
            setcookie('remember_me', $token, $expiryTime, '/', '', true, true);
        }
    }

    public function validateRememberMeToken($token) {
        $query = "SELECT user_id, expiry FROM user_tokens WHERE token = :token";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":token", $token);
        $stmt->execute();

        $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($tokenData && strtotime($tokenData['expiry']) > time()) {
            $this->id = $tokenData['user_id'];
            return true;
        }

        return false;
    }

    
}

