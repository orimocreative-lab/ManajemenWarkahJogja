<?php
require_once __DIR__ . '/../../Includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $warkah_id = intval($_POST['warkah_id']);
    $peminjam = $conn->real_escape_string(trim($_POST['peminjam']));
    $tanggal_pinjam = $conn->real_escape_string($_POST['tanggal_pinjam']);
    $tanggal_kembali = !empty($_POST['tanggal_kembali']) ? $conn->real_escape_string($_POST['tanggal_kembali']) : null;

    // Check if warkah is available
    $check_stmt = $conn->prepare("SELECT status FROM warkah WHERE id = ?");
    $check_stmt->bind_param('i', $warkah_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows === 0) {
        $error = "Warkah tidak ditemukan.";
    } else {
        $row = $check_result->fetch_assoc();
        if ($row['status'] !== 'Tersedia') {
            $error = "Warkah sudah dipinjam.";
        } else {
            $stmt = $conn->prepare("UPDATE warkah SET peminjam = ?, status = 'Dipinjam', tanggal_dipinjam = ?, tanggal_kembali = ? WHERE id = ?");
            $stmt->bind_param('sssi', $peminjam, $tanggal_pinjam, $tanggal_kembali, $warkah_id);
            if ($stmt->execute()) {
                // Get warkah data for audit
                $warkah_stmt = $conn->prepare("SELECT nomor_warkah, kelurahan FROM warkah WHERE id = ?");
                $warkah_stmt->bind_param('i', $warkah_id);
                $warkah_stmt->execute();
                $warkah_data = $warkah_stmt->get_result()->fetch_assoc();

                // Log peminjaman
                if (isLoggedIn()) {
                    log_audit(
                        $conn,
                        $_SESSION['user_id'],
                        $_SESSION['username'],
                        'PINJAM_WARKAH',
                        'Warkah dipinjam - Nomor: ' . $warkah_data['nomor_warkah'] . ', Peminjam: ' . $peminjam . ', Kelurahan: ' . $warkah_data['kelurahan'],
                        'warkah',
                        $warkah_id,
                        $warkah_data['nomor_warkah'],
                        null,
                        ['peminjam' => $peminjam, 'tanggal_pinjam' => $tanggal_pinjam, 'tanggal_kembali' => $tanggal_kembali, 'status' => 'Dipinjam']
                    );
                }

                header('Location: index.php');
                exit();
            } else {
                $error = $stmt->error;
            }
        }
    }
}

// Get available warkah
$warkah_result = $conn->query("SELECT id, nomor_warkah, kelurahan, kecamatan FROM warkah WHERE status = 'Tersedia' ORDER BY nomor_warkah");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pinjam Warkah</title>
    <link rel="stylesheet" href="/Arsip_Bon_Warkah/Assets/Css/Style.css">
</head>
<body>
<?php include __DIR__ . '/../../Includes/header.php'; ?>
<div class="container">
    <h2>Pinjam Warkah</h2>
    <?php if (!empty($error)): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="post">
        <label>Warkah:<br>
            <select name="warkah_id" required>
                <option value="">-- Pilih Warkah --</option>
                <?php while ($warkah = $warkah_result->fetch_assoc()): ?>
                    <option value="<?php echo $warkah['id']; ?>">
                        <?php echo htmlspecialchars($warkah['nomor_warkah'] . ' - ' . $warkah['kelurahan'] . ', ' . $warkah['kecamatan']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </label><br>

        <hr class="form-divider">

        <div class="form-section">
            <h4>Peminjaman Warkah</h4>
            <label>Peminjam:<br><input type="text" name="peminjam" required></label><br>
            <label>Tanggal Pinjam:<br><input type="date" name="tanggal_pinjam" required></label><br>
            <label>Tanggal Kembali:<br><input type="date" name="tanggal_kembali"></label><br>
        </div>

        <button type="submit">Pinjam</button>
    </form>
    <p><a href="index.php">Kembali</a></p>
</div>
<?php include __DIR__ . '/../../Includes/footer.php'; ?>
</body>
</html>