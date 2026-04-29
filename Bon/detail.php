<?php
require_once '../../includes/config.php';
if (!isLoggedIn()) redirect('../../login.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT * FROM bon_warkah WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$bon = $stmt->get_result()->fetch_assoc();

if (!$bon) redirect('index.php');

$berkas_stmt = $conn->prepare("SELECT * FROM berkas WHERE bon_warkah_id = ? ORDER BY created_at DESC");
$berkas_stmt->bind_param("i", $id);
$berkas_stmt->execute();
$berkas_list = $berkas_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

include '../../includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4 mb-4">Detail Data Warkah</h1>

    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-info-circle me-1"></i> Informasi Lengkap #<?= htmlspecialchars($bon['nomor_bon']) ?>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr><td width="40%"><strong>Nomor Warkah</strong></td><td>: <?= htmlspecialchars($bon['nomor_bon']) ?></td></tr>
                        <tr><td><strong>Nomor Hak</strong></td><td>: <?= htmlspecialchars($bon['nomor_hak']) ?></td></tr>
                        <tr><td><strong>Kelurahan</strong></td><td>: <?= htmlspecialchars($bon['kelurahan']) ?></td></tr>
                        <tr><td><strong>Kecamatan</strong></td><td>: <?= htmlspecialchars($bon['kecamatan']) ?></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr><td width="40%"><strong>Lokasi Warkah</strong></td><td>: <?= htmlspecialchars($bon['lokasi_warkah']) ?></td></tr>
                        <tr><td><strong>Pemegang Hak</strong></td><td>: <?= htmlspecialchars($bon['pemegang_hak'] ?? '-') ?></td></tr>
                        <tr><td><strong>Nomor DI 208</strong></td><td>: <?= htmlspecialchars($bon['status_terakhir']) ?></td></tr>
                    </table>
                </div>
            </div>
            <div class="mt-2"><strong>Keterangan:</strong><br><p class="text-muted"><?= nl2br(htmlspecialchars($bon['keterangan'])) ?></p></div>
            <hr>
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Peminjaman Warkah</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Peminjam:</strong> <?= htmlspecialchars($bon['peminjam']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong> <span class="badge bg-<?= strtolower(trim($bon['status'])) == 'dipinjam' ? 'warning' : 'success' ?>"><?= strtolower(trim($bon['status'])) == 'dipinjam' ? 'Dipinjam' : 'Tersedia' ?></span></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Tanggal Pinjam:</strong> <?= format_date_display($bon['tanggal_pinjam']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Tanggal Kembali:</strong> <?= format_date_display($bon['tanggal_kembali']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <a href="edit.php?id=<?= $bon['id'] ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Edit Data</a>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </div>
    </div>

    <h5 class="mb-3">Berkas Terlampir (PDF)</h5>
    <?php if (!empty($berkas_list)): ?>
        <div class="row">
            <?php foreach ($berkas_list as $berkas): ?>
                <div class="col-lg-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <span class="text-truncate"><strong><?= htmlspecialchars($berkas['nama_berkas']) ?></strong></span>
                            <a href="<?= $berkas['file_path'] ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-external-link-alt"></i></a>
                        </div>
                        <div class="card-body p-0"><embed src="<?= $berkas['file_path'] ?>" type="application/pdf" width="100%" height="450px" /></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Belum ada berkas PDF yang terunggah.</div>
    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>