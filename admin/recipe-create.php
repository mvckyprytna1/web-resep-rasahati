<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_admin_login();

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
        $check = $pdo->prepare("SELECT COUNT(*) FROM recipes WHERE slug = ?");
        $check->execute([$slug]);
        if ($check->fetchColumn() > 0) {
            $slug .= '-' . rand(100, 999);
        }

        $image_name = null;
        
        // Proses Upload Gambar
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp  = $_FILES['image']['tmp_name'];
            $file_name = $_FILES['image']['name'];
            $file_size = $_FILES['image']['size'];
            $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
            $max_size = 3 * 1024 * 1024; // Maksimal ukuran 3MB

            if (!in_array($file_ext, $allowed_exts)) {
                $error = 'Format file gambar tidak didukung! Gunakan format JPG, JPEG, PNG, atau WEBP.';
            } elseif ($file_size > $max_size) {
                $error = 'Ukuran gambar terlalu besar! Maksimal ukuran file adalah 3 Megabytes.';
            } else {
                // Rename unik otomatis untuk menghindari bentrok file
                $image_name = 'rec_' . bin2hex(random_bytes(8)) . '.' . $file_ext;
                $target_dir = __DIR__ . '/../uploads/recipes/';
                
                // Buat folder jika belum ada di public_html
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }

                if (!move_uploaded_file($file_tmp, $target_dir . $image_name)) {
                    $error = 'Gagal menyimpan unggahan file gambar ke server cPanel!';
                    $image_name = null;
                }
            }
        }

        // Simpan Data jika tidak ada error
        if (empty($error)) {
            $stmt = $pdo->prepare("
                INSERT INTO recipes (
                    category_id, title, slug, excerpt, description, image, 
                    cook_time, servings, difficulty, ingredients, steps, 
                    status, is_featured, created_by
                ) VALUES (
                    :category_id, :title, :slug, :excerpt, :description, :image, 
                    :cook_time, :servings, :difficulty, :ingredients, :steps, 
                    :status, :is_featured, :created_by
                )
            ");
            
            $stmt->execute([
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
                'created_by'  => $_SESSION['admin_user_id']
            ]);

            header("Location: recipes.php?msg=created");
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
    <title>Tambah Resep Baru - RasaHati</title>
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
                <h1 class="admin-title">Tambah Resep Baru</h1>
                <p style="color: var(--color-muted-text);">Tulis panduan resep masakan premium RasaHati baru.</p>
            </div>
            <div>
                <a href="recipes.php" class="btn btn-secondary btn-admin"><i class="fa-solid fa-chevron-left"></i> Kembali</a>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-box alert-box-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= e($error); ?></div>
        <?php endif; ?>

        <div class="admin-card">
            <form action="recipe-create.php" method="POST" enctype="multipart/form-data">
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="title">Judul Resep <span style="color: #C0392B;">*</span></label>
                        <input type="text" id="title" name="title" class="form-control" placeholder="Contoh: Spicy Honey Garlic Wings" required autofocus>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="category_id">Kategori Menu <span style="color: #C0392B;">*</span></label>
                        <select id="category_id" name="category_id" class="form-control" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id']; ?>"><?= e($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="excerpt">Kutipan Ringkas (Excerpt)</label>
                    <input type="text" id="excerpt" name="excerpt" class="form-control" placeholder="Tulis deskripsi 1 baris penggoda selera pembaca..." maxlength="255">
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Deskripsi Lengkap / Cerita Resep</label>
                    <textarea id="description" name="description" class="form-control" style="height: 120px;" placeholder="Tuliskan ulasan mendalam, asal masakan, atau pengantar menu masakan ini..."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="cook_time">Durasi Memasak (Menit) <span style="color: #C0392B;">*</span></label>
                        <input type="number" id="cook_time" name="cook_time" class="form-control" min="1" placeholder="Misal: 30" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="servings">Porsi Makan (Servings) <span style="color: #C0392B;">*</span></label>
                        <input type="number" id="servings" name="servings" class="form-control" min="1" placeholder="Misal: 4" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="difficulty">Tingkat Kesulitan <span style="color: #C0392B;">*</span></label>
                        <select id="difficulty" name="difficulty" class="form-control">
                            <option value="Easy">Easy</option>
                            <option value="Medium">Medium</option>
                            <option value="Hard">Hard</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="ingredients">Daftar Bahan-Bahan <span style="color: #C0392B;">*</span> <small style="color: var(--color-muted-text); font-weight: normal;">(Pisahkan per baris)</small></label>
                        <textarea id="ingredients" name="ingredients" class="form-control" style="height: 200px;" placeholder="Contoh:&#10;500g Sayap Ayam Segar&#10;3 Siung Bawang Putih cincang&#10;2 Sdm Madu Alami&#10;1 Sdt Garam Industri" required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="steps">Langkah & Instruksi Pembuatan <span style="color: #C0392B;">*</span> <small style="color: var(--color-muted-text); font-weight: normal;">(Pisahkan per baris)</small></label>
                        <textarea id="steps" name="steps" class="form-control" style="height: 200px;" placeholder="Contoh:&#10;Bersihkan ayam dan tiriskan sampai kering.&#10;Goreng sayap ayam ke minyak panas hingga kuning keemasan.&#10;Tumis bawang putih lalu tuangkan saus madu gurih.&#10;Campur rata ayam dan sajikan selagi renyah." required></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="image">Foto Hasil Masakan <small style="color: var(--color-muted-text); font-weight: normal;">(Format: JPG, PNG, WEBP. Maks 3MB)</small></label>
                    <input type="file" id="image" name="image" class="form-control" accept="image/*">
                </div>

                <div class="form-row" style="align-items: center; margin-top: 15px;">
                    <div class="form-group">
                        <label class="form-label" for="status">Status Publikasi</label>
                        <select id="status" name="status" class="form-control">
                            <option value="published">Published (Langsung Tampil)</option>
                            <option value="draft">Draft (Simpan sebagai Draf)</option>
                        </select>
                    </div>
                    <div class="form-group" style="padding-top: 15px;">
                        <label class="form-label" style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" id="is_featured" name="is_featured" value="1" style="transform: scale(1.3); accent-color: var(--color-orange);"> 
                            <span>Tampilkan di Menu Populer Pilihan Utama</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-admin" style="border-radius: 12px; margin-top: 25px; padding: 14px 30px;">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Publikasikan Resep Sekarang
                </button>
            </form>
        </div>

    </div>

</body>
</html>