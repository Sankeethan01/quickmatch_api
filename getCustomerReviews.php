<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once './Main Classes/CustomerReview.php';
require_once './Main Classes/Provider.php';

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    $getProviderId = new Provider();
    $provider_id = $getProviderId->getProviderId($user_id);

    if (!$provider_id) {
        http_response_code(404);
        echo json_encode(["message" => "Provider ID not found for the given User ID"]);
        exit();
    }

    $getCustomerReviews = new CustomerReview();

    try {
        $result = $getCustomerReviews->getReviewsByProviderId($provider_id);

        if ($result && is_array($result)) {
            http_response_code(200);
            echo json_encode(["data" => $result]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "No feedbacks found for this provider"]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["message" => "An error occurred: " . $e->getMessage()]);
    }
} 
elseif (isset($_GET['provider_id'])) {
    $prov_id = $_GET['provider_id'];

    $getCustomerReviews = new CustomerReview();

    try {
        $result = $getCustomerReviews->getReviewsByProviderId($prov_id);

        if ($result && is_array($result)) {
            http_response_code(200);
            echo json_encode(["data" => $result]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "No feedbacks found for this provider"]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["message" => "An error occurred: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "User ID or Provider ID is required"]);
}
