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

// Get status filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'pending';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get edit requests
$edit_requests = getEditRequests($conn, $status_filter, $per_page, $offset);
$total = countEditRequests($conn, $status_filter);
$total_pages = ceil($total / $per_page);

include '../../includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h1>Persetujuan Edit Data Warkah</h1>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-clipboard-check"></i> Daftar Permintaan Edit</h5>
        </div>
        <div class="card-body">
            <!-- Status Filter -->
            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item">
                    <a class="nav-link <?= $status_filter === 'pending' ? 'active' : '' ?>" 
                       href="?status=pending">
                        <i class="fas fa-hourglass-half"></i> Menunggu <span class="badge bg-warning"><?= countEditRequests($conn, 'pending') ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $status_filter === 'approved' ? 'active' : '' ?>" 
                       href="?status=approved">
                        <i class="fas fa-check-circle"></i> Disetujui <span class="badge bg-success"><?= countEditRequests($conn, 'approved') ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $status_filter === 'completed' ? 'active' : '' ?>" 
                       href="?status=completed">
                        <i class="fas fa-check-double"></i> Selesai <span class="badge bg-secondary"><?= countEditRequests($conn, 'completed') ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $status_filter === 'rejected' ? 'active' : '' ?>" 
                       href="?status=rejected">
                        <i class="fas fa-times-circle"></i> Ditolak <span class="badge bg-danger"><?= countEditRequests($conn, 'rejected') ?></span>
                    </a>
                </li>
            </ul>

            <!-- Requests Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No Bon</th>
                            <th>Peminjam</th>
                            <th>Diminta Oleh</th>
                            <th>Tanggal Request</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($edit_requests)): ?>
                            <?php foreach ($edit_requests as $req): ?>
                            <tr>
                                <td>
                                    <a href="detail.php?id=<?= $req['bon_id'] ?>" target="_blank">
                                        <?= htmlspecialchars($req['nomor_bon']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($req['peminjam']) ?></td>
                                <td><?= htmlspecialchars($req['requested_by_name']) ?> <br><small class="text-muted"><?= htmlspecialchars($req['requested_by_username']) ?></small></td>
                                <td><?= date('d-m-Y H:i', strtotime($req['requested_at'])) ?></td>
                                <td>
                                    <?php if ($req['status'] === 'pending'): ?>
                                        <span class="badge bg-warning"><i class="fas fa-hourglass-half"></i> Menunggu</span>
                                    <?php elseif ($req['status'] === 'approved'): ?>
                                        <span class="badge bg-success"><i class="fas fa-check"></i> Disetujui</span>
                                    <?php elseif ($req['status'] === 'completed'): ?>
                                        <span class="badge bg-secondary"><i class="fas fa-check-double"></i> Selesai</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="fas fa-times"></i> Ditolak</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($req['status'] === 'pending'): ?>
                                        <a href="edit.php?id=<?= $req['bon_id'] ?>" class="btn btn-sm btn-info" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="approval-edit.php?request_id=<?= $req['id'] ?>&action=approve" 
                                           class="btn btn-sm btn-success" 
                                           onclick="return confirm('Setujui permintaan edit ini?')">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" 
                                                data-bs-target="#rejectModal<?= $req['id'] ?>">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        
                                        <!-- Reject Modal -->
                                        <div class="modal fade" id="rejectModal<?= $req['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Tolak Permintaan Edit</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST" action="approval-edit.php?request_id=<?= $req['id'] ?>&action=reject">
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Alasan Penolakan</label>
                                                                <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-danger">Tolak</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- Detail Row (collapsible) -->
                            <?php if (!empty($req['proposed_changes'])): 
                                $changes = json_decode($req['proposed_changes'], true);
                            ?>
                            <tr class="table-light" style="display:none;" id="detail-<?= $req['id'] ?>">
                                <td colspan="6">
                                    <strong>Perubahan yang Diusulkan:</strong>
                                    <table class="table table-sm table-borderless mt-2">
                                        <tr>
                                            <th class="w-25">Field</th>
                                            <th class="w-35">Nilai Saat Ini</th>
                                            <th class="w-35">Nilai Usulan</th>
                                        </tr>
                                        <?php foreach ($changes as $field => $value): 
                                            $current_val = '';
                                            $stmt_bon = $conn->prepare("SELECT $field FROM bon_warkah WHERE id = ?");
                                            $stmt_bon->bind_param('i', $req['bon_id']);
                                            $stmt_bon->execute();
                                            $res_bon = $stmt_bon->get_result()->fetch_assoc();
                                            $current_val = $res_bon[$field] ?? '-';
                                            $stmt_bon->close();
                                        ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($field) ?></strong></td>
                                            <td><?= htmlspecialchars($current_val) ?></td>
                                            <td><span class="badge bg-info"><?= htmlspecialchars($value) ?></span></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </table>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <?php if ($status_filter === 'pending'): ?>
                                        Tidak ada permintaan edit yang menunggu persetujuan.
                                    <?php elseif ($status_filter === 'completed'): ?>
                                        Tidak ada permintaan edit yang sudah diselesaikan.
                                    <?php else: ?>
                                        Tidak ada permintaan edit dengan status ini.
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?status=<?= $status_filter ?>&page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
