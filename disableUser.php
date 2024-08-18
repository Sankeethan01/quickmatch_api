<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once './Main Classes/Admin.php';


if (isset($_GET['id'])) {
    
    $user_id = $_GET['id'];
    $disable_status = $_GET['status'];

    $disableUser = new Admin();

    $result = $disableUser->disableUser($user_id,$disable_status);

    if ($result) {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(404);
        echo json_encode($result);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "User ID is required."]);
    exit();
}
