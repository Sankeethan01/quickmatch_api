<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once './Main Classes/Verification.php';
require_once './Main Classes/Provider.php';

$provider_name = isset($_POST['name']) ? $_POST['name'] : null;
$provider_username = isset($_POST['username']) ? $_POST['username'] : null;
$provider_password = isset($_POST['password']) ? $_POST['password'] : null;
$provider_email = isset($_POST['email']) ? $_POST['email'] : null;
$service_category = isset($_POST['serviceCategory']) ? $_POST['serviceCategory'] : null;
$description = isset($_POST['description']) ? $_POST['description'] : null;
$provider_address = isset($_POST['address']) ? $_POST['address'] : null;
$services = isset($_POST['services']) ? $_POST['services'] : null;
$file = isset($_FILES['proof']) ? $_FILES['proof'] : null;

if (!$provider_name || !$provider_username || !$provider_email || !$provider_password || !$service_category || !$file || !$description || !$provider_address || !$services) {
    http_response_code(400);
    echo json_encode(["message" => "All data are required."]);
    exit();
}

$checkEmail = new Provider();
$rs = $checkEmail->checkEmailExists($provider_email);

if($rs)
{
    echo json_encode(array("message" => "Email is Already Exists...")); 
}
else{
    $addVerification = new Verification();
    $Result = $addVerification->addVerification($provider_name, $provider_username, $provider_password, $provider_email, $service_category, $file, $description, $provider_address, $services);
    
    if ($Result) {
        http_response_code(200);
        echo json_encode([
            "success" => true, 
            "message" => "Provider registered successfully.", 
            "data" => $Result
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false, 
            "message" => "Unable to register."
        ]);
    }
}

