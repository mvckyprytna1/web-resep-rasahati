<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_admin_login();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $slug = generate_slug($name);

    if (!empty($name)) {
        // Cek Keunikan Slug
        $check = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE slug = ?");
        $check->execute([$slug]);
        if ($check->fetchColumn() > 0) {
            $slug .= '-' . rand(10, 99);
        }

        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (:name, :slug, :description)");
        $stmt->execute([
            'name' => $name,
            'slug' => $slug,
            'description' => $description
        ]);

        header("Location: categories.php?msg=created");
        exit();
    } else {
        $error = 'Nama kategori wajib diisi!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kategori - RasaHati</title>
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
                <h1 class="admin-title">Tambah Kategori Baru</h1>
                <p style="color: var(--color-muted-text);">Buat grup piring sajian menu masakan RasaHati yang baru.</p>
            </div>
            <div>
                <a href="categories.php" class="btn btn-secondary btn-admin"><i class="fa-solid fa-chevron-left"></i> Kembali</a>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-box alert-box-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= e($error); ?></div>
        <?php endif; ?>

        <div class="admin-card">
            <form action="category-create.php" method="POST">
                <div class="form-group">
                    <label class="form-label" for="name">Nama Kategori</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Contoh: Dinner, Sweet Treats, Vegan Plan" required autofocus>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="description">Deskripsi Singkat</label>
                    <textarea id="description" name="description" class="form-control" style="height: 120px;" placeholder="Tuliskan keterangan pelengkap untuk kategori piring masakan ini..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-admin" style="border-radius: 12px; margin-top: 10px;">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Kategori
                </button>
            </form>
        </div>

    </div>

</body>
</html>