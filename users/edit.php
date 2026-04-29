<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    redirect('../../login.php');
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    redirect('index.php');
}

$user_data = null;
$error = '';

$stmt = $conn->prepare("SELECT id, username, nama_lengkap, role FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user_data = $result->fetch_assoc();
} else {
    redirect('index.php'); // User tidak ditemukan
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $nama_lengkap = $conn->real_escape_string($_POST['nama_lengkap']);
    $password = $_POST['password']; // Bisa kosong jika tidak ingin mengubah password
    $role = $conn->real_escape_string($_POST['role']);

    if (empty($username) || empty($nama_lengkap) || empty($role)) {
        $error = "Username, Nama Lengkap, dan Role harus diisi.";
    } elseif (!empty($password) && strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } else {
        $check_user_query = "SELECT id FROM users WHERE username = ? AND id != ?";
        $stmt_check = $conn->prepare($check_user_query);
        $stmt_check->bind_param("si", $username, $id);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $error = "Username sudah digunakan oleh user lain. Mohon pilih username lain.";
            $stmt_check->close();
        } else {
            $stmt_check->close();
            
            $query = "UPDATE users SET username = ?, nama_lengkap = ?, role = ?";
            $types = "sss";
            $params = [$username, $nama_lengkap, $role];

            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $query .= ", password = ?";
                $types .= "s";
                $params[] = $hashed_password;
            }
            
            $query .= " WHERE id = ?";
            $types .= "i";
            $params[] = $id;

            $stmt_update = $conn->prepare($query);
            if (!$stmt_update) {
                $error = "Prepare failed: (" . $conn->errno . ") " . $conn->error;
            } else {
                $stmt_update->bind_param($types, ...$params);
                if ($stmt_update->execute()) {
                    $stmt_update->close();
                    
                    $old_user_data = [
                        'username' => $user_data['username'],
                        'nama_lengkap' => $user_data['nama_lengkap'],
                        'role' => $user_data['role']
                    ];
                    
                    $new_user_data = [
                        'username' => $username,
                        'nama_lengkap' => $nama_lengkap,
                        'role' => $role
                    ];
                    
                    if (!empty($password)) {
                        $new_user_data['password'] = '[DIUBAH]';
                        $old_user_data['password'] = '[HIDDEN]';
                    }
                    
                    log_user_updated(
                        $conn,
                        $_SESSION['user_id'],
                        $_SESSION['username'],
                        $id,
                        $username,
                        compare_changes($old_user_data, $new_user_data)
                    );
                    
                    $_SESSION['success'] = "Data user berhasil diperbarui!";
                    redirect('index.php');
                } else {
                    $error = "Execute failed: (" . $stmt_update->errno . ") " . $stmt_update->error;
                    $stmt_update->close();
                }
            }
        }
    }
}
include '../../includes/header.php';
?>
                        

<h1 class="mt-4 mb-4">Edit User</h1>

<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-edit me-2"></i> Form Edit User
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
                <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user_data['username']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?= htmlspecialchars($user_data['nama_lengkap']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password (Kosongkan jika tidak ingin mengubah)</label>
                <input type="password" class="form-control" id="password" name="password">
                <small class="form-text text-muted">Minimal 6 karakter jika diisi.</small>
            </div>
            <div class="mb-4">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="user" <?= ($user_data['role'] == 'user') ? 'selected' : '' ?>>User</option>
                    <option value="admin" <?= ($user_data['role'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary me-2"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

