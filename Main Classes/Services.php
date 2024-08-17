<?php

require_once 'DbConnector.php';

class Services {
    private $pdo;
    private $service_category_id;
    private $service_name;

    public function __construct() {
        $db = new DBConnector();
        $this->pdo = $db->connect();
    }

    public function fetchServiceProviders($service_category_id) {
        $this->service_category_id = $service_category_id;
        try {
            $sql = "SELECT u.name, u.user_id, u.address, u.email, u.phone, u.username, u.user_type, 
                             p.description, p.status, p.service_category_id,p.services, p.provider_id, 
                             u.profile_image, s.service_name 
                      FROM user u
                      INNER JOIN provider p ON u.user_id = p.user_id
                      INNER JOIN service s ON s.service_category_id = p.service_category_id
                      WHERE p.service_category_id = :service_category_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':service_category_id', $this->service_category_id);
            $stmt->execute();
            $providers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $providers;
        } catch (PDOException $e) {
            return json_encode(array('message' => 'Error: ' . $e->getMessage()));
        }
    }
}