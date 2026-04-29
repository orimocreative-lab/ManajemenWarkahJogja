<?php
require_once '../../includes/config.php';
require_once '../../Includes/functions.php';
require_once '../../includes/data_yogyakarta.php';

if (!isLoggedIn()) {
    redirect('../../login.php');
}

$error = '';
$user_id = $_SESSION['user_id'] ?? ($_SESSION['id'] ?? 1); 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nomor           = $conn->real_escape_string($_POST['nomor']);
    $nomor_hak       = $conn->real_escape_string($_POST['nomor_hak']);
    $kelurahan       = $conn->real_escape_string($_POST['kelurahan']);
    $kecamatan       = $conn->real_escape_string($_POST['kecamatan']);
    $lokasi_warkah   = $conn->real_escape_string($_POST['lokasi_warkah']);
    $tanggal_pinjam  = $_POST['tanggal_pinjam'];
    $tanggal_kembali = !empty($_POST['tanggal_kembali']) ? $_POST['tanggal_kembali'] : NULL;
    $peminjam        = $conn->real_escape_string($_POST['peminjam']);
    $pemegang_hak    = $conn->real_escape_string($_POST['pemegang_hak']);
    $status          = $_POST['status'];
    $keterangan      = $conn->real_escape_string($_POST['keterangan']);
    $status_terakhir = $conn->real_escape_string($_POST['status_terakhir']);

    $query = "INSERT INTO bon_warkah (nomor_bon, nomor_hak, kelurahan, kecamatan, lokasi_warkah, tanggal_bon, 
              tanggal_pinjam, tanggal_kembali, peminjam, pemegang_hak, status, keterangan, status_terakhir, created_by) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);
    $tanggal_bon = date('Y-m-d');
    $stmt->bind_param("sssssssssssssi", 
        $nomor, $nomor_hak, $kelurahan, $kecamatan, $lokasi_warkah, $tanggal_bon,
        $tanggal_pinjam, $tanggal_kembali, $peminjam, $pemegang_hak, $status, $keterangan, $status_terakhir, $user_id);

    try {
        if ($stmt->execute()) {
            $new_id = $conn->insert_id;
            $stmt->close();
            
            // Prepare data untuk audit trail
            $new_data = [
                'nomor' => $nomor,
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
            
            // Catat ke audit trail
            log_bon_added($conn, $_SESSION['user_id'], $_SESSION['username'], $new_id, $nomor, $new_data);

            // LOGIKA UNGGAH BANYAK BERKAS
            if (!empty($_FILES['berkas_files']['name'][0])) {
                $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/Arsip_Bon_Warkah/uploads/berkas/';
                if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

                foreach ($_FILES['berkas_files']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['berkas_files']['error'][$key] === UPLOAD_ERR_OK) {
                        $nama_berkas = !empty($_POST['berkas_deskripsi'][$key]) ? 
                                       $conn->real_escape_string($_POST['berkas_deskripsi'][$key]) : 
                                       $conn->real_escape_string($_FILES['berkas_files']['name'][$key]);
                        
                        $filename = time() . '_' . rand(100, 999) . '.pdf';
                        if (move_uploaded_file($tmp_name, $target_dir . $filename)) {
                            $rel_path = '/Arsip_Bon_Warkah/uploads/berkas/' . $filename;
                            $q_f = "INSERT INTO berkas (bon_warkah_id, nama_berkas, file_path, uploaded_by, tipe_berkas, file_size) VALUES (?, ?, ?, ?, ?, ?)";
                            $st_f = $conn->prepare($q_f);
                            $file_size = filesize($target_dir . $filename);
                            $tipe_berkas = 'DOKUMEN';
                            $user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? 1;
                            $st_f->bind_param("issssi", $new_id, $nama_berkas, $rel_path, $user_id, $tipe_berkas, $file_size);
                            
                            if ($st_f->execute()) {
                                $berkas_id = $conn->insert_id;
                                $st_f->close();
                                
                                // Log berkas upload
                                log_berkas_action($conn, $_SESSION['user_id'], $_SESSION['username'], 'TAMBAH_BERKAS', $berkas_id, $nama_berkas, $new_id, 'bon_warkah', 'Unggah berkas pada saat pembuatan Data Warkah');
                            } else {
                                $st_f->close();
                            }
                        }
                    }
                }
            }
            
            $_SESSION['success'] = "Data Berhasil Disimpan!";
            header("Location: index.php");
            exit;
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            $error = "Gagal! Nomor Data Warkah [$nomor] sudah terdaftar. Gunakan nomor lain.";
        } else {
            $error = "Error: " . $e->getMessage();
        }
    }
}

include '../../includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4 mb-4">Tambah Data Warkah</h1>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
    
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data" id="form-tambah-warkah">
                <div class="row mb-3">
                    <div class="col-md-4"><label>Nomor Warkah</label><input type="text" name="nomor" class="form-control" required placeholder="contoh : W 123 / 2025"></div>
                    <div class="col-md-4"><label>Nomor Hak</label><input type="text" name="nomor_hak" class="form-control" required placeholder="contoh : HM 123 / 2025"></div>
                    <div class="col-md-4"><label>Nomor DI 208</label><input type="text" name="status_terakhir" class="form-control" placeholder="contoh : 123/2025"></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Kecamatan</label>
                        <select name="kecamatan" id="kecamatan-select" class="form-select" required>
                            <option value="">-- Pilih Kecamatan --</option>
                            <?php foreach (getKecamatan() as $kec): ?>
                                <option value="<?= htmlspecialchars($kec) ?>"><?= htmlspecialchars($kec) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Kelurahan</label>
                        <select name="kelurahan" id="kelurahan-select" class="form-select" required>
                            <option value="">-- Pilih Kelurahan --</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6"><label>Lokasi Warkah</label><input type="text" name="lokasi_warkah" class="form-control" required></div>
                    <div class="col-md-6"><label>Pemegang Hak</label><input type="text" name="pemegang_hak" class="form-control"></div>
                </div>
                <div style="border: 1px solid #ffffff; padding: 10px; margin: 10px 0; background-color: #ffffff;">
                    <h4 style="margin-top: 0;">Peminjaman Warkah</h4>
                    <p>Status default: <span class="badge bg-success">Tersedia</span></p>
                    <div class="row mb-3">
                        <div class="col-md-6"><label>Peminjam</label><input type="text" name="peminjam" class="form-control"></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><label>Tanggal Pinjam</label><input type="date" name="tanggal_pinjam" class="form-control"></div>
                        <div class="col-md-4"><label>Tanggal Kembali</label><input type="date" name="tanggal_kembali" class="form-control"></div>
                        <div class="col-md-4">
                            <label>Status</label>
                            <select name="status" class="form-select">
                                <option value="tersedia" selected>Tersedia</option>
                                <option value="dipinjam">Dipinjam</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="mb-3"><label>Keterangan</label><textarea name="keterangan" class="form-control" rows="2" placeholder="contoh : Balik Nama Waris"></textarea></div>
                
                <hr>
                <h5 class="text-primary"><i class="fas fa-file-pdf"></i> Unggah Berkas PDF</h5>
                
                <div id="berkas-container">
                    <div class="berkas-item card p-3 mb-2 bg-light shadow-sm">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <label class="small text-muted">File PDF</label>
                                <input type="file" name="berkas_files[]" class="form-control pdf-upload" accept=".pdf">
                            </div>
                            <div class="col-md-5">
                                <label class="small text-muted">Nama/Deskripsi Berkas</label>
                                <input type="text" name="berkas_deskripsi[]" class="form-control" placeholder="Contoh: Surat Permohonan">
                            </div>
                            <div class="col-md-1 text-end mt-4">
                                <button type="button" class="btn btn-danger btn-sm btn-remove" style="display:none;"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="button" id="add-berkas-btn" class="btn btn-info btn-sm mt-2">
                    <i class="fas fa-plus"></i> Tambah Baris Berkas
                </button>

                <div class="mt-5 border-top pt-3">
                    <button type="submit" class="btn btn-primary px-5">Simpan Semua Data</button>
                    <a href="index.php" class="btn btn-secondary px-4">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Data kelurahan dan kecamatan Yogyakarta
const yogyakartaData = <?php echo json_encode($yogyakarta_data); ?>;

document.getElementById('kecamatan-select').addEventListener('change', function() {
    const selectedKecamatan = this.value;
    const kelurahanSelect = document.getElementById('kelurahan-select');
    
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

document.getElementById('add-berkas-btn').onclick = function() {
    const container = document.getElementById('berkas-container');
    const items = container.getElementsByClassName('berkas-item');
    const newItem = items[0].cloneNode(true);
    newItem.querySelectorAll('input').forEach(input => input.value = '');
    const removeBtn = newItem.querySelector('.btn-remove');
    removeBtn.style.display = 'inline-block';
    removeBtn.onclick = function() { newItem.remove(); };
    container.appendChild(newItem);
};
</script>

<?php include '../../includes/footer.php'; ?>