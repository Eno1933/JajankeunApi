<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "jajankeun_db";

$conn = mysqli_connect($host, $user, $pass, $db);

if (mysqli_connect_errno()) {
    error_log("MySQL Connection Error ({$host}): " . mysqli_connect_error());
    http_response_code(500);
    echo json_encode([
      'success' => false,
      'message' => 'Database connection failed'
    ]);
    exit;
}

// Set character set
mysqli_set_charset($conn, 'utf8mb4');
?>
