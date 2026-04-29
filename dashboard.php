<?php
require_once 'includes/config.php';
require_once 'includes/reminder.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Get reminder data
$overdue = getOverdueItems($conn);
$upcoming = getUpcomingItems($conn, 7);

$stats = [
    'total_bon' => $conn->query("SELECT COUNT(*) FROM bon_warkah")->fetch_row()[0],
    'dipinjam' => $conn->query("SELECT COUNT(*) FROM bon_warkah WHERE status = 'dipinjam'")->fetch_row()[0],
    'dikembalikan' => $conn->query("SELECT COUNT(*) FROM bon_warkah WHERE status = 'dikembalikan'")->fetch_row()[0],
    'overdue' => count($overdue)
];
?>

<?php include 'includes/header.php'; ?>

<!-- Display Reminder Modal if there are overdue or upcoming items -->
<?= displayReminderPopup($overdue, $upcoming) ?>

<!-- Page Header -->
<div class="page-header">
    <h1>Dasbor Sistem Informasi Warkah</h1>
    <p>Kelola dan pantau data warkah dengan mudah</p>
</div>

<!-- Quick Guide Section -->
<div class="card guide-quick-card mb-5">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="guide-quick-title mb-2">
                    <i class="fas fa-lightbulb me-2 text-warning"></i>Baru Menggunakan Sistem?
                </h5>
                <p class="guide-quick-text mb-0">
                    Pelajari fitur-fitur utama sistem melalui panduan interaktif kami. Lihat simulasi cara menggunakan setiap fitur.
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="<?= BASE_URL ?>modules/panduan/index.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-book me-2"></i>Buka Panduan Lengkap
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-primary">
            <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
            <div class="card-body">
                <h5 class="stat-title">Total Data Warkah</h5>
                <h2 class="stat-value"><?= $stats['total_bon'] ?></h2>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-warning">
            <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
            <div class="card-body">
                <h5 class="stat-title">Sedang Dipinjam</h5>
                <h2 class="stat-value"><?= $stats['dipinjam'] ?></h2>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card <?= $stats['overdue'] > 0 ? 'bg-danger' : 'bg-success' ?>">
            <div class="stat-icon">
                <?= $stats['overdue'] > 0 ? '<i class="fas fa-exclamation-circle"></i>' : '<i class="fas fa-check-circle"></i>' ?>
            </div>
            <div class="card-body">
                <h5 class="stat-title"><?= $stats['overdue'] > 0 ? 'Berkas Tertunda' : 'Status' ?></h5>
                <h2 class="stat-value"><?= $stats['overdue'] > 0 ? $stats['overdue'] : '✓' ?></h2>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-success">
            <div class="stat-icon"><i class="fas fa-check"></i></div>
            <div class="card-body">
                <h5 class="stat-title">Tersedia</h5>
                <h2 class="stat-value"><?= $stats['dikembalikan'] ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Recent Data Table -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-history me-2"></i> Data Warkah Terbaru
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nomor</th>
                        <th>Nomor Hak</th>
                        <th>Kelurahan</th>
                        <th>Kecamatan</th>
                        <th>Lokasi Warkah</th>
                        <th>Status</th>
                        <th>Peminjam</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT * FROM bon_warkah ORDER BY id DESC LIMIT 5";
                    $result = $conn->query($query);
                    
                    if ($result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nomor_bon']) ?></td>
                            <td><?= htmlspecialchars($row['nomor_hak']) ?></td>
                            <td><?= htmlspecialchars($row['kelurahan']) ?></td>
                            <td><?= htmlspecialchars($row['kecamatan']) ?></td>
                            <td><?= htmlspecialchars($row['lokasi_warkah']) ?></td>
                            <td>
                                <span class="badge bg-<?= $row['status'] == 'dipinjam' ? 'warning' : 'success' ?>">
                                    <?= $row['status'] == 'dipinjam' ? 'Dipinjam' : 'Tersedia' ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['peminjam']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada data warkah terbaru.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>