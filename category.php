<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

$slug = trim($_GET['slug'] ?? '');
if (empty($slug)) {
    header("Location: recipes.php");
    exit();
}

// Cari kategori
$stmt_cat = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
$stmt_cat->execute([$slug]);
$category = $stmt_cat->fetch();

if (!$category) {
    header("Location: recipes.php");
    exit();
}

// Ambil resep published berdasarkan kategori
$stmt_rec = $pdo->prepare("
    SELECT r.*, c.name AS category_name 
    FROM recipes r 
    LEFT JOIN categories c ON r.category_id = c.id 
    WHERE r.status = 'published' AND r.category_id = ? 
    ORDER BY r.id DESC
");
$stmt_rec->execute([$category['id']]);
$recipes = $stmt_rec->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori: <?= e($category['name']); ?> - RasaHati</title>
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
                <li><a href="recipes.php" class="nav-link">Recipes</a></li>
                <li><a href="index.php#categories" class="nav-link active">Categories</a></li>
                <li><a href="index.php#why-us" class="nav-link">About</a></li>
                <li><a href="index.php#meal-plan" class="nav-link">Meal Plans</a></li>
            </ul>
        </div>
    </nav>

    <!-- Header Section -->
    <section class="recipes-section" style="padding-top: 140px; min-height: auto;">
        <div class="section-header" style="margin-bottom: 50px;">
            <span class="section-badge">Kategori Sajian</span>
            <h1 class="section-title" style="font-size: 2.8rem;"><?= e($category['name']); ?></h1>
            <p class="section-subtitle"><?= e($category['description'] ?? 'Berikut kumpulan panduan racikan bumbu masakan lezat pilihan.'); ?></p>
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
                            <span class="recipe-tag"><?= e($recipe['category_name']); ?></span>
                        </div>
                        <div class="recipe-body">
                            <div class="recipe-meta">
                                <span class="meta-item"><i class="fa-regular fa-clock"></i> <?= e($recipe['cook_time']); ?> Min</span>
                                <span class="meta-item"><i class="fa-solid fa-users"></i> <?= e($recipe['servings']); ?> Porsi</span>
                            </div>
                            <h3 class="recipe-title"><?= e($recipe['title']); ?></h3>
                            <p class="recipe-desc"><?= e($recipe['excerpt'] ?? 'Simak ulasan, daftar takaran bumbu masakan, dan langkah lengkap pembuatannya.'); ?></p>
                            <div class="recipe-footer">
                                <span class="recipe-level <?= strtolower($recipe['difficulty']); ?>"><?= e($recipe['difficulty']); ?></span>
                                <a href="recipe-detail.php?slug=<?= e($recipe['slug']); ?>" class="btn-view-recipe" style="color: var(--color-orange); font-weight: 600;">View Recipe <i class="fa-solid fa-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                    <div style="font-size: 3.5rem; color: var(--color-orange); margin-bottom: 20px;"><i class="fa-solid fa-utensils"></i></div>
                    <h3 style="font-family: var(--font-heading); font-size: 1.6rem; margin-bottom: 10px;">Masakan Belum Tersedia</h3>
                    <p style="color: var(--color-muted-text); max-width: 500px; margin: 0 auto;">Dapur kami saat ini sedang menyiapkan rangkaian formula resep terbaik untuk menu kategori ini. Dapatkan notifikasi dengan mendaftar buletin RasaHati.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- FOOTER SECTION -->
    <footer class="footer" style="margin-top: 100px;">
        <div class="footer-container">
            <div class="footer-brand-column">
                <a href="#" class="footer-logo"><span class="logo-accent">Rasa</span>Hati</a>
                <p class="footer-desc">Pionir platform literasi kuliner modern yang mempersembahkan resep-resep bercita rasa mewah internasional.</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 RasaHati. RasaHati Premium Culinary Experience. Didesain secara profesional.</p>
        </div>
    </footer>

    <script src="assets/js/script.js?v=4"></script>
</body>
</html>