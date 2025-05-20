<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');
require_once 'config.php'; // pastikan koneksi ke database

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $address = $_POST['address'] ?? '';

    if (empty($user_id) || empty($name) || empty($address)) {
        $response['success'] = false;
        $response['message'] = 'Semua field harus diisi.';
    } else {
        $query = "UPDATE users SET name = ?, address = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ssi', $name, $address, $user_id);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Profil berhasil diperbarui.';
        } else {
            $response['success'] = false;
            $response['message'] = 'Gagal memperbarui profil.';
        }

        $stmt->close();
    }
} else {
    $response['success'] = false;
    $response['message'] = 'Metode tidak diizinkan.';
}

echo json_encode($response);
?>
