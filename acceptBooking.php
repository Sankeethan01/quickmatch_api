<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
   
    http_response_code(200);
    exit();
}

require_once './Main Classes/Booking.php';
require_once './Main Classes/Mailer.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);


$booking_id = isset($data['booking_id']) ? $data['booking_id'] : null;
$booking_status = isset($data['booking_status']) ? $data['booking_status'] : null;

if (!$booking_id ) {
    http_response_code(400);
    echo json_encode(["message" => "booking ID is required"]);
    exit();
}

$acceptBookingStatus = new Booking();

$result = $acceptBookingStatus->changeBookingStatus($booking_id,$booking_status);

if ($result) {
    http_response_code(200);
    $emails = $acceptBookingStatus->getEmailsByBookingId($booking_id);
    $mailer = new Mailer();
    $message = 'Dear Customer, <br> Your service request is accepted by the provider. <br>Now you can develop a conversation with your provider. <br> Provider email address : '. $emails['provider_email'];
    $mailer->setInfo($emails['customer_email'],'Service Request Accepted',$message);
    if($mailer->send()){
    echo json_encode([
        "success" => true,
        "message" => "Booking status updated successfully."
    ]);}

    
    
   
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => 'Error while updating status'
    ]);
}
