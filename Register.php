<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}


include_once 'DbConnector.php';
include_once 'User.php';

$database = new DBConnector();
$db = $database->connect();

$user = new User($db);

$data = json_decode(file_get_contents("php://input"));

if(
    !empty($data->username) &&
    !empty($data->email) &&
    !empty($data->password)
) {
    $user->username = $data->username;
    $user->email = $data->email;
    $user->password = $data->password;

    if($user->signup()) {
        http_response_code(200);
        echo json_encode(array("message" => "User was successfully registered."));
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Unable to register the user."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete data."));
}

