<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once './Main Classes/Verification.php';

// Extract the 'id' from the query string
if (isset($_GET['id'])) {
    
    $verify_id = $_GET['id'];

    $deleteVerification = new Verification();

    $result = $deleteVerification->deleteVerification($verify_id);

    if ($result) {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(404);
        echo json_encode(false);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Verification ID is required."]);
    exit();
}
// finised