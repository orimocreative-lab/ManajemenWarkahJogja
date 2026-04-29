<?php
session_start();

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'arsip_bon_warkah');
define('BASE_URL', '/Arsip_Bon_Warkah/');

// Koneksi Database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Feature toggle: allow users to continue editing after admin approval until they save.
define('ALLOW_USER_EDIT_AFTER_APPROVAL', true);

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Include functions
require_once __DIR__ . '/functions.php';
?>
