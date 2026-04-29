<?php
require_once '../../includes/config.php';
require_once '../../includes/data_yogyakarta.php';

if (!isLoggedIn()) {
    redirect('../../login.php');
}

// Search functionality - Enhanced
$search_nomor = '';
$search_nomor_hak = '';
$search_kelurahan = '';
$search_kecamatan = '';
$search_peminjam = '';
$search_pemegang_hak = '';
$search_status = '';
$search_tanggal_from = '';
$search_tanggal_to = '';

if (isset($_GET['search_nomor'])) {
    $search_nomor = $conn->real_escape_string($_GET['search_nomor']);
}
if (isset($_GET['search_nomor_hak'])) {
    $search_nomor_hak = $conn->real_escape_string($_GET['search_nomor_hak']);
}
if (isset($_GET['search_kelurahan'])) {
    $search_kelurahan = $conn->real_escape_string($_GET['search_kelurahan']);
}
if (isset($_GET['search_kecamatan'])) {
    $search_kecamatan = $conn->real_escape_string($_GET['search_kecamatan']);
}
if (isset($_GET['search_peminjam'])) {
    $search_peminjam = $conn->real_escape_string($_GET['search_peminjam']);
}
if (isset($_GET['search_pemegang_hak'])) {
    $search_pemegang_hak = $conn->real_escape_string($_GET['search_pemegang_hak']);
}
if (isset($_GET['search_status'])) {
    $search_status = $conn->real_escape_string($_GET['search_status']);
}
if (isset($_GET['search_tanggal_from'])) {
    $search_tanggal_from = $conn->real_escape_string($_GET['search_tanggal_from']);
}
if (isset($_GET['search_tanggal_to'])) {
    $search_tanggal_to = $conn->real_escape_string($_GET['search_tanggal_to']);
}

$where_conditions = [];

if (!empty($search_nomor)) {
    $where_conditions[] = "nomor_bon LIKE '%$search_nomor%'";
}
if (!empty($search_nomor_hak)) {
    $where_conditions[] = "nomor_hak LIKE '%$search_nomor_hak%'";
}
if (!empty($search_kelurahan)) {
    $where_conditions[] = "kelurahan LIKE '%$search_kelurahan%'";
}
if (!empty($search_kecamatan)) {
    $where_conditions[] = "kecamatan LIKE '%$search_kecamatan%'";
}
if (!empty($search_peminjam)) {
    $where_conditions[] = "peminjam LIKE '%$search_peminjam%'";
}
if (!empty($search_pemegang_hak)) {
    $where_conditions[] = "pemegang_hak LIKE '%$search_pemegang_hak%'";
}
if (!empty($search_status)) {
    $where_conditions[] = "status = '$search_status'";
}
if (!empty($search_tanggal_from)) {
    $where_conditions[] = "tanggal_pinjam >= '$search_tanggal_from'";
}
if (!empty($search_tanggal_to)) {
    $where_conditions[] = "tanggal_pinjam <= '$search_tanggal_to'";
}

$where = '';
if (!empty($where_conditions)) {
    $where = " WHERE " . implode(" AND ", $where_conditions);
}

// Pagination
$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

$total_query = "SELECT COUNT(*) FROM bon_warkah $where";
$total_rows = $conn->query($total_query)->fetch_row()[0];
$total_pages = ceil($total_rows / $per_page);

// Get data
$query = "SELECT * FROM bon_warkah $where ORDER BY id DESC LIMIT $offset, $per_page";
$result = $conn->query($query);
?>

<?php include '../../includes/header.php'; ?>

<h1 class="mt-4 mb-4">Inventarisir Data Warkah</h1>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i> Daftar Data Warkah</h5>
        <div>
            <a href="tambah.php" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Tambah Baru
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Advanced Search Form -->
        <form method="GET" class="mb-4" id="search-form">
            <div class="card bg-light p-3">
                <h6 class="mb-3"><i class="fas fa-filter me-2"></i> Pencarian Lanjutan</h6>
                
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Nomor Data Warkah</label>
                        <input type="text" class="form-control" name="search_nomor" placeholder="Cari nomor..." value="<?= htmlspecialchars($search_nomor) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nomor Hak</label>
                        <input type="text" class="form-control" name="search_nomor_hak" placeholder="Cari nomor hak..." value="<?= htmlspecialchars($search_nomor_hak) ?>">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Kecamatan</label>
                        <select class="form-select" name="search_kecamatan">
                            <option value="">-- Semua Kecamatan --</option>
                            <?php foreach (getKecamatan() as $kec): ?>
                                <option value="<?= htmlspecialchars($kec) ?>" <?= $search_kecamatan == $kec ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kec) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kelurahan</label>
                        <select class="form-select" name="search_kelurahan" id="search-kelurahan">
                            <option value="">-- Semua Kelurahan --</option>
                            <?php
                                if (!empty($search_kecamatan) && isset($yogyakarta_data[$search_kecamatan])) {
                                    foreach ($yogyakarta_data[$search_kecamatan] as $kel) {
                                        $selected = ($search_kelurahan == $kel) ? 'selected' : '';
                                        echo "<option value=\"" . htmlspecialchars($kel) . "\" $selected>" . htmlspecialchars($kel) . "</option>";
                                    }
                                }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Peminjam</label>
                        <input type="text" class="form-control" name="search_peminjam" placeholder="Cari peminjam..." value="<?= htmlspecialchars($search_peminjam) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Pemegang Hak</label>
                        <input type="text" class="form-control" name="search_pemegang_hak" placeholder="Cari pemegang hak..." value="<?= htmlspecialchars($search_pemegang_hak ?? '') ?>">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="search_status">
                            <option value="">-- Semua Status --</option>
                            <option value="dipinjam" <?= $search_status == 'dipinjam' ? 'selected' : '' ?>>Dipinjam</option>
                            <option value="dikembalikan" <?= $search_status == 'dikembalikan' ? 'selected' : '' ?>>Tersedia</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> Cari</button>
                    <a href="index.php" class="btn btn-secondary"><i class="fas fa-redo me-1"></i> Reset</a>
                </div>
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
                        <th>Nomor</th>
                        <th>Nomor Hak</th>
                        <th>Kelurahan</th>
                        <th>Kecamatan</th>
                        <th>Lokasi Warkah</th>
                        <th>Pemegang Hak</th>
                        <th>Status</th>
                        <th>Peminjam</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nomor_bon']) ?></td>
                            <td><?= htmlspecialchars($row['nomor_hak']) ?></td>
                            <td><?= htmlspecialchars($row['kelurahan']) ?></td>
                            <td><?= htmlspecialchars($row['kecamatan']) ?></td>
                            <td><?= htmlspecialchars($row['lokasi_warkah']) ?></td>
                            <td><?= htmlspecialchars($row['pemegang_hak'] ?? '') ?></td>
                            <td>
                                <span class="badge bg-<?= $row['status'] == 'dipinjam' ? 'warning' : 'success' ?>">
                                    <?= $row['status'] == 'dipinjam' ? 'Dipinjam' : 'Tersedia' ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['peminjam']) ?></td>
                            <td class="text-center">
                                <a href="detail.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info" title="Lihat Detail"><i class="fas fa-eye"></i></a>
                                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                <?php if (isAdmin()): ?>
                                    <a href="hapus.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" title="Hapus"
                                       onclick="return confirm('Anda yakin ingin menghapus data warkah ini?')"><i class="fas fa-trash-alt"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-4">Tidak ada data warkah ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): 
            $query_params = http_build_query([
                'search_nomor' => $search_nomor,
                'search_nomor_hak' => $search_nomor_hak,
                'search_kelurahan' => $search_kelurahan,
                'search_kecamatan' => $search_kecamatan,
                'search_peminjam' => $search_peminjam,
                'search_status' => $search_status,
                'search_tanggal_from' => $search_tanggal_from,
                'search_tanggal_to' => $search_tanggal_to
            ]);
        ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&<?= $query_params ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript for Dynamic Kelurahan Selection -->
<script>
const yogyakartaData = <?php echo json_encode($yogyakarta_data); ?>;

document.querySelector('select[name="search_kecamatan"]').addEventListener('change', function() {
    const selectedKecamatan = this.value;
    const kelurahanSelect = document.getElementById('search-kelurahan');
    const currentKelurahan = '<?= htmlspecialchars($search_kelurahan) ?>';
    
    kelurahanSelect.innerHTML = '<option value="">-- Semua Kelurahan --</option>';
    
    if (selectedKecamatan && yogyakartaData[selectedKecamatan]) {
        yogyakartaData[selectedKecamatan].forEach(kel => {
            const option = document.createElement('option');
            option.value = kel;
            option.text = kel;
            if (kel === currentKelurahan) {
                option.selected = true;
            }
            kelurahanSelect.appendChild(option);
        });
    }
});
</script>

<?php include '../../includes/footer.php'; ?>