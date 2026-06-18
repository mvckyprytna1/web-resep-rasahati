<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

$search = trim($_GET['search'] ?? '');
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Query dasar
$query_str = "
    SELECT r.*, c.name AS category_name 
    FROM recipes r 
    LEFT JOIN categories c ON r.category_id = c.id 
    WHERE r.status = 'published'
";

$params = [];

if (!empty($search)) {
    $query_str .= " AND (r.title LIKE :search OR r.excerpt LIKE :search OR r.description LIKE :search)";
    $params['search'] = '%' . $search . '%';
}

if ($category_id > 0) {
    $query_str .= " AND r.category_id = :category_id";
    $params['category_id'] = $category_id;
}

$query_str .= " ORDER BY r.id DESC";

$stmt = $pdo->prepare($query_str);
$stmt->execute($params);
$recipes = $stmt->fetchAll();

// Ambil semua kategori untuk filter
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resep Kuliner RasaHati - RasaHati</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=4">
</head>
<body>

    <nav class="navbar scrolled">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <span class="logo-accent">Rasa</span>Hati
            </a>
            
            <ul class="nav-links" id="nav-links">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="recipes.php" class="nav-link active">Recipes</a></li>
                <li><a href="index.php#categories" class="nav-link">Categories</a></li>
                <li><a href="index.php#why-us" class="nav-link">About</a></li>
                <li><a href="index.php#meal-plan" class="nav-link">Meal Plans</a></li>
                <li class="mobile-auth">
                    <?php if (is_admin_logged_in()): ?>
                        <a href="admin/dashboard.php" class="btn-signup"><i class="fa-solid fa-gauge"></i> Admin Panel</a>
                    <?php else: ?>
                        <a href="admin/login.php" class="btn-login">Login Admin</a>
                    <?php endif; ?>
                </li>
            </ul>

            <div class="nav-actions">
                <div class="nav-auth">
                    <?php if (is_admin_logged_in()): ?>
                        <a href="admin/dashboard.php" class="btn-signup" style="background-color: var(--color-dark-brown); font-size: 0.8rem; padding: 8px 16px;"><i class="fa-solid fa-gauge"></i> Dashboard</a>
                    <?php else: ?>
                        <a href="admin/login.php" class="btn-login"><i class="fa-solid fa-user-gear"></i> Admin Area</a>
                    <?php endif; ?>
                </div>
                <button class="hamburger" id="hamburger" aria-label="Menu Navigasi">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </button>
            </div>
        </div>
    </nav>

    <!-- Header Section -->
    <section class="recipes-section" style="padding-top: 140px; min-height: auto;">
        <div class="section-header" style="margin-bottom: 40px;">
            <span class="section-badge">Arsip Resep Kuliner</span>
            <h1 class="section-title" style="font-size: 2.8rem;">Daftar Semua Resep</h1>
            <p class="section-subtitle">Temukan racikan rasa dari piring masakan modern buatan Anda sendiri.</p>
        </div>

        <!-- Filter & Search Panel -->
        <div class="admin-container" style="max-width: 1000px; margin-top: 0; margin-bottom: 45px;">
            <form action="recipes.php" method="GET" style="display: flex; gap: 15px; flex-wrap: wrap;">
                <div style="flex-grow: 1; min-width: 280px; position: relative;">
                    <input type="text" name="search" class="form-control" value="<?= e($search); ?>" placeholder="Ketik nama bahan baku atau judul masakan..." style="background-color: var(--color-white); padding-left: 20px; border-radius: 50px; border: 1.5px solid var(--color-border-soft);">
                </div>
                
                <div style="min-width: 200px;">
                    <select name="category" class="form-control" onchange="this.form.submit()" style="background-color: var(--color-white); border-radius: 50px; border: 1.5px solid var(--color-border-soft);">
                        <option value="0">Semua Kategori</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id']; ?>" <?= $category_id === (int)$cat['id'] ? 'selected' : ''; ?>><?= e($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary" style="padding: 12px 28px; border-radius: 50px;">
                    <i class="fa-solid fa-magnifying-glass"></i> Cari
                </button>
                
                <?php if (!empty($search) || $category_id > 0): ?>
                    <a href="recipes.php" class="btn btn-secondary" style="border-radius: 50px; padding: 12px 24px;">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Grid Resep -->
        <div class="recipes-grid">
            <?php if (count($recipes) > 0): ?>
                <?php foreach ($recipes as $recipe): ?>
                    <div class="recipe-card">
                        <div class="recipe-img-container">
                            <?php if (!empty($recipe['image'])): ?>
                                <img src="uploads/recipes/<?= e($recipe['image']); ?>" alt="<?= e($recipe['title']); ?>" class="recipe-img" onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80'">
                            <?php else: ?>
                                <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80" alt="Default Image" class="recipe-img">
                            <?php endif; ?>
                            <span class="recipe-tag"><?= e($recipe['category_name'] ?? 'Resep'); ?></span>
                        </div>
                        <div class="recipe-body">
                            <div class="recipe-meta">
                                <span class="meta-item"><i class="fa-regular fa-clock"></i> <?= e($recipe['cook_time']); ?> Min</span>
                                <span class="meta-item"><i class="fa-solid fa-users"></i> <?= e($recipe['servings']); ?> Porsi</span>
                            </div>
                            <h3 class="recipe-title"><?= e($recipe['title']); ?></h3>
                            <p class="recipe-desc"><?= e($recipe['excerpt'] ?? 'Tekan tombol untuk mempelajari instruksi racikan bahan dan takaran masakan.'); ?></p>
                            <div class="recipe-footer">
                                <span class="recipe-level <?= strtolower($recipe['difficulty']); ?>"><?= e($recipe['difficulty']); ?></span>
                                <a href="recipe-detail.php?slug=<?= e($recipe['slug']); ?>" class="btn-view-recipe" style="color: var(--color-orange); font-weight: 600;">View Recipe <i class="fa-solid fa-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                    <div style="font-size: 3.5rem; color: var(--color-orange); margin-bottom: 20px;"><i class="fa-solid fa-cookie-bite"></i></div>
                    <h3 style="font-family: var(--font-heading); font-size: 1.6rem; margin-bottom: 10px;">Resep Tidak Ditemukan</h3>
                    <p style="color: var(--color-muted-text); max-width: 500px; margin: 0 auto;">Maaf, resep masakan dengan parameter pencarian tersebut saat ini belum terdaftar di dapur RasaHati.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- FOOTER SECTION -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-brand-column">
                <a href="#" class="footer-logo"><span class="logo-accent">Rasa</span>Hati</a>
                <p class="footer-desc">Pionir platform literasi kuliner modern yang mempersembahkan resep-resep bercita rasa mewah internasional dengan metode memasak yang disederhanakan.</p>
            </div>
            <div class="footer-links-column">
                <h4>Explore</h4>
                <ul>
                    <li><a href="recipes.php">Popular Recipes</a></li>
                    <li><a href="index.php#categories">Food Categories</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 RasaHati. RasaHati Premium Culinary Experience. Didesain secara profesional.</p>
        </div>
    </footer>

    <script src="assets/js/script.js?v=4"></script>
</body>
</html>