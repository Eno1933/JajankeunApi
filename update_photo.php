<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'config.php';

$userId = $_POST['user_id'] ?? '';
if (empty($userId) || !isset($_FILES['photo'])) {
    echo json_encode(['success' => false, 'message' => 'User ID atau foto tidak dikirim']);
    exit;
}

$targetDir = "uploads/profile/";
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
$filename = "user_" . $userId . "_" . time() . "." . $ext;
$targetFile = $targetDir . $filename;

if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
    $stmt = $conn->prepare("UPDATE users SET photo = ? WHERE id = ?");
    $stmt->bind_param("si", $filename, $userId);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "photo" => $filename]);
    } else {
        echo json_encode(["success" => false, "message" => "Gagal update database"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Gagal upload file"]);
}
?>
