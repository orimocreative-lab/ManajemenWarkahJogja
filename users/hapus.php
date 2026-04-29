<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    redirect('../../login.php');
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    $_SESSION['error'] = "ID user tidak valid.";
    redirect('index.php');
}

if ($id == $_SESSION['user_id']) {
    $_SESSION['error'] = "Anda tidak dapat menghapus akun Anda sendiri.";
    redirect('index.php');
}

$stmt_get = $conn->prepare("SELECT username, nama_lengkap FROM users WHERE id = ?");
$stmt_get->bind_param("i", $id);
$stmt_get->execute();
$result = $stmt_get->get_result();
$user_to_delete = $result->fetch_assoc();
$stmt_get->close();

$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Log user deletion
    if ($user_to_delete) {
        log_user_deleted($conn, $_SESSION['user_id'], $_SESSION['username'], $id, $user_to_delete['username'], $user_to_delete['nama_lengkap']);
    }
    $_SESSION['success'] = "User berhasil dihapus!";
} else {
    $_SESSION['error'] = "Error: " . $stmt->error;
}

redirect('index.php');
?>