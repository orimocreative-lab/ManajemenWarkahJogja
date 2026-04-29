<?php
require_once '../../includes/config.php';

if (!isLoggedIn()) {
    redirect('../../login.php');
}


function validateDate($date) {
    if (empty($date)) return false;
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return false;
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}


$filter = [
    'start_date' => isset($_GET['start_date']) ? $_GET['start_date'] : '',
    'end_date' => isset($_GET['end_date']) ? $_GET['end_date'] : '',
    'date_type' => isset($_GET['date_type']) ? $_GET['date_type'] : 'pinjam', // Default: pinjam
    'status' => isset($_GET['status']) ? $_GET['status'] : '',
    'search_query' => isset($_GET['search_query']) ? $_GET['search_query'] : ''
];


if (!validateDate($filter['start_date'])) {
    $filter['start_date'] = '';
}
if (!validateDate($filter['end_date'])) {
    $filter['end_date'] = '';
}


$display_start_date = $filter['start_date'] ? date('d/m/Y', strtotime($filter['start_date'])) : '';
$display_end_date = $filter['end_date'] ? date('d/m/Y', strtotime($filter['end_date'])) : '';

$where_clauses = [];
$params = [];
$types = '';


$date_column = ($filter['date_type'] == 'bon') ? 'tanggal_bon' : 'tanggal_pinjam';

if (!empty($filter['start_date'])) {
    $where_clauses[] = "$date_column >= ?";
    $params[] = $filter['start_date'];
    $types .= 's';
}
if (!empty($filter['end_date'])) {
    $where_clauses[] = "$date_column <= ?";
    $params[] = $filter['end_date']; 
    $types .= 's';
}

if (!empty($filter['status'])) {
    $where_clauses[] = "status = ?";
    $params[] = $filter['status'];
    $types .= 's';
}


if (!empty(trim($filter['search_query']))) {
    $search_term = '%' . trim($filter['search_query']) . '%';
    $where_clauses[] = "(peminjam LIKE ? OR nomor_bon LIKE ? OR nomor_warkah LIKE ?)";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'sss';
}

$where = '';
if (!empty($where_clauses)) {
    $where = " WHERE " . implode(' AND ', $where_clauses);
}

$query = "SELECT * FROM bon_warkah $where ORDER BY tanggal_pinjam DESC";
$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<?php include '../../includes/header.php'; ?>

<h1 class="mt-4 mb-4">Laporan Data Warkah</h1>

<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-filter me-2"></i> Filter Laporan
    </div>
    <div class="card-body">
        <form method="GET" id="filterForm">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label for="date_type" class="form-label">Filter Berdasarkan</label>
                    <select class="form-select" id="date_type" name="date_type">
                        <option value="pinjam" <?= $filter['date_type'] == 'pinjam' ? 'selected' : '' ?>>Tanggal Pinjam</option>
                        <option value="bon" <?= $filter['date_type'] == 'bon' ? 'selected' : '' ?>>Tanggal Data Warkah</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="start_date" class="form-label">Tanggal</label>
                    <input type="text" class="form-control flatpickr" id="start_date" name="start_date" placeholder="dd/mm/yyyy" value="<?= htmlspecialchars($display_start_date) ?>">
                </div>
           
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Semua Status</option>
                        <option value="dipinjam" <?= $filter['status'] == 'dipinjam' ? 'selected' : '' ?>>Dipinjam</option>
                        <option value="dikembalikan" <?= $filter['status'] == 'dikembalikan' ? 'selected' : '' ?>>Tersedia</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="search_query" class="form-label">Cari (Peminjam, No. Data Warkah, No. Warkah)</label>
                    <input type="text" class="form-control" id="search_query" name="search_query" placeholder="Cari..." value="<?= htmlspecialchars($filter['search_query']) ?>">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Filter & Cari</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-table me-2"></i> Data Laporan</h5>
        <a href="cetak.php?<?= http_build_query($filter) ?>" class="btn btn-success btn-sm" target="_blank">
            <i class="fas fa-print me-1"></i> Cetak Laporan
        </a>
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
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
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
                            <td colspan="7" class="text-center py-4">Tidak Ada Data Warkah Ditemukan</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>

flatpickr(".flatpickr", {
    dateFormat: "d/m/Y",
    locale: { 
        firstDayOfWeek: 1, 
        weekdays: {
            shorthand: ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"],
            longhand: ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"]
        },
        months: {
            shorthand: ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"],
            longhand: ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"]
        }
    },
    allowInput: true 
});


function convertToYMD(dateStr) {
    if (!dateStr) return '';
    

    const regex = /^(\d{1,2})\/(\d{1,2})\/(\d{4})$/;
    const match = dateStr.match(regex);
    
    if (match) {
        const day = parseInt(match[1], 10);
        const month = parseInt(match[2], 10);
        const year = parseInt(match[3], 10);
        
        const date = new Date(year, month - 1, day);
        if (date.getFullYear() === year && date.getMonth() === month - 1 && date.getDate() === day) {
            return year + '-' + String(month).padStart(2, '0') + '-' + String(day).padStart(2, '0');
        }
    }
    
    return '';
}

document.getElementById('filterForm').addEventListener('submit', function(e) {
    let hasError = false;
    
    const startDate = document.getElementById('start_date').value;
    const convertedStart = convertToYMD(startDate);
    if (startDate && !convertedStart) {
        alert('Format tanggal mulai tidak valid. Gunakan dd/mm/yyyy (contoh: 15/10/2023)');
        document.getElementById('start_date').focus();
        hasError = true;
    } else if (convertedStart) {
        document.getElementById('start_date').value = convertedStart;
    }
    
    const endDate = document.getElementById('end_date').value;
    const convertedEnd = convertToYMD(endDate);
    if (endDate && !convertedEnd) {
        alert('Format tanggal selesai tidak valid. Gunakan dd/mm/yyyy (contoh: 15/10/2023)');
        document.getElementById('end_date').focus();
        hasError = true;
    } else if (convertedEnd) {
        document.getElementById('end_date').value = convertedEnd;
    }
    
    if (hasError) {
        e.preventDefault(); // Hentikan submit jika ada error
    }
});
</script>

<?php include '../../includes/footer.php'; ?>