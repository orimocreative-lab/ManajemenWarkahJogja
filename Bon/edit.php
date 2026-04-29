<?php
require_once '../../includes/config.php';
require_once '../../Includes/functions.php';
require_once '../../includes/data_yogyakarta.php';

if (!isLoggedIn()) {
    redirect('../../login.php');
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) redirect('index.php');

// Ambil data lama SEBELUM diupdate untuk keperluan Audit Trail
$stmt_old = $conn->prepare("SELECT * FROM bon_warkah WHERE id = ?");
$stmt_old->bind_param("i", $id);
$stmt_old->execute();
$old_data = $stmt_old->get_result()->fetch_assoc();
$stmt_old->close();

// 1. LOGIKA HAPUS BERKAS
if (isset($_GET['delete_file'])) {
    $file_id = intval($_GET['delete_file']);
    $st_path = $conn->prepare("SELECT * FROM berkas WHERE id = ? AND bon_warkah_id = ?");
    $st_path->bind_param("ii", $file_id, $id);
    $st_path->execute();
    $res_file = $st_path->get_result()->fetch_assoc();
    $st_path->close();

    if ($res_file) {
        $full_path = $_SERVER['DOCUMENT_ROOT'] . $res_file['file_path'];
        if (file_exists($full_path)) unlink($full_path);
        
        if ($conn->query("DELETE FROM berkas WHERE id = $file_id")) {
            // Catat log hapus berkas
            log_berkas_action($conn, $_SESSION['user_id'], $_SESSION['username'], 'HAPUS_BERKAS', $file_id, $res_file['nama_berkas'], $id, 'bon_warkah', 'Dihapus dari Data Warkah');
            $_SESSION['success'] = "Berkas berhasil dihapus.";
        }
    }
    header("Location: edit.php?id=$id");
    exit;
}

// 2. LOGIKA UPDATE DATA (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Cek permission
    // regular users are allowed to submit edit requests for any record
    // (previously restricted to creator-only)

    
    if (isUser()) {
        // cek apakah ada request yang telah disetujui oleh admin untuk user ini
        $approved_request = (defined('ALLOW_USER_EDIT_AFTER_APPROVAL') && ALLOW_USER_EDIT_AFTER_APPROVAL)
                             ? getApprovedEditRequest($conn, $id, $_SESSION['user_id'])
                             : null;
        if ($approved_request) {
            // pengguna memperoleh hak edit sementara, proses update langsung
            $nomor_bon       = $conn->real_escape_string($_POST['nomor_bon']);
            $nomor_hak       = $conn->real_escape_string($_POST['nomor_hak']);
            $kelurahan       = $conn->real_escape_string($_POST['kelurahan']);
            $kecamatan       = $conn->real_escape_string($_POST['kecamatan']);
            $lokasi_warkah   = $conn->real_escape_string($_POST['lokasi_warkah']);
            $tanggal_pinjam  = !empty($_POST['tanggal_pinjam']) ? $_POST['tanggal_pinjam'] : NULL;
            $tanggal_kembali = !empty($_POST['tanggal_kembali']) ? $_POST['tanggal_kembali'] : NULL;
            $peminjam        = $conn->real_escape_string($_POST['peminjam']);
            $pemegang_hak    = $conn->real_escape_string($_POST['pemegang_hak']);
            $status          = $_POST['status'];
            $keterangan      = $conn->real_escape_string($_POST['keterangan']);
            $status_terakhir = $conn->real_escape_string($_POST['status_terakhir']);

            $query_upd = "UPDATE bon_warkah SET nomor_bon=?, nomor_hak=?, kelurahan=?, kecamatan=?, lokasi_warkah=?, 
                      tanggal_pinjam=?, tanggal_kembali=?, peminjam=?, pemegang_hak=?, status=?, keterangan=?, status_terakhir=? 
                      WHERE id=?";
            $stmt_upd = $conn->prepare($query_upd);
            $stmt_upd->bind_param("ssssssssssssi", $nomor_bon, $nomor_hak, $kelurahan, $kecamatan, $lokasi_warkah, 
                                  $tanggal_pinjam, $tanggal_kembali, $peminjam, $pemegang_hak, $status, $keterangan, $status_terakhir, $id);

            if ($stmt_upd->execute()) {
                $stmt_upd->close();
                // Prepare new data for comparison
                $new_data = [
                    'nomor_bon' => $nomor_bon, 'nomor_hak' => $nomor_hak, 'kelurahan' => $kelurahan,
                    'kecamatan' => $kecamatan, 'lokasi_warkah' => $lokasi_warkah,
                    'tanggal_pinjam' => $tanggal_pinjam, 'tanggal_kembali' => $tanggal_kembali,
                    'peminjam' => $peminjam, 'pemegang_hak' => $pemegang_hak, 'status' => $status, 'keterangan' => $keterangan,
                    'status_terakhir' => $status_terakhir
                ];
                // Log audit trail with old and new values
                log_bon_updated($conn, $_SESSION['user_id'], $_SESSION['username'], $id, $old_data['nomor_bon'], $old_data, $new_data);

                // mark the approved request as completed so user must request again in the future
                $stmt_done = $conn->prepare("UPDATE edit_requests SET status = 'completed', updated_at = NOW() WHERE id = ?");
                $stmt_done->bind_param('i', $approved_request['id']);
                $stmt_done->execute();
                $stmt_done->close();

                // Logika Upload Berkas Baru (identical to admin branch)
                if (!empty($_FILES['berkas_files']['name'][0])) {
                    $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/Arsip_Bon_Warkah/uploads/berkas/';
                    if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
                    
                    foreach ($_FILES['berkas_files']['tmp_name'] as $key => $tmp_name) {
                        if ($_FILES['berkas_files']['error'][$key] === UPLOAD_ERR_OK) {
                            $orig_name = $conn->real_escape_string($_POST['berkas_deskripsi'][$key] ?: $_FILES['berkas_files']['name'][$key]);
                            $filename = time() . '_' . rand(100, 999) . '.pdf';
                            if (move_uploaded_file($tmp_name, $target_dir . $filename)) {
                                $rel_path = '/Arsip_Bon_Warkah/uploads/berkas/' . $filename;
                                $q_ins = "INSERT INTO berkas (bon_warkah_id, nama_berkas, file_path, uploaded_by, tipe_berkas, file_size) VALUES (?, ?, ?, ?, ?, ?)";
                                $st_ins = $conn->prepare($q_ins);
                                $file_size = filesize($target_dir . $filename);
                                $tipe_berkas = 'DOKUMEN';
                                $user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? 1;
                                $st_ins->bind_param("issssi", $id, $orig_name, $rel_path, $user_id, $tipe_berkas, $file_size);
                                if ($st_ins->execute()) {
                                    $berkas_id = $conn->insert_id;
                                    $st_ins->close();
                                    // Log berkas upload
                                    log_berkas_action($conn, $_SESSION['user_id'], $_SESSION['username'], 'TAMBAH_BERKAS', $berkas_id, $orig_name, $id, 'bon_warkah', 'Ditambahkan saat edit Data Warkah');
                                } else {
                                    $st_ins->close();
                                }
                            }
                        }
                    }
                }
                $_SESSION['success'] = "Data berhasil diperbarui.";
                header("Location: edit.php?id=$id");
                exit;
            } else {
                $stmt_upd->close();
            }
        } else {
            // User harus submit request edit
            $proposed_changes = [
                'nomor_bon' => $_POST['nomor_bon'],
                'nomor_hak' => $_POST['nomor_hak'],
                'kelurahan' => $_POST['kelurahan'],
                'kecamatan' => $_POST['kecamatan'],
                'lokasi_warkah' => $_POST['lokasi_warkah'],
                'tanggal_pinjam' => !empty($_POST['tanggal_pinjam']) ? $_POST['tanggal_pinjam'] : NULL,
                'tanggal_kembali' => !empty($_POST['tanggal_kembali']) ? $_POST['tanggal_kembali'] : NULL,
                'peminjam' => $_POST['peminjam'],
                'pemegang_hak' => $_POST['pemegang_hak'],
                'status' => $_POST['status'],
                'keterangan' => $_POST['keterangan'],
                'status_terakhir' => $_POST['status_terakhir']
            ];
            
            $result = submitEditRequest($conn, $id, $_SESSION['user_id'], $proposed_changes);
            
            if ($result['success']) {
                $_SESSION['success'] = $result['message'];
            } else {
                $_SESSION['error'] = $result['error'];
            }
            
            header("Location: edit.php?id=$id");
            exit;
        }
    } else {
        // Admin bisa langsung edit
        $nomor_bon       = $conn->real_escape_string($_POST['nomor_bon']);
        $nomor_hak       = $conn->real_escape_string($_POST['nomor_hak']);
        $kelurahan       = $conn->real_escape_string($_POST['kelurahan']);
        $kecamatan       = $conn->real_escape_string($_POST['kecamatan']);
        $lokasi_warkah   = $conn->real_escape_string($_POST['lokasi_warkah']);
        $tanggal_pinjam  = !empty($_POST['tanggal_pinjam']) ? $_POST['tanggal_pinjam'] : NULL;
        $tanggal_kembali = !empty($_POST['tanggal_kembali']) ? $_POST['tanggal_kembali'] : NULL;
        $peminjam        = $conn->real_escape_string($_POST['peminjam']);
        $pemegang_hak    = $conn->real_escape_string($_POST['pemegang_hak']);
        $status          = $_POST['status'];
        $keterangan      = $conn->real_escape_string($_POST['keterangan']);
        $status_terakhir = $conn->real_escape_string($_POST['status_terakhir']);

        $query_upd = "UPDATE bon_warkah SET nomor_bon=?, nomor_hak=?, kelurahan=?, kecamatan=?, lokasi_warkah=?, 
                      tanggal_pinjam=?, tanggal_kembali=?, peminjam=?, pemegang_hak=?, status=?, keterangan=?, status_terakhir=? 
                      WHERE id=?";
        $stmt_upd = $conn->prepare($query_upd);
        $stmt_upd->bind_param("ssssssssssssi", $nomor_bon, $nomor_hak, $kelurahan, $kecamatan, $lokasi_warkah, 
                              $tanggal_pinjam, $tanggal_kembali, $peminjam, $pemegang_hak, $status, $keterangan, $status_terakhir, $id);

            if ($stmt_upd->execute()) {
                $stmt_upd->close();
                // Prepare new data for comparison
                $new_data = [
                    'nomor_bon' => $nomor_bon,
                    'nomor_hak' => $nomor_hak,
                    'kelurahan' => $kelurahan,
                    'kecamatan' => $kecamatan,
                    'lokasi_warkah' => $lokasi_warkah,
                    'tanggal_pinjam' => $tanggal_pinjam,
                    'tanggal_kembali' => $tanggal_kembali,
                    'peminjam' => $peminjam,
                    'pemegang_hak' => $pemegang_hak,
                    'status' => $status,
                    'keterangan' => $keterangan,
                    'status_terakhir' => $status_terakhir
                ];

                // Log audit trail dengan old dan new values
                log_bon_updated($conn, $_SESSION['user_id'], $_SESSION['username'], $id, $old_data['nomor_bon'], $old_data, $new_data);

                // Logika Upload Berkas Baru
            if (!empty($_FILES['berkas_files']['name'][0])) {
                $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/Arsip_Bon_Warkah/uploads/berkas/';
                if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
                
                foreach ($_FILES['berkas_files']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['berkas_files']['error'][$key] === UPLOAD_ERR_OK) {
                        $orig_name = $conn->real_escape_string($_POST['berkas_deskripsi'][$key] ?: $_FILES['berkas_files']['name'][$key]);
                        $filename = time() . '_' . rand(100, 999) . '.pdf';
                        if (move_uploaded_file($tmp_name, $target_dir . $filename)) {
                            $rel_path = '/Arsip_Bon_Warkah/uploads/berkas/' . $filename;
                            $q_ins = "INSERT INTO berkas (bon_warkah_id, nama_berkas, file_path, uploaded_by, tipe_berkas, file_size) VALUES (?, ?, ?, ?, ?, ?)";
                            $st_ins = $conn->prepare($q_ins);
                            $file_size = filesize($target_dir . $filename);
                            $tipe_berkas = 'DOKUMEN';
                            $user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? 1;
                            $st_ins->bind_param("issssi", $id, $orig_name, $rel_path, $user_id, $tipe_berkas, $file_size);
                            if ($st_ins->execute()) {
                                $berkas_id = $conn->insert_id;
                                $st_ins->close();
                                // Log berkas upload
                                log_berkas_action($conn, $_SESSION['user_id'], $_SESSION['username'], 'TAMBAH_BERKAS', $berkas_id, $orig_name, $id, 'bon_warkah', 'Ditambahkan saat edit Data Warkah');
                            } else {
                                $st_ins->close();
                            }
                        }
                    }
                }
            }
            $_SESSION['success'] = "Data berhasil diperbarui.";
            header("Location: edit.php?id=$id");
            exit;
        } else {
            $stmt_upd->close();
        }
    }
}

// Ambil data untuk ditampilkan di form
$stmt = $conn->prepare("SELECT * FROM bon_warkah WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$bon = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Ambil request edit yang pending jika ada
$edit_request = getEditRequest($conn, $id);

// Jika ada request yang sudah disetujui untuk user ini, biarkan form terisi dengan nilai usulan
$approved_request = null;
if (isUser() && defined('ALLOW_USER_EDIT_AFTER_APPROVAL') && ALLOW_USER_EDIT_AFTER_APPROVAL) {
    $approved_request = getApprovedEditRequest($conn, $id, $_SESSION['user_id']);
    if ($approved_request && !empty($approved_request['proposed_changes'])) {
        $changes = json_decode($approved_request['proposed_changes'], true);
        foreach ($changes as $field => $val) {
            // override nilai form jika field tersedia
            $bon[$field] = $val;
        }
    }
}

$berkas_stmt = $conn->prepare("SELECT * FROM berkas WHERE bon_warkah_id = ? ORDER BY created_at DESC");
$berkas_stmt->bind_param("i", $id);
$berkas_stmt->execute();
$existing_berkas = $berkas_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$berkas_stmt->close();

include '../../includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4 mb-4">Edit Data Warkah</h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Admin Edit Request Status -->
    <?php if (isAdmin() && $edit_request): ?>
    <div class="alert alert-warning border border-warning">
        <h5 class="alert-heading"><i class="fas fa-hourglass-half"></i> Permintaan Edit Menunggu Persetujuan</h5>
        <p class="mb-2"><strong>Dari:</strong> <?= htmlspecialchars($edit_request['requested_by_name'] ?? $edit_request['nama_lengkap'] ?? '-') ?> (<?= htmlspecialchars($edit_request['requested_by_username'] ?? $edit_request['username'] ?? '-') ?>)</p>
        <p class="mb-2"><strong>Tanggal Request:</strong> <?= date('d-m-Y H:i', strtotime($edit_request['requested_at'])) ?></p>
        
        <?php if (!empty($edit_request['proposed_changes'])): 
            $changes = json_decode($edit_request['proposed_changes'], true);
        ?>
        <div class="mb-3">
            <strong>Perubahan yang Diusulkan:</strong>
            <table class="table table-sm table-borderless mt-2">
                <tr>
                    <th class="w-25">Field</th>
                    <th class="w-35">Nilai Saat Ini</th>
                    <th class="w-35">Nilai Usulan</th>
                </tr>
                <?php foreach ($changes as $field => $value): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($field) ?></strong></td>
                    <td><?= htmlspecialchars($bon[$field] ?? '-') ?></td>
                    <td><span class="badge bg-info"><?= htmlspecialchars($value) ?></span></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php endif; ?>
        
        <div class="mt-3">
            <a href="approval-list.php?request_id=<?= $edit_request['id'] ?>&action=approve" 
               class="btn btn-success btn-sm" onclick="return confirm('Setujui permintaan edit ini?')">
                <i class="fas fa-check"></i> Setujui Edit
            </a>
            <a href="approval-list.php?request_id=<?= $edit_request['id'] ?>&action=reject" 
               class="btn btn-danger btn-sm" onclick="return confirm('Tolak permintaan edit ini?')">
                <i class="fas fa-times"></i> Tolak Edit
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- User Edit Request Status -->
    <?php if (isUser()): ?>
        <?php if (!empty($edit_request)): ?>
        <div class="alert alert-info border border-info">
            <h5 class="alert-heading"><i class="fas fa-info-circle"></i> Status Permintaan Edit</h5>
            <p class="mb-0"><strong>Status:</strong> <span class="badge bg-warning">Menunggu Persetujuan Admin</span></p>
            <p class="mb-0"><small class="text-muted">Permintaan Anda dikirim pada <?= date('d-m-Y H:i', strtotime($edit_request['requested_at'])) ?></small></p>
        </div>
        <?php elseif (!empty($approved_request)): ?>
        <div class="alert alert-success border border-success">
            <h5 class="alert-heading"><i class="fas fa-check-circle"></i> Edit Disetujui</h5>
            <p class="mb-0">Permintaan edit Anda telah disetujui. Anda dapat melakukan perubahan terakhir dan menyimpan data. Setelah disimpan, permintaan baru diperlukan untuk perubahan berikutnya.</p>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-7">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white"><i class="fas fa-edit"></i> Form Edit Data</div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row mb-3">
                            <div class="col-md-4"><label>Nomor Warkah</label><input type="text" name="nomor_bon" class="form-control" value="<?= htmlspecialchars($bon['nomor_bon']) ?>" required></div>
                            <div class="col-md-4"><label>Nomor Hak</label><input type="text" name="nomor_hak" class="form-control" value="<?= htmlspecialchars($bon['nomor_hak']) ?>" required placeholder="contoh : HM 123 / 2025"></div>
                            <div class="col-md-4"><label>Nomor DI 208</label><input type="text" name="status_terakhir" class="form-control" value="<?= htmlspecialchars($bon['status_terakhir']) ?>" placeholder="contoh : 123/2025"></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Kecamatan</label>
                                <select name="kecamatan" id="kecamatan-select-edit" class="form-select" required>
                                    <option value="">-- Pilih Kecamatan --</option>
                                    <?php foreach (getKecamatan() as $kec): ?>
                                        <option value="<?= htmlspecialchars($kec) ?>" <?= $bon['kecamatan']==$kec?'selected':'' ?>><?= htmlspecialchars($kec) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Kelurahan</label>
                                <select name="kelurahan" id="kelurahan-select-edit" class="form-select" required>
                                    <option value="">-- Pilih Kelurahan --</option>
                                    <?php
                                        $kecSelected = $bon['kecamatan'];
                                        if ($kecSelected && isset($yogyakarta_data[$kecSelected])) {
                                            foreach ($yogyakarta_data[$kecSelected] as $kel) {
                                                $selected = ($bon['kelurahan'] == $kel) ? 'selected' : '';
                                                echo "<option value=\"" . htmlspecialchars($kel) . "\" $selected>" . htmlspecialchars($kel) . "</option>";
                                            }
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6"><label>Lokasi Warkah</label><input type="text" name="lokasi_warkah" class="form-control" value="<?= htmlspecialchars($bon['lokasi_warkah']) ?>" required></div>
                            <div class="col-md-6"><label>Pemegang Hak</label><input type="text" name="pemegang_hak" class="form-control" value="<?= htmlspecialchars($bon['pemegang_hak'] ?? '') ?>"></div>
                        </div>
                        <div style="border: 1px solid #ffffff; padding: 10px; margin: 10px 0; background-color: #ffffff;">
                            <h4 style="margin-top: 0;">Peminjaman Warkah</h4>
                            <p>Status saat ini: <span class="badge bg-<?= $bon['status'] == 'dipinjam' ? 'warning' : 'success' ?>"><?= $bon['status'] == 'dipinjam' ? 'Dipinjam' : 'Tersedia' ?></span></p>
                            <div class="row mb-3">
                                <div class="col-md-6"><label>Peminjam</label><input type="text" name="peminjam" class="form-control" value="<?= htmlspecialchars($bon['peminjam']) ?>"></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4"><label>Tanggal Pinjam</label><input type="date" name="tanggal_pinjam" class="form-control" value="<?= $bon['tanggal_pinjam'] ?>"></div>
                                <div class="col-md-4"><label>Tanggal Kembali</label><input type="date" name="tanggal_kembali" class="form-control" value="<?= $bon['tanggal_kembali'] ?>"></div>
                                <div class="col-md-4">
                                    <label>Status</label>
                                    <select name="status" class="form-select">
                                        <option value="tersedia" <?= $bon['status']!='dipinjam'?'selected':'' ?>>Tersedia</option>
                                        <option value="dipinjam" <?= $bon['status']=='dipinjam'?'selected':'' ?>>Dipinjam</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3"><label>Keterangan</label><textarea name="keterangan" class="form-control" rows="2" placeholder="contoh : Balik Nama Waris"><?= htmlspecialchars($bon['keterangan']) ?></textarea></div>

                        <?php if (isAdmin()): ?>
                        <hr>
                        <h5 class="text-primary">Tambah Berkas PDF Baru</h5>
                        <div id="berkas-container-edit">
                            <div class="berkas-item-edit mb-2 p-2 border rounded bg-light">
                                <div class="row align-items-center">
                                    <div class="col-md-6"><input type="file" name="berkas_files[]" class="form-control pdf-upload" accept=".pdf"></div>
                                    <div class="col-md-5"><input type="text" name="berkas_deskripsi[]" class="form-control" placeholder="Deskripsi Berkas"></div>
                                    <div class="col-md-1"><button type="button" class="btn btn-danger btn-sm remove-edit-field" style="display:none;"><i class="fas fa-times"></i></button></div>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="add-btn-edit" class="btn btn-sm btn-info mb-3">+ Tambah Baris</button>
                        <?php endif; ?>
                        
                        <div class="mt-4">
                            <?php if (isAdmin()): ?>
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            <?php else: ?>
                                <?php if (!empty($approved_request)): ?>
                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                    <small class="d-block mt-2 text-muted">Setelah menyimpan, Anda harus mengajukan permintaan edit baru untuk perubahan selanjutnya.</small>
                                <?php else: ?>
                                    <button type="submit" class="btn btn-warning" <?= !empty($edit_request) ? 'disabled' : '' ?>>
                                        <i class="fas fa-paper-plane"></i> Kirim Permintaan Edit
                                    </button>
                                    <?php if (!empty($edit_request)): ?>
                                        <small class="d-block mt-2 text-muted">Anda sudah memiliki permintaan edit yang menunggu persetujuan</small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                            <a href="index.php" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-info text-white">Preview Dokumen</div>
                <div class="card-body" style="max-height: 800px; overflow-y: auto;">
                    <?php if (!empty($existing_berkas)): ?>
                        <?php foreach ($existing_berkas as $file): ?>
                            <div class="mb-4 border p-2 rounded bg-light shadow-sm">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-truncate" style="max-width: 70%;"><strong><?= htmlspecialchars($file['nama_berkas']) ?></strong></small>
                                    <a href="edit.php?id=<?= $id ?>&delete_file=<?= $file['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus berkas ini?')"><i class="fas fa-trash"></i></a>
                                </div>
                                <embed src="<?= $file['file_path'] ?>" type="application/pdf" width="100%" height="350px" />
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center text-muted py-5">Belum ada berkas PDF.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Data kelurahan dan kecamatan Yogyakarta
const yogyakartaData = <?php echo json_encode($yogyakarta_data); ?>;

document.getElementById('kecamatan-select-edit').addEventListener('change', function() {
    const selectedKecamatan = this.value;
    const kelurahanSelect = document.getElementById('kelurahan-select-edit');
    
    kelurahanSelect.innerHTML = '<option value="">-- Pilih Kelurahan --</option>';
    
    if (selectedKecamatan && yogyakartaData[selectedKecamatan]) {
        yogyakartaData[selectedKecamatan].forEach(kel => {
            const option = document.createElement('option');
            option.value = kel;
            option.text = kel;
            kelurahanSelect.appendChild(option);
        });
    }
});

document.getElementById('add-btn-edit').onclick = function() {
    const container = document.getElementById('berkas-container-edit');
    const newItem = container.querySelector('.berkas-item-edit').cloneNode(true);
    newItem.querySelectorAll('input').forEach(i => i.value = '');
    const rm = newItem.querySelector('.remove-edit-field');
    rm.style.display = 'block';
    rm.onclick = function() { newItem.remove(); };
    container.appendChild(newItem);
};
</script>
<?php include '../../includes/footer.php'; ?>