// Fungsi untuk menambah field upload berkas dinamis
function addBerkasField() {
    const container = document.getElementById('berkas-fields-container');
    if (!container) return;
    
    const fieldCount = container.querySelectorAll('.berkas-field').length;
    const fieldNum = fieldCount + 1;
    
    const newField = document.createElement('div');
    newField.className = 'berkas-field';
    newField.id = 'berkas-field-' + fieldNum;
    newField.innerHTML = `
        <div class="berkas-field-group">
            <div class="form-row">
                <div class="form-col">
                    <label>Nama Berkas:</label>
                    <input type="text" name="nama_berkas[]" placeholder="Contoh: Surat Permohonan" class="form-control">
                </div>
                <div class="form-col">
                    <label>Deskripsi (opsional):</label>
                    <input type="text" name="deskripsi_berkas[]" placeholder="Deskripsi singkat" class="form-control">
                </div>
            </div>
            <div class="form-row">
                <div class="form-col">
                    <label>Upload PDF:</label>
                    <input type="file" name="file_berkas[]" accept=".pdf" class="form-control pdf-upload" required>
                    <small>Format: PDF (Maksimal 5MB)</small>
                </div>
                <div class="form-col action-col">
                    <button type="button" class="btn btn-danger" onclick="removeBerkasField('${fieldNum}')">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </div>
            </div>
        </div>
    `;
    
    container.appendChild(newField);
    
  
    const pdfInput = newField.querySelector('.pdf-upload');
    pdfInput.addEventListener('change', validatePdfFile);
}

// Fungsi untuk menghapus field upload berkas
function removeBerkasField(fieldNum) {
    const field = document.getElementById('berkas-field-' + fieldNum);
    if (field && confirm('Apakah Anda yakin ingin menghapus field ini?')) {
        field.remove();
    }
}

// Validasi file PDF
function validatePdfFile(e) {
    const input = e.target;
    const file = input.files[0];
    
    if (!file) return;
    
    // Check extension
    const fileName = file.name.toLowerCase();
    if (!fileName.endsWith('.pdf')) {
        alert('Hanya file PDF yang diizinkan!');
        input.value = '';
        return;
    }
    
    // Check size (5MB)
    const maxSize = 5 * 1024 * 1024;
    if (file.size > maxSize) {
        alert('Ukuran file terlalu besar! Maksimal 5MB.');
        input.value = '';
        return;
    }
    
    // Show file info
    const fieldGroup = input.closest('.berkas-field-group');
    let fileInfo = fieldGroup.querySelector('.file-info');
    if (!fileInfo) {
        fileInfo = document.createElement('div');
        fileInfo.className = 'file-info';
        fieldGroup.appendChild(fileInfo);
    }
    fileInfo.innerHTML = `✓ File terseleksi: ${file.name} (${formatFileSize(file.size)})`;
}

// Format ukuran file
function formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Preview list berkas sebelum submit
function previewBerkasBeforeSubmit() {
    const container = document.getElementById('berkas-fields-container');
    if (!container) return;
    
    const fields = container.querySelectorAll('.berkas-field');
    let validCount = 0;
    let fileList = [];
    
    fields.forEach((field, index) => {
        const nama = field.querySelector('input[name="nama_berkas[]"]').value.trim();
        const fileInput = field.querySelector('input[name="file_berkas[]"]');
        const file = fileInput.files[0];
        
        if (file) {
            validCount++;
            fileList.push({
                nama: nama || 'Berkas ' + (index + 1),
                file: file.name,
                size: formatFileSize(file.size)
            });
        }
    });
    
    return fileList;
}

// Form submission validation
document.addEventListener('DOMContentLoaded', function() {
    const formTambahWarkah = document.getElementById('form-tambah-warkah');
    if (formTambahWarkah) {
        formTambahWarkah.addEventListener('submit', function(e) {
            // Validasi data warkah
            const nomorWarkah = document.querySelector('input[name="nomor_warkah"]');
            if (nomorWarkah && !nomorWarkah.value.trim()) {
                e.preventDefault();
                alert('Nomor Warkah tidak boleh kosong!');
                nomorWarkah.focus();
                return false;
            }
            
                    // Validasi bahwa ada minimal 1 berkas (mendukung dua nama input: file_berkas[] dan berkas_files[])
                    const files = Array.from(document.querySelectorAll('input[name="file_berkas[]"], input[name="berkas_files[]"]'));
            let hasFile = false;
                    files.forEach(input => {
                        if (input && input.files && input.files.length > 0) {
                            hasFile = true;
                        }
                    });
            
            if (!hasFile) {
                e.preventDefault();
                alert('Minimal harus ada 1 berkas PDF yang diupload!');
                return false;
            }
        });
    }
    
    // Initialize file inputs dengan event listener
    const fileInputs = document.querySelectorAll('.pdf-upload');
    fileInputs.forEach(input => {
        input.addEventListener('change', validatePdfFile);
    });
});

// Initialize Bootstrap tooltips for sidebar icons and toggle enable/disable based on collapsed state
document.addEventListener('DOMContentLoaded', function() {
    try {
        const sidebarItems = Array.from(document.querySelectorAll('.list-group-item[title]'));
        const tooltipInstances = sidebarItems.map(el => new bootstrap.Tooltip(el, {container: 'body', trigger: 'hover'}));
        const sidebar = document.querySelector('.sidebar');

        function updateTooltips() {
            if (!sidebar) return;
            const isCollapsed = parseInt(window.getComputedStyle(sidebar).width) <= 90 && !sidebar.matches(':hover');
            tooltipInstances.forEach(t => {
                if (isCollapsed) t.enable(); else t.disable();
            });
        }

        updateTooltips();
        if (sidebar) {
            sidebar.addEventListener('mouseenter', updateTooltips);
            sidebar.addEventListener('mouseleave', updateTooltips);
        }
        // also update on window resize
        window.addEventListener('resize', updateTooltips);
    } catch (e) {
        console.warn('Tooltip init error:', e);
    }
});

// Fungsi untuk menghapus berkas dari halaman edit (AJAX)
function deleteBerkasItem(berkasId, nomorWarkah) {
    if (!confirm('Apakah Anda yakin ingin menghapus berkas ini?')) {
        return;
    }
    
    const deleteBtn = document.querySelector(`[data-berkas-id="${berkasId}"]`);
    if (!deleteBtn) return;
    
    // Disable button saat loading
    const originalText = deleteBtn.innerHTML;
    deleteBtn.disabled = true;
    deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menghapus...';
    
    fetch('/Arsip_Bon_Warkah/Modules/Warkah/delete-berkas.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'berkas_id=' + berkasId + '&nomor_warkah=' + encodeURIComponent(nomorWarkah)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Hapus elemen dari DOM
            const berkasItem = document.getElementById('berkas-item-' + berkasId);
            if (berkasItem) {
                berkasItem.style.transition = 'opacity 0.3s ease';
                berkasItem.style.opacity = '0';
                setTimeout(() => berkasItem.remove(), 300);
            }
            alert('Berkas berhasil dihapus!');
        } else {
            alert('Gagal menghapus berkas: ' + (data.error || 'Unknown error'));
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan: ' + error);
        deleteBtn.disabled = false;
        deleteBtn.innerHTML = originalText;
    });
}

// Validasi PDF file
function validatePdfFile(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    const maxSize = 5 * 1024 * 1024; // 5MB
    const validType = 'application/pdf';
    
    if (file.type !== validType) {
        alert('Hanya file PDF yang diizinkan!');
        e.target.value = '';
        return;
    }
    
    if (file.size > maxSize) {
        alert('Ukuran file terlalu besar (maksimal 5MB)!');
        e.target.value = '';
        return;
    }
}

// Select all berkas checkbox
function selectAllBerkas() {
    const selectAllCheckbox = document.getElementById('select-all-berkas');
    const berkasCheckboxes = document.querySelectorAll('.berkas-checkbox');
    
    berkasCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
}

// Delete selected berkas
function deleteSelectedBerkas() {
    const selectedBerkas = document.querySelectorAll('.berkas-checkbox:checked');
    if (selectedBerkas.length === 0) {
        alert('Pilih minimal satu berkas untuk dihapus!');
        return;
    }
    
    if (!confirm('Apakah Anda yakin ingin menghapus ' + selectedBerkas.length + ' berkas?')) {
        return;
    }
    
    const ids = Array.from(selectedBerkas).map(cb => cb.value);
    
    fetch('hapus.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'berkas_ids=' + ids.join(',')
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Berkas berhasil dihapus!');
            location.reload();
        } else {
            alert('Gagal menghapus berkas: ' + data.error);
        }
    });
}

// Download file
function downloadFile(berkasId) {
    window.location.href = 'download.php?id=' + berkasId;
}

// Validasi bentuk form edit warkah
document.addEventListener('DOMContentLoaded', function() {
    const formEditWarkah = document.getElementById('form-edit-warkah');
    if (formEditWarkah) {
        formEditWarkah.addEventListener('submit', function(e) {
            const nomorWarkah = document.querySelector('input[name="nomor_warkah"]');
            if (nomorWarkah && !nomorWarkah.value.trim()) {
                e.preventDefault();
                alert('Nomor Warkah tidak boleh kosong!');
                nomorWarkah.focus();
                return false;
            }
        });
    }
});

// Original code - kept for backward compatibility
const form = document.querySelector('form#yourFormId');
if (form) {
  form.addEventListener('submit', e => {
    const input = form.querySelector('input[name="yourInputName"]');
    if (!input.value.trim()) {
      e.preventDefault();
      alert('Field tidak boleh kosong!');
      input.focus();
    }
  });
}

// Sidebar toggle (responsive): toggles `toggled` on #wrapper
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menu-toggle');
    const wrapper = document.getElementById('wrapper');

    if (!menuToggle || !wrapper) return;

    menuToggle.addEventListener('click', function(e) {
        e.preventDefault();
        wrapper.classList.toggle('toggled');

        // On small screens, add overlay class to body when sidebar is opened for clarity
        const isMobile = window.innerWidth < 768;
        if (isMobile) {
            const opened = wrapper.classList.contains('toggled');
            document.body.classList.toggle('sidebar-open', opened);
        }
    });

    // Close mobile sidebar when clicking outside
    document.addEventListener('click', function(ev) {
        if (window.innerWidth >= 768) return; // only mobile
        if (!wrapper.classList.contains('toggled')) return;
        const target = ev.target;
        const sidebar = document.querySelector('.sidebar');
        if (sidebar && !sidebar.contains(target) && !menuToggle.contains(target)) {
            wrapper.classList.remove('toggled');
            document.body.classList.remove('sidebar-open');
        }
    });

    // Ensure overlay class removed on resize to desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768) {
            document.body.classList.remove('sidebar-open');
        }
    });
});
