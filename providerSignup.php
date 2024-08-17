<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
   
    http_response_code(200);
    exit();
}

require_once './Main Classes/Verification.php';
require_once './Main Classes/Provider.php';

$data = json_decode(file_get_contents("php://input"));

if (isset($data->verify_id)) {
    $verify_id = $data->verify_id;

     $getVerifyDetail = new Verification();
     $rs = $getVerifyDetail->verifyProvider($verify_id);
 
      if($rs)
      {
        $verify_id = $rs['verify_id'];
        $username = $rs['provider_username'];
        $email = $rs['provider_email'];
        $password = $rs['provider_email'];
        $service_category_id = $rs['service_category'];
        $services = $rs['services'];
        $name = $rs['provider_name'];
        $address = $rs['provider_address'];
        $description = $rs['description'];
        
        $providerSignup = new Provider();
        
        $Result = $providerSignup->registerProvider($username,$email,$password,$name,$address,$verify_id,$service_category_id,$description,$services);
        
        if($Result['success']) {
            http_response_code(200);
            echo json_encode(array("success"=>true,"message" => "Provider was successfully verified."));
        } else {
            http_response_code(400);
            echo json_encode(array("success"=>false,"message" => $Result['message']));
        }
      }
      else{
        echo json_encode(array("success"=>false,"message" => "no verification details found for that id.."));
      }
    
    
}
else{
    echo json_encode(array("success"=>false,"message" => "verify id is required."));
}

