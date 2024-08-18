<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
   
    http_response_code(200);
    exit();
}

require_once './Main Classes/Booking.php';

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid JSON data."]);
    exit();
}

$service_category_id = isset($data['service_category_id']) ? $data['service_category_id'] : null;
$provider_id = isset($data['provider_id']) ? $data['provider_id'] : null;
$customer_id = isset($data['customer_id']) ? $data['customer_id'] : null;
$customer_name = isset($data['customer_name']) ? $data['customer_name'] : null;
$provider_Name = isset($data['provider_Name']) ? $data['provider_Name'] : null;
$booking_status = isset($data['booking_status']) ? $data['booking_status'] : null;
$booking_date = isset($data['booking_date']) ? $data['booking_date'] : null;
$customer_email = isset($data['customer_email']) ? $data['customer_email'] : null;
$provider_email = isset($data['provider_email']) ? $data['provider_email'] : null;
$service = isset($data['service']) ? $data['service'] : null;
$customer_address = isset($data['customer_address']) ? htmlspecialchars(strip_tags($data['customer_address'])) : null;
$additional_notes = isset($data['additional_notes']) ? htmlspecialchars(strip_tags($data['additional_notes'])) : null;

if (!$service_category_id  || !$provider_id || !$customer_id || !$customer_name || !$provider_Name || !$booking_status || !$booking_date || !$service || !$customer_address || !$customer_email  || !$provider_email) {
    http_response_code(400);
    echo json_encode(["message" => "data are required"]);
    exit();
}

$createBooking = new Booking();

$Result = $createBooking->createBooking($service_category_id,$customer_name,$provider_Name,$provider_id,$customer_id,$booking_status,$booking_date,$service,$customer_address,$additional_notes,$customer_email,$provider_email);

if ($Result) {
    http_response_code(200);
    echo json_encode(["success" => true, "message" => "Booking successful."]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Unable to write the booking."]);
}

