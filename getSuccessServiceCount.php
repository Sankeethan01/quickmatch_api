<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    http_response_code(200);
    exit();
}

require_once './Main Classes/Booking.php';

$booking_status = isset($_GET['booking_status']) ? ($_GET['booking_status']) : null;

$serviceCount = new Booking();

$result = $serviceCount->getSuccessfulServiceCount($booking_status);

if ($result) {
    http_response_code(200);
    echo json_encode($result);
} else {
    http_response_code(404);
    echo json_encode(["message" => "User not found"]);
}