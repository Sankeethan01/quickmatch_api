<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
   
    http_response_code(200);
    exit();
}

require_once './Main Classes/Customer.php';
require_once './Main Classes/Mailer.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);


$email = isset($data['email']) ? $data['email'] : null;
$username = isset($data['username']) ? $data['username'] : null;

if (!$email || !$username ) {
    http_response_code(400);
    echo json_encode(["message" => "booking ID is required"]);
    exit();
}

$checkCredentials = new Customer();

$result = $checkCredentials->checkCredentials($email,$username);

if ($result) {
    $otp = rand(100000, 999999);
    $mailer = new Mailer();
    $msg='Dear User, <br> Your verification code is :  '.$otp.'<br> Use this 6 digit code to verify and change your password';
    $mailer->setInfo($email,'OTP Verification',$msg);
    if($mailer->send())
    {
    http_response_code(200);
    echo json_encode(["success" => true, "message" => "OTP sent to your email.", "otp"=>$otp,]);
    }

} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => 'Invalid email or username']);
}
