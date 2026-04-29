/**
 * Reminder Function - Real-time notification for overdue items
 * Check every 5 minutes for overdue items
 */

// Function to show toast notification
function showReminderToast(overdueCount, upcomingCount) {
    const toastHTML = `
        <div class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body bg-warning-light p-3">
                    <i class="fas fa-bell me-2 text-warning"></i>
                    <strong>Pengingat:</strong> ${overdueCount > 0 ? `${overdueCount} berkas belum tersedia. ` : ''}${upcomingCount > 0 ? `${upcomingCount} berkas akan jatuh tempo.` : ''}
                    <a href="#" class="ms-2 text-primary" onclick="window.location.href = '/Arsip_Bon_Warkah/Modules/Bon/index.php'">Lihat Detail</a>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    const toastContainer = document.getElementById('toastContainer');
    if (toastContainer) {
        toastContainer.insertAdjacentHTML('beforeend', toastHTML);
        const toastElement = toastContainer.lastElementChild;
        const toast = new bootstrap.Toast(toastElement);
        toast.show();
    }
}

// Function to check for overdue items periodically
function checkReminders() {
    fetch('/Arsip_Bon_Warkah/api/check-reminders.php')
        .then(response => response.json())
        .then(data => {
            if (data.overdue_count > 0 || data.upcoming_count > 0) {
                showReminderToast(data.overdue_count, data.upcoming_count);
            }
        })
        .catch(error => console.error('Error checking reminders:', error));
}

// Check reminders when page loads
document.addEventListener('DOMContentLoaded', function() {
    checkReminders();
    
    // Check every 5 minutes (300000 ms)
    setInterval(checkReminders, 5 * 60 * 1000);
});
