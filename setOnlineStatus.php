<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once './Main Classes/Provider.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$provider_id = isset($data['providerId']) ? $data['providerId'] : null;
$status = isset($data['status']) ? $data['status'] : null;

if (!$provider_id) {
    http_response_code(400);
    echo json_encode(["message" => "Provider ID is required"]);
    exit();
}

if ($status === null) { // Ensure status is not NULL or undefined
    http_response_code(400);
    echo json_encode(["message" => "Status is required"]);
    exit();
}

$setOnlineStatus = new Provider();

$result = $setOnlineStatus->setStatus($provider_id, $status);

if ($result) {
    http_response_code(200);
    echo json_encode(["success" => true, "message" => "Status updated successfully."]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => 'Error while updating status']);
}
