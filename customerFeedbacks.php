<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
   
    http_response_code(200);
    exit();
}

require_once './Main Classes/Feedback.php';

$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

if (!$user_id) {
    http_response_code(400);
    echo json_encode(["message" => "User ID is required"]);
    exit();
}
$customerFeedbacks = new Feedback();

try {
    $result = $customerFeedbacks->getFeedbacksById($user_id);

    if ($result && is_array($result)) {
        http_response_code(200);
        echo json_encode(["data" => $result]);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "No feedbacks found for this user"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "An error occurred: " . $e->getMessage()]);
}
