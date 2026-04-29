<?php
require_once '../../includes/config.php';

if (!isLoggedIn()) {
    redirect('../../login.php');
}

// Ambil filter dari URL
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$status = $_GET['status'] ?? '';
$search_query = $_GET['search_query'] ?? ''; // Tambahkan search_query

// Validasi tanggal sederhana
if ($start_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) $start_date = '';
if ($end_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) $end_date = '';

// Siapkan query dengan filter
$where = [];
$params = [];
$types = '';

if ($start_date) {
    $where[] = "tanggal_pinjam >= ?";
    $params[] = $start_date;
    $types .= 's';
}
if ($end_date) {
    $where[] = "tanggal_pinjam <= ?";
    $params[] = $end_date;
    $types .= 's';
}
if ($status && in_array($status, ['dipinjam', 'dikembalikan'])) {
    $where[] = "status = ?";
    $params[] = $status;
    $types .= 's';
}

// Tambahkan kondisi pencarian
if (!empty($search_query)) {
    $search_term = '%' . $search_query . '%';
    $where[] = "(peminjam LIKE ? OR nomor_bon LIKE ? OR nomor_warkah LIKE ?)";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'sss';
}

$sql = "SELECT * FROM bon_warkah";
if ($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY tanggal_pinjam DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Cetak Laporan Data Warkah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 20px;
            color: #343a40;
        }
        h2 {
            margin-bottom: 25px;
            color: #004085;
            font-weight: 700;
            text-align: center;
        }
        p {
            font-size: 0.95rem;
            margin-bottom: 5px;
        }
        table {
            font-size: 13px;
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            border: 1px solid #dee2e6;
            padding: 8px 12px;
            text-align: left;
            vertical-align: top;
        }
        table th {
            background-color: #e9ecef;
            font-weight: 600;
            color: #343a40;
        }
        table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            padding: 0.4em 0.6em;
            font-size: 0.75em;
            font-weight: 600;
            border-radius: 0.25rem;
            display: inline-block;
        }
        .badge.bg-warning {
            background-color: #ffc107;
            color: #343a40;
        }
        .badge.bg-success {
            background-color: #28a745;
            color: #fff;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .container {
                width: auto;
                margin: 0;
                padding: 0;
            }
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            thead {
                display: table-header-group;
            }
            tfoot {
                display: table-footer-group;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center">Laporan Data Warkah</h2>
    <p>
        <strong>Periode:</strong> <?= $start_date ? date('d/m/Y', strtotime($start_date)) : 'Semua Tanggal' ?> s/d <?= $end_date ? date('d/m/Y', strtotime($end_date)) : 'Semua Tanggal' ?><br />
        <strong>Status:</strong> <?= $status ? ($status == 'dipinjam' ? 'Dipinjam' : 'Tersedia') : 'Semua Status' ?><br />
        <strong>Pencarian:</strong> <?= $search_query ? htmlspecialchars($search_query) : 'Tidak Ada' ?>
    </p>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>No.</th>
                <th>Nomor Data Warkah</th>
                <th>Tanggal Data Warkah</th>
                <th>Peminjam</th>
                <th>Unit Kerja</th>
                <th>Nomor Warkah</th>
                <th>Jenis Warkah</th>
                <th>Lokasi Warkah</th>
                <th>Tanggal Pinjam</th>
                <th>Tanggal Kembali</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['nomor_bon']) ?></td>
                    <td><?= date('d/m/Y', strtotime($row['tanggal_bon'])) ?></td>
                    <td><?= htmlspecialchars($row['peminjam']) ?></td>
                    <td><?= htmlspecialchars($row['unit_kerja']) ?></td>
                    <td><?= htmlspecialchars($row['nomor_warkah']) ?></td>
                    <td><?= htmlspecialchars($row['jenis_warkah']) ?></td>
                    <td><?= htmlspecialchars($row['lokasi_warkah']) ?></td>
                    <td><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])) ?></td>
                    <td><?= $row['tanggal_kembali'] ? date('d/m/Y', strtotime($row['tanggal_kembali'])) : '-' ?></td>
                    <td>
                        <span class="badge bg-<?= $row['status'] == 'dipinjam' ? 'warning' : 'success' ?>">
                            <?= $row['status'] == 'dipinjam' ? 'Dipinjam' : 'Tersedia' ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($row['keterangan']) ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="12" class="text-center">Tidak ada data ditemukan.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="text-center no-print mt-4">
        <button onclick="window.print()" class="btn btn-primary me-2"><i class="fas fa-print me-1"></i> Cetak</button>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
    </div>
</div>
</body>
</html>