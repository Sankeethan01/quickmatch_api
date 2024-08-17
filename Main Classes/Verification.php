<?php

require_once 'DbConnector.php';

class Verification{
    private $verify_id;
    private $provider_name;
    private $provider_username;
    private $provider_password;
    private $provider_email;
    private $service_category;
    private $registered_date;
    private $file;
    private $description;
    private $provider_address;
    private $services;
    private $pdo;

    public function __construct()
    {
        $db = new DBConnector();
        $this->pdo = $db->connect();
    }

    public function addVerification($provider_name, $provider_username, $provider_password, $provider_email, $service_category, $file, $description, $provider_address, $services)
{
    $this->provider_name = $provider_name;
    $this->provider_username = $provider_username;
    $this->provider_password = $provider_password;
    $this->provider_email = $provider_email;
    $this->service_category = $service_category;
    $this->file = $file;
    $this->description = $description;
    $this->provider_address = $provider_address;
    $this->services = $services;

    try {
        $targetDir = "verifypdfs/";
        $fileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

        if ($fileType != "pdf") {
            http_response_code(400);
            echo json_encode(["message" => "Only PDF files are allowed."]);
            return false;
        }

        if ($file["size"] > 5000000) {
            http_response_code(400);
            echo json_encode(["message" => "File is too large."]);
            return false;
        }

        $uniqueFileName = uniqid() . "_" . basename($file["name"]);
        $targetFile = $targetDir . $uniqueFileName;

        if (!move_uploaded_file($file["tmp_name"], $targetFile)) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to upload file."]);
            return false;
        }

        $hashedPassword = password_hash($this->provider_password, PASSWORD_BCRYPT);

        $stmt = $this->pdo->prepare("INSERT INTO verification (provider_name, provider_username, provider_password, provider_email, service_category, proof, description, provider_address, services) 
            VALUES (:provider_name, :provider_username, :provider_password, :provider_email, :service_category, :proof, :description, :provider_address, :services)");
        $stmt->bindParam(':provider_name', $this->provider_name);
        $stmt->bindParam(':provider_username', $this->provider_username);
        $stmt->bindParam(':provider_password', $hashedPassword);
        $stmt->bindParam(':provider_email', $this->provider_email);
        $stmt->bindParam(':service_category', $this->service_category);
        $stmt->bindParam(':proof', $uniqueFileName);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':provider_address', $this->provider_address);
        $stmt->bindParam(':services', $this->services);

        if ($stmt->execute()) {
            http_response_code(200);
            return true;
        } else {
            http_response_code(500);
            return false;
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Failed to process verification. " . $e->getMessage()]);
        return false;
    }
}

public function getVerifications() {
    try {
        $stmt = $this->pdo->prepare("SELECT * FROM verification");
        $stmt->execute();
        $verification = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $verification;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Failed to retrieve verifications. " . $e->getMessage()]);
        exit;
    }
}

public function deleteVerification($verify_id) {
    $this->verify_id = $verify_id;
    try {
        $stmt = $this->pdo->prepare("DELETE FROM verification WHERE verify_id = :verify_id");
        $stmt->bindParam(':verify_id', $this->verify_id);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Failed to delete verification data. " . $e->getMessage()]);
        exit;
    }
}

public function verifyProvider($verify_id) {
    $this->verify_id = $verify_id;
    try {
        $stmt = $this->pdo->prepare("SELECT * FROM verification WHERE verify_id = :verify_id");
        $stmt->bindParam(':verify_id', $this->verify_id );
        $stmt->execute();
        $verificationData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($verificationData) {
            return $verificationData;
        }
       else{
        return false;
       }
       
    } catch (PDOException $e) {
        return ['success' => false, 'message' => "Failed to get verification data: " . $e->getMessage()];
    }
}

}