<?php
/**
 * Reminder Module - Cek berkas yang belum kembali
 * Fungsi untuk mengecek dan menampilkan notifikasi berkas yang sudah melewati tanggal kembali
 */

function getOverdueItems($conn, $user_id = null) {
    $query = "SELECT id, nomor_bon, peminjam, unit_kerja, nomor_warkah, jenis_warkah, lokasi_warkah, tanggal_pinjam, tanggal_kembali, keterangan, status 
              FROM bon_warkah 
              WHERE status = 'dipinjam' 
              AND tanggal_kembali IS NOT NULL 
              AND DATE(tanggal_kembali) < CURDATE()
              ORDER BY tanggal_kembali ASC";
    
    $result = $conn->query($query);
    $items = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
    }
    
    return $items;
}

function getUpcomingItems($conn, $days = 7) {
    $query = "SELECT id, nomor_bon, peminjam, unit_kerja, nomor_warkah, jenis_warkah, lokasi_warkah, tanggal_pinjam, tanggal_kembali, keterangan, status 
              FROM bon_warkah 
              WHERE status = 'dipinjam' 
              AND tanggal_kembali IS NOT NULL 
              AND DATE(tanggal_kembali) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL $days DAY)
              ORDER BY tanggal_kembali ASC";
    
    $result = $conn->query($query);
    $items = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
    }
    
    return $items;
}

function displayReminderPopup($overdue, $upcoming) {
    if (empty($overdue) && empty($upcoming)) {
        return '';
    }
    
    ob_start();
    ?>
    <div id="reminderModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white" id="reminderHeader">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php if (!empty($overdue)): ?>
                            Pengingat: Berkas Belum Tersedia
                        <?php else: ?>
                            Pengingat: Berkas akan Jatuh Tempo
                        <?php endif; ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <style>
                        .accordion-button:not(.collapsed) {
                            background-color: #f8f9fa;
                            box-shadow: none;
                        }
                        .accordion-button:focus {
                            box-shadow: none;
                            border-color: #dee2e6;
                        }
                        .accordion-item {
                            border: 1px solid #dee2e6;
                            margin-bottom: 8px;
                        }
                    </style>
                    <?php if (!empty($overdue)): ?>
                        <div class="mb-4">
                            <h6 class="text-danger mb-3">
                                <i class="fas fa-times-circle me-2"></i>
                                <strong><?= count($overdue) ?> Berkas Sudah Melewati Tanggal Kembali:</strong>
                            </h6>
                            <div class="accordion" id="accordionOverdue">
                                <?php foreach ($overdue as $index => $item): ?>
                                    <?php 
                                        $date_back = new DateTime($item['tanggal_kembali']);
                                        $today = new DateTime();
                                        $diff = $today->diff($date_back);
                                        $days_late = $diff->days;
                                    ?>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingOverdue<?= $index ?>">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOverdue<?= $index ?>" aria-expanded="false" aria-controls="collapseOverdue<?= $index ?>">
                                                <span class="me-3"><strong><?= htmlspecialchars($item['nomor_bon']) ?></strong></span>
                                                <span class="me-3"><?= htmlspecialchars($item['peminjam']) ?></span>
                                                <span class="badge bg-danger"><?= $days_late ?> hari tertunda</span>
                                            </button>
                                        </h2>
                                        <div id="collapseOverdue<?= $index ?>" class="accordion-collapse collapse" aria-labelledby="headingOverdue<?= $index ?>" data-bs-parent="#accordionOverdue">
                                            <div class="accordion-body p-0">
                                                <table class="table table-borderless table-sm mb-0">
                                                    <tbody>
                                                        <tr>
                                                            <td style="width: 35%;" class="text-muted fw-bold">No. Bon</td>
                                                            <td><?= htmlspecialchars($item['nomor_bon']) ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted fw-bold">Peminjam</td>
                                                            <td><?= htmlspecialchars($item['peminjam']) ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted fw-bold">Unit Kerja</td>
                                                            <td><?= htmlspecialchars($item['unit_kerja'] ?? '-') ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted fw-bold">No. Warkah</td>
                                                            <td><?= htmlspecialchars($item['nomor_warkah']) ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted fw-bold">Jenis Warkah</td>
                                                            <td><?= htmlspecialchars($item['jenis_warkah'] ?? '-') ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted fw-bold">Lokasi Warkah</td>
                                                            <td><?= htmlspecialchars($item['lokasi_warkah'] ?? '-') ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted fw-bold">Tanggal Pinjam</td>
                                                            <td><?= date('d/m/Y', strtotime($item['tanggal_pinjam'])) ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted fw-bold">Tanggal Kembali</td>
                                                            <td>
                                                                <span class="badge bg-danger"><?= date('d/m/Y', strtotime($item['tanggal_kembali'])) ?></span>
                                                                <small class="text-danger d-block mt-1">(Tertunda <?= $days_late ?> hari)</small>
                                                            </td>
                                                        </tr>
                                                        <?php if (!empty($item['keterangan'])): ?>
                                                        <tr>
                                                            <td class="text-muted fw-bold">Keterangan</td>
                                                            <td><?= htmlspecialchars($item['keterangan']) ?></td>
                                                        </tr>
                                                        <?php endif; ?>
                                                        <tr>
                                                            <td class="text-muted fw-bold">Aksi</td>
                                                            <td>
                                                                <a href="<?= BASE_URL ?>Modules/Bon/detail.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-primary me-2" target="_blank">
                                                                    <i class="fas fa-eye me-1"></i>Lihat Detail
                                                                </a>
                                                                <a href="<?= BASE_URL ?>Modules/Bon/edit.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-warning" target="_blank">
                                                                    <i class="fas fa-edit me-1"></i>Edit
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($upcoming)): ?>
                        <div class="mb-4">
                            <h6 class="text-warning mb-3">
                                <i class="fas fa-clock me-2"></i>
                                <strong><?= count($upcoming) ?> Berkas akan Jatuh Tempo dalam 7 hari ke depan:</strong>
                            </h6>
                            <div class="accordion" id="accordionUpcoming">
                                <?php foreach ($upcoming as $index => $item): ?>
                                    <?php 
                                        $date_back = new DateTime($item['tanggal_kembali']);
                                        $today = new DateTime();
                                        $diff = $today->diff($date_back);
                                        $days_left = $diff->days;
                                    ?>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingUpcoming<?= $index ?>">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseUpcoming<?= $index ?>" aria-expanded="false" aria-controls="collapseUpcoming<?= $index ?>">
                                                <span class="me-3"><strong><?= htmlspecialchars($item['nomor_bon']) ?></strong></span>
                                                <span class="me-3"><?= htmlspecialchars($item['peminjam']) ?></span>
                                                <span class="badge bg-warning text-dark"><?= $days_left ?> hari lagi</span>
                                            </button>
                                        </h2>
                                        <div id="collapseUpcoming<?= $index ?>" class="accordion-collapse collapse" aria-labelledby="headingUpcoming<?= $index ?>" data-bs-parent="#accordionUpcoming">
                                            <div class="accordion-body p-0">
                                                <table class="table table-borderless table-sm mb-0">
                                                    <tbody>
                                                        <tr>
                                                            <td style="width: 35%;" class="text-muted fw-bold">No. Bon</td>
                                                            <td><?= htmlspecialchars($item['nomor_bon']) ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted fw-bold">Peminjam</td>
                                                            <td><?= htmlspecialchars($item['peminjam']) ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted fw-bold">Unit Kerja</td>
                                                            <td><?= htmlspecialchars($item['unit_kerja'] ?? '-') ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted fw-bold">No. Warkah</td>
                                                            <td><?= htmlspecialchars($item['nomor_warkah']) ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted fw-bold">Jenis Warkah</td>
                                                            <td><?= htmlspecialchars($item['jenis_warkah'] ?? '-') ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted fw-bold">Lokasi Warkah</td>
                                                            <td><?= htmlspecialchars($item['lokasi_warkah'] ?? '-') ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted fw-bold">Tanggal Pinjam</td>
                                                            <td><?= date('d/m/Y', strtotime($item['tanggal_pinjam'])) ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted fw-bold">Tanggal Kembali</td>
                                                            <td>
                                                                <span class="badge bg-warning text-dark"><?= date('d/m/Y', strtotime($item['tanggal_kembali'])) ?></span>
                                                                <small class="text-warning d-block mt-1">(<?= $days_left ?> hari lagi)</small>
                                                            </td>
                                                        </tr>
                                                        <?php if (!empty($item['keterangan'])): ?>
                                                        <tr>
                                                            <td class="text-muted fw-bold">Keterangan</td>
                                                            <td><?= htmlspecialchars($item['keterangan']) ?></td>
                                                        </tr>
                                                        <?php endif; ?>
                                                        <tr>
                                                            <td class="text-muted fw-bold">Aksi</td>
                                                            <td>
                                                                <a href="<?= BASE_URL ?>Modules/Bon/detail.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-primary me-2" target="_blank">
                                                                    <i class="fas fa-eye me-1"></i>Lihat Detail
                                                                </a>
                                                                <a href="<?= BASE_URL ?>Modules/Bon/edit.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-warning" target="_blank">
                                                                    <i class="fas fa-edit me-1"></i>Edit
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <a href="<?= BASE_URL ?>Modules/Bon/index.php" class="btn btn-primary">
                        <i class="fas fa-list me-1"></i>Lihat Semua Bon
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const reminderModal = new bootstrap.Modal(document.getElementById('reminderModal'));
            reminderModal.show();
        });
    </script>
    <?php
    return ob_get_clean();
}
?>
