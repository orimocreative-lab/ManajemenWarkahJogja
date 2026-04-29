<?php
require_once '../../includes/config.php';

if (!isLoggedIn()) {
    redirect('../../login.php');
}
?>

<?php include '../../includes/header.php'; ?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-book me-2"></i> Panduan Penggunaan Sistem</h1>
    <p>Pelajari fitur-fitur utama sistem informasi warkah melalui simulasi interaktif</p>
</div>

<!-- Guide Container -->
<div class="guide-container">
    <!-- Introduction Card -->
    <div class="card guide-intro-card mb-5">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h4 class="guide-title mb-3">
                        <i class="fas fa-lightbulb me-2 text-warning"></i>Selamat Datang di Sistem Informasi KERATON
                    </h4>
                    <p class="guide-subtitle mb-0">
                        Sistem ini dirancang untuk membantu Anda mengelola dan memantau data warkah (dokumen) dengan mudah, efisien, dan aman.
                        Pilih salah satu fitur di bawah untuk mempelajari cara menggunakannya.
                    </p>
                </div>
                <div class="col-lg-4 text-center">
                    <div class="guide-icon-large">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Guide Modules Grid -->
    <div class="row mb-5">
        <!-- Modul 1: Cari Data & Filter -->
        <div class="col-lg-6 mb-4">
            <div class="card guide-module-card h-100" onclick="toggleGuideContent('cari-data')">
                <div class="card-body">
                    <div class="guide-module-header">
                        <div class="guide-module-icon bg-primary">
                            <i class="fas fa-search"></i>
                        </div>
                        <h5 class="guide-module-title">1. Cari & Filter Data</h5>
                    </div>
                    <p class="guide-module-desc">
                        Temukan data warkah dengan cepat menggunakan fitur pencarian dan filter lanjutan.
                    </p>
                    <div class="guide-module-features">
                        <small class="badge bg-light text-dark me-2"><i class="fas fa-search me-1"></i>Cari</small>
                        <small class="badge bg-light text-dark me-2"><i class="fas fa-filter me-1"></i>Filter</small>
                        <small class="badge bg-light text-dark"><i class="fas fa-calendar me-1"></i>Tanggal</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modul 2: Tambah Data -->
        <div class="col-lg-6 mb-4">
            <div class="card guide-module-card h-100" onclick="toggleGuideContent('tambah-data')">
                <div class="card-body">
                    <div class="guide-module-header">
                        <div class="guide-module-icon bg-success">
                            <i class="fas fa-plus"></i>
                        </div>
                        <h5 class="guide-module-title">2. Tambah Data Warkah</h5>
                    </div>
                    <p class="guide-module-desc">
                        Tambahkan warkah baru ke dalam sistem dengan informasi lengkap.
                    </p>
                    <div class="guide-module-features">
                        <small class="badge bg-light text-dark me-2"><i class="fas fa-plus-circle me-1"></i>Buat</small>
                        <small class="badge bg-light text-dark me-2"><i class="fas fa-paperclip me-1"></i>Upload</small>
                        <small class="badge bg-light text-dark"><i class="fas fa-save me-1"></i>Simpan</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modul 3: Edit & Hapus Data -->
        <div class="col-lg-6 mb-4">
            <div class="card guide-module-card h-100" onclick="toggleGuideContent('edit-hapus')">
                <div class="card-body">
                    <div class="guide-module-header">
                        <div class="guide-module-icon bg-warning">
                            <i class="fas fa-edit"></i>
                        </div>
                        <h5 class="guide-module-title">3. Edit & Hapus Data</h5>
                    </div>
                    <p class="guide-module-desc">
                        Ubah atau hapus data warkah yang sudah ada di sistem.
                    </p>
                    <div class="guide-module-features">
                        <small class="badge bg-light text-dark me-2"><i class="fas fa-pencil-alt me-1"></i>Edit</small>
                        <small class="badge bg-light text-dark me-2"><i class="fas fa-trash me-1"></i>Hapus</small>
                        <small class="badge bg-light text-dark"><i class="fas fa-check me-1"></i>Konfirmasi</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modul 4: Cetak Laporan -->
        <div class="col-lg-6 mb-4">
            <div class="card guide-module-card h-100" onclick="toggleGuideContent('cetak-laporan')">
                <div class="card-body">
                    <div class="guide-module-header">
                        <div class="guide-module-icon bg-danger">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <h5 class="guide-module-title">4. Cetak & Export Laporan</h5>
                    </div>
                    <p class="guide-module-desc">
                        Buat laporan data dan cetak atau export dalam format PDF.
                    </p>
                    <div class="guide-module-features">
                        <small class="badge bg-light text-dark me-2"><i class="fas fa-chart-bar me-1"></i>Laporan</small>
                        <small class="badge bg-light text-dark me-2"><i class="fas fa-print me-1"></i>Cetak</small>
                        <small class="badge bg-light text-dark"><i class="fas fa-download me-1"></i>Export</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modul 5: Audit Trail -->
        <div class="col-lg-6 mb-4">
            <div class="card guide-module-card h-100" onclick="toggleGuideContent('audit-trail')">
                <div class="card-body">
                    <div class="guide-module-header">
                        <div class="guide-module-icon bg-info">
                            <i class="fas fa-history"></i>
                        </div>
                        <h5 class="guide-module-title">5. Audit Trail (Admin)</h5>
                    </div>
                    <p class="guide-module-desc">
                        Pantau setiap aktivitas pengguna dan perubahan data dalam sistem.
                    </p>
                    <div class="guide-module-features">
                        <small class="badge bg-light text-dark me-2"><i class="fas fa-eye me-1"></i>Monitor</small>
                        <small class="badge bg-light text-dark me-2"><i class="fas fa-search me-1"></i>Cari</small>
                        <small class="badge bg-light text-dark"><i class="fas fa-list me-1"></i>Riwayat</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modul 6: Manajemen User -->
        <div class="col-lg-6 mb-4">
            <div class="card guide-module-card h-100" onclick="toggleGuideContent('manajemen-user')">
                <div class="card-body">
                    <div class="guide-module-header">
                        <div class="guide-module-icon bg-secondary">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5 class="guide-module-title">6. Manajemen User (Admin)</h5>
                    </div>
                    <p class="guide-module-desc">
                        Kelola akun pengguna dan atur peran/role di sistem.
                    </p>
                    <div class="guide-module-features">
                        <small class="badge bg-light text-dark me-2"><i class="fas fa-user-plus me-1"></i>Tambah</small>
                        <small class="badge bg-light text-dark me-2"><i class="fas fa-user-edit me-1"></i>Edit</small>
                        <small class="badge bg-light text-dark"><i class="fas fa-user-slash me-1"></i>Hapus</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Guide Content Panels -->
    <div class="guide-content-panels mt-4">
        <!-- Cari & Filter Data Panel -->
        <div id="cari-data" class="guide-content-panel">
            <div class="card guide-detail-card">
                <div class="card-header guide-detail-header">
                    <h5><i class="fas fa-search me-2 text-primary"></i>Cara Mencari & Filter Data Warkah</h5>
                </div>
                <div class="card-body">
                    <div class="guide-steps">
                        <!-- Step 1 -->
                        <div class="guide-step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h6>Buka Menu Data Bon Warkah</h6>
                                <p>Klik menu <strong>"Data Bon Warkah"</strong> di navbar untuk melihat daftar semua data.</p>
                                <div class="step-demo">
                                    <code>modules/Bon/index.php</code> - Baris pencarian dan filter
                                </div>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="guide-step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h6>Gunakan Form Pencarian</h6>
                                <p>Di halaman utama, Anda akan melihat form pencarian dengan berbagai pilihan field: nomor bon, nomor hak, kecamatan, kelurahan, peminjam, dan status.</p>
                                <div class="step-demo">
                                    <button class="btn btn-sm btn-primary" onclick="simulator.simulateSearch()">
                                        <i class="fas fa-search me-1"></i> Simulasi Pencarian
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="guide-step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h6>Filter Berdasarkan Kriteria</h6>
                                <p>Isi minimal satu field untuk filter data. Sistem mendukung filter berdasarkan:</p>
                                <ul class="ms-3">
                                    <li>Nomor Bon atau Nomor Hak</li>
                                    <li>Lokasi (Kecamatan & Kelurahan)</li>
                                    <li>Nama Peminjam</li>
                                    <li>Status (Dipinjam/Tersedia)</li>
                                    <li>Rentang Tanggal</li>
                                </ul>
                                <div class="step-demo">
                                    <button class="btn btn-sm btn-info" onclick="simulator.simulateFilter()">
                                        <i class="fas fa-filter me-1"></i> Simulasi Filter
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div class="guide-step">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h6>Klik Tombol "Cari"</h6>
                                <p>Setelah mengisi kriteria filter, klik tombol <strong>"Cari"</strong> untuk menampilkan hasil pencarian.</p>
                                <div class="step-demo">
                                    <button class="btn btn-sm btn-success" onclick="simulator.simulateSearch()">
                                        <i class="fas fa-check me-1"></i> Simulasi Eksekusi Pencarian
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Step 5 -->
                        <div class="guide-step">
                            <div class="step-number">5</div>
                            <div class="step-content">
                                <h6>Lihat Hasil & Navigasi Halaman</h6>
                                <p>Data akan ditampilkan dalam tabel dengan pagination. Setiap halaman menampilkan maksimal 10 data. Gunakan tombol "Previous" dan "Next" untuk navigasi.</p>
                                <div class="step-demo">
                                    <small>Kode: modules/Bon/index.php (Baris ~45-60)</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="guide-tips mt-4">
                        <h6><i class="fas fa-lightbulb me-2 text-warning"></i>Tips Penting:</h6>
                        <ul>
                            <li>Cari berdasarkan nomor hak atau nomor bon untuk hasil paling akurat</li>
                            <li>Gunakan filter status untuk melihat data yang masih dipinjam atau sudah tersedia</li>
                            <li>Rentang tanggal membantu Anda melacak aktivitas dalam periode tertentu</li>
                            <li>Klik tombol "Reset" untuk menghapus semua filter dan melihat semua data</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tambah Data Panel -->
        <div id="tambah-data" class="guide-content-panel" style="display:none;">
            <div class="card guide-detail-card">
                <div class="card-header guide-detail-header">
                    <h5><i class="fas fa-plus me-2 text-success"></i>Cara Menambah Data Warkah Baru</h5>
                </div>
                <div class="card-body">
                    <div class="guide-steps">
                        <!-- Step 1 -->
                        <div class="guide-step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h6>Buka Halaman Data Bon Warkah</h6>
                                <p>Klik menu <strong>"Data Bon Warkah"</strong> di navbar.</p>
                                <div class="step-demo">
                                    <code>modules/Bon/index.php</code>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="guide-step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h6>Klik Tombol "Tambah Baru"</h6>
                                <p>Cari dan klik tombol <strong>"+ Tambah Baru"</strong> di bagian atas halaman untuk membuka form tambah data.</p>
                                <div class="step-demo">
                                    <button class="btn btn-sm btn-primary" onclick="simulator.simulateAddData()">
                                        <i class="fas fa-plus me-1"></i> Simulasi Klik Tambah
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="guide-step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h6>Isi Form Data Warkah</h6>
                                <p>Lengkapi semua field yang diperlukan:</p>
                                <ul class="ms-3">
                                    <li><strong>Nomor Hak:</strong> Nomor identitas tanah (auto-generate)</li>
                                    <li><strong>Kelurahan:</strong> Pilih lokasi kelurahan</li>
                                    <li><strong>Kecamatan:</strong> Pilih lokasi kecamatan</li>
                                    <li><strong>Nama Peminjam:</strong> Nama orang yang meminjam</li>
                                    <li><strong>No. Identitas Peminjam:</strong> KTP atau ID lainnya</li>
                                    <li><strong>Alamat Peminjam:</strong> Alamat lengkap</li>
                                    <li><strong>Tanggal Pinjam & Tgl Kembali:</strong> Estimasi waktu peminjaman</li>
                                </ul>
                                <div class="step-demo">
                                    <small>Kode: modules/Bon/tambah.php (Form input)</small>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div class="guide-step">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h6>Upload Berkas (Opsional)</h6>
                                <p>Jika ada berkas pendukung (scan dokumen, foto, dll), upload melalui form file. Sistem akan menyimpannya secara aman.</p>
                                <div class="step-demo">
                                    <button class="btn btn-sm btn-info" onclick="simulator.simulateUploadFile()">
                                        <i class="fas fa-paperclip me-1"></i> Simulasi Upload Berkas
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Step 5 -->
                        <div class="guide-step">
                            <div class="step-number">5</div>
                            <div class="step-content">
                                <h6>Klik Tombol "Simpan"</h6>
                                <p>Setelah semua data terisi, klik tombol <strong>"Simpan"</strong> untuk menyimpan data ke database. Sistem akan memberikan notifikasi jika berhasil.</p>
                                <div class="step-demo">
                                    <button class="btn btn-sm btn-success" onclick="simulator.submitAddData()">
                                        <i class="fas fa-save me-1"></i> Simulasi Simpan Data
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="guide-tips mt-4">
                        <h6><i class="fas fa-lightbulb me-2 text-warning"></i>Tips Penting:</h6>
                        <ul>
                            <li>Nomor hak dan lokasi harus diisi dengan benar untuk identifikasi yang akurat</li>
                            <li>Data peminjam lengkap sangat penting untuk tracking pengembalian</li>
                            <li>Upload berkas dokumen untuk dokumentasi dan referensi di kemudian hari</li>
                            <li>Setiap data baru akan otomatis dicatat dalam Audit Trail</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit & Hapus Data Panel -->
        <div id="edit-hapus" class="guide-content-panel" style="display:none;">
            <div class="card guide-detail-card">
                <div class="card-header guide-detail-header">
                    <h5><i class="fas fa-edit me-2 text-warning"></i>Cara Edit & Hapus Data Warkah</h5>
                </div>
                <div class="card-body">
                    <div class="guide-steps">
                        <!-- Step 1 -->
                        <div class="guide-step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h6>Buka Halaman Data Bon Warkah</h6>
                                <p>Klik menu <strong>"Data Bon Warkah"</strong> untuk melihat daftar semua data.</p>
                                <div class="step-demo">
                                    <code>modules/Bon/index.php</code>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="guide-step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h6>Cari Data yang Ingin Diubah/Dihapus</h6>
                                <p>Gunakan fitur pencarian dan filter untuk menemukan data yang ingin diubah atau dihapus.</p>
                                <div class="step-demo">
                                    <button class="btn btn-sm btn-primary" onclick="simulator.simulateSearch()">
                                        <i class="fas fa-search me-1"></i> Simulasi Cari Data
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="guide-step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h6>Klik Tombol "Edit" atau "Ubah"</h6>
                                <p>Pada baris data yang ingin diubah, klik tombol <strong>"Edit"</strong> (ikon pensil) atau <strong>"Ubah"</strong> untuk membuka form edit.</p>
                                <div class="step-demo">
                                    <button class="btn btn-sm btn-warning" onclick="simulator.simulateEditData()">
                                        <i class="fas fa-edit me-1"></i> Simulasi Buka Edit
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div class="guide-step">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h6>Ubah Data yang Diinginkan</h6>
                                <p>Pada form edit, ubah field yang diperlukan (nama peminjam, tanggal kembali, status, dll). Anda dapat mengubah data apapun yang sudah disimpan.</p>
                                <div class="step-demo">
                                    <small>Kode: modules/Bon/edit.php (Form edit data)</small>
                                </div>
                            </div>
                        </div>

                        <!-- Step 5 -->
                        <div class="guide-step">
                            <div class="step-number">5</div>
                            <div class="step-content">
                                <h6>Simpan Perubahan atau Hapus Data</h6>
                                <p>Setelah mengedit:</p>
                                <ul class="ms-3">
                                    <li><strong>Klik "Simpan Perubahan"</strong> untuk menyimpan update data</li>
                                    <li><strong>Klik "Hapus Data"</strong> untuk menghapus data (memerlukan konfirmasi)</li>
                                </ul>
                                <div class="step-demo">
                                    <button class="btn btn-sm btn-success me-2" onclick="simulator.submitEditData()">
                                        <i class="fas fa-save me-1"></i> Simulasi Simpan Edit
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="simulator.simulateDeleteData()">
                                        <i class="fas fa-trash me-1"></i> Simulasi Hapus
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Step 6 -->
                        <div class="guide-step">
                            <div class="step-number">6</div>
                            <div class="step-content">
                                <h6>Konfirmasi Aksi (untuk Hapus)</h6>
                                <p>Jika menghapus data, sistem akan menampilkan popup konfirmasi. Klik "Ya, Hapus" untuk menyelesaikan penghapusan.</p>
                                <div class="step-demo">
                                    <button class="btn btn-sm btn-outline-danger" onclick="simulator.confirmDelete()">
                                        <i class="fas fa-check-circle me-1"></i> Simulasi Konfirmasi Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="guide-tips mt-4">
                        <h6><i class="fas fa-lightbulb me-2 text-warning"></i>Tips Penting:</h6>
                        <ul>
                            <li>Periksa data dengan teliti sebelum menghapus - aksi ini tidak dapat dibatalkan</li>
                            <li>Edit data peminjam jika ada perubahan informasi peminjam</li>
                            <li>Ubah status menjadi "Tersedia" ketika warkah sudah tersedia</li>
                            <li>Setiap perubahan atau penghapusan akan tercatat dalam Audit Trail untuk transparansi</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cetak Laporan Panel -->
        <div id="cetak-laporan" class="guide-content-panel" style="display:none;">
            <div class="card guide-detail-card">
                <div class="card-header guide-detail-header">
                    <h5><i class="fas fa-file-pdf me-2 text-danger"></i>Cara Cetak & Export Laporan</h5>
                </div>
                <div class="card-body">
                    <div class="guide-steps">
                        <!-- Step 1 -->
                        <div class="guide-step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h6>Buka Menu Laporan</h6>
                                <p>Klik menu <strong>"Laporan"</strong> di navbar untuk membuka halaman pembuatan laporan.</p>
                                <div class="step-demo">
                                    <code>modules/Laporan/index.php</code>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="guide-step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h6>Atur Filter Laporan</h6>
                                <p>Pilih filter yang diinginkan untuk membuat laporan yang spesifik:</p>
                                <ul class="ms-3">
                                    <li><strong>Filter Berdasarkan:</strong> Tanggal Pinjam atau Tanggal Bon</li>
                                    <li><strong>Tanggal Mulai & Akhir:</strong> Rentang tanggal laporan</li>
                                    <li><strong>Status:</strong> Dipinjam, Tersedia, atau Semua</li>
                                    <li><strong>Pencarian:</strong> Cari berdasarkan peminjam, nomor bon, atau nomor warkah</li>
                                </ul>
                                <div class="step-demo">
                                    <button class="btn btn-sm btn-primary" onclick="simulator.simulateSetFilter()">
                                        <i class="fas fa-sliders-h me-1"></i> Simulasi Atur Filter
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="guide-step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h6>Klik Tombol "Buat Laporan"</h6>
                                <p>Setelah mengatur filter, klik tombol <strong>"Buat Laporan"</strong> untuk menghasilkan laporan berdasarkan kriteria filter yang dipilih.</p>
                                <div class="step-demo">
                                    <button class="btn btn-sm btn-success" onclick="simulator.submitGenerateReport()">
                                        <i class="fas fa-chart-bar me-1"></i> Simulasi Buat Laporan
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div class="guide-step">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h6>Lihat Hasil Laporan</h6>
                                <p>Sistem akan menampilkan tabel laporan lengkap dengan data yang sesuai filter. Laporan menampilkan:</p>
                                <ul class="ms-3">
                                    <li>Nomor bon dan nomor hak</li>
                                    <li>Nama peminjam dan alamat</li>
                                    <li>Tanggal pinjam dan perkiraan kembali</li>
                                    <li>Status peminjaman (Dipinjam/Tersedia)</li>
                                </ul>
                                <div class="step-demo">
                                    <small>Kode: modules/Laporan/index.php (Query laporan)</small>
                                </div>
                            </div>
                        </div>

                        <!-- Step 5 -->
                        <div class="guide-step">
                            <div class="step-number">5</div>
                            <div class="step-content">
                                <h6>Export PDF atau Cetak Laporan</h6>
                                <p>Gunakan tombol di atas tabel laporan untuk export atau cetak:</p>
                                <ul class="ms-3">
                                    <li><strong>"Export PDF":</strong> Unduh laporan dalam format PDF</li>
                                    <li><strong>"Cetak":</strong> Buka dialog print browser untuk cetak langsung</li>
                                </ul>
                                <div class="step-demo">
                                    <button class="btn btn-sm btn-danger me-2" onclick="simulator.simulateExportPDF()">
                                        <i class="fas fa-file-pdf me-1"></i> Simulasi Export PDF
                                    </button>
                                    <button class="btn btn-sm btn-secondary" onclick="simulator.simulatePrint()">
                                        <i class="fas fa-print me-1"></i> Simulasi Cetak
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="guide-tips mt-4">
                        <h6><i class="fas fa-lightbulb me-2 text-warning"></i>Tips Penting:</h6>
                        <ul>
                            <li>Gunakan rentang tanggal yang tepat untuk laporan yang relevan dan fokus</li>
                            <li>Export PDF berguna untuk arsip digital dan pengiriman via email</li>
                            <li>Cetak laporan secara berkala untuk dokumentasi fisik</li>
                            <li>Laporan dapat digunakan sebagai bukti administrasi dan pertanggungjawaban warkah</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Audit Trail Panel -->
        <div id="audit-trail" class="guide-content-panel" style="display:none;">
            <div class="card guide-detail-card">
                <div class="card-header guide-detail-header">
                    <h5><i class="fas fa-history me-2 text-info"></i>Riwayat Aktivitas (Audit Trail) - Admin Only</h5>
                </div>
                <div class="card-body">
                    <p class="alert alert-info mb-3">
                        <i class="fas fa-shield-alt me-2"></i>
                        <strong>Fitur Admin:</strong> Hanya pengguna dengan role Admin yang dapat mengakses halaman Audit Trail.
                    </p>
                    <div class="guide-steps">
                        <!-- Step 1 -->
                        <div class="guide-step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h6>Login sebagai Admin</h6>
                                <p>Pastikan Anda login dengan akun yang memiliki role <strong>"Admin"</strong>.</p>
                                <div class="step-demo">
                                    <code>includes/functions.php - isLoggedIn(), roles</code>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="guide-step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h6>Buka Menu Audit Trail</h6>
                                <p>Klik menu <strong>"Admin"</strong> atau <strong>"Audit Trail"</strong> di navbar untuk membuka halaman riwayat aktivitas.</p>
                                <div class="step-demo">
                                    <code>modules/Audit/index.php</code>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="guide-step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h6>Lihat Daftar Aktivitas</h6>
                                <p>Sistem menampilkan daftar lengkap semua aktivitas pengguna dengan informasi:</p>
                                <ul class="ms-3">
                                    <li><strong>Nama Pengguna:</strong> Siapa yang melakukan aksi</li>
                                    <li><strong>Aksi:</strong> Jenis aksi (LOGIN, TAMBAH, EDIT, HAPUS, dll)</li>
                                    <li><strong>Entitas:</strong> Data apa yang diubah</li>
                                    <li><strong>Waktu:</strong> Kapan aksi dilakukan</li>
                                    <li><strong>IP Address:</strong> Dari mana aksi dilakukan</li>
                                </ul>
                                <div class="step-demo">
                                    <button class="btn btn-sm btn-info" onclick="simulator.simulateAuditTrail()">
                                        <i class="fas fa-list me-1"></i> Simulasi Lihat Daftar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div class="guide-step">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h6>Filter Aktivitas (Opsional)</h6>
                                <p>Gunakan filter untuk mencari aktivitas spesifik:</p>
                                <ul class="ms-3">
                                    <li>Filter berdasarkan <strong>Aksi</strong> (LOGIN, TAMBAH, EDIT, HAPUS)</li>
                                    <li>Filter berdasarkan <strong>Username</strong> pengguna tertentu</li>
                                    <li>Filter berdasarkan <strong>Tipe Entitas</strong> (warkah, bon, user, dll)</li>
                                    <li>Filter berdasarkan <strong>Rentang Tanggal</strong> aktivitas</li>
                                </ul>
                                <div class="step-demo">
                                    <button class="btn btn-sm btn-primary" onclick="simulator.simulateFilterAudit()">
                                        <i class="fas fa-filter me-1"></i> Simulasi Filter
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Step 5 -->
                        <div class="guide-step">
                            <div class="step-number">5</div>
                            <div class="step-content">
                                <h6>Lihat Detail Perubahan</h6>
                                <p>Klik pada baris aktivitas untuk melihat detail lengkap, termasuk:</p>
                                <ul class="ms-3">
                                    <li><strong>Nilai Lama:</strong> Data sebelum perubahan</li>
                                    <li><strong>Nilai Baru:</strong> Data setelah perubahan</li>
                                    <li><strong>Deskripsi Aksi:</strong> Detail lengkap perubahan</li>
                                </ul>
                                <div class="step-demo">
                                    <button class="btn btn-sm btn-info" onclick="simulator.showAuditDetail(0)">
                                        <i class="fas fa-eye me-1"></i> Simulasi Lihat Detail
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Step 6 -->
                        <div class="guide-step">
                            <div class="step-number">6</div>
                            <div class="step-content">
                                <h6>Export Laporan Audit (Opsional)</h6>
                                <p>Untuk dokumentasi lebih lanjut, export data audit trail ke file untuk disimpan atau dianalisis.</p>
                                <div class="step-demo">
                                    <button class="btn btn-sm btn-danger" onclick="simulator.simulateExportAudit()">
                                        <i class="fas fa-file-pdf me-1"></i> Simulasi Export Audit
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="guide-tips mt-4">
                        <h6><i class="fas fa-lightbulb me-2 text-warning"></i>Tips Penting:</h6>
                        <ul>
                            <li>Audit Trail membantu Anda melacak siapa yang melakukan perubahan dan kapan</li>
                            <li>Gunakan fitur ini untuk monitoring keamanan dan akuntabilitas sistem</li>
                            <li>Periksa secara berkala untuk mendeteksi aktivitas yang tidak biasa atau mencurigakan</li>
                            <li>Simpan laporan audit sebagai bukti administrasi yang sah dan dapat diaudit</li>
                            <li>Setiap login, logout, tambah, edit, dan hapus data otomatis tercatat</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manajemen User Panel -->
        <div id="manajemen-user" class="guide-content-panel" style="display:none;">
            <div class="card guide-detail-card">
                <div class="card-header guide-detail-header">
                    <h5><i class="fas fa-users me-2 text-secondary"></i>Manajemen User - Admin Only</h5>
                </div>
                <div class="card-body">
                    <p class="alert alert-info mb-3">
                        <i class="fas fa-shield-alt me-2"></i>
                        <strong>Fitur Admin:</strong> Hanya pengguna dengan role Admin yang dapat mengelola user lain.
                    </p>
                    <div class="guide-steps">
                        <!-- Step 1 -->
                        <div class="guide-step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h6>Login sebagai Admin</h6>
                                <p>Akses menu manajemen user hanya tersedia untuk Admin.</p>
                                <div class="step-demo">
                                    <code>modules/users/index.php</code>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="guide-step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h6>Buka Menu Manajemen User</h6>
                                <p>Klik menu <strong>"Admin"</strong> atau <strong>"Manajemen User"</strong> di navbar untuk melihat daftar semua user.</p>
                                <div class="step-demo">
                                    <code>modules/users/index.php</code> - Daftar user dengan pencarian
                                </div>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="guide-step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h6>Lihat Daftar User</h6>
                                <p>Halaman menampilkan tabel semua user dengan informasi:</p>
                                <ul class="ms-3">
                                    <li><strong>Username:</strong> Username login user</li>
                                    <li><strong>Nama Lengkap:</strong> Nama panjang user</li>
                                    <li><strong>Role:</strong> Admin atau User Biasa</li>
                                    <li><strong>Dibuat Pada:</strong> Tanggal pembuatan akun</li>
                                </ul>
                                <div class="step-demo">
                                    <button class="btn btn-sm btn-info" onclick="simulator.simulateViewUsers()">
                                        <i class="fas fa-list me-1"></i> Simulasi Lihat Daftar User
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div class="guide-step">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h6>Cari User (Opsional)</h6>
                                <p>Gunakan form pencarian untuk menemukan user tertentu berdasarkan username, nama lengkap, atau role.</p>
                                <div class="step-demo">
                                    <button class="btn btn-sm btn-primary" onclick="simulator.simulateSearchUser()">
                                        <i class="fas fa-search me-1"></i> Simulasi Cari User
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Step 5 -->
                        <div class="guide-step">
                            <div class="step-number">5</div>
                            <div class="step-content">
                                <h6>Tambah User Baru</h6>
                                <p>Klik tombol <strong>"+ Tambah User Baru"</strong> di bagian atas halaman untuk membuat akun user baru.</p>
                                <div class="step-demo">
                                    <button class="btn btn-sm btn-success" onclick="simulator.simulateAddUser()">
                                        <i class="fas fa-user-plus me-1"></i> Simulasi Tambah User
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Step 6 -->
                        <div class="guide-step">
                            <div class="step-number">6</div>
                            <div class="step-content">
                                <h6>Isi Form User Baru</h6>
                                <p>Lengkapi form dengan informasi user baru:</p>
                                <ul class="ms-3">
                                    <li><strong>Username:</strong> Username unik untuk login</li>
                                    <li><strong>Password:</strong> Password aman (minimal 8 karakter)</li>
                                    <li><strong>Nama Lengkap:</strong> Nama panjang user</li>
                                    <li><strong>Role:</strong> Pilih Admin atau User Biasa</li>
                                </ul>
                                <div class="step-demo">
                                    <small>Kode: modules/users/tambah.php (Form tambah user)</small>
                                </div>
                            </div>
                        </div>

                        <!-- Step 7 -->
                        <div class="guide-step">
                            <div class="step-number">7</div>
                            <div class="step-content">
                                <h6>Edit atau Hapus User</h6>
                                <p>Pada setiap baris user di tabel, tersedia tombol aksi:</p>
                                <ul class="ms-3">
                                    <li><strong>Edit:</strong> Ubah data user (nama, role, password)</li>
                                    <li><strong>Hapus:</strong> Menghapus akun user (memerlukan konfirmasi)</li>
                                </ul>
                                <div class="step-demo">
                                    <button class="btn btn-sm btn-warning me-2" onclick="simulator.simulateEditUser()">
                                        <i class="fas fa-user-edit me-1"></i> Simulasi Edit User
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="simulator.simulateDeleteUser()">
                                        <i class="fas fa-user-slash me-1"></i> Simulasi Hapus User
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="guide-tips mt-4">
                        <h6><i class="fas fa-lightbulb me-2 text-warning"></i>Tips Penting:</h6>
                        <ul>
                            <li>Setiap user harus memiliki username unik dan password yang kuat</li>
                            <li>Role Admin memiliki akses ke menu administratif (Audit Trail, Manajemen User)</li>
                            <li>Role User biasa hanya bisa menggunakan fitur data warkah, bon, dan laporan</li>
                            <li>Catat password baru user untuk mereka sebelum memberikan akses</li>
                            <li>Hapus akun user yang sudah tidak aktif untuk keamanan sistem</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/guide-simulator.js"></script>

<script>
function toggleGuideContent(contentId) {
    document.querySelectorAll('.guide-content-panel').forEach(panel => {
        panel.style.display = 'none';
    });
    
    const selectedPanel = document.getElementById(contentId);
    if (selectedPanel) {
        selectedPanel.style.display = 'block';
        selectedPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}
</script>

<?php include '../../includes/footer.php'; ?>
