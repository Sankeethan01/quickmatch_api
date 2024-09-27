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

if (!$booking_id || !$booking_status) {
    http_response_code(400);
    echo json_encode(["message" => "Booking ID or booking status is required"]);
    exit();
}

$customerCancelBooking = new Booking();

$result = $customerCancelBooking->changeBookingStatus($booking_id,$booking_status);

if ($result) {
    $mails = $customerCancelBooking->getEmailsByBookingId($booking_id);
    $mailer = new Mailer();
    $msg='Dear Provider, <br> Service request is cancelled by the customer.<br> For further enquiries contact : '.$mails['customer_email'].' or visit the application';
    $mailer->setInfo($mails['provider_email'],'Cancellation of Service request',$msg);
    if($mailer->send()){
    http_response_code(200);
    echo json_encode(["success" => true, "message" => "Booking status updated successfully."]);}
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => 'Error while updating status']);
}
