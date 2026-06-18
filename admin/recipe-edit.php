<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_admin_login();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: recipes.php");
    exit();
}

// Ambil Resep Terpilih
$stmt_rec = $pdo->prepare("SELECT * FROM recipes WHERE id = ?");
$stmt_rec->execute([$id]);
$recipe = $stmt_rec->fetch();

if (!$recipe) {
    header("Location: recipes.php");
    exit();
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $title = trim($_POST['title'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $cook_time = (int)($_POST['cook_time'] ?? 0);
    $servings = (int)($_POST['servings'] ?? 0);
    $difficulty = $_POST['difficulty'] ?? 'Easy';
    $ingredients = trim($_POST['ingredients'] ?? '');
    $steps = trim($_POST['steps'] ?? '');
    $status = $_POST['status'] ?? 'published';
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    $slug = generate_slug($title);

    // Validasi Form
    if (empty($title) || empty($ingredients) || empty($steps) || $cook_time <= 0 || $servings <= 0) {
        $error = 'Harap isi seluruh field bertanda wajib dengan benar!';
    } else {
        // Cek Keunikan Slug
        $check = $pdo->prepare("SELECT COUNT(*) FROM recipes WHERE slug = ? AND id != ?");
        $check->execute([$slug, $id]);
        if ($check->fetchColumn() > 0) {
            $slug .= '-' . rand(100, 999);
        }

        $image_name = $recipe['image']; // Default pakai gambar lama
        
        // Proses Upload Gambar Baru jika ada
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp  = $_FILES['image']['tmp_name'];
            $file_name = $_FILES['image']['name'];
            $file_size = $_FILES['image']['size'];
            $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
            $max_size = 3 * 1024 * 1024; // 3MB

            if (!in_array($file_ext, $allowed_exts)) {
                $error = 'Format file gambar tidak didukung! Gunakan format JPG, JPEG, PNG, atau WEBP.';
            } elseif ($file_size > $max_size) {
                $error = 'Ukuran gambar terlalu besar! Maksimal ukuran file adalah 3 Megabytes.';
            } else {
                // Rename acak
                $image_name = 'rec_' . bin2hex(random_bytes(8)) . '.' . $file_ext;
                $target_dir = __DIR__ . '/../uploads/recipes/';
                
                if (move_uploaded_file($file_tmp, $target_dir . $image_name)) {
                    // Hapus gambar lama agar menghemat space disk hosting cPanel
                    if (!empty($recipe['image']) && file_exists($target_dir . $recipe['image'])) {
                        unlink($target_dir . $recipe['image']);
                    }
                } else {
                    $error = 'Gagal menyimpan unggahan file gambar baru ke server!';
                    $image_name = $recipe['image'];
                }
            }
        }

        // Jalankan Update jika tidak ada error
        if (empty($error)) {
            $update = $pdo->prepare("
                UPDATE recipes SET 
                    category_id = :category_id, title = :title, slug = :slug, excerpt = :excerpt, 
                    description = :description, image = :image, cook_time = :cook_time, 
                    servings = :servings, difficulty = :difficulty, ingredients = :ingredients, 
                    steps = :steps, status = :status, is_featured = :is_featured 
                WHERE id = :id
            ");
            
            $update->execute([
                'category_id' => $category_id,
                'title'       => $title,
                'slug'        => $slug,
                'excerpt'     => $excerpt,
                'description' => $description,
                'image'       => $image_name,
                'cook_time'   => $cook_time,
                'servings'    => $servings,
                'difficulty'  => $difficulty,
                'ingredients' => $ingredients,
                'steps'       => $steps,
                'status'      => $status,
                'is_featured' => $is_featured,
                'id'          => $id
            ]);

            header("Location: recipes.php?msg=updated");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Resep Masakan - RasaHati</title>
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
                <h1 class="admin-title">Edit Resep Kuliner</h1>
                <p style="color: var(--color-muted-text);">Modifikasi panduan, waktu saji, maupun tahapan resep.</p>
            </div>
            <div>
                <a href="recipes.php" class="btn btn-secondary btn-admin"><i class="fa-solid fa-chevron-left"></i> Kembali</a>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-box alert-box-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= e($error); ?></div>
        <?php endif; ?>

        <div class="admin-card">
            <form action="recipe-edit.php?id=<?= $id; ?>" method="POST" enctype="multipart/form-data">
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="title">Judul Resep <span style="color: #C0392B;">*</span></label>
                        <input type="text" id="title" name="title" class="form-control" value="<?= e($recipe['title']); ?>" required autofocus>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="category_id">Kategori Menu <span style="color: #C0392B;">*</span></label>
                        <select id="category_id" name="category_id" class="form-control" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id']; ?>" <?= $recipe['category_id'] == $cat['id'] ? 'selected' : ''; ?>><?= e($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="excerpt">Kutipan Ringkas (Excerpt)</label>
                    <input type="text" id="excerpt" name="excerpt" class="form-control" value="<?= e($recipe['excerpt']); ?>" maxlength="255">
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Deskripsi Lengkap / Cerita Resep</label>
                    <textarea id="description" name="description" class="form-control" style="height: 120px;"><?= e($recipe['description']); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="cook_time">Durasi Memasak (Menit) <span style="color: #C0392B;">*</span></label>
                        <input type="number" id="cook_time" name="cook_time" class="form-control" min="1" value="<?= e($recipe['cook_time']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="servings">Porsi Makan (Servings) <span style="color: #C0392B;">*</span></label>
                        <input type="number" id="servings" name="servings" class="form-control" min="1" value="<?= e($recipe['servings']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="difficulty">Tingkat Kesulitan <span style="color: #C0392B;">*</span></label>
                        <select id="difficulty" name="difficulty" class="form-control">
                            <option value="Easy" <?= $recipe['difficulty'] === 'Easy' ? 'selected' : ''; ?>>Easy</option>
                            <option value="Medium" <?= $recipe['difficulty'] === 'Medium' ? 'selected' : ''; ?>>Medium</option>
                            <option value="Hard" <?= $recipe['difficulty'] === 'Hard' ? 'selected' : ''; ?>>Hard</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="ingredients">Daftar Bahan-Bahan <span style="color: #C0392B;">*</span> <small style="color: var(--color-muted-text); font-weight: normal;">(Pisahkan per baris)</small></label>
                        <textarea id="ingredients" name="ingredients" class="form-control" style="height: 200px;" required><?= e($recipe['ingredients']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="steps">Langkah & Instruksi Pembuatan <span style="color: #C0392B;">*</span> <small style="color: var(--color-muted-text); font-weight: normal;">(Pisahkan per baris)</small></label>
                        <textarea id="steps" name="steps" class="form-control" style="height: 200px;" required><?= e($recipe['steps']); ?></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="image">Ganti Foto Hasil Masakan <small style="color: var(--color-muted-text); font-weight: normal;">(Biarkan kosong jika tidak ingin mengubah foto masakan)</small></label>
                    <input type="file" id="image" name="image" class="form-control" accept="image/*">
                    <?php if (!empty($recipe['image'])): ?>
                        <div style="margin-top: 10px;">
                            <span style="font-size: 0.8rem; color: var(--color-muted-text); display: block; margin-bottom: 5px;">Foto Masakan Aktif Saat Ini:</span>
                            <img src="../uploads/recipes/<?= e($recipe['image']); ?>" alt="Current Img" style="width: 120px; height: 120px; border-radius: 12px; object-fit: cover; border: 1px solid var(--color-border-soft);">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-row" style="align-items: center; margin-top: 15px;">
                    <div class="form-group">
                        <label class="form-label" for="status">Status Publikasi</label>
                        <select id="status" name="status" class="form-control">
                            <option value="published" <?= $recipe['status'] === 'published' ? 'selected' : ''; ?>>Published (Langsung Tampil)</option>
                            <option value="draft" <?= $recipe['status'] === 'draft' ? 'selected' : ''; ?>>Draft (Simpan sebagai Draf)</option>
                        </select>
                    </div>
                    <div class="form-group" style="padding-top: 15px;">
                        <label class="form-label" style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" id="is_featured" name="is_featured" value="1" <?= $recipe['is_featured'] == 1 ? 'checked' : ''; ?> style="transform: scale(1.3); accent-color: var(--color-orange);"> 
                            <span>Tampilkan di Menu Populer Pilihan Utama</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-admin" style="border-radius: 12px; margin-top: 25px; padding: 14px 30px;">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Seluruh Perubahan Resep
                </button>
            </form>
        </div>

    </div>

</body>
</html>