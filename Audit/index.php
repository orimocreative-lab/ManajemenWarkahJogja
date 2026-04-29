<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isLoggedIn()) {
    redirect('../../login.php');
}

// Hanya admin yang bisa melihat audit trail
if ($_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Anda tidak memiliki akses ke halaman ini.';
    redirect('../../dashboard.php');
}

// Pagination
$per_page = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Build filters
$filters = [];

if (!empty($_GET['action'])) {
    $filters['action'] = $_GET['action'];
}

if (!empty($_GET['username'])) {
    $filters['username'] = $_GET['username'];
}

if (!empty($_GET['entity_type'])) {
    $filters['entity_type'] = $_GET['entity_type'];
}

if (!empty($_GET['date_from'])) {
    $filters['date_from'] = $_GET['date_from'];
}

if (!empty($_GET['date_to'])) {
    $filters['date_to'] = $_GET['date_to'];
}

// Get audit logs
$logs = get_audit_logs($conn, $filters, $per_page, $offset);
$total = count_audit_logs($conn, $filters);
$total_pages = ceil($total / $per_page);

// Clean up any audit logs with action = 0 (from previous bug)
// Convert them to a generic action name based on entity_type
$cleanup_query = "UPDATE audit_trail SET action = CASE 
    WHEN action = 0 AND entity_type = 'warkah_berkas' THEN 'UNGGAH_BERKAS'
    WHEN action = 0 AND entity_type = 'bon_warkah' THEN 'EDIT_DATA_WARKAH'
    WHEN action = 0 THEN 'SYSTEM_ACTION'
    ELSE action
END WHERE action = 0";
$conn->query($cleanup_query);

include '../../includes/header.php';

// Helper function untuk format action
function get_action_icon($action) {
    $action = strtoupper($action);
    if (strpos($action, 'LOGIN') !== false) return '<i class="fas fa-sign-in-alt"></i>';
    if (strpos($action, 'LOGOUT') !== false) return '<i class="fas fa-sign-out-alt"></i>';
    if (strpos($action, 'TAMBAH') !== false || strpos($action, 'UNGGAH') !== false) return '<i class="fas fa-plus-circle"></i>';
    if (strpos($action, 'EDIT') !== false) return '<i class="fas fa-edit"></i>';
    if (strpos($action, 'HAPUS') !== false) return '<i class="fas fa-trash-alt"></i>';
    return '<i class="fas fa-cog"></i>';
}

function get_action_color($action) {
    $action = strtoupper($action);
    if (strpos($action, 'LOGIN') !== false) return 'success';
    if (strpos($action, 'LOGOUT') !== false) return 'info';
    if (strpos($action, 'TAMBAH') !== false || strpos($action, 'UNGGAH') !== false) return 'primary';
    if (strpos($action, 'EDIT') !== false) return 'warning';
    if (strpos($action, 'HAPUS') !== false) return 'danger';
    return 'secondary';
}

function format_entity_type($type) {
    $types = [
        'bon_warkah' => 'Data Warkah',
        'warkah' => 'Warkah',
        'warkah_berkas' => 'Berkas PDF',
        'users' => 'User'
    ];
    return $types[$type] ?? $type;
}
?>

<div class="container-fluid px-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mt-4 mb-3"><i class="fas fa-history me-2"></i>Audit Trail - Catatan Aktivitas Sistem</h1>
            <p class="text-muted mb-0"><i class="fas fa-info-circle"></i> Monitor dan lacak semua aktivitas pengguna dalam sistem</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary shadow-sm">
                <div class="card-body">
                    <div class="text-primary font-weight-bold text-uppercase mb-1">Total Aktivitas</div>
                    <div class="h3 mb-0"><?= $total ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success shadow-sm">
                <div class="card-body">
                    <div class="text-success font-weight-bold text-uppercase mb-1">Login</div>
                    <div class="h3 mb-0"><?= count_audit_logs($conn, ['action' => 'LOGIN']) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning shadow-sm">
                <div class="card-body">
                    <div class="text-warning font-weight-bold text-uppercase mb-1">Perubahan Data</div>
                    <div class="h3 mb-0"><?= count_audit_logs($conn, ['action' => 'EDIT']) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-danger shadow-sm">
                <div class="card-body">
                    <div class="text-danger font-weight-bold text-uppercase mb-1">Penghapusan</div>
                    <div class="h3 mb-0"><?= count_audit_logs($conn, ['action' => 'HAPUS']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <h6 class="mb-0"><i class="fas fa-filter me-2"></i> Filter Pencarian</h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <label for="action" class="form-label fw-bold">Aksi</label>
                    <select name="action" id="action" class="form-select form-select-sm">
                        <option value="">-- Semua Aksi --</option>
                        <option value="LOGIN" <?= isset($_GET['action']) && $_GET['action'] === 'LOGIN' ? 'selected' : '' ?>>LOGIN</option>
                        <option value="LOGOUT" <?= isset($_GET['action']) && $_GET['action'] === 'LOGOUT' ? 'selected' : '' ?>>LOGOUT</option>
                        <option value="TAMBAH" <?= isset($_GET['action']) && strpos($_GET['action'], 'TAMBAH') !== false ? 'selected' : '' ?>>TAMBAH DATA</option>
                        <option value="EDIT" <?= isset($_GET['action']) && strpos($_GET['action'], 'EDIT') !== false ? 'selected' : '' ?>>EDIT DATA</option>
                        <option value="HAPUS" <?= isset($_GET['action']) && strpos($_GET['action'], 'HAPUS') !== false ? 'selected' : '' ?>>HAPUS DATA</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="username" class="form-label fw-bold">Username</label>
                    <input type="text" class="form-control form-control-sm" name="username" id="username" placeholder="Nama user" value="<?= isset($_GET['username']) ? htmlspecialchars($_GET['username']) : '' ?>">
                </div>
                <div class="col-md-2">
                    <label for="entity_type" class="form-label fw-bold">Tipe Entitas</label>
                    <select name="entity_type" id="entity_type" class="form-select form-select-sm">
                        <option value="">-- Semua Tipe --</option>
                        <option value="bon_warkah" <?= isset($_GET['entity_type']) && $_GET['entity_type'] === 'bon_warkah' ? 'selected' : '' ?>>Data Warkah</option>
                        <option value="warkah" <?= isset($_GET['entity_type']) && $_GET['entity_type'] === 'warkah' ? 'selected' : '' ?>>Warkah</option>
                        <option value="warkah_berkas" <?= isset($_GET['entity_type']) && $_GET['entity_type'] === 'warkah_berkas' ? 'selected' : '' ?>>Berkas PDF</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label fw-bold">Dari Tanggal</label>
                    <input type="date" class="form-control form-control-sm" name="date_from" id="date_from" value="<?= isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : '' ?>">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label fw-bold">Sampai Tanggal</label>
                    <input type="date" class="form-control form-control-sm" name="date_to" id="date_to" value="<?= isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : '' ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search me-1"></i> Cari</button>
                    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-redo me-1"></i> Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Activity List -->
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h6 class="mb-0"><i class="fas fa-list me-2"></i> Catatan Aktivitas (<?= $total ?> records)</h6>
        </div>
        <div class="card-body p-0">
            <?php if (empty($logs)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox" style="font-size: 3rem; color: #ccc;"></i>
                    <p class="text-muted mt-3">Tidak ada data audit trail</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width: 160px;"><i class="fas fa-clock"></i> Waktu</th>
                                <th style="width: 120px;"><i class="fas fa-user"></i> User</th>
                                <th style="width: 150px;"><i class="fas fa-tasks"></i> Aksi</th>
                                <th style="width: 130px;"><i class="fas fa-folder"></i> Tipe</th>
                                <th><i class="fas fa-align-left"></i> Detail</th>
                                <th style="width: 140px;"><i class="fas fa-globe"></i> IP</th>
                                <th style="width: 60px;" class="text-center">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr class="align-middle">
                                    <td>
                                        <div class="fw-bold"><?= date('d/m/Y', strtotime($log['created_at'])) ?></div>
                                        <small class="text-muted"><?= date('H:i:s', strtotime($log['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($log['username']) ?></div>
                                    </td>
                                    <td>
                                        <?php
                                        $action_color = get_action_color($log['action']);
                                        $action_icon = get_action_icon($log['action']);
                                        ?>
                                        <span class="badge bg-<?= $action_color ?> d-inline-flex align-items-center gap-1">
                                            <?= $action_icon ?>
                                            <span><?= htmlspecialchars($log['action']) ?></span>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($log['entity_type'])): ?>
                                            <span class="badge bg-secondary"><?= format_entity_type($log['entity_type']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="detail-cell" data-log-id="<?= $log['id'] ?>">
                                            <?php if (!empty($log['entity_name'])): ?>
                                                <strong><?= htmlspecialchars($log['entity_name']) ?></strong><br>
                                            <?php endif; ?>
                                            <?php if (!empty($log['details'])): ?>
                                                <small class="text-muted d-block text-truncate" style="max-width: 300px;">
                                                    <?php
                                                    $detail = htmlspecialchars($log['details']);
                                                    echo substr($detail, 0, 80);
                                                    if (strlen($detail) > 80) echo '...';
                                                    ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-monospace" title="<?= htmlspecialchars($log['ip']) ?>">
                                            <?= htmlspecialchars($log['ip']) ?>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary audit-detail-btn" data-log-id="<?= $log['id'] ?>" aria-label="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>

                                <div id="auditContent<?= $log['id'] ?>" class="d-none audit-detail-content">
                                    <div class="audit-detail-inner">
                                        <div class="audit-detail-header">
                                            <h5 class="fw-bold"><i class="fas fa-magnifying-glass-plus me-2"></i> Detail Aktivitas #<?= $log['id'] ?></h5>
                                        </div>
                                        <div class="audit-detail-body">
                                            <div class="alert alert-light border-primary border-start border-5 mb-4">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-2">
                                                            <small class="text-muted">Aksi Dilakukan</small>
                                                            <div class="h6 mb-0">
                                                                <span class="badge bg-<?= get_action_color($log['action']) ?> p-2">
                                                                    <?= get_action_icon($log['action']) ?> <?= htmlspecialchars($log['action']) ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-2">
                                                            <small class="text-muted">Status</small>
                                                            <div class="h6 mb-0">
                                                                <span class="badge <?= $log['status'] === 'success' ? 'bg-success' : 'bg-danger' ?> p-2">
                                                                    <i class="fas <?= $log['status'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                                                                    <?= ucfirst(htmlspecialchars($log['status'])) ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <h6 class="text-primary fw-bold mb-3"><i class="fas fa-clock"></i> Waktu Aktivitas</h6>
                                                    <p class="mb-1"><strong><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></strong></p>
                                                    <small class="text-muted"><?= date('l', strtotime($log['created_at'])) ?></small>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6 class="text-primary fw-bold mb-3"><i class="fas fa-user"></i> Pengguna</h6>
                                                    <p class="mb-0"><strong><?= htmlspecialchars($log['username']) ?></strong></p>
                                                    <?php if (!empty($log['user_id'])): ?>
                                                        <small class="text-muted">ID: <?= htmlspecialchars($log['user_id']) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <h6 class="text-primary fw-bold mb-3"><i class="fas fa-folder"></i> Jenis Data</h6>
                                                    <?php if (!empty($log['entity_type'])): ?>
                                                        <p class="mb-0"><span class="badge bg-secondary p-2"><?= format_entity_type($log['entity_type']) ?></span></p>
                                                    <?php else: ?>
                                                        <p class="text-muted mb-0">-</p>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6 class="text-primary fw-bold mb-3"><i class="fas fa-hashtag"></i> Identitas Data</h6>
                                                    <?php if (!empty($log['entity_id'])): ?>
                                                        <p class="mb-0"><code><?= htmlspecialchars($log['entity_id']) ?></code></p>
                                                    <?php else: ?>
                                                        <p class="text-muted mb-0">-</p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <?php if (!empty($log['entity_name'])): ?>
                                                <div class="mb-4">
                                                    <h6 class="text-primary fw-bold mb-2"><i class="fas fa-tag"></i> Nama Data</h6>
                                                    <div class="alert alert-info p-3 mb-0">
                                                        <strong><?= htmlspecialchars($log['entity_name']) ?></strong>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <div class="mb-4">
                                                <h6 class="text-primary fw-bold mb-2"><i class="fas fa-globe"></i> Informasi Jaringan</h6>
                                                <div class="card border-light">
                                                    <div class="card-body py-2">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <small><strong>IP Address:</strong></small><br>
                                                                <code><?= htmlspecialchars($log['ip']) ?></code>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <?php if (!empty($log['details'])): ?>
                                                <div class="mb-4">
                                                    <h6 class="text-primary fw-bold mb-2"><i class="fas fa-align-left"></i> Detail Aktivitas</h6>
                                                    <div class="alert alert-info p-3">
                                                        <code style="word-break: break-word; white-space: pre-wrap;">
                                                            <?= htmlspecialchars($log['details']) ?>
                                                        </code>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($log['old_values'])): ?>
                                                <div class="mb-4">
                                                    <h6 class="text-warning fw-bold mb-2"><i class="fas fa-history"></i> Nilai Sebelumnya</h6>
                                                    <div class="alert alert-warning p-3 bg-light">
                                                        <code style="word-break: break-word; white-space: pre-wrap; font-size: 0.85rem;">
                                                            <?= htmlspecialchars($log['old_values']) ?>
                                                        </code>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($log['new_values'])): ?>
                                                <div class="mb-4">
                                                    <h6 class="text-success fw-bold mb-2"><i class="fas fa-check-circle"></i> Nilai Sesudahnya</h6>
                                                    <div class="alert alert-success p-3 bg-light">
                                                        <code style="word-break: break-word; white-space: pre-wrap; font-size: 0.85rem;">
                                                            <?= htmlspecialchars($log['new_values']) ?>
                                                        </code>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($log['old_values']) && !empty($log['new_values'])): ?>
                                                <div class="mb-4">
                                                    <h6 class="text-primary fw-bold mb-2"><i class="fas fa-exchange-alt"></i> Ringkasan Perubahan</h6>
                                                    <div class="border rounded p-3 bg-light">
                                                        <?php echo generate_change_summary($log['old_values'], $log['new_values'], $log['entity_type']); ?>
                                                    </div>
                                                </div>
                                            <?php elseif (!empty($log['old_values']) && empty($log['new_values'])): ?>
                                                <div class="mb-4">
                                                    <h6 class="text-danger fw-bold mb-2"><i class="fas fa-trash-alt"></i> Ringkasan Penghapusan</h6>
                                                    <div class="border rounded p-3 bg-light">
                                                        <?php echo generate_change_summary($log['old_values'], $log['new_values'], $log['entity_type']); ?>
                                                    </div>
                                                </div>
                                            <?php elseif (empty($log['old_values']) && !empty($log['new_values'])): ?>
                                                <div class="mb-4">
                                                    <h6 class="text-success fw-bold mb-2"><i class="fas fa-plus-circle"></i> Ringkasan Penambahan</h6>
                                                    <div class="border rounded p-3 bg-light">
                                                        <?php echo generate_change_summary($log['old_values'], $log['new_values'], $log['entity_type']); ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="audit-detail-footer text-end">
                                            <button type="button" class="btn btn-secondary audit-popup-close-btn"><i class="fas fa-times"></i> Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4 pt-3 border-top">
                        <ul class="pagination justify-content-center mb-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?page=1<?= !empty($_GET) ? '&' . http_build_query(array_diff_key($_GET, ['page' => ''])) : '' ?>">
                                        <i class="fas fa-step-backward"></i> Awal
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?page=<?= $page - 1 ?><?= !empty($_GET) ? '&' . http_build_query(array_diff_key($_GET, ['page' => ''])) : '' ?>">
                                        <i class="fas fa-chevron-left"></i> Sebelumnya
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php 
                            // Show page numbers with smart range
                            $start = max(1, $page - 2);
                            $end = min($total_pages, $page + 2);
                            
                            if ($start > 1) {
                                echo '<li class="page-item"><span class="page-link">...</span></li>';
                            }
                            
                            for ($i = $start; $i <= $end; $i++):
                            ?>
                                <?php if ($i == $page): ?>
                                    <li class="page-item active"><span class="page-link"><?= $i ?></span></li>
                                <?php else: ?>
                                    <li class="page-item">
                                        <a class="page-link" href="index.php?page=<?= $i ?><?= !empty($_GET) ? '&' . http_build_query(array_diff_key($_GET, ['page' => ''])) : '' ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($end < $total_pages): ?>
                                <li class="page-item"><span class="page-link">...</span></li>
                            <?php endif; ?>

                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?page=<?= $page + 1 ?><?= !empty($_GET) ? '&' . http_build_query(array_diff_key($_GET, ['page' => ''])) : '' ?>">
                                        Berikutnya <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?page=<?= $total_pages ?><?= !empty($_GET) ? '&' . http_build_query(array_diff_key($_GET, ['page' => ''])) : '' ?>">
                                        Akhir <i class="fas fa-step-forward"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.audit-detail-btn {
    min-width: 38px;
    min-height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    user-select: none;
    position: relative;
    z-index: 1;
    cursor: pointer;
    border-color: #0d6efd !important;
    color: #0d6efd !important;
    background-color: transparent !important;
}
.audit-detail-btn:hover, .audit-detail-btn:active, .audit-detail-btn:focus {
    background-color: transparent !important;
    color: #0d6efd !important;
    border-color: #0d6efd !important;
    transform: none !important;
    box-shadow: none !important;
    outline: none !important;
}

table.table tbody tr { transition: none !important; }
table.table tbody tr:hover { background-color: transparent !important; }

.detail-cell { user-select: text; cursor: default; }

.audit-popup-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.45);
    z-index: 1050;
    display: none;
}
.audit-popup {
    position: fixed;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    background: #fff;
    width: min(920px, 95%);
    max-height: 86vh;
    overflow: auto;
    border-radius: 8px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.25);
    z-index: 1060;
    display: none;
}
.audit-popup .audit-detail-inner { padding: 1.25rem; }
.audit-popup .audit-detail-header { margin-bottom: 0.75rem; }
.audit-popup .audit-detail-footer { margin-top: .75rem; }
.audit-popup .audit-popup-close { position: absolute; right: 8px; top: 8px; border: none; background: transparent; font-size: 1.25rem; cursor: pointer; }
.d-none { display: none !important; }
</style>

<!-- Single reusable popup HTML -->
<div id="auditPopupOverlay" class="audit-popup-overlay"></div>
<div id="auditPopup" class="audit-popup" role="dialog" aria-modal="true">
    <button id="auditPopupClose" class="audit-popup-close" aria-label="Tutup">&times;</button>
    <div class="audit-detail-inner">
        <div id="auditPopupBody"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('auditPopupOverlay');
    const popup = document.getElementById('auditPopup');
    const popupBody = document.getElementById('auditPopupBody');
    const closeBtns = function() {
        overlay.style.display = 'none';
        popup.style.display = 'none';
        document.body.style.overflow = '';
        popupBody.innerHTML = '';
    };

    document.getElementById('auditPopupClose').addEventListener('click', closeBtns);
    overlay.addEventListener('click', closeBtns);

    // open on click, copy content from hidden block
    document.querySelectorAll('.audit-detail-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = btn.getAttribute('data-log-id');
            const hidden = document.getElementById('auditContent' + id);
            if (!hidden) return;
            popupBody.innerHTML = hidden.innerHTML;
            overlay.style.display = 'block';
            popup.style.display = 'block';
            document.body.style.overflow = 'hidden';
        });
    });

    // close buttons inside copied content
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('audit-popup-close-btn')) {
            closeBtns();
        }
    });
});
</script>

<?php include '../../includes/footer.php'; ?>