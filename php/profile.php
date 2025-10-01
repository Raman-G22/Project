<?php
header('Content-Type: application/json');
require _DIR_ . '/../vendor/autoload.php';
$mysqli = new mysqli('127.0.0.1', 'root', '', 'user_auth');
$mongoClient = new MongoDB\Client("mongodb://127.0.0.1:27017");
$profileCollection = $mongoClient->user_profiles->profiles;
$redis = new Predis\Client();
$userId = null;
$headers = getallheaders();
if (isset($headers['Authorization'])) {
    list(, $token) = explode(' ', $headers['Authorization'], 2);
    if ($token) {
        $userId = $redis->get("session:" . $token);
    }
}

if (!$userId) {
    http_response_code(401); 
    echo json_encode(['status' => 'error', 'message' => 'Authentication failed.']);
    exit;
}
$userId = (int) $userId;
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $mysqli->prepare("SELECT username, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $mysql_data = $stmt->get_result()->fetch_assoc();
    $mongo_data = $profileCollection->findOne(['user_id' => $userId]);

    $profile = [
        'username' => $mysql_data['username'],
        'email' => $mysql_data['email'],
        'age' => $mongo_data['age'] ?? '',
        'dob' => $mongo_data['dob'] ?? '',
        'contact' => $mongo_data['contact'] ?? ''
    ];
    echo json_encode(['status' => 'success', 'data' => $profile]);

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $updateData = [
        'age' => $data['age'] ?? null,
        'dob' => $data['dob'] ?? null,
        'contact' => $data['contact'] ?? null
    ];
    $profileCollection->updateOne(
        ['user_id' => $userId],
        ['$set' => $updateData],
        ['upsert' => true]
    );
    echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully.']);
}
?>