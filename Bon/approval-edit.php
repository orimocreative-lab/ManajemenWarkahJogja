<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isLoggedIn()) {
    redirect('../../login.php');
}

if (!isAdmin()) {
    $_SESSION['error'] = 'Hanya admin yang dapat mengakses halaman ini.';
    redirect('index.php');
}

// Handle approve/reject via AJAX or form submission
if (isset($_GET['request_id'])) {
    $request_id = intval($_GET['request_id']);
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    if ($action === 'approve') {
        $result = approveEditRequest($conn, $request_id, $_SESSION['user_id']);
        if ($result['success']) {
            if (defined('ALLOW_USER_EDIT_AFTER_APPROVAL') && ALLOW_USER_EDIT_AFTER_APPROVAL) {
                // perubahan tidak langsung diterapkan; pengguna akan melihatnya saat membuka form
                $_SESSION['success'] = 'Permintaan edit berhasil disetujui. Pengguna dapat mengubah dan menyimpan sendiri.';
            } else {
                // fitur non-aktif -> terapkan perubahan seperti sebelumnya
                $stmt = $conn->prepare("SELECT er.bon_id, er.proposed_changes FROM edit_requests er WHERE er.id = ?");
                $stmt->bind_param('i', $request_id);
                $stmt->execute();
                $req_data = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                
                if ($req_data && !empty($req_data['proposed_changes'])) {
                    $changes = json_decode($req_data['proposed_changes'], true);
                    $bon_id = $req_data['bon_id'];
                    
                    // Ambil data lama sebelum update
                    $stmt_old = $conn->prepare("SELECT * FROM bon_warkah WHERE id = ?");
                    $stmt_old->bind_param('i', $bon_id);
                    $stmt_old->execute();
                    $old_data = $stmt_old->get_result()->fetch_assoc();
                    $stmt_old->close();
                    
                    // Build update query dynamically
                    $update_fields = [];
                    $types = '';
                    $values = [];
                    
                    foreach ($changes as $field => $value) {
                        $update_fields[] = "$field = ?";
                        $types .= 's';
                        $values[] = $value;
                    }
                    
                    $types .= 'i';
                    $values[] = $bon_id;
                    
                    $update_query = "UPDATE bon_warkah SET " . implode(', ', $update_fields) . " WHERE id = ?";
                    $stmt_upd = $conn->prepare($update_query);
                    
                    if ($stmt_upd) {
                        $stmt_upd->bind_param($types, ...$values);
                        if ($stmt_upd->execute()) {
                            // Log the update from approved request
                            $user_info = $conn->query("SELECT username FROM users WHERE id = " . $_SESSION['user_id'])->fetch_assoc();
                            $bon_info = $conn->query("SELECT nomor_bon FROM bon_warkah WHERE id = $bon_id")->fetch_assoc();
                            log_bon_updated($conn, $_SESSION['user_id'], $user_info['username'], $bon_id, $bon_info['nomor_bon'], $old_data, $changes);
                            
                            $_SESSION['success'] = 'Permintaan edit berhasil disetujui dan data telah diperbarui.';
                        }
                        $stmt_upd->close();
                    }
                }
            }
        } else {
            $_SESSION['error'] = $result['error'];
        }
    } elseif ($action === 'reject') {
        $rejection_reason = isset($_POST['rejection_reason']) ? $_POST['rejection_reason'] : '';
        $result = rejectEditRequest($conn, $request_id, $_SESSION['user_id'], $rejection_reason);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['error'];
        }
    }
}

header("Location: approval-list.php");
exit;
?>

