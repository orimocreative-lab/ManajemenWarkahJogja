<?php
require_once __DIR__ . '/../../Includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $warkah_id = intval($_POST['warkah_id']);

    // Check if warkah is borrowed
    $check_stmt = $conn->prepare("SELECT status, nomor_warkah, kelurahan, peminjam FROM warkah WHERE id = ?");
    $check_stmt->bind_param('i', $warkah_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows === 0) {
        $error = "Warkah tidak ditemukan.";
    } else {
        $row = $check_result->fetch_assoc();
        if ($row['status'] !== 'Dipinjam') {
            $error = "Warkah tidak sedang dipinjam.";
        } else {
            $stmt = $conn->prepare("UPDATE warkah SET peminjam = NULL, status = 'Tersedia', tanggal_dipinjam = NULL, tanggal_kembali = NULL WHERE id = ?");
            $stmt->bind_param('i', $warkah_id);
            if ($stmt->execute()) {
                // Log pengembalian
                if (isLoggedIn()) {
                    log_audit(
                        $conn,
                        $_SESSION['user_id'],
                        $_SESSION['username'],
                        'KEMBALI_WARKAH',
                        'Warkah dikembalikan - Nomor: ' . $row['nomor_warkah'] . ', Peminjam sebelumnya: ' . $row['peminjam'] . ', Kelurahan: ' . $row['kelurahan'],
                        'warkah',
                        $warkah_id,
                        $row['nomor_warkah'],
                        null,
                        ['status' => 'Tersedia', 'peminjam' => null, 'tanggal_dipinjam' => null, 'tanggal_kembali' => null]
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

// Get borrowed warkah
$warkah_result = $conn->query("SELECT id, nomor_warkah, kelurahan, kecamatan, peminjam FROM warkah WHERE status = 'Dipinjam' ORDER BY nomor_warkah");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kembalikan Warkah</title>
    <link rel="stylesheet" href="/Arsip_Bon_Warkah/Assets/Css/Style.css">
</head>
<body>
<?php include __DIR__ . '/../../Includes/header.php'; ?>
<div class="container">
    <h2>Kembalikan Warkah</h2>
    <?php if (!empty($error)): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="post">
        <label>Warkah:<br>
            <select name="warkah_id" required>
                <option value="">-- Pilih Warkah --</option>
                <?php while ($warkah = $warkah_result->fetch_assoc()): ?>
                    <option value="<?php echo $warkah['id']; ?>">
                        <?php echo htmlspecialchars($warkah['nomor_warkah'] . ' - ' . $warkah['kelurahan'] . ', ' . $warkah['kecamatan'] . ' (Peminjam: ' . $warkah['peminjam'] . ')'); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </label><br>

        <button type="submit">Kembalikan</button>
    </form>
    <p><a href="index.php">Kembali</a></p>
</div>
<?php include __DIR__ . '/../../Includes/footer.php'; ?>
</body>
</html>