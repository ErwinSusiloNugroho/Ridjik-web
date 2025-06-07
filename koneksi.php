<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'ridjik';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    header('Content-Type: application/json');
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'status' => 'error',
        'message' => 'Koneksi database gagal: ' . $conn->connect_error
    ]);
    exit;
}
?>
