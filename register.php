<?php
// register.php

// 1. CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Untuk preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

require_once 'config.php'; // koneksi database

// 2. Ambil dan validasi input
$name     = $_POST['name']     ?? '';
$username = $_POST['username'] ?? '';
$email    = $_POST['email']    ?? '';
$password = $_POST['password'] ?? '';
$phone    = $_POST['phone']    ?? '';
$address  = $_POST['address']  ?? '';

// Validasi wajib
if (empty($name) || empty($username) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Name, username, email, dan password wajib diisi"
    ]);
    exit;
}

// 3. Cek apakah username atau email sudah terdaftar
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    http_response_code(409);
    echo json_encode([
        "success" => false,
        "message" => "Username atau email sudah terdaftar"
    ]);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// 4. Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// 5. Siapkan foto default
$defaultPhoto = 'default_profile.png';

// 6. Simpan user ke database (termasuk kolom photo)
$stmt = $conn->prepare("
    INSERT INTO users (name, username, email, password, phone, address, photo)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param(
    "sssssss",
    $name,
    $username,
    $email,
    $hashedPassword,
    $phone,
    $address,
    $defaultPhoto
);

if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Register berhasil!",
        "data"    => [
            "id"       => $stmt->insert_id,
            "name"     => $name,
            "username" => $username,
            "email"    => $email,
            "phone"    => $phone,
            "address"  => $address,
            "photo"    => $defaultPhoto,
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Register gagal: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
