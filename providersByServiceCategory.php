<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
   
    http_response_code(200);
    exit();
}

require_once './Main Classes/Services.php';

$service_category_id = isset($_GET['service_category_id']) ? ($_GET['service_category_id']) : null;

if (!$service_category_id) {
    http_response_code(400);
    echo json_encode(["message" => "category ID is required"]);
    exit();
}

$providersByService = new Services();

try {
    $result = $providersByService->fetchServiceProviders($service_category_id);

    if ($result && count($result) > 0) {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "No providers found for this category"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "An error occurred: " . $e->getMessage()]);
}
