<?php
require_once __DIR__ . '/../../Includes/config.php';

$warkah_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($warkah_id <= 0) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor_warkah = $conn->real_escape_string(trim($_POST['nomor_warkah']));
    $kecamatan = $conn->real_escape_string(trim($_POST['kecamatan']));
    $kelurahan = $conn->real_escape_string(trim($_POST['kelurahan']));
    $nomor_hak = $conn->real_escape_string(trim($_POST['nomor_hak']));
    $peminjam = $conn->real_escape_string(trim($_POST['peminjam']));
    $pemegang_hak = $conn->real_escape_string(trim($_POST['pemegang_hak']));
    $status = in_array($_POST['status'], ['Tersedia','Dipinjam']) ? $_POST['status'] : 'Tersedia';
    $tanggal_dipinjam = !empty($_POST['tanggal_dipinjam']) ? $conn->real_escape_string($_POST['tanggal_dipinjam']) : null;
    $tanggal_kembali = !empty($_POST['tanggal_kembali']) ? $conn->real_escape_string($_POST['tanggal_kembali']) : null;

    $stmt = $conn->prepare("UPDATE warkah SET nomor_warkah = ?, kecamatan = ?, kelurahan = ?, nomor_hak = ?, peminjam = ?, pemegang_hak = ?, status = ?, tanggal_dipinjam = ?, tanggal_kembali = ? WHERE id = ?");
    $stmt->bind_param('sssssssssi', $nomor_warkah, $kecamatan, $kelurahan, $nomor_hak, $peminjam, $pemegang_hak, $status, $tanggal_dipinjam, $tanggal_kembali, $warkah_id);
    if ($stmt->execute()) {
        // Log edit
        if (isLoggedIn()) {
            log_audit(
                $conn,
                $_SESSION['user_id'],
                $_SESSION['username'],
                'EDIT_WARKAH',
                'Warkah diedit - Nomor: ' . $nomor_warkah . ', Kelurahan: ' . $kelurahan,
                'warkah',
                $warkah_id,
                $nomor_warkah,
                null,
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

// Get warkah data
$stmt = $conn->prepare("SELECT * FROM warkah WHERE id = ?");
$stmt->bind_param('i', $warkah_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header('Location: index.php');
    exit();
}
$warkah = $result->fetch_assoc();

require_once __DIR__ . '/../../includes/data_yogyakarta.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Edit Warkah</title>
    <link rel="stylesheet" href="/Arsip_Bon_Warkah/Assets/Css/Style.css">
</head>
<body>
<?php include __DIR__ . '/../../Includes/header.php'; ?>
<div class="container">
    <h2>Edit Warkah</h2>
    <?php if (!empty($error)): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <label>Nomor Warkah:<br><input type="text" name="nomor_warkah" value="<?php echo htmlspecialchars($warkah['nomor_warkah']); ?>" required></label><br>
        <label>Kecamatan:<br>
            <select name="kecamatan" id="kecamatan-select" required>
                <option value="">-- Pilih Kecamatan --</option>
                <?php foreach (getKecamatan() as $kec): ?>
                    <option value="<?= htmlspecialchars($kec) ?>" <?php if ($warkah['kecamatan'] === $kec) echo 'selected'; ?>><?= htmlspecialchars($kec) ?></option>
                <?php endforeach; ?>
            </select>
        </label><br>
        <label>Kelurahan:<br>
            <select name="kelurahan" id="kelurahan-select" required>
                <option value="">-- Pilih Kelurahan --</option>
                <?php
                $kec = $warkah['kecamatan'];
                if (isset($yogyakarta_data[$kec])) {
                    foreach ($yogyakarta_data[$kec] as $kel) {
                        $selected = ($warkah['kelurahan'] === $kel) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($kel) . "' $selected>" . htmlspecialchars($kel) . "</option>";
                    }
                }
                ?>
            </select>
        </label><br>
        <label>Nomor Hak:<br><input type="text" name="nomor_hak" value="<?php echo htmlspecialchars($warkah['nomor_hak']); ?>" required></label><br>

        <hr class="form-divider">
        <div class="form-section">
            <h4>Peminjaman Warkah</h4>
            <p>Status saat ini: <span class="badge bg-<?= $warkah['status'] == 'Dipinjam' ? 'warning' : 'success' ?>"><?= $warkah['status'] == 'Dipinjam' ? 'Dipinjam' : 'Tersedia' ?></span></p>
            <label>Peminjam:<br><input type="text" name="peminjam" value="<?php echo htmlspecialchars($warkah['peminjam']); ?>"></label><br>
            <label>Pemegang Hak:<br><input type="text" name="pemegang_hak" value="<?php echo htmlspecialchars($warkah['pemegang_hak'] ?? ''); ?>"></label><br>
            <label>Status:<br>
                <select name="status">
                    <option value="Tersedia" <?php if ($warkah['status'] === 'Tersedia') echo 'selected'; ?>>Tersedia</option>
                    <option value="Dipinjam" <?php if ($warkah['status'] === 'Dipinjam') echo 'selected'; ?>>Dipinjam</option>
                </select>
            </label><br>
            <label>Tanggal Dipinjam:<br><input type="date" name="tanggal_dipinjam" value="<?php echo htmlspecialchars($warkah['tanggal_dipinjam']); ?>"></label><br>
            <label>Tanggal Kembali:<br><input type="date" name="tanggal_kembali" value="<?php echo htmlspecialchars($warkah['tanggal_kembali']); ?>"></label><br>
        </div>

        <button type="submit">Simpan Perubahan</button>
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