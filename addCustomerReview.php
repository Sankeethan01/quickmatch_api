<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
   
    http_response_code(200);
    exit();
}

require_once './Main Classes/CustomerReview.php';

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid JSON data."]);
    exit();
}

$provider_id = isset($data['provider_id']) ? $data['provider_id'] : null;
$reviewer = isset($data['reviewer']) ? $data['reviewer'] : null;
$rating = isset($data['rating']) ? $data['rating'] : null;
$comment = isset($data['comment']) ? $data['comment'] : null;

if (!$provider_id || !$reviewer || !$rating || !$comment) {
    http_response_code(400);
    echo json_encode(["message" => "data are required"]);
    exit();
}

$addCustomerReview = new CustomerReview();
$Result = $addCustomerReview->addReview($provider_id,$reviewer,$comment,$rating);

if ($Result) {
    http_response_code(200);
    echo json_encode([
        "success" => true, 
        "message" => "Review added successfully.", 
        "data" => $Result
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false, 
        "message" => "Unable to write the review."
    ]);
}

