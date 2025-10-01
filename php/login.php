<?php
header('Content-Type: application/json');
require _DIR_ . '/../vendor/autoload.php';
$mysqli = new mysqli('127.0.0.1', 'root', '', 'user_auth');
$redis = new Predis\Client();
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['email']) || empty($data['password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Email and password are required.']);
    exit;
}

$email = $data['email'];
$password = $data['password'];

$stmt = $mysqli->prepare("SELECT id, password_hash FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    if (password_verify($password, $user['password_hash'])) {
        $token = bin2hex(random_bytes(32));
        $user_id = $user['id'];
        $redis->setex("session:" . $token, 3600, $user_id);

        echo json_encode(['status' => 'success', 'token' => $token]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid credentials.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid credentials.']);
}
$stmt->close();
$mysqli->close();
?>