<?php
include_once 'Main Classes/User.php';

class Provider extends User
{
    private $provider_id;
    private $verify_id;
    private $description;
    private $services;
    private $charge_per_day;
    private $qualification;
    private $status;
    private $service_category_id;

    public function __construct()
    {
        parent::__construct();
    }

    public function registerProvider($username, $email, $password, $name, $address, $verify_id,$service_category_id, $description,$services)
    {  
         $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->name = $name;
        $this->service_category_id = $service_category_id;
        $this->address = $address;
        $this->verify_id = $verify_id;
        $this->description = $description;
        $this->services = $services;
        $this->user_type = 'provider';

        if($this->isAlreadyExists())
      {
        return ['success' => false, 'message' => "Provider Already Verified"];;
      }

        try{
            $stmt = $this->pdo->prepare("INSERT INTO user (name, username, email, password, user_type, address) 
                VALUES (:name, :username, :email, :password, :user_type, :address)");
            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':username', $this->username);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':password', $this->password);
            $stmt->bindParam(':user_type', $this->user_type);
            $stmt->bindParam(':address', $this->address);
    
            if ($stmt->execute()) {
                $this->user_id = $this->pdo->lastInsertId();
    
          
            $st = $this->pdo->prepare("INSERT INTO provider (verify_id, description, service_category_id, services, user_id) 
                    VALUES (:verify_id, :description, :service_category_id, :services, :user_id)");
            $st->bindParam(':verify_id', $this->verify_id);
            $st->bindParam(':description', $this->description);
            $st->bindParam(':services', $this->services);
            $st->bindParam(':service_category_id', $this->service_category_id);
            $st->bindParam(':user_id', $this->user_id);
    
                if ($st->execute()) {
                    return ['success' => true, 'message' => "Provider verified successfully"];
                } else {
                    return ['success' => false, 'message' => "Failed to verify provider: "];
                }
            } else {
                return ['success' => false, 'message' => "Failed to verify provider as an user: "];
            }
        }
        catch(PDOException $e)
        {
            return ['success' => false, 'message' => "Failed to process verification: " . $e->getMessage()];
        }
    }

    public function getProviderId($user_id) {
        $this->user_id = $user_id;
        try {
            $sql = "SELECT provider_id FROM provider WHERE user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result === false) {
                return null;
            }

            return $result['provider_id'];
        } catch (PDOException $e) {
            return ['error' => 'An error occurred while fetching data: ' . $e->getMessage()];
        }
    }

    public function setStatus($provider_id,$status) {
         $this->provider_id = $provider_id;
         $this->status = $status;
         try {
            $sql = "UPDATE provider SET status = :status WHERE provider_id = :provider_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(":provider_id", $this->provider_id);
            $stmt->bindParam(":status", $this->status);
            $rs = $stmt->execute();
            return $rs;
        } catch (PDOException $e) {
            return false; // Return false on error
        }
    }

    public function getProviderDetails($user_id)
    {
        try {
            $userDetails = parent::getDetails($user_id);

            $sql = "SELECT * FROM provider WHERE user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();

            $providerDetails = $stmt->fetch(PDO::FETCH_ASSOC);

            $result = array_merge($userDetails, $providerDetails);

            return $result;
        } catch (PDOException $e) {
            return ['error' => 'An error occurred while fetching data: ' . $e->getMessage()];
        }
    }


    public function getAllProviders() {
        try {
            $stmt = $this->pdo->prepare("SELECT
            user.user_id,
            user.name,
            user.username,
            user.email,
            user.phone,
            user.user_type,
            user.address,
            user.disable_status,
            user.national_id,
            user.profile_image,
            provider.provider_id AS provider_id,
            provider.description AS description,
            provider.services AS services,
            provider.charge_per_day AS charge,
            provider.qualification AS qualification,
            provider.status AS status,
            service.service_name AS service_category
          FROM user 
          INNER JOIN provider ON user.user_id = provider.user_id
          INNER JOIN service ON provider.service_category_id = service.service_category_id
          WHERE user.user_type = 'provider' ORDER BY user.user_id DESC");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $users;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to fetch providers. " . $e->getMessage()]);
            exit;
        }
    }

    public function updateProviderDetails($user_id, $username, $name, $phone, $address, $national_id, $profile_image, $provider_id, $description, $services, $charge_per_day, $qualification)
    {
        parent::updateDetails($user_id, $username, $name, $phone, $address, $national_id, $profile_image);

        $this->provider_id = $provider_id;
        $this->description = $description;
        $this->services = $services;
        $this->charge_per_day = $charge_per_day;
        $this->qualification = $qualification;
        try {
            $sql = "UPDATE provider SET description = :description, services = :services, charge_per_day = :charge_per_day, qualification = :qualification WHERE provider_id = :provider_id";
            $stmt = $this->pdo->prepare($sql);
            $rs = $stmt->execute([
                'description' => $this->description,
                'services' => $this->services,
                'charge_per_day' => $this->charge_per_day,
                'qualification' => $this->qualification,
                'provider_id' => $this->provider_id,
            ]);

            if ($rs) {
                return ['success' => true];
            } else {
                return ['success' => false];
            }
        } catch (PDOException $e) {
            http_response_code(500);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function checkEmailExists($email)
    {
        $this->email = $email;
        try{
            $stmt = $this->pdo->prepare("SELECT email FROM USER WHERE email=:email");
            $stmt->bindParam(':email',$this->email);
            
            if ($stmt->rowCount() > 0) {
                http_response_code(200);
                return true;
            } else {
                http_response_code(500);
                return false;
            
        }
    }
    catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Failed to verify email. " . $e->getMessage()]);
        return false;
    }
}

public function checkVerifyId($verify_id){
    $this->verify_id = $verify_id;
    try{
        $stmt = $this->pdo->prepare("SELECT verify_id FROM provider WHERE verify_id= :verify_id");
        $stmt->bindParam(':verify_id',$this->verify_id);
        $stmt->execute();

        if($stmt->rowCount() > 0)
        {
            return true;
        }
        else{
            return false;
        }
    }
    catch(PDOException $e)
    {
        return ['message' => "Failed to get verification data: " . $e->getMessage()];
    }
  }


}
