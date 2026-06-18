<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_admin_login();

$msg = $_GET['msg'] ?? '';

// Proses Hapus Kategori
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    
    // Proteksi hapus kategori jika kategori tersebut masih dipakai di tabel resep
    $check = $pdo->prepare("SELECT COUNT(*) FROM recipes WHERE category_id = ?");
    $check->execute([$delete_id]);
    $count = $check->fetchColumn();

    if ($count > 0) {
        header("Location: categories.php?msg=cannot_delete");
        exit();
    } else {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$delete_id]);
        header("Location: categories.php?msg=deleted");
        exit();
    }
}

// Ambil semua kategori
$categories = $pdo->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - RasaHati</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=4">
</head>
<body class="admin-body">

    <nav class="navbar scrolled" style="position: absolute;">
        <div class="nav-container">
            <a href="../index.php" class="logo"><span class="logo-accent">Rasa</span>Hati <span style="font-size: 0.9rem; font-weight: 500; color: var(--color-muted-text);">Admin</span></a>
            <ul class="nav-links" style="display: flex;">
                <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                <li><a href="categories.php" class="nav-link active">Kelola Kategori</a></li>
                <li><a href="recipes.php" class="nav-link">Kelola Resep</a></li>
                <li><a href="logout.php" class="nav-link" style="color: #C0392B;"><i class="fa-solid fa-power-off"></i> Keluar</a></li>
            </ul>
        </div>
    </nav>

    <div class="admin-container">
        
        <div class="admin-title-row" style="margin-top: 40px;">
            <div>
                <h1 class="admin-title">Kelola Kategori</h1>
                <p style="color: var(--color-muted-text);">Kelompokkan resep agar pencarian dan penyajian menu makanan terasa rapi.</p>
            </div>
            <div>
                <a href="category-create.php" class="btn btn-primary btn-admin"><i class="fa-solid fa-plus"></i> Tambah Kategori</a>
            </div>
        </div>

        <?php if ($msg === 'created'): ?>
            <div class="alert-box alert-box-success"><i class="fa-solid fa-circle-check"></i> Kategori sukses ditambahkan!</div>
        <?php elseif ($msg === 'updated'): ?>
            <div class="alert-box alert-box-success"><i class="fa-solid fa-circle-check"></i> Kategori sukses diperbarui!</div>
        <?php elseif ($msg === 'deleted'): ?>
            <div class="alert-box alert-box-success"><i class="fa-solid fa-circle-check"></i> Kategori sukses dihapus!</div>
        <?php elseif ($msg === 'cannot_delete'): ?>
            <div class="alert-box alert-box-danger"><i class="fa-solid fa-triangle-exclamation"></i> Kategori tidak bisa dihapus karena masih digunakan oleh beberapa resep aktif!</div>
        <?php endif; ?>

        <div class="admin-card">
            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori</th>
                            <th>Slug</th>
                            <th>Deskripsi Singkat</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($categories as $cat): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><strong><?= e($cat['name']); ?></strong></td>
                                <td><code><?= e($cat['slug']); ?></code></td>
                                <td style="color: var(--color-muted-text);"><?= e($cat['description'] ?? '-'); ?></td>
                                <td style="text-align: right; display: flex; gap: 8px; justify-content: flex-end;">
                                    <a href="category-edit.php?id=<?= $cat['id']; ?>" class="btn-admin btn-edit" style="padding: 6px 12px; border-radius: 8px;"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                    <a href="categories.php?delete_id=<?= $cat['id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')" class="btn-admin btn-danger" style="padding: 6px 12px; border-radius: 8px;"><i class="fa-solid fa-trash"></i> Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>