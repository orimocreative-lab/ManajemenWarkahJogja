<?php
require_once '../../includes/config.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    redirect('../../login.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $nama_lengkap = $conn->real_escape_string($_POST['nama_lengkap']);
    $password = $_POST['password'];
    $role = $conn->real_escape_string($_POST['role']);

    if (empty($username) || empty($nama_lengkap) || empty($password) || empty($role)) {
        $error = "Semua field harus diisi.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } else {
        $check_user_query = "SELECT id FROM users WHERE username = ?";
        $stmt_check = $conn->prepare($check_user_query);
        $stmt_check->bind_param("s", $username);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $error = "Username sudah digunakan. Mohon pilih username lain.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $query_utama = "INSERT INTO users (username, nama_lengkap, password, role) 
                            VALUES (?, ?, ?, ?)";
            $stmt_utama = $conn->prepare($query_utama);
            
            if (!$stmt_utama) {
                $error = "Prepare failed: (" . $conn->errno . ") " . $conn->error;
            } else {
                $stmt_utama->bind_param("ssss", $username, $nama_lengkap, $hashed_password, $role);
                
                if ($stmt_utama->execute()) {
                    $new_user_id = $conn->insert_id;
                    
                    log_user_created($conn, $_SESSION['user_id'], $_SESSION['username'], $username, $nama_lengkap, $role);
                    
                    $_SESSION['success'] = "User berhasil ditambahkan!";
                    redirect('index.php');
                } else {
                    $error = "Execute failed: (" . $stmt_utama->errno . ") " . $stmt_utama->error;
                }
            }
        }
    }
}
?>

<?php include '../../includes/header.php'; ?>

<h1 class="mt-4 mb-4">Tambah User Baru</h1>

<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-user-plus me-2"></i> Form Tambah User
    </div>
    <div class="card-body">
        <?php if (isset($error) && $error != ''): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
                <small class="form-text text-muted">Minimal 6 karakter.</small>
            </div>
            <div class="mb-4">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="user" <?= (($_POST['role'] ?? '') == 'user') ? 'selected' : '' ?>>User</option>
                    <option value="admin" <?= (($_POST['role'] ?? '') == 'admin') ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary me-2"><i class="fas fa-save me-1"></i> Simpan User</button>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>   