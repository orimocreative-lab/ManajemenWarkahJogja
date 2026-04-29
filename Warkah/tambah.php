<?php
require_once __DIR__ . '/../../Includes/config.php';
require_once __DIR__ . '/../../includes/data_yogyakarta.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor_warkah = $conn->real_escape_string(trim($_POST['nomor_warkah']));
    $kecamatan = $conn->real_escape_string(trim($_POST['kecamatan']));
    $kelurahan = $conn->real_escape_string(trim($_POST['kelurahan']));
    $nomor_hak = $conn->real_escape_string(trim($_POST['nomor_hak']));
    $peminjam = !empty($_POST['peminjam']) ? $conn->real_escape_string(trim($_POST['peminjam'])) : null;
    $pemegang_hak = !empty($_POST['pemegang_hak']) ? $conn->real_escape_string(trim($_POST['pemegang_hak'])) : null;
    $status = in_array($_POST['status'] ?? '', ['Tersedia','Dipinjam']) ? $_POST['status'] : 'Tersedia';
    $tanggal_dipinjam = !empty($_POST['tanggal_dipinjam']) ? $conn->real_escape_string($_POST['tanggal_dipinjam']) : null;
    $tanggal_kembali = !empty($_POST['tanggal_kembali']) ? $conn->real_escape_string($_POST['tanggal_kembali']) : null;

    $upload_base = __DIR__ . '/../../Assets/uploads/warkah/';
    $target_dir = $upload_base . preg_replace('/[^a-zA-Z0-9-_\.]/', '_', $nomor_warkah) . '/';
    if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

    function handle_file($field, $target_dir) {
        if (empty($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return null;
        $f = $_FILES[$field];
        $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
        $name = preg_replace('/[^a-zA-Z0-9-_\.]/', '_', pathinfo($f['name'], PATHINFO_FILENAME));
        $newname = $name . '_' . time() . '.' . $ext;
        $dest = $target_dir . $newname;
        if (move_uploaded_file($f['tmp_name'], $dest)) {
            $rel = '/Arsip_Bon_Warkah/Assets/uploads/warkah/' . basename($target_dir) . '/' . $newname;
            return $rel;
        }
        return null;
    }

    $file_ktp = handle_file('file_ktp', $target_dir);
    $file_surat_permohonan = handle_file('file_surat_permohonan', $target_dir);
    $file_alas_hak = handle_file('file_alas_hak', $target_dir);
    $file_bukti_penguasaan = handle_file('file_bukti_penguasaan', $target_dir);
    $file_sk_pemberian_hak = handle_file('file_sk_pemberian_hak', $target_dir);
    $file_peta_bidang = handle_file('file_peta_bidang', $target_dir);
    $file_berita_acara = handle_file('file_berita_acara', $target_dir);
    $file_bukti_pajak = handle_file('file_bukti_pajak', $target_dir);
    $file_akta_ppat = handle_file('file_akta_ppat', $target_dir);

    $stmt = $conn->prepare("INSERT INTO warkah (nomor_warkah, kecamatan, kelurahan, nomor_hak, peminjam, pemegang_hak, status, tanggal_dipinjam, tanggal_kembali, file_ktp, file_surat_permohonan, file_alas_hak, file_bukti_penguasaan, file_sk_pemberian_hak, file_peta_bidang, file_berita_acara, file_bukti_pajak, file_akta_ppat) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssssssssssssssss', $nomor_warkah, $kecamatan, $kelurahan, $nomor_hak, $peminjam, $pemegang_hak, $status, $tanggal_dipinjam, $tanggal_kembali, $file_ktp, $file_surat_permohonan, $file_alas_hak, $file_bukti_penguasaan, $file_sk_pemberian_hak, $file_peta_bidang, $file_berita_acara, $file_bukti_pajak, $file_akta_ppat);
    if ($stmt->execute()) {
        $new_warkah_id = $conn->insert_id;
        
        $warkah_data = [
            'nomor_warkah' => $nomor_warkah,
            'kecamatan' => $kecamatan,
            'kelurahan' => $kelurahan,
            'nomor_hak' => $nomor_hak,
            'peminjam' => $peminjam,
            'pemegang_hak' => $pemegang_hak,
            'status' => $status,
            'tanggal_dipinjam' => $tanggal_dipinjam,
            'tanggal_kembali' => $tanggal_kembali
        ];
        
        // Log warkah creation
        if (isLoggedIn()) {
            log_audit(
                $conn,
                $_SESSION['user_id'],
                $_SESSION['username'],
                'TAMBAH_WARKAH',
                'Warkah baru dibuat - Nomor: ' . $nomor_warkah . ', Kelurahan: ' . $kelurahan,
                'warkah',
                $new_warkah_id,
                $nomor_warkah,
                null,
                $warkah_data
            );
        }
        
        header('Location: index.php');
        exit();
    } else {
        $error = $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tambah Warkah</title>
    <link rel="stylesheet" href="/Arsip_Bon_Warkah/Assets/Css/Style.css">
</head>
<body>
<?php include __DIR__ . '/../../Includes/header.php'; ?>
<div class="container">
    <h2>Tambah Warkah</h2>
    <?php if (!empty($error)): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <label>Nomor Warkah:<br><input type="text" name="nomor_warkah" required></label><br>
        <label>Kecamatan:<br>
            <select name="kecamatan" id="kecamatan-select" required>
                <option value="">-- Pilih Kecamatan --</option>
                <?php foreach (getKecamatan() as $kec): ?>
                    <option value="<?= htmlspecialchars($kec) ?>"><?= htmlspecialchars($kec) ?></option>
                <?php endforeach; ?>
            </select>
        </label><br>
        <label>Kelurahan:<br>
            <select name="kelurahan" id="kelurahan-select" required>
                <option value="">-- Pilih Kelurahan --</option>
            </select>
        </label><br>
        <label>Nomor Hak:<br><input type="text" name="nomor_hak" required></label><br>

        <div class="form-section">
            <h4>Peminjaman Warkah</h4>
            <p>Status default: <span class="badge bg-success">Tersedia</span></p>
            <label>Peminjam:<br><input type="text" name="peminjam"></label><br>
            <label>Pemegang Hak:<br><input type="text" name="pemegang_hak"></label><br>
            <label>Status:<br>
                <select name="status">
                    <option value="Tersedia" selected>Tersedia</option>
                    <option value="Dipinjam">Dipinjam</option>
                </select>
            </label><br>
            <label>Tanggal Dipinjam:<br><input type="date" name="tanggal_dipinjam"></label><br>
            <label>Tanggal Kembali:<br><input type="date" name="tanggal_kembali"></label><br>
        </div>

        <hr class="form-divider">
        <h4>Keterangan & Upload Dokumen</h4>
        <label>Salinan identitas pemohon (KTP, akta perusahaan):<br><input type="file" name="file_ktp"></label><br>
        <label>Surat permohonan pendaftaran tanah:<br><input type="file" name="file_surat_permohonan"></label><br>
        <label>Alas hak tanah:<br><input type="file" name="file_alas_hak"></label><br>
        <label>Bukti penguasaan tanah:<br><input type="file" name="file_bukti_penguasaan"></label><br>
        <label>Surat Keputusan Pemberian Hak atas Tanah:<br><input type="file" name="file_sk_pemberian_hak"></label><br>
        <label>Peta bidang tanah dan gambar ukur:<br><input type="file" name="file_peta_bidang"></label><br>
        <label>Berita acara pemeriksaan dan pengukuran tanah:<br><input type="file" name="file_berita_acara"></label><br>
        <label>Bukti pembayaran pajak (BPHTB, PPh, PBB):<br><input type="file" name="file_bukti_pajak"></label><br>
        <label>Akta notaris/PPAT:<br><input type="file" name="file_akta_ppat"></label><br>

        <button type="submit">Simpan</button>
    </form>
    <p><a href="index.php">Kembali</a></p>
</div>

<script>
// Data kelurahan dan kecamatan dari includes
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
</script>

<?php include __DIR__ . '/../../Includes/footer.php'; ?>
</body>
</html>
