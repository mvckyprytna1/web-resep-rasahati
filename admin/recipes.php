<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_admin_login();

$msg = $_GET['msg'] ?? '';

// Proses Hapus Resep
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    
    // Cari tahu nama file gambar lama untuk dihapus secara bersih dari hosting cPanel
    $find_img = $pdo->prepare("SELECT image FROM recipes WHERE id = ?");
    $find_img->execute([$delete_id]);
    $old_image = $find_img->fetchColumn();

    if ($old_image && file_exists(__DIR__ . '/../uploads/recipes/' . $old_image)) {
        unlink(__DIR__ . '/../uploads/recipes/' . $old_image);
    }

    $stmt = $pdo->prepare("DELETE FROM recipes WHERE id = ?");
    $stmt->execute([$delete_id]);
    
    header("Location: recipes.php?msg=deleted");
    exit();
}

// Ambil Seluruh Data Resep + Kategori relasional
$stmt = $pdo->query("
    SELECT r.*, c.name AS category_name 
    FROM recipes r 
    LEFT JOIN categories c ON r.category_id = c.id 
    ORDER BY r.id DESC
");
$recipes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Resep Kuliner - RasaHati</title>
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
                <li><a href="categories.php" class="nav-link">Kelola Kategori</a></li>
                <li><a href="recipes.php" class="nav-link active">Kelola Resep</a></li>
                <li><a href="logout.php" class="nav-link" style="color: #C0392B;"><i class="fa-solid fa-power-off"></i> Keluar</a></li>
            </ul>
        </div>
    </nav>

    <div class="admin-container">
        
        <div class="admin-title-row" style="margin-top: 40px;">
            <div>
                <h1 class="admin-title">Kelola Resep Kuliner</h1>
                <p style="color: var(--color-muted-text);">Tambahkan panduan masakan lezat yang menggugah cita rasa bagi dunia internasional.</p>
            </div>
            <div>
                <a href="recipe-create.php" class="btn btn-primary btn-admin"><i class="fa-solid fa-plus"></i> Tambah Resep Baru</a>
            </div>
        </div>

        <?php if ($msg === 'created'): ?>
            <div class="alert-box alert-box-success"><i class="fa-solid fa-circle-check"></i> Resep masakan sukses dipublikasikan!</div>
        <?php elseif ($msg === 'updated'): ?>
            <div class="alert-box alert-box-success"><i class="fa-solid fa-circle-check"></i> Resep masakan berhasil diperbarui!</div>
        <?php elseif ($msg === 'deleted'): ?>
            <div class="alert-box alert-box-success"><i class="fa-solid fa-circle-check"></i> Resep masakan berhasil dihapus!</div>
        <?php endif; ?>

        <div class="admin-card">
            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Gambar</th>
                            <th>Judul Resep</th>
                            <th>Kategori</th>
                            <th>Dibuat</th>
                            <th>Detail Piring</th>
                            <th>Status</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recipes) > 0): ?>
                            <?php $no = 1; foreach ($recipes as $rec): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td>
                                        <?php if (!empty($rec['image'])): ?>
                                            <img src="../uploads/recipes/<?= e($rec['image']); ?>" alt="Img" style="width: 55px; height: 55px; border-radius: 8px; object-fit: cover;">
                                        <?php else: ?>
                                            <div style="width: 55px; height: 55px; background-color: var(--color-cream); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--color-orange);"><i class="fa-solid fa-image"></i></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= e($rec['title']); ?></strong>
                                    </td>
                                    <td><?= e($rec['category_name'] ?? 'Uncategorized'); ?></td>
                                    <td style="font-size: 0.8rem; color: var(--color-muted-text);"><?= date('d M Y', strtotime($rec['created_at'])); ?></td>
                                    <td style="font-size: 0.8rem;">
                                        ⏱️ <?= e($rec['cook_time']); ?> Menit<br>
                                        👥 <?= e($rec['servings']); ?> Porsi<br>
                                        📶 <?= e($rec['difficulty']); ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $rec['status'] === 'published' ? 'badge-success' : 'badge-warning'; ?>">
                                            <?= ucfirst(e($rec['status'])); ?>
                                        </span>
                                        <?php if ($rec['is_featured'] == 1): ?>
                                            <span class="badge badge-primary"><i class="fa-solid fa-star"></i> Featured</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right; vertical-align: middle;">
                                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                            <a href="recipe-edit.php?id=<?= $rec['id']; ?>" class="btn-admin btn-edit" style="padding: 6px 12px; border-radius: 8px;"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                            <a href="recipes.php?delete_id=<?= $rec['id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus resep masakan ini?')" class="btn-admin btn-danger" style="padding: 6px 12px; border-radius: 8px;"><i class="fa-solid fa-trash"></i> Hapus</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: var(--color-muted-text); padding: 40px;">Belum ada resep makanan yang ditulis.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>