<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
   
    http_response_code(200);
    exit();
}

require_once './Main Classes/Customer.php';

$data = json_decode(file_get_contents("php://input"));

$username = htmlspecialchars(strip_tags($data->username));
$email = htmlspecialchars(strip_tags($data->email));
$password = htmlspecialchars(strip_tags($data->password));
$password = password_hash($password,PASSWORD_BCRYPT);  
$name = htmlspecialchars(strip_tags($data->fullName));
$name = htmlspecialchars(strip_tags($data->fullName));
$address = htmlspecialchars(strip_tags($data->fullAddress));  

$customerRegister = new Customer();

$Result = $customerRegister->registerUser($username,$email,$password,$name,$address);

if($Result) {
    http_response_code(200);
    echo json_encode(array("message" => "Customer was successfully registered."));
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to register the Customer."));
}

