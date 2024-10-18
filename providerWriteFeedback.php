<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
   
    http_response_code(200);
    exit();
}

require_once './Main Classes/Feedback.php';

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid JSON data."]);
    exit();
}

$user_id = isset($data['user_id']) ? $data['user_id'] : null;
$feedback = isset($data['feedback']) ? $data['feedback'] : null;

if (!$user_id || !$feedback) {
    http_response_code(400);
    echo json_encode(["message" => "User ID and feedback are required"]);
    exit();
}

$providerWriteFeedback = new Feedback();

$Result = $providerWriteFeedback->writeFeedback($user_id,$feedback);

if ($Result) {
    http_response_code(200);
    echo json_encode(["success" => true, "message" => "Feedback was successfully stored."]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Unable to write the feedback."]);
}

