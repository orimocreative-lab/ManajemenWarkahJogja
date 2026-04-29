<?php
require_once '../../includes/config.php';

if (!isLoggedIn()) {
    header('HTTP/1.0 403 Forbidden');
    exit('Unauthorized');
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header('HTTP/1.0 404 Not Found');
    exit('File not found');
}

// Ambil informasi berkas
$stmt = $conn->prepare("SELECT b.*, bw.nomor_bon FROM berkas b JOIN bon_warkah bw ON b.bon_warkah_id = bw.id WHERE b.id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header('HTTP/1.0 404 Not Found');
    exit('File not found');
}

$berkas = $result->fetch_assoc();
$stmt->close();

// Construct full file path
$file_path = $_SERVER['DOCUMENT_ROOT'] . $berkas['file_path'];

// Check if file exists
if (!file_exists($file_path)) {
    header('HTTP/1.0 404 Not Found');
    exit('File not found on server');
}

// Get file info
$file_size = filesize($file_path);
$file_name = $berkas['nama_berkas'] . '.pdf';

// Log download activity
log_audit($conn, $_SESSION['user_id'], $_SESSION['username'], 'DOWNLOAD_BERKAS', 'Berkas ID: ' . $id . ', Data Warkah: ' . $berkas['nomor_bon']);

// Set headers untuk download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . urlencode($file_name) . '"');
header('Content-Length: ' . $file_size);
header('Pragma: no-cache');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

// Output file
readfile($file_path);
exit;
?>
