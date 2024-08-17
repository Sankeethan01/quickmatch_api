<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
   
    http_response_code(200);
    exit();
}

require_once './Main Classes/Booking.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);


$booking_id = isset($data['booking_id']) ? $data['booking_id'] : null;
$booking_status = isset($data['booking_status']) ? $data['booking_status'] : null;

if (!$booking_id || !$booking_status) {
    http_response_code(400);
    echo json_encode(["message" => "Booking ID or booking status is required"]);
    exit();
}

$customerCancelBooking = new Booking();

$result = $customerCancelBooking->changeBookingStatus($booking_id,$booking_status);

if ($result) {
    http_response_code(200);
    echo json_encode(["success" => true, "message" => "Booking status updated successfully."]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => 'Error while updating status']);
}
