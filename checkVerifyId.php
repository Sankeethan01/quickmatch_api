<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    http_response_code(200);
    exit();
}

require_once './Main Classes/Provider.php';

$verify_id = isset($_GET['verify_id']) ? ($_GET['verify_id']) : null;

if (!$verify_id) {
    http_response_code(400);
    echo json_encode(["message" => "verify ID is required"]);
    exit();
}

$checkVerify = new Provider();

$result = $checkVerify->checkVerifyId($verify_id);

if ($result) {
    echo json_encode(true);
} else {
    echo json_encode(false);
}