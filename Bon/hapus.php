<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isLoggedIn()) {
    redirect('../../login.php');
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    $_SESSION['error'] = "ID data warkah tidak valid.";
    redirect('index.php');
}

// Get data warkah data before deletion for audit trail
$stmt_select = $conn->prepare("SELECT * FROM bon_warkah WHERE id = ?");
$stmt_select->bind_param("i", $id);
$stmt_select->execute();
$result = $stmt_select->get_result();
$bon_data = $result->fetch_assoc();
$stmt_select->close();

// Query untuk menghapus data warkah
$stmt = $conn->prepare("DELETE FROM bon_warkah WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $stmt->close();
    
    // Log deletion to audit trail
    if ($bon_data) {
        log_bon_deleted(
            $conn,
            $_SESSION['user_id'],
            $_SESSION['username'],
            $id,
            $bon_data['nomor_bon'],
            $bon_data
        );
    }
    $_SESSION['success'] = "Data warkah berhasil dihapus!";
} else {
    $stmt->close();
    $_SESSION['error'] = "Error: " . $stmt->error;
}

redirect('index.php');
?>