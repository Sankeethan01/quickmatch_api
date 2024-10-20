<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
   
    http_response_code(200);
    exit();
}

require_once './Main Classes/Finance.php';
require_once './Main Classes/Provider.php';

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid JSON data."]);
    exit();
}

$user_id = isset($data['user_id']) ? $data['user_id'] : null;
$customer_name = isset($data['customer_name']) ? $data['customer_name'] : null;
$date = isset($data['date']) ? $data['date'] : null;
$amount = isset($data['amount']) ? $data['amount'] : null;
$service = isset($data['service']) ? $data['service'] : null;

if (!$user_id || !$customer_name || !$date || !$amount || !$service) {
    http_response_code(400);
    echo json_encode(["message" => "data are required"]);
    exit();
}

$providerDetail = new Provider();
$provider_id = $providerDetail->getProviderId($user_id);


$addFinance = new Finance();
$Result = $addFinance->addFinance($provider_id,$customer_name,$date,$amount,$service);

if ($Result) {
    http_response_code(200);
    echo json_encode([
        "success" => true, 
        "message" => "finance added successfully.", 
        "data" => $Result
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false, 
        "message" => "Unable to write the finance. Error"
    ]);
}

