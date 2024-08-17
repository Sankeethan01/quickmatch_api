<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    http_response_code(200);
    exit();
}

require_once './Main Classes/Provider.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

$user_id = $_POST['user_id'];
$provider_id = $_POST['provider_id'];
$name = isset($_POST['name']) ? htmlspecialchars(strip_tags($_POST['name'])) : null;
$username = isset($_POST['username']) ? htmlspecialchars(strip_tags($_POST['username'])) : null;
$phone = isset($_POST['contactNumber']) ? htmlspecialchars(strip_tags($_POST['contactNumber'])) : null;
$address = isset($_POST['location']) ? htmlspecialchars(strip_tags($_POST['location'])) : null;
$national_id = isset($_POST['nationalId']) ? htmlspecialchars(strip_tags($_POST['nationalId'])) : null;
$description = isset($_POST['bio']) ? htmlspecialchars(strip_tags($_POST['bio'])) : null;
$services = isset($_POST['services']) ? htmlspecialchars(strip_tags($_POST['services'])) : null;
$charge_per_day = isset($_POST['chargesPerDay']) ? htmlspecialchars(strip_tags($_POST['chargesPerDay'])) : null;
$qualification = isset($_POST['qualifications']) ? htmlspecialchars(strip_tags($_POST['qualifications'])) : null;
$profile_image = null;

if (empty($user_id) || empty($provider_id)) {
    http_response_code(400);
    echo json_encode(["message" => "User ID and Provider ID are required."]);
    exit;
}

if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
    $uploadDir = 'profile_images/';
    
    // Generate a random file name to prevent conflicts
    $uniqueName = uniqid('img_', true);
    $imageFileType = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
    $uploadFile = $uploadDir . $uniqueName . '.' . $imageFileType;

    // Check if the file is an actual image
    $check = getimagesize($_FILES['profile_image']['tmp_name']);
    if ($check === false) {
        http_response_code(400);
        echo json_encode(["message" => "File is not an image."]);
        exit;
    }

    // Limit file size to 500KB
    if ($_FILES['profile_image']['size'] > 5000000) {
        http_response_code(400);
        echo json_encode(["message" => "File is too large. Maximum size is 500KB."]);
        exit;
    }

    // Only allow certain file formats
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowedTypes)) {
        http_response_code(400);
        echo json_encode(["message" => "Only JPG, JPEG, PNG, and GIF files are allowed."]);
        exit;
    }

    // Move the file to the upload directory
    if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadFile)) {
        http_response_code(500);
        echo json_encode(["message" => "Failed to upload profile image."]);
        exit;
    }

    $profile_image = basename($uploadFile);
}


$updateProviderProfile = new Provider();
    $result = $updateProviderProfile->updateProviderDetails(
        $user_id,
        $username,
        $name,
        $phone,
        $address,
        $national_id,
        $profile_image,
        $provider_id,
        $description,
        $services,
        $charge_per_day,
        $qualification
    );

    if ($result) {
        http_response_code(200);
        echo json_encode(["success" => true, "message" => "Provider details updated successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Failed to update profile."]);
    }
}