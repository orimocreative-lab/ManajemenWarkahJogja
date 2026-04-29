<?php
require_once '../../includes/config.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    redirect('../../login.php');
}

$search = '';
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
}

$where = '';
if (!empty($search)) {
    $where = " WHERE username LIKE '%$search%' OR nama_lengkap LIKE '%$search%' OR role LIKE '%$search%'";
}

$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

$total_query = "SELECT COUNT(*) FROM users $where";
$total_rows = $conn->query($total_query)->fetch_row()[0];
$total_pages = ceil($total_rows / $per_page);

$query = "SELECT id, username, nama_lengkap, role, created_at FROM users $where ORDER BY created_at DESC LIMIT $offset, $per_page";
$result = $conn->query($query);
?>

<?php include '../../includes/header.php'; ?>

<h1 class="mt-4 mb-4">Manajemen User</h1>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-users me-2"></i> Daftar User</h5>
        <div>
            <a href="tambah.php" class="btn btn-primary btn-sm">
                <i class="fas fa-user-plus me-1"></i> Tambah User Baru
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Search Form -->
        <form method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Cari berdasarkan Username, Nama, atau Role..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search me-1"></i> Cari</button>
                <?php if (!empty($search)): ?>
                    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-times me-1"></i> Reset</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['success'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Nama Lengkap</th>
                        <th>Role</th>
                        <th>Dibuat Pada</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                            <td><span class="badge bg-<?= $row['role'] == 'admin' ? 'primary' : 'info' ?>"><?= ucfirst($row['role']) ?></span></td>
                            <td><?= date('d/m/Y H:i:s', strtotime($row['created_at'])) ?></td>
                            <td class="text-center">
                                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                <a href="hapus.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" title="Hapus"
                                   onclick="return confirm('Anda yakin ingin menghapus user ini? Tindakan ini tidak dapat dibatalkan.')"><i class="fas fa-trash-alt"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">Tidak ada user ditemukan.</td>
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
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= htmlspecialchars($search) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>