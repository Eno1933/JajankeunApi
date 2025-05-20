<?php
// login.php

// 1. CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// 2. Preflight request – langsung keluar
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 3. Ambil input POST (gunakan filter_input untuk keamanan ekstra)
$usernameOrEmail = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// 4. Debug opsional – bisa dihapus di production
// echo json_encode(["debug_username" => $usernameOrEmail, "debug_password" => $password]); exit;

// 5. Validasi input
if (trim($usernameOrEmail) === '' || trim($password) === '') {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Semua field harus diisi"
    ]);
    exit;
}

require_once 'config.php'; // koneksi database

// 6. Cek user di database
$stmt = $conn->prepare(
    "SELECT id, name, username, email, password, role, phone, address, photo
 FROM users 
 WHERE username = ? OR email = ?
     LIMIT 1"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Query gagal: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
$stmt->execute();
$result = $stmt->get_result();

// 7. Verifikasi hasil
if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
        // 8. Berhasil login
        echo json_encode([
            "success" => true,
            "message" => "Login berhasil",
            "data" => [
                "id" => $user['id'],
                "name" => $user['name'],
                "username" => $user['username'],
                "email" => $user['email'],
                "role" => $user['role'],
                "phone" => $user['phone'],
                "address" => $user['address'],
                "photo" => $user['photo'] ?? '', // tambahkan ini
            ]

        ]);
    } else {
        // Password salah
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Password salah"
        ]);
    }
} else {
    // User tidak ditemukan
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "User tidak ditemukan"
    ]);
}

$stmt->close();
$conn->close();
?>