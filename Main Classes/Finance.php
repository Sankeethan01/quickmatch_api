<?php
require_once 'DbConnector.php';
class Finance
{
    private $finance_id;
    private $provider_id;
    private $customer_name;
    private $date;
    private $amount;
    private $service;
    private $pdo;

    public function __construct()
    {
        $db = new DBConnector();
        $this->pdo = $db->connect();
    }

    public function addFinance($provider_id, $customer_name, $date, $amount,$service)
    {
        $this->provider_id = $provider_id;
        $this->customer_name = $customer_name;
        $this->date = $date;
        $this->amount = $amount;
        $this->service = $service;
        try {
            $sql = "INSERT INTO finance (provider_id, customer_name, date, amount,service) VALUES (:provider_id, :customer_name, :date, :amount,:service)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':provider_id', $this->provider_id);
            $stmt->bindParam(':customer_name', $this->customer_name);
            $stmt->bindParam(':date', $this->date);
            $stmt->bindParam(':amount', $this->amount);
            $stmt->bindParam(':service', $this->service);

            $response = $stmt->execute();

            return $response;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to add finance details. " . $e->getMessage()]);
            exit;
        }
    }

    public function getFinanceByProviderId($provider_id)
    {
        $this->provider_id = $provider_id;
        try {
            $sql = "SELECT * FROM finance WHERE provider_id = :provider_id ORDER BY finance_id DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':provider_id', $provider_id);
            $stmt->execute();
            $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($details) {
                return $details;
            } else {
                return [];
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to retrieve finance details. " . $e->getMessage()]);
            exit;
        }
    }
}
