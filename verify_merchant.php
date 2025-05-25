<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$targetDir = "uploads/";
$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Include DB connection
    require_once 'config.php';

    // Validate required fields
    if (
        empty($_POST['user_id']) ||
        empty($_POST['nik']) ||
        empty($_POST['business_name']) ||
        !isset($_FILES['ktp_photo']) ||
        !isset($_FILES['stall_photo'])
    ) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi.']);
        exit;
    }

    $user_id = $_POST['user_id'];
    $nik = $_POST['nik'];
    $business_name = $_POST['business_name'];

    // Save uploaded images
    $ktpFileName = uniqid('ktp_') . '_' . basename($_FILES["ktp_photo"]["name"]);
    $stallFileName = uniqid('stall_') . '_' . basename($_FILES["stall_photo"]["name"]);

    $ktpPath = $targetDir . $ktpFileName;
    $stallPath = $targetDir . $stallFileName;

    if (!move_uploaded_file($_FILES["ktp_photo"]["tmp_name"], $ktpPath) ||
        !move_uploaded_file($_FILES["stall_photo"]["tmp_name"], $stallPath)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal upload file.']);
        exit;
    }

    // Simpan ke DB
    $stmt = $conn->prepare("INSERT INTO pedagang_profiles (user_id, nik, business_name, ktp_photo_path, stall_photo_path, verification_status) VALUES (?, ?, ?, ?, ?, 'pending')");

    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Query error: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("issss", $user_id, $nik, $business_name, $ktpPath, $stallPath);
    $success = $stmt->execute();

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Pengajuan berhasil disimpan.']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan data. Mungkin user sudah mengajukan.']);
    }

    $stmt->close();
    $conn->close();
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan.']);
}
