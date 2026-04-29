<?php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function compare_changes($old, $new) {
    $changes = [];
    foreach ($new as $key => $value) {
        if (isset($old[$key]) && $old[$key] !== $value) {
            $changes[$key] = [
                'old' => $old[$key],
                'new' => $value
            ];
        }
    }
    return $changes;
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

function is_valid_date_value($date) {
    if (empty($date)) {
        return false;
    }

    $date = trim($date);
    if (in_array($date, ['0000-00-00', '0000-00-00 00:00:00'], true)) {
        return false;
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        [$year, $month, $day] = explode('-', $date);
        return checkdate((int)$month, (int)$day, (int)$year);
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $date)) {
        $dt = DateTime::createFromFormat('Y-m-d H:i:s', $date);
        return $dt && $dt->format('Y-m-d H:i:s') === $date;
    }

    return false;
}

function format_date_display($date, $format = 'd/m/Y') {
    return is_valid_date_value($date) ? date($format, strtotime($date)) : '-';
}

/**
 * Log an action to audit_trail table.
 *
 * @param mysqli $conn
 * @param int|null $user_id
 * @param string $username
 * @param string $action
 * @param string $details
 * @return bool
 */
function log_audit($conn, $user_id, $username, $action, $details = '', $entity_type = null, $entity_id = null, $entity_name = null, $old_values = null, $new_values = null, $status = 'success') {
    $ip = get_client_ip();
    
    $old_v = $old_values ? json_encode($old_values) : null;
    $new_v = $new_values ? json_encode($new_values) : null;
    $det = is_array($details) ? json_encode($details) : $details;
    
    $query = "INSERT INTO audit_trail (user_id, username, action, entity_type, entity_id, entity_name, old_values, new_values, details, ip, status, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    if ($stmt = $conn->prepare($query)) {
        $uid = $user_id !== null ? (int)$user_id : null;
        $eid = $entity_id !== null ? (int)$entity_id : null;
        $stmt->bind_param('isisissssss', $uid, $username, $action, $entity_type, $eid, $entity_name, $old_v, $new_v, $det, $ip, $status);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }
    return false;
}

function log_berkas_uploaded($conn, $user_id, $username, $berkas_id, $datawarkah_id, $berkas_data) {
    $nama = $berkas_data['nama_berkas'] ?? "Berkas #$berkas_id";
    $detail = "Unggah berkas baru: $nama pada Data Warkah ID: $datawarkah_id";
    return log_audit($conn, $user_id, $username, 'UNGGAH_BERKAS', $detail, 'warkah_berkas', $berkas_id, $nama);
}

/**
 * Log user login
 *
 * @param mysqli $conn
 * @param int $user_id
 * @param string $username
 * @param bool $success
 * @return bool
 */
function log_login($conn, $user_id, $username, $success = true) {
    $action = $success ? 'LOGIN_BERHASIL' : 'LOGIN_GAGAL';
    $details = $success ? 'User berhasil login' : 'Percobaan login gagal';
    return log_audit($conn, $user_id, $username, $action, $details);
}

/**
 * Log user logout
 *
 * @param mysqli $conn
 * @param int $user_id
 * @param string $username
 * @return bool
 */
function log_logout($conn, $user_id, $username) {
    return log_audit($conn, $user_id, $username, 'LOGOUT', 'User logout dari sistem');
}

/**
 * Log user creation
 *
 * @param mysqli $conn
 * @param int $user_id
 * @param string $username
 * @param string $new_username
 * @param string $nama_lengkap
 * @param string $role
 * @return bool
 */
function log_user_created($conn, $user_id, $username, $new_username, $nama_lengkap, $role) {
    $details = sprintf(
        'User baru dibuat - Username: %s, Nama: %s, Role: %s',
        $new_username,
        $nama_lengkap,
        $role
    );
    return log_audit($conn, $user_id, $username, 'USER_DIBUAT', $details);
}

/**
 * Log user update
 *
 * @param mysqli $conn
 * @param int $user_id
 * @param string $username
 * @param int $updated_user_id
 * @param string $updated_username
 * @param array $changes
 * @return bool
 */
function log_user_updated($conn, $user_id, $username, $updated_user_id, $updated_username, $changes = []) {
    $change_details = '';
    if (!empty($changes)) {
        $change_details = ' - Perubahan: ';
        foreach ($changes as $field => $vals) {
            $change_details .= sprintf('%s (%s → %s), ', $field, $vals['old'], $vals['new']);
        }
        $change_details = rtrim($change_details, ', ');
    }
    
    $details = sprintf(
        'User diperbarui - ID: %d, Username: %s%s',
        $updated_user_id,
        $updated_username,
        $change_details
    );
    return log_audit($conn, $user_id, $username, 'USER_DIPERBARUI', $details);
}

/**
 * Log user deletion
 *
 * @param mysqli $conn
 * @param int $user_id
 * @param string $username
 * @param int $deleted_user_id
 * @param string $deleted_username
 * @param string $deleted_name
 * @return bool
 */
function log_user_deleted($conn, $user_id, $username, $deleted_user_id, $deleted_username, $deleted_name) {
    $details = sprintf(
        'User dihapus - ID: %d, Username: %s, Nama: %s',
        $deleted_user_id,
        $deleted_username,
        $deleted_name
    );
    return log_audit($conn, $user_id, $username, 'USER_DIHAPUS', $details);
}

/**
 * Log data warkah/document action
 *
 * @param mysqli $conn
 * @param int $user_id
 * @param string $username
 * @param string $action (TAMBAH_DATA_WARKAH, EDIT_DATA_WARKAH, HAPUS_DATA_WARKAH, PINJAM_DATA_WARKAH, KEMBALIKAN_DATA_WARKAH)
 * @param int $datawarkah_id
 * @param string $nomor_datawarkah
 * @param string $details
 * @return bool
 */
function log_bon_action($conn, $user_id, $username, $action, $datawarkah_id, $nomor_datawarkah, $details = '') {
    $full_details = sprintf('Data Warkah ID: %d, No: %s', $datawarkah_id, $nomor_datawarkah);
    if (!empty($details)) {
        $full_details .= ' - ' . $details;
    }
    return log_audit($conn, $user_id, $username, $action, $full_details);
}

/**
 * Handle PDF file upload untuk Warkah
 *
 * @param string $field_name Nama field dari $_FILES
 * @param string $target_dir Direktori tujuan upload
 * @param int $max_size Ukuran maksimal file dalam bytes (default 5MB)
 * @return array Array dengan keys: 'success' (bool), 'file_path' (string), 'error' (string)
 */
function handle_pdf_upload($field_name, $target_dir, $max_size = 5242880) {
    $result = ['success' => false, 'file_path' => '', 'error' => ''];
    
    // Validasi field ada dan tidak error
    if (empty($_FILES[$field_name]) || $_FILES[$field_name]['error'] !== UPLOAD_ERR_OK) {
        $result['error'] = 'File tidak diunggah atau terjadi error';
        return $result;
    }
    
    $file = $_FILES[$field_name];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Validasi extension (hanya PDF)
    if ($ext !== 'pdf') {
        $result['error'] = 'Hanya file PDF yang diizinkan';
        return $result;
    }
    
    // Validasi ukuran
    if ($file['size'] > $max_size) {
        $result['error'] = 'Ukuran file terlalu besar (maksimal 5MB)';
        return $result;
    }
    
    // Buat direktori jika belum ada
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    // Generate nama file unik
    $name = preg_replace('/[^a-zA-Z0-9-_\.]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
    $newname = $name . '_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
    $dest = $target_dir . $newname;
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        // Return relative path
        $rel_path = str_replace('\\', '/', $dest);
        $rel_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $rel_path);
        $result['success'] = true;
        $result['file_path'] = $rel_path;
    } else {
        $result['error'] = 'Gagal mengupload file';
    }
    
    return $result;
}

/**
 * Get all berkas untuk warkah tertentu
 *
 * @param mysqli $conn
 * @param int $warkah_id
 * @return array Array of berkas data
 */
function get_warkah_berkas($conn, $warkah_id) {
    $stmt = $conn->prepare("SELECT * FROM warkah_berkas WHERE warkah_id = ? ORDER BY created_at DESC");
    $stmt->bind_param('i', $warkah_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $berkas = [];
    while ($row = $result->fetch_assoc()) {
        $berkas[] = $row;
    }
    $stmt->close();
    return $berkas;
}

/**
 * Tambah berkas untuk warkah
 *
 * @param mysqli $conn
 * @param int $warkah_id
 * @param string $nama_berkas
 * @param string $file_path
 * @param string $tipe_berkas
 * @param int $ukuran_file
 * @param string $uploaded_by
 * @param string $deskripsi
 * @return array Array dengan keys: 'success' (bool), 'id' (int/null), 'error' (string)
 */
function tambah_warkah_berkas($conn, $warkah_id, $nama_berkas, $file_path, $tipe_berkas, $ukuran_file, $uploaded_by, $deskripsi = '') {
    $result = ['success' => false, 'id' => null, 'error' => ''];
    
    $stmt = $conn->prepare("INSERT INTO warkah_berkas (warkah_id, nama_berkas, deskripsi, file_path, tipe_berkas, ukuran_file, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        $result['error'] = 'Database error: ' . $conn->error;
        return $result;
    }
    
    $stmt->bind_param('isssisis', $warkah_id, $nama_berkas, $deskripsi, $file_path, $tipe_berkas, $ukuran_file, $uploaded_by);
    
    if ($stmt->execute()) {
        $result['success'] = true;
        $result['id'] = $conn->insert_id;
    } else {
        $result['error'] = 'Gagal menyimpan data berkas: ' . $stmt->error;
    }
    
    $stmt->close();
    return $result;
}

/**
 * Delete berkas
 *
 * @param mysqli $conn
 * @param int $berkas_id
 * @param bool $delete_file Apakah file fisik juga dihapus
 * @return array Array dengan keys: 'success' (bool), 'error' (string)
 */
function delete_warkah_berkas($conn, $berkas_id, $delete_file = true) {
    $result = ['success' => false, 'error' => ''];
    
    // Ambil data berkas
    $stmt = $conn->prepare("SELECT file_path FROM warkah_berkas WHERE id = ?");
    $stmt->bind_param('i', $berkas_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows === 0) {
        $result['error'] = 'Berkas tidak ditemukan';
        $stmt->close();
        return $result;
    }
    
    $berkas = $res->fetch_assoc();
    $stmt->close();
    
    // Hapus file fisik jika diminta
    if ($delete_file && !empty($berkas['file_path'])) {
        $file_path = $_SERVER['DOCUMENT_ROOT'] . $berkas['file_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    // Hapus dari database
    $stmt = $conn->prepare("DELETE FROM warkah_berkas WHERE id = ?");
    $stmt->bind_param('i', $berkas_id);
    
    if ($stmt->execute()) {
        $result['success'] = true;
    } else {
        $result['error'] = 'Gagal menghapus berkas dari database: ' . $stmt->error;
    }
    
    $stmt->close();
    return $result;
}

/**
 * Format ukuran file untuk display
 *
 * @param int $bytes
 * @return string Formatted size (B, KB, MB, GB)
 */
function format_file_size($bytes) {
    $sizes = ['B', 'KB', 'MB', 'GB'];
    if ($bytes == 0) return '0 B';
    $i = floor(log($bytes, 1024));
    return round($bytes / pow(1024, $i), 2) . ' ' . $sizes[$i];
}

/**
 * Log Warkah (Document) addition
 *
 * @param mysqli $conn
 * @param int $user_id
 * @param string $username
 * @param int $warkah_id
 * @param string $nomor_warkah
 * @param array $new_values
 * @return bool
 */
function log_warkah_added($conn, $user_id, $username, $warkah_id, $nomor_warkah, $new_values = []) {
    $details = "Menambah Warkah baru nomor: $nomor_warkah";
    return log_audit($conn, $user_id, $username, 'TAMBAH_WARKAH', $details, 'warkah', $warkah_id, $nomor_warkah, null, $new_values);
}

/**
 * Log Warkah update
 *
 * @param mysqli $conn
 * @param int $user_id
 * @param string $username
 * @param int $warkah_id
 * @param string $nomor_warkah
 * @param array $old_values
 * @param array $new_values
 * @return bool
 */
function log_warkah_updated($conn, $user_id, $username, $warkah_id, $nomor_warkah, $old_values = [], $new_values = []) {
    $details = "Memperbarui data Warkah nomor: $nomor_warkah";
    return log_audit($conn, $user_id, $username, 'EDIT_WARKAH', $details, 'warkah', $warkah_id, $nomor_warkah, $old_values, $new_values);
}

/**
 * Log Warkah deletion
 *
 * @param mysqli $conn
 * @param int $user_id
 * @param string $username
 * @param int $warkah_id
 * @param string $nomor_warkah
 * @param array $deleted_values
 * @return bool
 */
function log_warkah_deleted($conn, $user_id, $username, $warkah_id, $nomor_warkah, $deleted_values = []) {
    $details = "Menghapus Warkah nomor: $nomor_warkah";
    return log_audit($conn, $user_id, $username, 'HAPUS_WARKAH', $details, 'warkah', $warkah_id, $nomor_warkah, $deleted_values, null);
}

/**
 * Log Data Warkah addition with changes
 *
 * @param mysqli $conn
 * @param int $user_id
 * @param string $username
 * @param int $datawarkah_id
 * @param string $nomor_datawarkah
 * @param array $new_values
 * @return bool
 */
function log_bon_added($conn, $user_id, $username, $datawarkah_id, $nomor_datawarkah, $new_values = []) {
    $details = "Menambah Data Warkah baru nomor: $nomor_datawarkah";
    return log_audit($conn, $user_id, $username, 'TAMBAH_DATA_WARKAH', $details, 'bon_warkah', $datawarkah_id, $nomor_datawarkah, null, $new_values);
}

/**
 * Log Data Warkah update with old and new values
 *
 * @param mysqli $conn
 * @param int $user_id
 * @param string $username
 * @param int $datawarkah_id
 * @param string $nomor_datawarkah
 * @param array $old_values
 * @param array $new_values
 * @return bool
 */
function log_bon_updated($conn, $user_id, $username, $datawarkah_id, $nomor_datawarkah, $old_values = [], $new_values = []) {
    $details = "Memperbarui data Data Warkah nomor: $nomor_datawarkah";
    return log_audit($conn, $user_id, $username, 'EDIT_DATA_WARKAH', $details, 'bon_warkah', $datawarkah_id, $nomor_datawarkah, $old_values, $new_values);
}

/**
 * Log Data Warkah deletion
 *
 * @param mysqli $conn
 * @param int $user_id
 * @param string $username
 * @param int $datawarkah_id
 * @param string $nomor_datawarkah
 * @param array $deleted_values
 * @return bool
 */
function log_bon_deleted($conn, $user_id, $username, $datawarkah_id, $nomor_datawarkah, $deleted_values = []) {
    $details = "Menghapus Data Warkah nomor: $nomor_datawarkah";
    return log_audit($conn, $user_id, $username, 'HAPUS_DATA_WARKAH', $details, 'bon_warkah', $datawarkah_id, $nomor_datawarkah, $deleted_values, null);
}

/**
 * Log Berkas action
 *
 * @param mysqli $conn
 * @param int $user_id
 * @param string $username
 * @param string $action TAMBAH_BERKAS, EDIT_BERKAS, HAPUS_BERKAS
 * @param int $berkas_id
 * @param string $nama_berkas
 * @param int|null $entity_id Parent entity ID (bon_id or warkah_id)
 * @param string $entity_type Parent entity type (bon_warkah or warkah)
 * @param string $details
 * @return bool
 */
function log_berkas_action($conn, $user_id, $username, $action, $berkas_id, $nama_berkas, $entity_id = null, $entity_type = 'bon_warkah', $details = '') {
    $full_details = "Berkas: $nama_berkas";
    if (!empty($details)) {
        $full_details .= " - $details";
    }
    return log_audit($conn, $user_id, $username, $action, $full_details, 'warkah_berkas', $berkas_id, $nama_berkas);
}

/**
 * Get audit trail logs dengan filter
 *
 * @param mysqli $conn
 * @param array $filters Array dengan filter options: action, user_id, entity_type, date_from, date_to
 * @param int $limit
 * @param int $offset
 * @return array Array of audit trail records
 */
function get_audit_logs($conn, $filters = [], $limit = 100, $offset = 0) {
    $where = "WHERE 1=1";
    
    if (!empty($filters['action'])) {
        $action = $conn->real_escape_string($filters['action']);
        $where .= " AND action LIKE '%$action%'";
    }
    
    if (!empty($filters['user_id'])) {
        $user_id = intval($filters['user_id']);
        $where .= " AND user_id = $user_id";
    }
    
    if (!empty($filters['username'])) {
        $username = $conn->real_escape_string($filters['username']);
        $where .= " AND username LIKE '%$username%'";
    }
    
    if (!empty($filters['entity_type'])) {
        $entity_type = $conn->real_escape_string($filters['entity_type']);
        $where .= " AND entity_type = '$entity_type'";
    }
    
    if (!empty($filters['date_from'])) {
        $date_from = $conn->real_escape_string($filters['date_from']);
        $where .= " AND DATE(created_at) >= '$date_from'";
    }
    
    if (!empty($filters['date_to'])) {
        $date_to = $conn->real_escape_string($filters['date_to']);
        $where .= " AND DATE(created_at) <= '$date_to'";
    }
    
    $query = "SELECT * FROM audit_trail $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
    $result = $conn->query($query);
    
    $logs = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
    }
    
    return $logs;
}

/**
 * Get total count of audit trail logs dengan filter
 *
 * @param mysqli $conn
 * @param array $filters
 * @return int
 */
function count_audit_logs($conn, $filters = []) {
    $where = "WHERE 1=1";
    
    if (!empty($filters['action'])) {
        $action = $conn->real_escape_string($filters['action']);
        $where .= " AND action LIKE '%$action%'";
    }
    
    if (!empty($filters['user_id'])) {
        $user_id = intval($filters['user_id']);
        $where .= " AND user_id = $user_id";
    }
    
    if (!empty($filters['username'])) {
        $username = $conn->real_escape_string($filters['username']);
        $where .= " AND username LIKE '%$username%'";
    }
    
    if (!empty($filters['entity_type'])) {
        $entity_type = $conn->real_escape_string($filters['entity_type']);
        $where .= " AND entity_type = '$entity_type'";
    }
    
    if (!empty($filters['date_from'])) {
        $date_from = $conn->real_escape_string($filters['date_from']);
        $where .= " AND DATE(created_at) >= '$date_from'";
    }
    
    if (!empty($filters['date_to'])) {
        $date_to = $conn->real_escape_string($filters['date_to']);
        $where .= " AND DATE(created_at) <= '$date_to'";
    }
    
    $query = "SELECT COUNT(*) as total FROM audit_trail $where";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return intval($row['total']);
}

/**
 * Cek apakah user adalah admin
 *
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Cek apakah user adalah regular user
 *
 * @return bool
 */
function isUser() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'user';
}

/**
 * Cek apakah user adalah pembuat record tertentu
 *
 * @param int $created_by User ID yang membuat record
 * @return bool
 */
function isCreatedBy($created_by) {
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] == $created_by;
}

/**
 * Submit request edit ke admin untuk bon_warkah
 *
 * @param mysqli $conn
 * @param int $bon_id
 * @param int $user_id
 * @param array $proposed_changes Array dengan perubahan yang diusulkan
 * @return array Array dengan keys: 'success' (bool), 'request_id' (int/null), 'error' (string), 'message' (string)
 */
function submitEditRequest($conn, $bon_id, $user_id, $proposed_changes = []) {
    $result = ['success' => false, 'request_id' => null, 'error' => '', 'message' => ''];
    
    // Cek apakah bon_id valid
    $stmt_bon = $conn->prepare("SELECT id FROM bon_warkah WHERE id = ?");
    $stmt_bon->bind_param('i', $bon_id);
    $stmt_bon->execute();
    if ($stmt_bon->get_result()->num_rows === 0) {
        $result['error'] = 'Data Warkah tidak ditemukan';
        $stmt_bon->close();
        return $result;
    }
    $stmt_bon->close();
    
    // Cek apakah sudah ada request yang masih aktif (pending atau, jika fitur diaktifkan, sudah disetujui tapi belum disimpan)
    if (defined('ALLOW_USER_EDIT_AFTER_APPROVAL') && ALLOW_USER_EDIT_AFTER_APPROVAL) {
        $stmt_check = $conn->prepare("SELECT id FROM edit_requests WHERE bon_id = ? AND status IN ('pending','approved')");
    } else {
        $stmt_check = $conn->prepare("SELECT id FROM edit_requests WHERE bon_id = ? AND status = 'pending'");
    }
    $stmt_check->bind_param('i', $bon_id);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        $result['error'] = 'Sudah ada permintaan edit yang sedang berlangsung untuk data ini';
        $stmt_check->close();
        return $result;
    }
    $stmt_check->close();
    
    // Simpan request edit
    $proposed_json = !empty($proposed_changes) ? json_encode($proposed_changes) : null;
    $stmt = $conn->prepare("INSERT INTO edit_requests (bon_id, requested_by, proposed_changes, status) VALUES (?, ?, ?, 'pending')");
    
    if (!$stmt) {
        $result['error'] = 'Database error: ' . $conn->error;
        return $result;
    }
    
    $stmt->bind_param('iis', $bon_id, $user_id, $proposed_json);
    
    if ($stmt->execute()) {
        $result['success'] = true;
        $result['request_id'] = $conn->insert_id;
        $result['message'] = 'Permintaan edit berhasil dikirim ke admin. Menunggu persetujuan...';
        
        // Log audit
        $user_info = $conn->query("SELECT username FROM users WHERE id = $user_id")->fetch_assoc();
        log_audit($conn, $user_id, $user_info['username'], 'REQUEST_EDIT_BON', "Mengirim permintaan edit untuk Bon ID: $bon_id", 'edit_requests', $conn->insert_id, "Request Edit Bon #$bon_id");
    } else {
        $result['error'] = 'Gagal menyimpan permintaan: ' . $stmt->error;
    }
    
    $stmt->close();
    return $result;
}

/**
 * Get edit request untuk bon tertentu
 *
 * @param mysqli $conn
 * @param int $bon_id
 * @return array|null Array edit request atau null jika tidak ada
 */
function getEditRequest($conn, $bon_id) {
    $stmt = $conn->prepare("SELECT er.*, u.username AS requested_by_username, u.nama_lengkap AS requested_by_name FROM edit_requests er 
                           JOIN users u ON er.requested_by = u.id 
                           WHERE er.bon_id = ? AND er.status = 'pending' 
                           ORDER BY er.requested_at DESC LIMIT 1");
    $stmt->bind_param('i', $bon_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $request = $result->num_rows > 0 ? $result->fetch_assoc() : null;
    $stmt->close();
    return $request;
}

/**
 * Retrieve an approved edit request that has not yet been applied by the user.
 *
 * @param mysqli $conn
 * @param int $bon_id
 * @param int $user_id
 * @return array|null
 */
function getApprovedEditRequest($conn, $bon_id, $user_id) {
    // hanya relevan ketika fitur 'ALLOW_USER_EDIT_AFTER_APPROVAL' diaktifkan
    if (!defined('ALLOW_USER_EDIT_AFTER_APPROVAL') || !ALLOW_USER_EDIT_AFTER_APPROVAL) {
        return null;
    }

    $stmt = $conn->prepare("SELECT er.*, u.username, u.nama_lengkap FROM edit_requests er 
                           JOIN users u ON er.requested_by = u.id 
                           WHERE er.bon_id = ? AND er.requested_by = ? AND er.status = 'approved' 
                           ORDER BY er.approved_at DESC LIMIT 1");
    $stmt->bind_param('ii', $bon_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $request = $result->num_rows > 0 ? $result->fetch_assoc() : null;
    $stmt->close();
    return $request;
}

/**
 * Get semua edit requests untuk admin approval
 *
 * @param mysqli $conn
 * @param string $status 'pending', 'approved', 'rejected', atau empty untuk semua
 * @param int $limit
 * @param int $offset
 * @return array Array of edit requests
 */
function getEditRequests($conn, $status = 'pending', $limit = 50, $offset = 0) {
    $where = '1=1';
    if (!empty($status)) {
        $status = $conn->real_escape_string($status);
        $where .= " AND er.status = '$status'";
    }
    
    $query = "SELECT er.*, 
                     u.username as requested_by_username, u.nama_lengkap as requested_by_name,
                     au.username as approved_by_username, au.nama_lengkap as approved_by_name,
                     bw.nomor_bon, bw.peminjam, bw.kelurahan
              FROM edit_requests er
              JOIN users u ON er.requested_by = u.id
              LEFT JOIN users au ON er.approved_by = au.id
              JOIN bon_warkah bw ON er.bon_id = bw.id
              WHERE $where
              ORDER BY er.requested_at DESC
              LIMIT $limit OFFSET $offset";
    
    $result = $conn->query($query);
    $requests = [];
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
    return $requests;
}

/**
 * Count edit requests
 *
 * @param mysqli $conn
 * @param string $status 'pending', 'approved', 'rejected', atau empty untuk semua
 * @return int Total count
 */
function countEditRequests($conn, $status = 'pending') {
    $where = '1=1';
    if (!empty($status)) {
        $status = $conn->real_escape_string($status);
        $where .= " AND status = '$status'";
    }
    
    $query = "SELECT COUNT(*) as total FROM edit_requests WHERE $where";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return intval($row['total']);
}

/**
 * Approve edit request
 *
 * @param mysqli $conn
 * @param int $request_id
 * @param int $admin_id
 * @return array Array dengan keys: 'success' (bool), 'error' (string), 'message' (string)
 */
function approveEditRequest($conn, $request_id, $admin_id) {
    $result = ['success' => false, 'error' => '', 'message' => ''];
    
    // Ambil data request
    $stmt = $conn->prepare("SELECT * FROM edit_requests WHERE id = ?");
    $stmt->bind_param('i', $request_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows === 0) {
        $result['error'] = 'Permintaan edit tidak ditemukan';
        $stmt->close();
        return $result;
    }
    
    $request = $res->fetch_assoc();
    $stmt->close();
    
    // Update request status
    $stmt_upd = $conn->prepare("UPDATE edit_requests SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?");
    $stmt_upd->bind_param('ii', $admin_id, $request_id);
    
    if ($stmt_upd->execute()) {
        $stmt_upd->close();
        $result['success'] = true;
        $result['message'] = 'Permintaan edit berhasil disetujui';
        
        // Log audit
        $admin_info = $conn->query("SELECT username FROM users WHERE id = $admin_id")->fetch_assoc();
        $bon_info = $conn->query("SELECT nomor_bon FROM bon_warkah WHERE id = " . $request['bon_id'])->fetch_assoc();
        log_audit($conn, $admin_id, $admin_info['username'], 'APPROVE_EDIT_REQUEST', "Menyetujui permintaan edit untuk Bon: " . $bon_info['nomor_bon'], 'edit_requests', $request_id, "Approval Edit Request");
    } else {
        $result['error'] = 'Gagal menyetujui permintaan: ' . $stmt_upd->error;
        $stmt_upd->close();
    }
    
    return $result;
}

/**
 * Reject edit request
 *
 * @param mysqli $conn
 * @param int $request_id
 * @param int $admin_id
 * @param string $rejection_reason Alasan penolakan
 * @return array Array dengan keys: 'success' (bool), 'error' (string), 'message' (string)
 */
function rejectEditRequest($conn, $request_id, $admin_id, $rejection_reason = '') {
    $result = ['success' => false, 'error' => '', 'message' => ''];
    
    // Ambil data request
    $stmt = $conn->prepare("SELECT * FROM edit_requests WHERE id = ?");
    $stmt->bind_param('i', $request_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows === 0) {
        $result['error'] = 'Permintaan edit tidak ditemukan';
        $stmt->close();
        return $result;
    }
    
    $request = $res->fetch_assoc();
    $stmt->close();
    
    // Update request status
    $reason = !empty($rejection_reason) ? $rejection_reason : null;
    $stmt_upd = $conn->prepare("UPDATE edit_requests SET status = 'rejected', approved_by = ?, approved_at = NOW(), rejection_reason = ? WHERE id = ?");
    $stmt_upd->bind_param('isi', $admin_id, $reason, $request_id);
    
    if ($stmt_upd->execute()) {
        $stmt_upd->close();
        $result['success'] = true;
        $result['message'] = 'Permintaan edit berhasil ditolak';
        
        // Log audit
        $admin_info = $conn->query("SELECT username FROM users WHERE id = $admin_id")->fetch_assoc();
        $bon_info = $conn->query("SELECT nomor_bon FROM bon_warkah WHERE id = " . $request['bon_id'])->fetch_assoc();
        log_audit($conn, $admin_id, $admin_info['username'], 'REJECT_EDIT_REQUEST', "Menolak permintaan edit untuk Bon: " . $bon_info['nomor_bon'] . " - Alasan: $reason", 'edit_requests', $request_id, "Rejection Edit Request");
    } else {
        $result['error'] = 'Gagal menolak permintaan: ' . $stmt_upd->error;
        $stmt_upd->close();
    }
    
    return $result;
}

/**
 * Generate human-readable summary of changes for audit trail
 *
 * @param string $old_values_json JSON string of old values
 * @param string $new_values_json JSON string of new values
 * @param string $entity_type Type of entity being changed
 * @return string HTML formatted summary of changes
 */
function generate_change_summary($old_values_json, $new_values_json, $entity_type = 'bon_warkah') {
    $summary = '';
    
    if (empty($old_values_json) && empty($new_values_json)) {
        return '<div class="text-muted"><em>Tidak ada data perubahan</em></div>';
    }
    
    $old_values = json_decode($old_values_json, true);
    $new_values = json_decode($new_values_json, true);
    
    if ($old_values === null && $new_values === null) {
        return '<div class="text-muted"><em>Data tidak valid</em></div>';
    }
    
    // Define field labels based on entity type
    $field_labels = [
        'bon_warkah' => [
            'nomor_hak' => 'Nomor Hak',
            'kelurahan' => 'Kelurahan',
            'kecamatan' => 'Kecamatan', 
            'lokasi_warkah' => 'Lokasi Warkah',
            'tanggal_pinjam' => 'Tanggal Pinjam',
            'tanggal_kembali' => 'Tanggal Kembali',
            'peminjam' => 'Peminjam',
            'status' => 'Status',
            'keterangan' => 'Keterangan',
            'status_terakhir' => 'Status Terakhir'
        ],
        'warkah' => [
            'nomor_warkah' => 'Nomor Warkah',
            'jenis_warkah' => 'Jenis Warkah',
            'tanggal_warkah' => 'Tanggal Warkah',
            'nama_pemegang_hak' => 'Nama Pemegang Hak',
            'letak_tanah' => 'Letak Tanah',
            'luas_tanah' => 'Luas Tanah',
            'batas_utara' => 'Batas Utara',
            'batas_selatan' => 'Batas Selatan',
            'batas_timur' => 'Batas Timur',
            'batas_barat' => 'Batas Barat'
        ],
        'users' => [
            'username' => 'Username',
            'email' => 'Email',
            'role' => 'Role',
            'nama_lengkap' => 'Nama Lengkap'
        ]
    ];
    
    $labels = $field_labels[$entity_type] ?? [];
    
    $changes = [];
    
    // Handle new data (creation)
    if ($old_values === null && is_array($new_values)) {
        $summary .= '<div class="alert alert-success p-2 mb-2"><strong><i class="fas fa-plus-circle"></i> Data Baru Ditambahkan</strong></div>';
        foreach ($new_values as $field => $value) {
            if (!empty($value)) {
                $label = $labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
                $changes[] = "<strong>$label:</strong> " . htmlspecialchars($value);
            }
        }
    }
    // Handle deletion
    elseif (is_array($old_values) && $new_values === null) {
        $summary .= '<div class="alert alert-danger p-2 mb-2"><strong><i class="fas fa-trash-alt"></i> Data Dihapus</strong></div>';
        foreach ($old_values as $field => $value) {
            if (!empty($value)) {
                $label = $labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
                $changes[] = "<strong>$label:</strong> <del>" . htmlspecialchars($value) . "</del>";
            }
        }
    }
    // Handle updates
    elseif (is_array($old_values) && is_array($new_values)) {
        // Detect type of change
        $change_type = 'update';
        $is_borrowing = false;
        $is_returning = false;
        
        if (isset($new_values['status']) && isset($old_values['status'])) {
            if ($old_values['status'] === 'tersedia' && $new_values['status'] === 'dipinjam') {
                $change_type = 'borrowing';
                $is_borrowing = true;
            } elseif ($old_values['status'] === 'dipinjam' && $new_values['status'] === 'tersedia') {
                $change_type = 'returning';
                $is_returning = true;
            }
        }
        
        if ($change_type === 'borrowing') {
            $summary .= '<div class="alert alert-success p-2 mb-2"><strong><i class="fas fa-hand-holding"></i> 📖 Peminjaman Data Warkah</strong></div>';
        } elseif ($change_type === 'returning') {
            $summary .= '<div class="alert alert-info p-2 mb-2"><strong><i class="fas fa-undo"></i> ↩️ Pengembalian Data Warkah</strong></div>';
        } else {
            $summary .= '<div class="alert alert-warning p-2 mb-2"><strong><i class="fas fa-edit"></i> ✏️ Perubahan Metadata</strong></div>';
        }
        
        $has_changes = false;
        foreach ($new_values as $field => $new_value) {
            $old_value = $old_values[$field] ?? '';
            
            // Normalize values for comparison
            $old_normalized = trim(strval($old_value));
            $new_normalized = trim(strval($new_value));
            
            if ($old_normalized !== $new_normalized) {
                $has_changes = true;
                $label = $labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
                
                // Special handling for status changes
                if ($field === 'status') {
                    $status_labels = [
                        'tersedia' => '<span class="badge bg-success">Tersedia</span>',
                        'dipinjam' => '<span class="badge bg-warning">Dipinjam</span>',
                        'hilang' => '<span class="badge bg-danger">Hilang</span>',
                        'rusak' => '<span class="badge bg-secondary">Rusak</span>'
                    ];
                    
                    $old_status = $status_labels[$old_value] ?? htmlspecialchars($old_value);
                    $new_status = $status_labels[$new_value] ?? htmlspecialchars($new_value);
                    
                    if ($old_value === 'tersedia' && $new_value === 'dipinjam') {
                        $changes[] = "<strong>$label:</strong> <span class='text-success'>📖 Data dipinjam</span> $old_status → $new_status";
                    } elseif ($old_value === 'dipinjam' && $new_value === 'tersedia') {
                        $changes[] = "<strong>$label:</strong> <span class='text-info'>↩️ Data dikembalikan</span> $old_status → $new_status";
                    } else {
                        $changes[] = "<strong>$label:</strong> $old_status → $new_status";
                    }
                }
                // Special handling for peminjam field
                elseif ($field === 'peminjam') {
                    if (empty($old_value) && !empty($new_value)) {
                        $changes[] = "<strong>$label:</strong> <span class='text-success'>👤 Dipinjam oleh: " . htmlspecialchars($new_value) . "</span>";
                    } elseif (!empty($old_value) && empty($new_value)) {
                        $changes[] = "<strong>$label:</strong> <span class='text-info'>📚 Dikembalikan dari: " . htmlspecialchars($old_value) . "</span>";
                    } elseif ($old_value !== $new_value) {
                        $changes[] = "<strong>$label:</strong> " . htmlspecialchars($old_value) . " → " . htmlspecialchars($new_value);
                    }
                }
                // Special handling for dates
                elseif (in_array($field, ['tanggal_pinjam', 'tanggal_kembali', 'tanggal_warkah'])) {
                    $old_date = !empty($old_value) ? date('d/m/Y', strtotime($old_value)) : '<em>Kosong</em>';
                    $new_date = !empty($new_value) ? date('d/m/Y', strtotime($new_value)) : '<em>Kosong</em>';
                    
                    if ($field === 'tanggal_pinjam') {
                        if (empty($old_value) && !empty($new_value)) {
                            $changes[] = "<strong>$label:</strong> <span class='text-success'>📅 Tanggal peminjaman: $new_date</span>";
                        } elseif (!empty($old_value) && empty($new_value)) {
                            $changes[] = "<strong>$label:</strong> <span class='text-info'>📅 Tanggal peminjaman dihapus</span>";
                        } else {
                            $changes[] = "<strong>$label:</strong> $old_date → $new_date";
                        }
                    } elseif ($field === 'tanggal_kembali') {
                        if (empty($old_value) && !empty($new_value)) {
                            $changes[] = "<strong>$label:</strong> <span class='text-info'>↩️ Tanggal pengembalian: $new_date</span>";
                        } elseif (!empty($old_value) && empty($new_value)) {
                            $changes[] = "<strong>$label:</strong> <span class='text-warning'>⏳ Tanggal pengembalian dihapus</span>";
                        } else {
                            $changes[] = "<strong>$label:</strong> $old_date → $new_date";
                        }
                    } else {
                        $changes[] = "<strong>$label:</strong> $old_date → $new_date";
                    }
                }
                // Regular field changes
                else {
                    $old_display = !empty($old_value) ? htmlspecialchars($old_value) : '<em>Kosong</em>';
                    $new_display = !empty($new_value) ? htmlspecialchars($new_value) : '<em>Kosong</em>';
                    $changes[] = "<strong>$label:</strong> $old_display → $new_display";
                }
            }
        }
        
        if (!$has_changes) {
            $summary .= '<div class="text-muted"><em>Tidak ada perubahan pada data</em></div>';
            return $summary;
        }
    }
    
    if (!empty($changes)) {
        $summary .= '<ul class="list-unstyled mb-0">';
        foreach ($changes as $change) {
            $summary .= "<li class=\"mb-1\">$change</li>";
        }
        $summary .= '</ul>';
    }
    
    return $summary;
}

?>

