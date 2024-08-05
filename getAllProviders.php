<?php

require_once 'DbConnector.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

$con = new DBConnector();
$db = $con->connect();

$query = "SELECT
            user.name,
            user.username,
            user.email,
            user.phone,
            user.user_type,
            user.address,
            user.national_id,
            user.profile_image,
            provider.provider_id AS provider_id,
            provider.description AS description,
            provider.services AS services,
            provider.charge_per_day AS charge,
            provider.qualification AS qualification,
            provider.status AS status,
            service.service_name AS service_category
          FROM user
          INNER JOIN provider ON user.user_id = provider.user_id
          INNER JOIN service ON provider.service_category_id = service.service_category_id
          WHERE user.user_type = 'provider'";

$stmt = $db->prepare($query);

if ($stmt->execute()) {
    $providers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($providers);
} else {
    echo json_encode(['error' => 'Error fetching providers']);
}
?>
