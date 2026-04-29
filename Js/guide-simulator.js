// ========== INTERACTIVE GUIDE SIMULATOR ==========

class GuideSimulator {
    constructor() {
        this.simulationCount = {
            'add': 0,
            'bon': 0,
            'report': 0,
            'audit': 0
        };
    }

    // === DATA WARKAH SIMULATIONS ===
    simulateAddData() {
        const modalHtml = `
            <div class="modal fade" id="simulateAddModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Simulasi: Tambah Data Warkah Baru</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="simulateDataForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label"><strong>Nomor Hak</strong></label>
                                        <input type="text" class="form-control" placeholder="Contoh: 123.456" value="789.012" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label"><strong>Nomor Warkah</strong></label>
                                        <input type="text" class="form-control" placeholder="Auto-generate" value="BON-2026-0051" disabled>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label"><strong>Kelurahan</strong></label>
                                        <select class="form-select" required>
                                            <option value="">-- Pilih Kelurahan --</option>
                                            <option selected>Pondok Labu</option>
                                            <option>Jagakarsa</option>
                                            <option>Mampang Prapatan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label"><strong>Kecamatan</strong></label>
                                        <select class="form-select" required>
                                            <option value="">-- Pilih Kecamatan --</option>
                                            <option selected>Jakarta Selatan</option>
                                            <option>Jakarta Pusat</option>
                                            <option>Jakarta Utara</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label"><strong>Lokasi Warkah</strong></label>
                                    <input type="text" class="form-control" placeholder="Contoh: Rak A-03, Box 15" value="Rak C-01, Box 7" required>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label"><strong>Kategori</strong></label>
                                        <select class="form-select" required>
                                            <option value="">-- Pilih Kategori --</option>
                                            <option selected>Sertifikat Tanah</option>
                                            <option>Akte Perubahan</option>
                                            <option>Surat Keterangan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label"><strong>Status</strong></label>
                                        <select class="form-select" required>
                                            <option selected>Tersedia</option>
                                            <option>Dipinjam</option>
                                            <option>Rusak</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label"><strong>Keterangan</strong></label>
                                    <textarea class="form-control" rows="3" placeholder="Catatan tambahan...">Berkas lengkap, kondisi baik</textarea>
                                </div>

                                <div class="alert alert-info alert-sm">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <small>Data ini adalah simulasi. Tidak akan disimpan ke database sebenarnya.</small>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-primary" onclick="simulator.submitAddData()">
                                <i class="fas fa-check me-2"></i>Simulasi Simpan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('simulateAddModal'));
        modal.show();

        document.getElementById('simulateAddModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }

    submitAddData() {
        this.simulationCount['add']++;
        const form = document.getElementById('simulateDataForm');
        const nomorHak = form.querySelector('input[placeholder="Contoh: 123.456"]').value;
        const nomorBon = form.querySelector('input[disabled]').value;
        const kelurahan = form.querySelector('select').value;
        const lokasi = form.querySelector('input[placeholder="Contoh: Rak A-03, Box 15"]').value;

        // Hide modal
        bootstrap.Modal.getInstance(document.getElementById('simulateAddModal')).hide();

        // Show success animation
        this.showSuccessAnimation('Data Warkah Tersimpan', `
            <div class="success-details">
                <p><strong>Nomor Bon:</strong> ${nomorBon}</p>
                <p><strong>Nomor Hak:</strong> ${nomorHak}</p>
                <p><strong>Kelurahan:</strong> ${kelurahan}</p>
                <p><strong>Lokasi:</strong> ${lokasi}</p>
                <p><strong>Waktu:</strong> ${new Date().toLocaleString('id-ID')}</p>
                <hr>
                <p><small>✓ Data berhasil disimpan</small></p>
                <p><small>✓ Audit trail telah dicatat</small></p>
                <p><small>✓ Pencarian terindeks otomatis</small></p>
            </div>
        `);
    }

    simulateEditData() {
        const modalHtml = `
            <div class="modal fade" id="simulateEditModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-warning text-dark">
                            <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Simulasi: Edit Data Warkah</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="simulateEditForm">
                                <div class="alert alert-info">
                                    <i class="fas fa-search me-2"></i>Memilih data: BON-2026-0048 (Nomor Hak: 765.432)
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label"><strong>Nomor Hak</strong></label>
                                        <input type="text" class="form-control" value="765.432" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label"><strong>Nomor Bon</strong></label>
                                        <input type="text" class="form-control" value="BON-2026-0048" readonly>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label"><strong>Lokasi Warkah</strong> (diubah)</label>
                                        <div style="display: flex; gap: 10px; align-items: center;">
                                            <input type="text" class="form-control text-decoration-line-through" value="Rak B-02, Box 5" readonly style="opacity: 0.6;">
                                            <span class="text-muted" style="white-space: nowrap;">→</span>
                                            <input type="text" class="form-control" value="Rak B-05, Box 8" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label"><strong>Status</strong> (diubah)</label>
                                        <div style="display: flex; gap: 10px; align-items: center;">
                                            <select class="form-select" disabled style="opacity: 0.6;">
                                                <option selected>Tersedia</option>
                                            </select>
                                            <span class="text-muted" style="white-space: nowrap;">→</span>
                                            <select class="form-select" required>
                                                <option>Tersedia</option>
                                                <option selected>Dipinjam</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label"><strong>Keterangan</strong></label>
                                    <textarea class="form-control" rows="2" placeholder="Catatan perubahan...">Pemindahan lokasi penyimpanan, dipinjam oleh Admin</textarea>
                                </div>

                                <div class="alert alert-secondary">
                                    <strong>Informasi Perubahan:</strong>
                                    <ul style="margin-bottom: 0; padding-left: 20px; margin-top: 8px;">
                                        <li>Lokasi: Rak B-02, Box 5 → Rak B-05, Box 8</li>
                                        <li>Status: Tersedia → Dipinjam</li>
                                        <li>Diubah oleh: Admin Sistem</li>
                                        <li>Waktu perubahan sebelumnya: 2026-01-28 14:30:22</li>
                                    </ul>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-warning" onclick="simulator.submitEditData()">
                                <i class="fas fa-save me-2"></i>Simulasi Ubah
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('simulateEditModal'));
        modal.show();

        document.getElementById('simulateEditModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }

    submitEditData() {
        const form = document.getElementById('simulateEditForm');
        const lokasiBaru = form.querySelector('input[value="Rak B-05, Box 8"]').value;
        const statusBaru = form.querySelector('select:last-of-type').value;

        bootstrap.Modal.getInstance(document.getElementById('simulateEditModal')).hide();

        this.showSuccessAnimation('Data Warkah Diperbarui', `
            <div class="success-details">
                <p><strong>Nomor Bon:</strong> BON-2026-0048</p>
                <p><strong>Perubahan diterapkan:</strong></p>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Lokasi: Rak B-05, Box 8</li>
                    <li>Status: ${statusBaru}</li>
                </ul>
                <p><strong>Waktu:</strong> ${new Date().toLocaleString('id-ID')}</p>
                <hr>
                <p><small>✓ Data berhasil diperbarui</small></p>
                <p><small>✓ Perubahan tercatat dalam audit trail</small></p>
                <p><small>✓ Versi sebelumnya tersimpan di riwayat</small></p>
            </div>
        `);
    }

    simulateDeleteData() {
        const modalHtml = `
            <div class="modal fade" id="simulateDeleteModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title"><i class="fas fa-trash me-2"></i>Simulasi: Hapus Data Warkah</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Konfirmasi Penghapusan</strong>
                            </div>
                            
                            <p>Apakah Anda yakin ingin menghapus data berikut?</p>
                            
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;">
                                <p><strong>Nomor Bon:</strong> BON-2026-0037</p>
                                <p><strong>Nomor Hak:</strong> 456.789</p>
                                <p><strong>Kelurahan:</strong> Kramat Jati</p>
                                <p style="margin: 0;"><strong>Status:</strong> <span class="badge bg-success">Tersedia</span></p>
                            </div>

                            <div class="alert alert-info">
                                <small><i class="fas fa-info-circle me-2"></i>Data yang dihapus dapat dipulihkan dalam 30 hari melalui fitur restore backup.</small></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-danger" onclick="simulator.confirmDeleteData()">
                                <i class="fas fa-check me-2"></i>Ya, Hapus Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('simulateDeleteModal'));
        modal.show();

        document.getElementById('simulateDeleteModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }

    confirmDeleteData() {
        bootstrap.Modal.getInstance(document.getElementById('simulateDeleteModal')).hide();
        
        this.showSuccessAnimation('Data Warkah Dihapus', `
            <div class="success-details">
                <p><strong>Nomor Bon:</strong> BON-2026-0037</p>
                <p><strong>Status:</strong> Dihapus</p>
                <p><strong>Waktu:</strong> ${new Date().toLocaleString('id-ID')}</p>
                <hr>
                <p><small>✓ Data berhasil dihapus</small></p>
                <p><small>✓ Data dapat dipulihkan dalam 30 hari</small></p>
                <p><small>✓ Penghapusan tercatat dalam audit trail sebagai "DELETE"</small></p>
            </div>
        `);
    }

    // === BON PEMINJAMAN SIMULATIONS ===
    simulateCreateBon() {
        const modalHtml = `
            <div class="modal fade" id="simulateBonModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title"><i class="fas fa-file-contract me-2"></i>Simulasi: Buat Bon Peminjaman</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="simulateBonForm">
                                <fieldset>
                                    <legend class="h6 mb-3">Data Peminjam</legend>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label"><strong>Nama Peminjam</strong></label>
                                            <input type="text" class="form-control" placeholder="Nama lengkap" value="Budi Santoso" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label"><strong>No. Identitas (KTP/SIM)</strong></label>
                                            <input type="text" class="form-control" placeholder="Nomor identitas" value="3174012345678901" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Alamat</strong></label>
                                        <input type="text" class="form-control" placeholder="Alamat lengkap" value="Jl. Gatot Subroto No. 45, Jakarta Selatan" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"><strong>No. Telepon</strong></label>
                                        <input type="tel" class="form-control" placeholder="+62 8xx xxxx xxxx" value="+62 812 3456 7890" required>
                                    </div>
                                </fieldset>

                                <hr>

                                <fieldset>
                                    <legend class="h6 mb-3">Data Peminjaman</legend>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label"><strong>Pilih Warkah</strong></label>
                                            <select class="form-select" required>
                                                <option selected>BON-2026-0045 (Nomor Hak: 654.321)</option>
                                                <option>BON-2026-0044 (Nomor Hak: 543.210)</option>
                                                <option>BON-2026-0043 (Nomor Hak: 432.109)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label"><strong>Tgl Peminjaman</strong></label>
                                            <input type="date" class="form-control" value="2026-01-30" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Estimasi Tgl Kembali</strong></label>
                                        <input type="date" class="form-control" value="2026-02-13" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Keperluan Peminjaman</strong></label>
                                        <textarea class="form-control" rows="2" required>Untuk pengurusan perpanjangan sertifikat tanah</textarea>
                                    </div>
                                </fieldset>

                                <div class="alert alert-info alert-sm">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <small>Bon peminjaman ini akan dicetak dan ditandatangani oleh kedua belah pihak.</small>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-primary" onclick="simulator.submitCreateBon()">
                                <i class="fas fa-check me-2"></i>Simulasi Buat Bon
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('simulateBonModal'));
        modal.show();

        document.getElementById('simulateBonModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }

    submitCreateBon() {
        const form = document.getElementById('simulateBonForm');
        const peminjam = form.querySelector('input[placeholder="Nama lengkap"]').value;
        const warkah = form.querySelector('select').value;
        const tglKembali = form.querySelector('input[value="2026-02-13"]').value;
        const bonNumber = 'BON-PJMN-2026-' + String(Math.floor(Math.random() * 10000)).padStart(5, '0');

        bootstrap.Modal.getInstance(document.getElementById('simulateBonModal')).hide();

        this.showSuccessAnimation('Bon Peminjaman Dibuat', `
            <div class="success-details">
                <p><strong>Nomor Bon Peminjaman:</strong> ${bonNumber}</p>
                <p><strong>Peminjam:</strong> ${peminjam}</p>
                <p><strong>Warkah:</strong> ${warkah}</p>
                <p><strong>Estimasi Kembali:</strong> ${new Date(tglKembali).toLocaleDateString('id-ID')}</p>
                <p><strong>Waktu Pembuatan:</strong> ${new Date().toLocaleString('id-ID')}</p>
                <hr>
                <p><small>✓ Bon peminjaman berhasil dibuat</small></p>
                <p><small>✓ Status warkah berubah menjadi "Dipinjam"</small></p>
                <p><small>✓ Pengingat otomatis akan dikirim 3 hari sebelum jatuh tempo</small></p>
            </div>
        `);
    }

    simulateReturnData() {
        const modalHtml = `
            <div class="modal fade" id="simulateReturnModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title"><i class="fas fa-undo me-2"></i>Simulasi: Pengembalian Warkah</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="fas fa-check-circle me-2"></i>Data bon peminjaman ditemukan
                            </div>

                            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;">
                                <p><strong>Nomor Bon:</strong> BON-PJMN-2026-08234</p>
                                <p><strong>Peminjam:</strong> Siti Nurhaliza</p>
                                <p><strong>Warkah:</strong> BON-2026-0041</p>
                                <p><strong>Tgl Peminjaman:</strong> 2026-01-15</p>
                                <p><strong>Estimasi Kembali:</strong> 2026-01-29</p>
                                <p style="margin: 0;"><strong>Status:</strong> <span class="badge bg-warning">Tertunda 1 hari</span></p>
                            </div>

                            <form id="simulateReturnForm">
                                <div class="mb-3">
                                    <label class="form-label"><strong>Tanggal Pengembalian</strong></label>
                                    <input type="date" class="form-control" value="2026-01-30" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><strong>Kondisi Warkah</strong></label>
                                    <select class="form-select" required>
                                        <option selected>Baik</option>
                                        <option>Kurang Baik (Ada Kerusakan Minor)</option>
                                        <option>Rusak Parah</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><strong>Catatan</strong></label>
                                    <textarea class="form-control" rows="2" placeholder="Catatan kondisi atau keluhan">Warkah dalam kondisi baik, tidak ada kerusakan</textarea>
                                </div>
                            </form>

                            <div class="alert alert-warning">
                                <small><i class="fas fa-info-circle me-2"></i>Pengembalian tertunda 1 hari dari estimasi. Denda otomatis (jika ada) akan diterapkan.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-success" onclick="simulator.submitReturnData()">
                                <i class="fas fa-check me-2"></i>Simulasi Tandai Tersedia
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('simulateReturnModal'));
        modal.show();

        document.getElementById('simulateReturnModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }

    submitReturnData() {
        const form = document.getElementById('simulateReturnForm');
        const tglKembali = form.querySelector('input[type="date"]').value;
        const kondisi = form.querySelector('select').value;

        bootstrap.Modal.getInstance(document.getElementById('simulateReturnModal')).hide();

        this.showSuccessAnimation('Warkah Diterima Kembali', `
            <div class="success-details">
                <p><strong>Nomor Bon:</strong> BON-PJMN-2026-08234</p>
                <p><strong>Peminjam:</strong> Siti Nurhaliza</p>
                <p><strong>Tgl Pengembalian:</strong> ${new Date(tglKembali).toLocaleDateString('id-ID')}</p>
                <p><strong>Kondisi:</strong> ${kondisi}</p>
                <hr>
                <p><small>✓ Pengembalian berhasil dicatat</small></p>
                <p><small>✓ Status warkah berubah menjadi "Tersedia"</small></p>
                <p><small>✓ Denda keterlambatan (Rp 50.000) telah dihitung</small></p>
            </div>
        `);
    }

    // === LAPORAN SIMULATIONS ===
    simulateGenerateReport() {
        const modalHtml = `
            <div class="modal fade" id="simulateReportModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title"><i class="fas fa-chart-pie me-2"></i>Simulasi: Buat Laporan Warkah</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="simulateReportForm">
                                <div class="mb-3">
                                    <label class="form-label"><strong>Jenis Laporan</strong></label>
                                    <select class="form-select" required>
                                        <option selected>Laporan Total Warkah</option>
                                        <option>Laporan Peminjaman Aktif</option>
                                        <option>Laporan Per Kategori</option>
                                        <option>Laporan Warkah Rusak</option>
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label"><strong>Dari Tanggal</strong></label>
                                        <input type="date" class="form-control" value="2026-01-01" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label"><strong>Sampai Tanggal</strong></label>
                                        <input type="date" class="form-control" value="2026-01-30" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label"><strong>Filter Kecamatan</strong></label>
                                        <select class="form-select">
                                            <option selected>-- Semua Kecamatan --</option>
                                            <option>Jakarta Pusat</option>
                                            <option>Jakarta Selatan</option>
                                            <option>Sleman</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label"><strong>Filter Kategori</strong></label>
                                        <select class="form-select">
                                            <option selected>-- Semua Kategori --</option>
                                            <option>Sertifikat Tanah</option>
                                            <option>Akte Perubahan</option>
                                            <option>Surat Keterangan</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label"><strong>Filter Status</strong></label>
                                    <div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="chk1" checked>
                                            <label class="form-check-label" for="chk1">✓ Tersedia</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="chk2" checked>
                                            <label class="form-check-label" for="chk2">✓ Dipinjam</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="chk3">
                                            <label class="form-check-label" for="chk3">Rusak</label>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-success" onclick="simulator.submitGenerateReport()">
                                <i class="fas fa-sync me-2"></i>Simulasi Buat Laporan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('simulateReportModal'));
        modal.show();

        document.getElementById('simulateReportModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }

    submitGenerateReport() {
        const form = document.getElementById('simulateReportForm');
        const jenis = form.querySelector('select').value;
        const tglAwal = form.querySelector('input[value="2026-01-01"]').value;
        const tglAkhir = form.querySelector('input[value="2026-01-30"]').value;

        bootstrap.Modal.getInstance(document.getElementById('simulateReportModal')).hide();

        // Show report result
        const reportHtml = `
            <div class="modal fade" id="reportResultModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title"><i class="fas fa-file-pdf me-2"></i>${jenis}</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <strong>Periode:</strong> ${new Date(tglAwal).toLocaleDateString('id-ID')} - ${new Date(tglAkhir).toLocaleDateString('id-ID')}
                            </div>

                            <h6 class="mb-3">Ringkasan Data:</h6>
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Kategori</th>
                                        <th class="text-center">Tersedia</th>
                                        <th class="text-center">Dipinjam</th>
                                        <th class="text-center">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Sertifikat Tanah</td>
                                        <td class="text-center">87</td>
                                        <td class="text-center">5</td>
                                        <td class="text-center"><strong>92</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Akte Perubahan</td>
                                        <td class="text-center">56</td>
                                        <td class="text-center">3</td>
                                        <td class="text-center"><strong>59</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Surat Keterangan</td>
                                        <td class="text-center">78</td>
                                        <td class="text-center">4</td>
                                        <td class="text-center"><strong>82</strong></td>
                                    </tr>
                                    <tr class="table-primary">
                                        <td><strong>TOTAL</strong></td>
                                        <td class="text-center"><strong>221</strong></td>
                                        <td class="text-center"><strong>12</strong></td>
                                        <td class="text-center"><strong>233</strong></td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center;">
                                        <h5 style="color: var(--primary-color); margin-bottom: 5px;">221</h5>
                                        <small>Warkah Tersedia</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div style="background: #fff3cd; padding: 15px; border-radius: 8px; text-align: center;">
                                        <h5 style="color: var(--warning-color); margin-bottom: 5px;">12</h5>
                                        <small>Warkah Dipinjam</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" onclick="simulator.simulateExportPDF()">
                                <i class="fas fa-file-pdf me-2"></i>Export PDF
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="simulator.simulatePrint()">
                                <i class="fas fa-print me-2"></i>Cetak
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', reportHtml);
        const modal = new bootstrap.Modal(document.getElementById('reportResultModal'));
        modal.show();

        document.getElementById('reportResultModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }

    simulateExportPDF() {
        bootstrap.Modal.getInstance(document.getElementById('reportResultModal')).hide();
        this.showSuccessAnimation('Laporan Diekspor', `
            <div class="success-details">
                <p><i class="fas fa-check me-2" style="color: var(--accent-color);"></i><strong>File berhasil diunduh</strong></p>
                <p><strong>Nama File:</strong> Laporan_Warkah_Jan2026.pdf</p>
                <p><strong>Ukuran:</strong> 2.4 MB</p>
                <p><strong>Waktu:</strong> ${new Date().toLocaleTimeString('id-ID')}</p>
                <hr>
                <small>File tersimpan di folder Downloads Anda</small>
            </div>
        `);
    }

    simulatePrint() {
        bootstrap.Modal.getInstance(document.getElementById('reportResultModal')).hide();
        this.showSuccessAnimation('Cetak Laporan', `
            <div class="success-details">
                <p><i class="fas fa-print me-2" style="color: var(--primary-color);"></i><strong>Mengirim ke printer...</strong></p>
                <p><strong>Printer:</strong> Canon Pixma (Default)</p>
                <p><strong>Halaman:</strong> 5 halaman</p>
                <p><strong>Waktu:</strong> ${new Date().toLocaleTimeString('id-ID')}</p>
                <hr>
                <small>Laporan akan dicetak dalam beberapa saat</small>
            </div>
        `);
    }

    // === AUDIT TRAIL SIMULATIONS ===
    simulateAuditTrail() {
        const auditData = [
            { user: 'Admin', action: 'UPDATE', entity: 'BON-2026-0045', details: 'Ubah status: Tersedia → Dipinjam', time: '14:30' },
            { user: 'Budi', action: 'CREATE', entity: 'BON-2026-0044', details: 'Tambah data warkah baru', time: '13:15' },
            { user: 'Admin', action: 'DELETE', entity: 'BON-2026-0043', details: 'Hapus data warkah', time: '11:45' },
            { user: 'Siti', action: 'UPDATE', entity: 'BON-PJMN-2026-08234', details: 'Tandai pengembalian', time: '10:20' },
            { user: 'Admin', action: 'UPDATE', entity: 'BON-2026-0042', details: 'Ubah lokasi: Rak B-02 → Rak C-05', time: '09:50' }
        ];

        const auditHtml = `
            <div class="modal fade" id="auditTrailModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title"><i class="fas fa-history me-2"></i>Simulasi: Audit Trail (Riwayat Aktivitas)</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" placeholder="Cari berdasarkan user, aksi, atau entitas...">
                                </div>
                            </div>

                            <div class="audit-timeline">
                                ${auditData.map((audit, idx) => `
                                    <div class="audit-item" onclick="simulator.showAuditDetail(${idx})">
                                        <div class="audit-marker ${audit.action.toLowerCase()}"></div>
                                        <div class="audit-content">
                                            <div class="audit-header">
                                                <span class="audit-action badge bg-${audit.action === 'CREATE' ? 'success' : audit.action === 'UPDATE' ? 'warning' : 'danger'}">${audit.action}</span>
                                                <span class="audit-time text-muted ms-2">${audit.time}</span>
                                            </div>
                                            <p class="audit-text mb-1"><strong>${audit.user}</strong> ${audit.action === 'CREATE' ? 'menambah' : audit.action === 'UPDATE' ? 'mengubah' : 'menghapus'} <code>${audit.entity}</code></p>
                                            <small class="text-muted">${audit.details}</small>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger btn-sm" onclick="simulator.simulateExportAudit()">
                                <i class="fas fa-file-pdf me-2"></i>Export PDF
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', auditHtml);
        
        // Add styles for audit timeline
        const style = document.createElement('style');
        style.innerHTML = `
            .audit-timeline {
                position: relative;
                padding: 20px 0;
            }

            .audit-item {
                display: flex;
                margin-bottom: 25px;
                padding: 12px;
                border-radius: 8px;
                cursor: pointer;
                transition: all 0.2s ease;
            }

            .audit-item:hover {
                background: #f8f9fa;
                transform: translateX(4px);
            }

            .audit-marker {
                width: 16px;
                height: 16px;
                border-radius: 50%;
                margin-right: 15px;
                margin-top: 2px;
                flex-shrink: 0;
                position: relative;
            }

            .audit-marker.create {
                background: linear-gradient(135deg, #51cf66, #82c91e);
            }

            .audit-marker.update {
                background: linear-gradient(135deg, #ffa94d, #ff922b);
            }

            .audit-marker.delete {
                background: linear-gradient(135deg, #fa5252, #dc3545);
            }

            .audit-content {
                flex: 1;
            }

            .audit-header {
                display: flex;
                align-items: center;
                margin-bottom: 5px;
            }

            .audit-action {
                font-size: 0.75rem;
                padding: 3px 8px;
                font-weight: 600;
            }

            .audit-time {
                font-size: 0.85rem;
            }

            .audit-text {
                font-size: 0.95rem;
                color: #1a1a1a;
                margin: 0;
            }

            .audit-text code {
                background: #f0f4f8;
                padding: 2px 6px;
                border-radius: 4px;
                color: #1e3d72;
                font-weight: 500;
                font-size: 0.9rem;
            }
        `;
        document.head.appendChild(style);

        const modal = new bootstrap.Modal(document.getElementById('auditTrailModal'));
        modal.show();

        document.getElementById('auditTrailModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
            style.remove();
        });
    }

    showAuditDetail(index) {
        this.showSuccessAnimation('Detail Aktivitas', `
            <div class="success-details">
                <p><strong>Pengguna:</strong> Admin</p>
                <p><strong>Aksi:</strong> UPDATE</p>
                <p><strong>Entitas:</strong> BON-2026-0045</p>
                <p><strong>Waktu:</strong> 2026-01-30 14:30:22</p>
                <p><strong>IP Address:</strong> 192.168.1.105</p>
                <hr>
                <p><strong>Perubahan Data:</strong></p>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li><strong>Status:</strong> Tersedia → Dipinjam</li>
                    <li><strong>Peminjam:</strong> - → Budi Santoso</li>
                    <li><strong>Tgl Peminjaman:</strong> - → 2026-01-30</li>
                </ul>
            </div>
        `);
    }

    simulateExportAudit() {
        this.showSuccessAnimation('Audit Trail Diekspor', `
            <div class="success-details">
                <p><i class="fas fa-check me-2" style="color: var(--accent-color);"></i><strong>File berhasil diunduh</strong></p>
                <p><strong>Nama File:</strong> Audit_Trail_Jan2026.pdf</p>
    // === SEARCH & FILTER SIMULATIONS ===
    simulateSearch() {
        const modalHtml = `
            <div class="modal fade" id="simulateSearchModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title"><i class="fas fa-search me-2"></i>Simulasi: Pencarian Data</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Anda sedang mencari data bon warkah dengan kriteria:</p>
                            <div class="alert alert-light border">
                                <strong>Nomor Bon:</strong> BON-2026
                                <br><strong>Status:</strong> Dipinjam
                            </div>
                            <p>Sistem sedang memproses pencarian...</p>
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Mencari...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('simulateSearchModal'));
        modal.show();

        setTimeout(() => {
            this.showSuccessAnimation('Pencarian Selesai', `
                <div class="success-details">
                    <p><strong>Ditemukan:</strong> 5 data</p>
                    <p><strong>Kriteria:</strong> Nomor Bon seperti "BON-2026" dengan Status "Dipinjam"</p>
                    <p>Hasil pencarian ditampilkan dalam tabel dengan pagination</p>
                </div>
            `);
            document.getElementById('simulateSearchModal').remove();
        }, 2000);
    }

    simulateFilter() {
        this.showSuccessAnimation('Filter Diterapkan', `
            <div class="success-details">
                <p><strong>Filter Aktif:</strong></p>
                <p>✓ Kecamatan: Jakarta Selatan</p>
                <p>✓ Status: Tersedia</p>
                <p>✓ Rentang Tanggal: 1-15 Januari 2026</p>
                <p><strong>Hasil:</strong> 12 data yang sesuai kriteria</p>
            </div>
        `);
    }

    // === TAMBAH DATA SIMULATIONS ===
    simulateUploadFile() {
        const modalHtml = `
            <div class="modal fade" id="uploadFileModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title"><i class="fas fa-paperclip me-2"></i>Simulasi: Upload Berkas</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Pilih Berkas</label>
                                <input type="file" class="form-control">
                            </div>
                            <div class="alert alert-info">
                                <small>Format: PDF, JPG, PNG (Max 5MB)</small>
                            </div>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 75%">75%</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-info btn-sm" onclick="simulator.confirmUploadFile()">Upload</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('uploadFileModal'));
        modal.show();
    }

    confirmUploadFile() {
        document.getElementById('uploadFileModal').remove();
        this.showSuccessAnimation('Berkas Berhasil Diunggah', `
            <div class="success-details">
                <p><strong>Nama Berkas:</strong> surat_hak_milik_123.pdf</p>
                <p><strong>Ukuran:</strong> 2.5 MB</p>
                <p><strong>Waktu Upload:</strong> 2 Januari 2026, 14:30</p>
                <p>Berkas telah tersimpan dan terhubung dengan data warkah</p>
            </div>
        `);
    }

    submitEditData() {
        this.showSuccessAnimation('Perubahan Data Tersimpan', `
            <div class="success-details">
                <p><strong>Data Warkah ID:</strong> 1234</p>
                <p><strong>Perubahan:</strong></p>
                <p>✓ Nama Peminjam: Ahmad Rizki → Ahmad Rizki S.H.</p>
                <p>✓ Status: Dipinjam → Tersedia</p>
                <p><strong>Waktu:</strong> 2 Januari 2026, 15:45</p>
                <p><strong>Tercatat dalam Audit Trail</strong></p>
            </div>
        `);
    }

    // === EDIT & DELETE SIMULATIONS ===
    simulateEditData() {
        const modalHtml = `
            <div class="modal fade" id="simulateEditModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-warning">
                            <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Simulasi: Edit Data Warkah</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form>
                                <div class="mb-3">
                                    <label class="form-label">Nama Peminjam</label>
                                    <input type="text" class="form-control" value="Siti Nurhaliza">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">No. Identitas</label>
                                    <input type="text" class="form-control" value="3201023405100005">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select">
                                        <option selected>Dipinjam</option>
                                        <option>Tersedia</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-warning btn-sm" onclick="simulator.submitEditData()">Simpan Perubahan</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('simulateEditModal'));
        modal.show();
    }

    simulateDeleteData() {
        const confirmHtml = `
            <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content border-danger">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title"><i class="fas fa-trash me-2"></i>Hapus Data?</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p><strong>Data yang akan dihapus:</strong></p>
                            <p class="bg-light p-2 rounded">Bon Warkah #1234 - Peminjam: Ahmad Rizki</p>
                            <p class="text-muted small">Aksi ini tidak dapat dibatalkan!</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="simulator.confirmDelete()">Ya, Hapus</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', confirmHtml);
        const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        modal.show();
    }

    confirmDelete() {
        document.getElementById('deleteConfirmModal').remove();
        this.showSuccessAnimation('Data Berhasil Dihapus', `
            <div class="success-details">
                <p><strong>Bon Warkah ID:</strong> 1234</p>
                <p><strong>Peminjam:</strong> Ahmad Rizki</p>
                <p><strong>Dihapus pada:</strong> 2 Januari 2026, 16:20</p>
                <p><strong>Catatan:</strong> Aksi penghapusan tercatat dalam Audit Trail</p>
            </div>
        `);
    }

    // === LAPORAN SIMULATIONS ===
    simulateSetFilter() {
        this.showSuccessAnimation('Filter Laporan Diatur', `
            <div class="success-details">
                <p><strong>Kriteria Laporan:</strong></p>
                <p>✓ Periode: 1 - 31 Januari 2026</p>
                <p>✓ Tipe Tanggal: Tanggal Pinjam</p>
                <p>✓ Status: Semua</p>
                <p>Siap membuat laporan. Klik tombol "Buat Laporan"</p>
            </div>
        `);
    }

    submitGenerateReport() {
        const modalHtml = `
            <div class="modal fade" id="reportModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title"><i class="fas fa-chart-bar me-2"></i>Laporan Data Warkah</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <h6>Ringkasan Laporan</h6>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h4 class="text-primary">45</h4>
                                            <small class="text-muted">Total Peminjaman</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h4 class="text-warning">12</h4>
                                            <small class="text-muted">Masih Dipinjam</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <table class="table table-sm table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>No. Bon</th>
                                        <th>Peminjam</th>
                                        <th>Tgl Pinjam</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>BON-2026-0001</td>
                                        <td>Ahmad Rizki</td>
                                        <td>01/01/2026</td>
                                        <td><span class="badge bg-warning">Dipinjam</span></td>
                                    </tr>
                                    <tr>
                                        <td>BON-2026-0002</td>
                                        <td>Siti Nurhaliza</td>
                                        <td>02/01/2026</td>
                                        <td><span class="badge bg-success">Tersedia</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger btn-sm" onclick="simulator.simulateExportPDF()"><i class="fas fa-file-pdf me-1"></i>Export PDF</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="simulator.simulatePrint()"><i class="fas fa-print me-1"></i>Cetak</button>
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('reportModal'));
        modal.show();
    }

    simulateExportPDF() {
        this.showSuccessAnimation('Laporan Diekspor ke PDF', `
            <div class="success-details">
                <p><strong>File:</strong> Laporan_Warkah_Januari2026.pdf</p>
                <p><strong>Ukuran:</strong> 1.2 MB</p>
                <p><strong>Lokasi:</strong> Folder Downloads</p>
                <p>Laporan siap untuk dikirim atau disimpan dalam arsip</p>
            </div>
        `);
    }

    simulatePrint() {
        alert('Jendela percetakan browser akan membuka. Pilih printer atau "Save as PDF" untuk menyimpan.');
    }

    // === AUDIT TRAIL SIMULATIONS ===
    simulateFilterAudit() {
        this.showSuccessAnimation('Filter Audit Trail Diterapkan', `
            <div class="success-details">
                <p><strong>Filter Aktif:</strong></p>
                <p>✓ Aksi: EDIT_DATA</p>
                <p>✓ Tanggal: 20-31 Januari 2026</p>
                <p><strong>Hasil:</strong> 28 aktivitas ditemukan</p>
            </div>
        `);
    }

    showAuditDetail(index) {
        const detailHtml = `
            <div class="modal fade" id="auditDetailModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title"><i class="fas fa-eye me-2"></i>Detail Aktivitas</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p><strong>Pengguna:</strong> admin_user</p>
                            <p><strong>Aksi:</strong> EDIT_DATA</p>
                            <p><strong>Entitas:</strong> Bon Warkah #1234</p>
                            <p><strong>Waktu:</strong> 28 Januari 2026, 14:35:22</p>
                            <p><strong>IP Address:</strong> 192.168.1.100</p>
                            <hr>
                            <h6>Perubahan Data:</h6>
                            <p><strong>Nilai Lama (Sebelum):</strong></p>
                            <div class="bg-light p-2 rounded mb-2">
                                Status: Dipinjam
                            </div>
                            <p><strong>Nilai Baru (Sesudah):</strong></p>
                            <div class="bg-light p-2 rounded">
                                Status: Tersedia
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', detailHtml);
        const modal = new bootstrap.Modal(document.getElementById('auditDetailModal'));
        modal.show();
    }

    simulateExportAudit() {
        this.showSuccessAnimation('Audit Trail Diekspor', `
            <div class="success-details">
                <p><strong>File:</strong> Audit_Trail_Jan2026.csv</p>
                <p><strong>Record:</strong> 2,847 aktivitas</p>
                <p><strong>Periode:</strong> 1-31 Januari 2026</p>
                <p>File CSV siap untuk analisis lebih lanjut di Excel atau tools lainnya</p>
            </div>
        `);
    }

    // === USER MANAGEMENT SIMULATIONS ===
    simulateViewUsers() {
        const userHtml = `
            <div class="modal fade" id="userListModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-secondary text-white">
                            <h5 class="modal-title"><i class="fas fa-users me-2"></i>Daftar User</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <table class="table table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>Username</th>
                                        <th>Nama Lengkap</th>
                                        <th>Role</th>
                                        <th>Dibuat Pada</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>admin_keraton</td>
                                        <td>Admin Keraton</td>
                                        <td><span class="badge bg-danger">Admin</span></td>
                                        <td>01/01/2025</td>
                                    </tr>
                                    <tr>
                                        <td>ahmad_rizki</td>
                                        <td>Ahmad Rizki</td>
                                        <td><span class="badge bg-primary">User</span></td>
                                        <td>15/06/2025</td>
                                    </tr>
                                    <tr>
                                        <td>siti_nur</td>
                                        <td>Siti Nurhaliza</td>
                                        <td><span class="badge bg-primary">User</span></td>
                                        <td>20/06/2025</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', userHtml);
        const modal = new bootstrap.Modal(document.getElementById('userListModal'));
        modal.show();
    }

    simulateSearchUser() {
        this.showSuccessAnimation('Pencarian User', `
            <div class="success-details">
                <p><strong>Pencarian:</strong> "ahmad"</p>
                <p><strong>Hasil ditemukan:</strong> 1 user</p>
                <p>Username: ahmad_rizki</p>
                <p>Nama: Ahmad Rizki (Role: User)</p>
            </div>
        `);
    }

    simulateAddUser() {
        const addUserHtml = `
            <div class="modal fade" id="addUserModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Tambah User Baru</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form>
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control" placeholder="username_baru">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input type="password" class="form-control" placeholder="Minimal 8 karakter">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" placeholder="Nama Lengkap User">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Role</label>
                                    <select class="form-select">
                                        <option selected>User</option>
                                        <option>Admin</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-success btn-sm" onclick="simulator.confirmAddUser()">Buat User</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', addUserHtml);
        const modal = new bootstrap.Modal(document.getElementById('addUserModal'));
        modal.show();
    }

    confirmAddUser() {
        document.getElementById('addUserModal').remove();
        this.showSuccessAnimation('User Baru Berhasil Dibuat', `
            <div class="success-details">
                <p><strong>Username:</strong> rudi_santoso</p>
                <p><strong>Nama:</strong> Rudi Santoso</p>
                <p><strong>Role:</strong> User</p>
                <p><strong>Dibuat pada:</strong> 2 Januari 2026, 10:15</p>
                <p>User dapat langsung login dengan kredensial yang telah diberikan</p>
            </div>
        `);
    }

    simulateEditUser() {
        const editUserHtml = `
            <div class="modal fade" id="editUserModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-warning">
                            <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i>Edit User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form>
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control" value="ahmad_rizki" disabled>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" value="Ahmad Rizki">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Role</label>
                                    <select class="form-select">
                                        <option selected>User</option>
                                        <option>Admin</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password Baru (Kosongkan jika tidak ada perubahan)</label>
                                    <input type="password" class="form-control">
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-warning btn-sm" onclick="simulator.confirmEditUser()">Simpan Perubahan</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', editUserHtml);
        const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
        modal.show();
    }

    confirmEditUser() {
        document.getElementById('editUserModal').remove();
        this.showSuccessAnimation('User Berhasil Diubah', `
            <div class="success-details">
                <p><strong>Username:</strong> ahmad_rizki</p>
                <p><strong>Perubahan:</strong> Nama diperbarui</p>
                <p><strong>Waktu:</strong> 2 Januari 2026, 11:45</p>
            </div>
        `);
    }

    simulateDeleteUser() {
        const deleteUserHtml = `
            <div class="modal fade" id="deleteUserModal" tabindex="-1">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content border-danger">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title"><i class="fas fa-user-slash me-2"></i>Hapus User?</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p><strong>User yang akan dihapus:</strong></p>
                            <p class="bg-light p-2 rounded">ahmad_rizki - Ahmad Rizki</p>
                            <p class="text-muted small">Aksi ini tidak dapat dibatalkan!</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="simulator.confirmDeleteUser()">Ya, Hapus</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', deleteUserHtml);
        const modal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
        modal.show();
    }

    confirmDeleteUser() {
        document.getElementById('deleteUserModal').remove();
        this.showSuccessAnimation('User Berhasil Dihapus', `
            <div class="success-details">
                <p><strong>Username:</strong> ahmad_rizki</p>
                <p><strong>Nama:</strong> Ahmad Rizki</p>
                <p><strong>Dihapus pada:</strong> 2 Januari 2026, 12:00</p>
                <p>User tidak lagi dapat login ke sistem</p>
            </div>
        `);
    }

                <p><strong>Jumlah Record:</strong> 2,847 aktivitas</p>
                <p><strong>Periode:</strong> 1 - 30 Januari 2026</p>
                <hr>
                <small>File tersimpan di folder Downloads Anda</small>
            </div>
        `);
    }

    // === HELPER FUNCTIONS ===
    showSuccessAnimation(title, content) {
        const successHtml = `
            <div class="modal fade" id="successModal" tabindex="-1">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-body text-center py-5">
                            <div class="success-checkmark mb-4" style="animation: scaleIn 0.5s ease;">
                                <i class="fas fa-check-circle" style="font-size: 4rem; color: var(--accent-color);"></i>
                            </div>
                            <h5 class="mb-3">${title}</h5>
                            <div class="success-content" style="font-size: 0.95rem; color: #666;">
                                ${content}
                            </div>
                        </div>
                        <div class="modal-footer border-0 justify-content-center">
                            <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">OK</button>
                        </div>
                    </div>
                </div>
            </div>
            <style>
                @keyframes scaleIn {
                    from { transform: scale(0); opacity: 0; }
                    to { transform: scale(1); opacity: 1; }
                }
                .success-details p {
                    margin-bottom: 8px;
                    text-align: left;
                }
                .success-details code {
                    background: #f0f4f8;
                    padding: 2px 6px;
                    border-radius: 4px;
                    color: #1e3d72;
                }
            </style>
        `;

        document.body.insertAdjacentHTML('beforeend', successHtml);
        const modal = new bootstrap.Modal(document.getElementById('successModal'));
        modal.show();

        document.getElementById('successModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }
}

// Initialize simulator
const simulator = new GuideSimulator();
