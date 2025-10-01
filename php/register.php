<?php
header('Content-Type: application/json');
require _DIR_ . '/../vendor/autoload.php';
$mysqli = new mysqli('127.0.0.1', 'root', '', 'user_auth');
$mongoClient = new MongoDB\Client("mongodb://127.0.0.1:27017");
$profileCollection = $mongoClient->user_profiles->profiles;
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit;
}

$username = $data['username'];
$email = $data['email'];
$password_hash = password_hash($data['password'], PASSWORD_BCRYPT);
$stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Username or email already exists.']);
    exit;
}

$stmt = $mysqli->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $email, $password_hash);

if ($stmt->execute()) {
    $user_id = $stmt->insert_id;
    $profileCollection->insertOne(['user_id' => $user_id]);
    echo json_encode(['status' => 'success', 'message' => 'Registration successful!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Registration failed.']);
}
$stmt->close();
$mysqli->close();
?>