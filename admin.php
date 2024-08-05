<?php


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require_once 'DbConnector.php';

class Admin{
    private $pdo;

    public function __construct() {
        $db = new DBConnector();
        $this -> pdo = $db->connect();
    }



 public function getAdminDetails($user_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM user WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    

    public function updateAdminDetails($id, $name, $username, $phone, $address, $profile_image = null) {
        if ($profile_image) {
            $stmt = $this->pdo->prepare("UPDATE user SET name = :name, username = :username, phone = :phone, address = :address, profile_image = :profile_image WHERE user_id = :id");
            $stmt->execute([
                'name' => $name,
                'username' => $username,
                'phone' => $phone,
                'address' => $address,
                'profile_image' => $profile_image,
                'id' => $id
            ]);
        } else {
            $stmt = $this->pdo->prepare("UPDATE user SET name = :name, username = :username, phone = :phone, address = :address WHERE user_id = :id");
            $stmt->execute([
                'name' => $name,
                'username' => $username,
                'phone' => $phone,
                'address' => $address,
                'id' => $id
            ]);
        }
    }
}



$admin = new Admin();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
     if (isset($_GET['user_id'])) {
        $user_id = filter_var($_GET['user_id'], FILTER_SANITIZE_NUMBER_INT);
        $data = $admin->getAdminDetails($user_id);
        if ($data) {
            http_response_code(200);
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Admin details not found.']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'User ID is required.']);
    }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    //update admin details
    $id = isset($_POST['user_id']) ? filter_var($_POST['user_id'], FILTER_SANITIZE_NUMBER_INT) : null;
    $name = isset($_POST['name']) ? filter_var($_POST['name'], FILTER_SANITIZE_STRING) : null;
    $username = isset($_POST['username']) ? filter_var($_POST['username'], FILTER_SANITIZE_STRING) : null;
    $phone = isset($_POST['phone']) ? filter_var($_POST['phone'], FILTER_SANITIZE_STRING) : null;
    $address = isset($_POST['address']) ? filter_var($_POST['address'], FILTER_SANITIZE_STRING) : null;
    $profile_image = null;

    if($id && $name && $username && $phone && $address)
    {
         if(isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK)
         {
            $uploadDir = 'profile_images/';
            $uploadFile = $uploadDir.basename($_FILES['profile_image']['name']);
            $imageFileType = strtolower(pathinfo($uploadFile,PATHINFO_EXTENSION));
            
            //validate profile image
            $check = getimagesize($_FILES['profile_image']['tmp_name']);
             if($check !== false && $_FILES['profile_image']['size'] <= 1000000)
             {
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadFile)) {
                    $profile_image = basename($uploadFile);
                } else {
                    http_response_code(500);
                    echo json_encode(["message" => "Failed to upload profile image."]);
                    exit;
                }
             }
             else
             {
                http_response_code(400);
                echo json_encode(["message" => "Invalid profile image."]);
                exit;
             }
         }
    

         

         $admin -> updateAdminDetails($id,$name,$username,$phone,$address,$profile_image);

           
           http_response_code(200);
           echo json_encode(["message" => "Admin details updated successfully"]);
   }
    else 
    {
        http_response_code(400);
        echo json_encode(["message" => "All fields are required."]);
     }

    }
    else{
        http_response_code(405);
    echo json_encode(["message" => "Method not allowed."]);
    }

    