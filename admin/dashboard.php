<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_admin_login();

// Mengambil Statistik Ringkas
$total_recipes = $pdo->query("SELECT COUNT(*) FROM recipes")->fetchColumn();
$total_categories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$featured_recipes = $pdo->query("SELECT COUNT(*) FROM recipes WHERE is_featured = 1")->fetchColumn();
$draft_recipes = $pdo->query("SELECT COUNT(*) FROM recipes WHERE status = 'draft'")->fetchColumn();

// Ambil resep terbaru
$stmt_latest = $pdo->query("
    SELECT r.*, c.name AS category_name 
    FROM recipes r 
    LEFT JOIN categories c ON r.category_id = c.id 
    ORDER BY r.id DESC LIMIT 5
");
$latest_recipes = $stmt_latest->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - RasaHati</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=4">
</head>
<body class="admin-body">

    <!-- Navbar Panel Admin -->
    <nav class="navbar scrolled" style="position: absolute;">
        <div class="nav-container">
            <a href="../index.php" class="logo"><span class="logo-accent">Rasa</span>Hati <span style="font-size: 0.9rem; font-weight: 500; color: var(--color-muted-text);">Admin</span></a>
            <ul class="nav-links" style="display: flex;">
                <li><a href="dashboard.php" class="nav-link active">Dashboard</a></li>
                <li><a href="categories.php" class="nav-link">Kelola Kategori</a></li>
                <li><a href="recipes.php" class="nav-link">Kelola Resep</a></li>
                <li><a href="logout.php" class="nav-link" style="color: #C0392B;"><i class="fa-solid fa-power-off"></i> Keluar</a></li>
            </ul>
        </div>
    </nav>

    <!-- Kontainer Panel -->
    <div class="admin-container">
        
        <div class="admin-title-row" style="margin-top: 40px;">
            <div>
                <h1 class="admin-title">Selamat Datang, <?= e($_SESSION['admin_user_name']); ?>!</h1>
                <p style="color: var(--color-muted-text); font-size: 0.95rem;">Kelola seluruh menu resep, masakan, dan kategori piring RasaHati secara cepat.</p>
            </div>
            <div>
                <a href="recipe-create.php" class="btn btn-primary btn-admin"><i class="fa-solid fa-plus"></i> Tambah Resep Baru</a>
            </div>
        </div>

        <!-- Grid Statistik -->
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-plate-wheat"></i></div>
                <div>
                    <div class="stat-number"><?= $total_recipes; ?></div>
                    <div class="stat-label">Total Resep</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="color: var(--color-soft-green); background-color: #F1F9EC;"><i class="fa-solid fa-tags"></i></div>
                <div>
                    <div class="stat-number"><?= $total_categories; ?></div>
                    <div class="stat-label">Total Kategori</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="color: #F1C40F; background-color: #FEF9E7;"><i class="fa-solid fa-star"></i></div>
                <div>
                    <div class="stat-number"><?= $featured_recipes; ?></div>
                    <div class="stat-label">Resep Populer (Featured)</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="color: #E67E22; background-color: #FDF2E9;"><i class="fa-solid fa-file-pen"></i></div>
                <div>
                    <div class="stat-number"><?= $draft_recipes; ?></div>
                    <div class="stat-label">Draf Tulisan</div>
                </div>
            </div>
        </div>

        <!-- Resep Terbaru -->
        <div class="admin-card">
            <h3 style="font-family: var(--font-heading); margin-bottom: 20px; font-size: 1.4rem;">Resep Terbaru Diunggah</h3>
            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Gambar</th>
                            <th>Judul Resep</th>
                            <th>Kategori</th>
                            <th>Waktu Masak</th>
                            <th>Status</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($latest_recipes) > 0): ?>
                            <?php $no = 1; foreach ($latest_recipes as $recipe): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td>
                                        <?php if (!empty($recipe['image'])): ?>
                                            <img src="../uploads/recipes/<?= e($recipe['image']); ?>" alt="Img" style="width: 50px; height: 50px; border-radius: 8px;">
                                        <?php else: ?>
                                            <div style="width: 50px; height: 50px; background-color: var(--color-cream); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--color-orange);"><i class="fa-solid fa-image"></i></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= e($recipe['title']); ?></strong></td>
                                    <td><?= e($recipe['category_name'] ?? 'Uncategorized'); ?></td>
                                    <td><?= e($recipe['cook_time']); ?> mnt</td>
                                    <td>
                                        <span class="badge <?= $recipe['status'] === 'published' ? 'badge-success' : 'badge-warning'; ?>">
                                            <?= ucfirst(e($recipe['status'])); ?>
                                        </span>
                                        <?php if ($recipe['is_featured'] == 1): ?>
                                            <span class="badge badge-primary"><i class="fa-solid fa-star"></i> Featured</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="recipe-edit.php?id=<?= $recipe['id']; ?>" class="btn-admin btn-edit" style="padding: 6px 12px; border-radius: 8px;"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--color-muted-text); padding: 30px;">Belum ada resep terdaftar di sistem.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>