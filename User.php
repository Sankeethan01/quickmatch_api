<?php


class User {
    private $conn;
    private $table_name = "user";

    public $user_id;

    public $username;
    public $email;
    public $password;
    public $user_type;
    public $name;
    public $profile_image;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function signup() {
        if ($this->isAlreadyExist()) {
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

        if ($stmt->execute()) {
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

        if ($user && password_verify($this->password, $user['password'])) {
            $this->user_id = $user['user_id'];
            $this->username = $user['username'];
            $this->user_type = $user['user_type'];
            $this->name = $user['name'];
            $this->profile_image = $user['profile_image'];

            

            if ($rememberMe) {
                $this->generateRememberMeToken();
            }

            return [
                'user_id' => $this->user_id,
                'username' => $this->username,
                'user_type' => $this->user_type,
                'message' => 'Login successful.'
            ];

        }
        return false;
    }

    private function isAlreadyExist() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    private function generateRememberMeToken() {
        $token = bin2hex(random_bytes(16));
        $expiryTime = time() + (86400 * 30); // 30 days
        $cookieTime =  date('Y-m-d H:i:s', $expiryTime);

        $query = "INSERT INTO user_token (user_id, token, expiry) VALUES (:user_id, :token, :expiry)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":token", $token);
        $stmt->bindParam(":expiry", $cookieTime);

        if ($stmt->execute()) {
            setcookie('remember_me', $token, $expiryTime, '/', '', true, true);
        }
    }

    public function validateRememberMeToken($token) {
        $query = "SELECT user_id, expiry FROM user_token WHERE token = :token";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":token", $token);
        $stmt->execute();

        $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($tokenData && strtotime($tokenData['expiry']) > time()) {
            $this->user_id = $tokenData['user_id'];
            return true;
        }

        return false;
    }

    public function fetchDetails() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :id";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":id", $this->user_id);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $this->username = $user['username'];
            $this->email = $user['email'];
            $this->user_type = $user['user_type'];
            $this->name = $user['name'];
            $this->profile_image = $user['profile_image'];
            return true;
        }

        return false;
    }
}

