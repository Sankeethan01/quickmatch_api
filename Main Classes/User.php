<?php
require_once 'DbConnector.php';
abstract class User
{
    protected $user_id;
    protected $username;
    protected $name;
    protected $email;
    protected $password;
    protected $phone;
    protected $address;
    protected $national_id;
    protected $user_type;
    protected $profile_image;
    protected $pdo;
    protected $disable_status;

    public function __construct()
    {
        $db = new DBConnector();
        $this->pdo = $db->connect();
    }

    public function isAlreadyExists()
    {

        $query = "SELECT email FROM user WHERE email = :email";
        $stmt = $this->pdo->prepare($query);

        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function login($email, $password)
    {

        $this->email = $email;
        $this->password = $password;

        try {
            $sql = "SELECT user_id, password, user_type, disable_status FROM user WHERE email = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(1, $this->email);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($this->password, $user['password'])) {
                if ($user['disable_status'] === 'disabled') {
                    return ['success' => false, 'message' => 'Your account has been disabled. Please contact support.'];
                }

                $this->user_id = $user['user_id'];
                $this->user_type = $user['user_type'];

                return ['user_type' => $this->user_type, 'user_id' => $this->user_id, 'success' => true, 'message' => 'Login Successful.'];
            } else {
                return false;
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to login. " . $e->getMessage()]);
        }
    }

    public function forgotPassword($email, $password)
    {
        $this->email = $email;
        $this->password = $password;

        try {
            $sql = "UPDATE user SET password = :password WHERE email = :email";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':password', $this->password);
            $stmt->bindParam('email', $this->email);

            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to change password. " . $e->getMessage()]);
        }
    }

    public function registerUser($username, $email, $password, $name, $address)
    {
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->name = $name;
        $this->address = $address;

        if ($this->isAlreadyExists()) {
            return false;
        }

        try {
            $sql = "INSERT INTO user (username,email,password,name,address) VALUES (?,?,?,?,?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(1, $this->username);
            $stmt->bindParam(2, $this->email);
            $stmt->bindParam(3, $this->password);
            $stmt->bindParam(4, $this->name);
            $stmt->bindParam(5, $this->address);
            $rs = $stmt->execute();

            if ($rs) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to register. " . $e->getMessage()]);
        }
    }

    public function updateDetails($user_id, $username, $name, $phone, $address, $national_id, $profile_image)
    {
        $this->user_id = $user_id;
        $this->username = $username;
        $this->name = $name;
        $this->phone = $phone;
        $this->address = $address;
        $this->national_id = $national_id;
        $this->profile_image = $profile_image;

        try {
            if ($this->profile_image) {
                $sql = "UPDATE user SET name = :name, username = :username, phone = :phone, address = :address,national_id = :national_id, profile_image = :profile_image WHERE user_id = :user_id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    'name' => $this->name,
                    'username' => $this->username,
                    'phone' => $this->phone,
                    'address' => $this->address,
                    'profile_image' => $this->profile_image,
                    'user_id' => $this->user_id,
                    'national_id' => $this->national_id
                ]);
            } else {
                $sql = "UPDATE user SET name = :name, username = :username, phone = :phone, address = :address, national_id = :national_id WHERE user_id = :user_id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    'name' => $this->name,
                    'username' => $this->username,
                    'phone' => $this->phone,
                    'address' => $this->address,
                    'user_id' => $this->user_id,
                    'national_id' => $this->national_id
                ]);
            }
            return ['success' => true];
        } catch (PDOException $e) {
            http_response_code(500);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getDetails($user_id)
    {
        $this->user_id = $user_id;
        try {
            $sql = "SELECT * FROM user WHERE user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':user_id', $this->user_id);
            $stmt->execute();
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);

            return $customer;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to retrieve customer details. " . $e->getMessage()]);
            exit;
        }
    }


    public function getTotalCount($user_type)
    {
        $this->user_type = $user_type;

        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) AS user_count FROM user WHERE user_type = :user_type");
            $stmt->bindParam(':user_type', $this->user_type);
            $stmt->execute();
            $count = $stmt->fetch(PDO::FETCH_ASSOC);
            return $count;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to retrieve user count. " . $e->getMessage()]);
            exit;
        }
    }

    public function getLastFiveUsers($user_type)
    {
        $this->user_type = $user_type;
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM user WHERE user_type = :user_type ORDER BY user_id DESC LIMIT 5");
            $stmt->bindParam(':user_type', $this->user_type);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $users;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to fetch last 5 users. " . $e->getMessage()]);
            exit;
        }
    }

    public function disableUser($user_id, $disable_status)
    {
        $this->user_id = $user_id;
        $this->disable_status = $disable_status;
        try {
            $stmt = $this->pdo->prepare("UPDATE user SET disable_status = :disable_status WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $this->user_id);
            $stmt->bindParam(':disable_status', $this->disable_status);
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'User is disabled'];
            }
            return ['success' => false, 'message' => 'Error in disabling user'];
        } catch (PDOException $e) {
            echo json_encode(["message" => "Failed to disable user. " . $e->getMessage()]);
        }
    }

    public function getUserCountWithMonth()
    {
        try {
            $query = "
        SELECT 
            DATE_FORMAT(registered_at, '%Y-%m') AS month, 
            user_type, 
            COUNT(*) AS count
        FROM 
            user
        GROUP BY 
            month, 
            user_type
        ORDER BY 
            month ASC;
    ";
    $stmt = $this->pdo->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $results;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
}
