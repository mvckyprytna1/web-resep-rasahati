<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

$slug = trim($_GET['slug'] ?? '');
if (empty($slug)) {
    header("Location: recipes.php");
    exit();
}

// Ambil Detail Resep + Kategori relasional
$stmt = $pdo->prepare("
    SELECT r.*, c.name AS category_name, c.slug AS category_slug 
    FROM recipes r 
    LEFT JOIN categories c ON r.category_id = c.id 
    WHERE r.slug = ? AND r.status = 'published'
");
$stmt->execute([$slug]);
$recipe = $stmt->fetch();

if (!$recipe) {
    header("Location: recipes.php");
    exit();
}

// Parsing daftar bahan & langkah memasak dari baris baru
$ingredients_list = parse_newline_to_array($recipe['ingredients']);
$steps_list = parse_newline_to_array($recipe['steps']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($recipe['title']); ?> - RasaHati</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=4">
    <style>
        .detail-header {
            padding-top: 150px;
            padding-bottom: 60px;
            background: linear-gradient(135deg, var(--color-warm-white) 70%, var(--color-cream) 100%);
        }
        .detail-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 24px;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 50px;
            margin-top: 50px;
        }
        .detail-hero-img-box {
            width: 100%;
            height: 480px;
            border-radius: 36px;
            overflow: hidden;
            box-shadow: var(--shadow-medium);
            border: 8px solid var(--color-white);
            margin-bottom: 40px;
        }
        .detail-hero-img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .detail-meta-pill-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }
        .meta-pill {
            background-color: var(--color-white);
            border: 1px solid var(--color-border-soft);
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 0.88rem;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }
        .recipe-block {
            background-color: var(--color-white);
            border: 1px solid var(--color-border-soft);
            border-radius: 28px;
            padding: 35px;
            box-shadow: var(--shadow-soft);
            margin-bottom: 30px;
        }
        .recipe-block-title {
            font-family: var(--font-heading);
            font-size: 1.6rem;
            color: var(--color-dark-brown);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1.5px solid var(--color-border-soft);
            padding-bottom: 15px;
        }
        /* Daftar List Bahan */
        .ingredient-list-ui {
            list-style: none;
        }
        .ingredient-list-ui li {
            padding: 12px 0;
            border-bottom: 1px dashed var(--color-border-soft);
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 0.95rem;
        }
        .ingredient-list-ui li:last-child {
            border-bottom: none;
        }
        .ingredient-checkbox {
            width: 18px;
            height: 18px;
            accent-color: var(--color-orange);
            cursor: pointer;
        }
        /* Langkah Tahapan Memasak */
        .step-list-ui {
            list-style: none;
            counter-reset: step-counter;
        }
        .step-list-ui li {
            position: relative;
            padding-left: 55px;
            margin-bottom: 25px;
            font-size: 0.95rem;
        }
        .step-list-ui li::before {
            counter-increment: step-counter;
            content: counter(step-counter);
            position: absolute;
            left: 0;
            top: 0;
            width: 36px;
            height: 36px;
            background-color: var(--color-cream);
            color: var(--color-deep-orange);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
        }
        @media (max-width: 768px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }
            .detail-hero-img-box {
                height: 300px;
            }
            .detail-header {
                padding-top: 110px;
            }
        }
    </style>
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
            </ul>
        </div>
    </nav>

    <!-- Header Detail -->
    <header class="detail-header">
        <div class="detail-container">
            
            <a href="recipes.php" style="color: var(--color-orange); font-weight: 600; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 15px;"><i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Resep</a>
            
            <h1 style="font-family: var(--font-heading); font-size: 3rem; color: var(--color-dark-brown); margin-bottom: 20px; line-height: 1.2;"><?= e($recipe['title']); ?></h1>
            
            <?php if (!empty($recipe['excerpt'])): ?>
                <p style="color: var(--color-muted-text); font-size: 1.15rem; font-style: italic; margin-bottom: 30px;"><?= e($recipe['excerpt']); ?></p>
            <?php endif; ?>

            <div class="detail-meta-pill-row">
                <span class="meta-pill" style="color: var(--color-deep-orange); font-weight: 600;"><i class="fa-solid fa-tags"></i> <?= e($recipe['category_name'] ?? 'Resep'); ?></span>
                <span class="meta-pill"><i class="fa-regular fa-clock"></i> <?= e($recipe['cook_time']); ?> Menit Memasak</span>
                <span class="meta-pill"><i class="fa-solid fa-users"></i> <?= e($recipe['servings']); ?> Porsi Makan</span>
                <span class="meta-pill"><i class="fa-solid fa-signal"></i> Level: <?= e($recipe['difficulty']); ?></span>
            </div>

            <!-- Gambar Unggulan Resep -->
            <div class="detail-hero-img-box">
                <?php if (!empty($recipe['image'])): ?>
                    <img src="uploads/recipes/<?= e($recipe['image']); ?>" alt="<?= e($recipe['title']); ?>" onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=1200&q=80'">
                <?php else: ?>
                    <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=1200&q=80" alt="Default Image">
                <?php endif; ?>
            </div>

            <!-- Grid Detail Konten -->
            <div class="detail-grid">
                
                <!-- Sisi Kiri: Bahan & Langkah Memasak -->
                <div>
                    
                    <?php if (!empty($recipe['description'])): ?>
                        <div class="recipe-block">
                            <h3 class="recipe-block-title" style="border: none; padding-bottom: 0; margin-bottom: 10px;"><i class="fa-solid fa-feather-pointed"></i> Catatan Koki</h3>
                            <p style="color: var(--color-muted-text); line-height: 1.7; font-size: 0.95rem; text-align: justify;"><?= nl2br(e($recipe['description'])); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Daftar Bahan baku masakan -->
                    <div class="recipe-block">
                        <h2 class="recipe-block-title"><i class="fa-solid fa-basket-shopping" style="color: var(--color-orange);"></i> Bahan Masakan</h2>
                        <ul class="ingredient-list-ui">
                            <?php foreach ($ingredients_list as $ingredient): ?>
                                <li>
                                    <input type="checkbox" class="ingredient-checkbox">
                                    <span><?= e($ingredient); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Instruksi Tahapan Memasak -->
                    <div class="recipe-block">
                        <h2 class="recipe-block-title"><i class="fa-solid fa-kitchen-set" style="color: var(--color-orange);"></i> Langkah Pembuatan</h2>
                        <ol class="step-list-ui">
                            <?php foreach ($steps_list as $step): ?>
                                <li><?= e($step); ?></li>
                            <?php endforeach; ?>
                        </ol>
                    </div>

                </div>

                <!-- Sisi Kanan: Sidebar & Nutrisi Inspirasi -->
                <div>
                    <div class="recipe-block" style="background-color: var(--color-cream); border-color: var(--color-border-soft);">
                        <h3 style="font-family: var(--font-heading); font-size: 1.3rem; margin-bottom: 15px; color: var(--color-dark-brown);">Panduan Masakan Sehat</h3>
                        <p style="font-size: 0.88rem; color: var(--color-muted-text); margin-bottom: 20px; line-height: 1.6;">Gunakan bahan organik segar dari pasar lokal demi menjaga tingkat kesegaran vitamin dan nutrisi optimal piring kuliner Anda.</p>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(42, 24, 16, 0.08); padding-bottom: 8px; font-size: 0.85rem;">
                                <span>Estimasi Kalori</span>
                                <strong>~340 kkal</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(42, 24, 16, 0.08); padding-bottom: 8px; font-size: 0.85rem;">
                                <span>Protein Nabati</span>
                                <strong>12 gram</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding-bottom: 8px; font-size: 0.85rem;">
                                <span>Lemak Sehat</span>
                                <strong>8 gram</strong>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </header>

    <!-- FOOTER SECTION -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-brand-column">
                <a href="#" class="footer-logo"><span class="logo-accent">Rasa</span>Hati</a>
                <p class="footer-desc">Platform kuliner modern yang menyajikan resep-resep bercita rasa premium dengan metode memasak yang disederhanakan agar mudah dipraktikkan.</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 RasaHati. RasaHati Premium Culinary Experience. Didesain secara profesional.</p>
        </div>
    </footer>

    <script src="assets/js/script.js?v=4"></script>
    <script>
        // Logika mencentang checklist bahan masakan di sidebar detail
        document.querySelectorAll('.ingredient-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const text = this.nextElementSibling;
                if (this.checked) {
                    text.style.textDecoration = 'line-through';
                    text.style.color = 'var(--color-muted-text)';
                } else {
                    text.style.textDecoration = 'none';
                    text.style.color = 'var(--color-dark-brown)';
                }
            });
        });
    </script>
</body>
</html>