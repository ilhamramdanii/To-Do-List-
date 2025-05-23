<?php
// Mulai session
session_start();

// Sertakan file function.php yang berisi semua fungsi logika To-Do List
require_once 'function.php';

// Tangani permintaan POST (form tambah, edit, hapus, toggle status)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tambah tugas baru
    if (isset($_POST['task']) && isset($_POST['deadline'])) {
        tambahTugas($_POST['task'], $_POST['deadline']);
        header('Location: ' . $_SERVER['PHP_SELF']); // Redirect untuk mencegah form resubmission
        exit;
    }

    // Toggle status tugas (selesai ↔ belum)
    if (isset($_POST['toggle'])) {
        toggleTugas((int)$_POST['toggle']);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // Hapus tugas
    if (isset($_POST['delete'])) {
        hapusTugas((int)$_POST['delete']);
        $_SESSION['taskDeleted'] = true; // Tandai untuk ditampilkan sebagai notifikasi
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // Simpan hasil edit tugas
    if (isset($_POST['edit_save']) && isset($_POST['edit_index'])) {
        editTugas((int)$_POST['edit_index'], $_POST['edit_text'], $_POST['edit_deadline']);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Cek apakah ada notifikasi penghapusan
$taskDeleted = false;
if (isset($_SESSION['taskDeleted']) && $_SESSION['taskDeleted']) {
    $taskDeleted = true;
    unset($_SESSION['taskDeleted']); // Hapus setelah ditampilkan
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>To-Do List Stylish</title>
    <!-- Bootstrap CSS dan ikon -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container py-5">
    <!-- Header aplikasi -->
    <div class="text-center mb-5">
        <h1 class="fw-bold">📋 To-Do List Stylish</h1>
        <p class="text-muted">Kelola tugasmu dengan rapi, cepat, dan elegan!</p>
    </div>

    <!-- ✅ Notifikasi sukses jika tugas dihapus -->
    <?php if ($taskDeleted): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            ✅ Tugas berhasil dihapus.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_POST['edit_index'])): ?>
        <!-- Form Edit Tugas -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="form-title mb-3">✏️ Edit Tugas</div>
                <form method="post" class="row g-2">
                    <input type="hidden" name="edit_index" value="<?= htmlspecialchars($_POST['edit_index']) ?>">
                    <div class="col-md-6">
                        <input type="text" name="edit_text" class="form-control" value="<?= htmlspecialchars($_POST['edit_text']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <input type="datetime-local" name="edit_deadline" class="form-control" value="<?= htmlspecialchars($_POST['edit_deadline']) ?>" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="edit_save" class="btn btn-success w-100">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <!-- Form Tambah Tugas Baru -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="form-title mb-3">➕ Tambah Tugas Baru</div>
                <form method="post" class="row g-2">
                    <div class="col-md-6">
                        <input type="text" name="task" class="form-control" placeholder="Contoh: Belajar PHP" required>
                    </div>
                    <div class="col-md-4">
                        <input type="datetime-local" name="deadline" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Tambah</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- ✅ Modal konfirmasi hapus -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="post" id="deleteForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Apakah Anda yakin ingin menghapus tugas ini?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <input type="hidden" name="delete" id="deleteInput">
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tampilkan daftar tugas -->
    <?php tampilkanDaftar(); ?>
</div>

<!-- JavaScript Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Script tambahan (belum digunakan karena tombol hapus pakai form biasa) -->
<script>
    // Jika ingin mengaktifkan modal hapus lewat tombol khusus
    document.querySelectorAll('.delete-button').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const index = this.getAttribute('data-index');
            document.getElementById('deleteInput').value = index;
            const modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
            modal.show();
        });
    });
</script>
</body>
</html>
