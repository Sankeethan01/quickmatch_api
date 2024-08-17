<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    http_response_code(200);
    exit();
}

require_once './Main Classes/Booking.php';

if($_GET['action'] === 'totalIncome'){
    $totalIncome = new Booking();

    $result =  $totalIncome->getTotalIncome();
    
    if ($result) {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "User not found"]);
    }
}
else{
    echo json_encode(["message" => "error occured"]);
    
}