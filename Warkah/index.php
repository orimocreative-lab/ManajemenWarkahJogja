<?php
require_once __DIR__ . '/../../Includes/config.php';
$res = $conn->query("SELECT * FROM warkah ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daftar Warkah</title>
    <link rel="stylesheet" href="/Arsip_Bon_Warkah/Assets/Css/Style.css">
}</head>
<body>
<?php include __DIR__ . '/../../Includes/header.php'; ?>
<div class="container">
    <h2>Daftar Warkah</h2>
    <p><a href="tambah.php">Tambah Warkah Baru</a> | <a href="pinjam.php">Pinjam Warkah</a> | <a href="kembali.php">Kembalikan Warkah</a></p>
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nomor Warkah</th>
                <th>Kecamatan</th>
                <th>Kelurahan</th>
                <th>Nomor Hak</th>
                <th>Peminjam</th>
                <th>Pemegang Hak</th>
                <th>Status</th>
                <th>Tanggal Dipinjam</th>
                <th>Tanggal Kembali</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($res && $res->num_rows > 0): while ($row = $res->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['nomor_warkah']); ?></td>
                <td><?php echo htmlspecialchars($row['kecamatan']); ?></td>
                <td><?php echo htmlspecialchars($row['kelurahan']); ?></td>
                <td><?php echo htmlspecialchars($row['nomor_hak']); ?></td>
                <td><?php echo htmlspecialchars($row['peminjam']); ?></td>
                <td><?php echo htmlspecialchars($row['pemegang_hak'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td><?php echo htmlspecialchars($row['tanggal_dipinjam']); ?></td>
                <td><?php echo htmlspecialchars($row['tanggal_kembali']); ?></td>
                <td><a href="edit.php?id=<?php echo $row['id']; ?>">Edit</a></td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="11">Tidak ada data.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../../Includes/footer.php'; ?>
</body>
</html>
