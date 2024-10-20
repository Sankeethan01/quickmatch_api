<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
   
    http_response_code(200);
    exit();
}

require_once './Main Classes/Mailer.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);


$email = isset($data['email']) ? $data['email'] : null;

if (!$email) {
    http_response_code(400);
    echo json_encode(["message" => "Email is required"]);
    exit();
}

    $otp = rand(100000, 999999);
    $mailer = new Mailer();
    $msg='Dear User, <br> Your verification code is :  '.$otp.'<br> Use this 6 digit code to verify and Proceed Registration';
    $mailer->setInfo($email,'OTP Verification',$msg);
    if($mailer->send())
    {
    http_response_code(200);
    echo json_encode(["success" => true, "message" => "OTP sent to your email.", "otp"=>$otp,]);
    }
    else
    {
       http_response_code(500);
       echo json_encode(["success" => false, "message" => "Error while sending OTP to your email."]);
    }
