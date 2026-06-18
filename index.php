<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

// Ambil Kategori Terpopuler/Aktif
$categories = $pdo->query("SELECT c.*, COUNT(r.id) as total_recipes FROM categories c LEFT JOIN recipes r ON c.id = r.category_id AND r.status = 'published' GROUP BY c.id ORDER BY c.name ASC")->fetchAll();

// Ambil Menu Resep Unggulan / Terpopuler (Featured = 1)
$featured_recipes = $pdo->query("SELECT r.*, c.name as category_name FROM recipes r LEFT JOIN categories c ON r.category_id = c.id WHERE r.status = 'published' AND r.is_featured = 1 ORDER BY r.id DESC LIMIT 6")->fetchAll();

// Jika resep populer kosong, ambil resep published biasa sebagai fallback
if (count($featured_recipes) === 0) {
    $featured_recipes = $pdo->query("SELECT r.*, c.name as category_name FROM recipes r LEFT JOIN categories c ON r.category_id = c.id WHERE r.status = 'published' ORDER BY r.id DESC LIMIT 6")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RasaHati - Resep Makanan Modern & Premium RasaHati</title>
    
    <!-- Meta SEO & Deskripsi -->
    <meta name="description" content="Temukan resep masakan rumahan, makanan sehat, dessert, masakan Nusantara, dan menu harian dengan tampilan premium, hangat, dan menggugah selera di RasaHati.">
    
    <!-- Google Fonts: Playfair Display & Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome CDN untuk Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Stylesheet Utama -->
    <link rel="stylesheet" href="assets/css/style.css?v=2">
</head>
<body>

    <!-- NAVBAR SECTION -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <span class="logo-accent">Rasa</span>Hati
            </a>
            
            <ul class="nav-links" id="nav-links">
                <li><a href="index.php" class="nav-link active">Home</a></li>
                <li><a href="recipes.php" class="nav-link">Recipes</a></li>
                <li><a href="#categories" class="nav-link">Categories</a></li>
                <li><a href="#why-us" class="nav-link">About</a></li>
                <li><a href="#meal-plan" class="nav-link">Meal Plans</a></li>
                <li class="mobile-auth">
                    <?php if (is_admin_logged_in()): ?>
                        <a href="admin/dashboard.php" class="btn-signup"><i class="fa-solid fa-gauge"></i> Admin Dashboard</a>
                        <a href="admin/logout.php" class="btn-login" style="color: #C0392B;"><i class="fa-solid fa-power-off"></i> Out</a>
                    <?php else: ?>
                        <a href="admin/login.php" class="btn-login"><i class="fa-solid fa-right-to-bracket"></i> Login Admin</a>
                    <?php endif; ?>
                </li>
                <li class="mobile-search">
                    <form action="recipes.php" method="GET" class="search-box-mobile">
                        <input type="text" name="search" placeholder="Cari resep kuliner...">
                        <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                    </form>
                </li>
            </ul>

            <div class="nav-actions">
                <div class="nav-auth">
                    <?php if (is_admin_logged_in()): ?>
                        <a href="admin/dashboard.php" class="btn-signup" style="background-color: var(--color-dark-brown); font-size: 0.8rem; padding: 8px 16px;"><i class="fa-solid fa-gauge"></i> Dashboard Admin</a>
                        <a href="admin/logout.php" class="btn-login" style="color: #C0392B; font-size: 0.8rem;"><i class="fa-solid fa-power-off"></i> Keluar</a>
                    <?php else: ?>
                        <a href="admin/login.php" class="btn-login"><i class="fa-solid fa-user-gear"></i> Admin Area</a>
                    <?php endif; ?>
                </div>
                <form action="recipes.php" method="GET" class="search-box">
                    <input type="text" name="search" id="search-input" placeholder="Cari resep...">
                    <button type="submit" id="search-btn" aria-label="Cari Resep"><i class="fa-solid fa-magnifying-glass"></i></button>
                </form>
                <button class="hamburger" id="hamburger" aria-label="Menu Navigasi">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </button>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero" id="home">
        <div class="hero-container">
            <div class="hero-content fade-in-element">
                <span class="hero-badge">
                    <i class="fa-solid fa-utensils"></i> Fresh Recipes Daily
                </span>
                <h1 class="hero-title">
                    Discover Recipes That Make Every Meal Feel Special
                </h1>
                <p class="hero-subtitle">
                    Jelajahi resep masakan yang lezat dan mudah diikuti. Dikurasi khusus untuk masakan rumahan harian, makan malam keluarga, diet sehat, serta momen kuliner tak terlupakan.
                </p>
                <div class="hero-actions">
                    <a href="recipes.php" class="btn btn-primary">
                        Explore Recipes <i class="fa-solid fa-arrow-right"></i>
                    </a>
                    <a href="#recipes" class="btn btn-secondary">
                        View Popular Menu
                    </a>
                </div>
            </div>
            
            <div class="hero-visual fade-in-element">
                <div class="organic-bg-shape"></div>
                
                <div class="main-food-card">
                    <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80" 
                         alt="Makanan Sehat Premium RasaHati" 
                         class="hero-food-img"
                         onerror="this.src='https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=800&q=80'">
                </div>
                
                <div class="floating-card card-time">
                    <div class="floating-icon icon-orange">
                        <i class="fa-regular fa-clock"></i>
                    </div>
                    <div>
                        <p class="float-title">30 Min Recipe</p>
                        <p class="float-desc">Mudah & Cepat</p>
                    </div>
                </div>

                <div class="floating-card card-rating">
                    <div class="floating-icon icon-yellow">
                        <i class="fa-solid fa-star"></i>
                    </div>
                    <div>
                        <p class="float-title">4.9 Rating</p>
                        <p class="float-desc">Ulasan Pengguna</p>
                    </div>
                </div>

                <div class="floating-card card-healthy">
                    <div class="floating-icon icon-green">
                        <i class="fa-solid fa-heart"></i>
                    </div>
                    <div>
                        <p class="float-title">Healthy Choice</p>
                        <p class="float-desc">Nutrisi Seimbang</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURED CATEGORIES SECTION -->
    <section class="categories" id="categories">
        <div class="section-header">
            <span class="section-badge">Kategori Pilihan</span>
            <h2 class="section-title">Explore by Category</h2>
            <p class="section-subtitle">Temukan formula resep yang sempurna sesuai dengan suasana hati, jadwal harian, dan selera lidah Anda.</p>
        </div>

        <div class="categories-grid">
            <?php foreach ($categories as $cat): ?>
                <?php
                    // Pilih icon kustom dinamis berdasarkan nama kategori
                    $icon = 'fa-bowl-food';
                    $cat_lower = strtolower($cat['name']);
                    if (str_contains($cat_lower, 'breakfast')) $icon = 'fa-mug-saucer';
                    elseif (str_contains($cat_lower, 'lunch')) $icon = 'fa-bowl-food';
                    elseif (str_contains($cat_lower, 'dinner')) $icon = 'fa-plate-wheat';
                    elseif (str_contains($cat_lower, 'dessert')) $icon = 'fa-ice-cream';
                    elseif (str_contains($cat_lower, 'healthy') || str_contains($cat_lower, 'vegan')) $icon = 'fa-seedling';
                    elseif (str_contains($cat_lower, 'indonesian') || str_contains($cat_lower, 'nusantara')) $icon = 'fa-pepper-hot';
                ?>
                <a href="category.php?slug=<?= e($cat['slug']); ?>" class="category-card">
                    <div class="category-icon-wrapper">
                        <i class="fa-solid <?= $icon; ?>"></i>
                    </div>
                    <h3 class="category-name"><?= e($cat['name']); ?></h3>
                    <span class="category-count"><?= $cat['total_recipes']; ?> Resep</span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- POPULAR RECIPES SECTION -->
    <section class="recipes-section" id="recipes">
        <div class="section-header">
            <span class="section-badge">Menu Favorit</span>
            <h2 class="section-title">Popular Recipes This Week</h2>
            <p class="section-subtitle">Koleksi hidangan paling disukai minggu ini oleh para koki rumahan profesional di seluruh dunia.</p>
        </div>

        <div class="recipes-grid">
            <?php foreach ($featured_recipes as $recipe): ?>
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
                        <p class="recipe-desc"><?= e($recipe['excerpt'] ?? 'Klik untuk mempelajari bumbu resep rahasia masakan lezat ini.'); ?></p>
                        <div class="recipe-footer">
                            <span class="recipe-level <?= strtolower($recipe['difficulty']); ?>"><?= e($recipe['difficulty']); ?></span>
                            <a href="recipe-detail.php?slug=<?= e($recipe['slug']); ?>" class="btn-view-recipe" style="color: var(--color-orange); font-weight: 600;">View Recipe <i class="fa-solid fa-chevron-right"></i></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- WHY CHOOSE US SECTION -->
    <section class="why-us" id="why-us">
        <div class="section-header">
            <span class="section-badge">Mengapa Kami</span>
            <h2 class="section-title">Cook Smarter, Eat Better</h2>
            <p class="section-subtitle">Kami menyederhanakan proses memasak yang rumit agar siapapun dapat menciptakan keajaiban kuliner di dapur rumah sendiri.</p>
        </div>

        <div class="benefit-container">
            <div class="benefit-card">
                <div class="benefit-icon-box">
                    <i class="fa-solid fa-clipboard-list"></i>
                </div>
                <h3 class="benefit-title">Easy Step-by-Step Recipes</h3>
                <p class="benefit-desc">Petunjuk memasak yang runtut, takaran akurat, beserta panduan visual sehingga dipastikan anti gagal bahkan bagi pemula sekalipun.</p>
            </div>

            <div class="benefit-card">
                <div class="benefit-icon-box">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </div>
                <h3 class="benefit-title">Smart Meal Inspiration</h3>
                <p class="benefit-desc">Rekomendasi resep cerdas harian yang disesuaikan dengan bahan sisa makanan di kulkas Anda untuk meminimalkan limbah dapur.</p>
            </div>

            <div class="benefit-card">
                <div class="benefit-icon-box">
                    <i class="fa-solid fa-apple-whole"></i>
                </div>
                <h3 class="benefit-title">Fresh & Healthy Ingredients</h3>
                <p class="benefit-desc">Fokus pada kombinasi nutrisi seimbang dan bahan alami utuh (whole food) untuk menopang gaya hidup sehat jangka panjang Anda.</p>
            </div>
        </div>
    </section>

    <!-- MEAL PLAN CTA SECTION -->
    <section class="meal-plan" id="meal-plan">
        <div class="meal-plan-card">
            <div class="meal-plan-content">
                <span class="meal-badge">Fitur Eksklusif</span>
                <h2 class="meal-title">Plan Your Meals Without the Stress</h2>
                <p class="meal-subtitle">Simpan resep favorit Anda, susun rencana menu mingguan otomatis, dan ciptakan kebiasaan belanja bahan masakan yang super efisien.</p>
                <button class="btn btn-primary" id="btn-meal-plan">Start Meal Planning</button>
            </div>
            <div class="meal-plan-preview">
                <div class="meal-item-card">
                    <div class="meal-day">Senin</div>
                    <div class="meal-food-info">
                        <p class="meal-name">Avocado Toast + Salmon</p>
                        <p class="meal-cal">420 kkal • Healthy Choice</p>
                    </div>
                    <span class="meal-done"><i class="fa-solid fa-check"></i></span>
                </div>
                <div class="meal-item-card active-meal">
                    <div class="meal-day">Selasa</div>
                    <div class="meal-food-info">
                        <p class="meal-name">Spicy Chicken Rice Bowl</p>
                        <p class="meal-cal">580 kkal • Lunch Special</p>
                    </div>
                    <span class="meal-done-empty"><i class="fa-regular fa-circle"></i></span>
                </div>
                <div class="meal-item-card">
                    <div class="meal-day">Rabu</div>
                    <div class="meal-food-info">
                        <p class="meal-name">Creamy Garlic Pasta</p>
                        <p class="meal-cal">650 kkal • Family Dinner</p>
                    </div>
                    <span class="meal-done-empty"><i class="fa-regular fa-circle"></i></span>
                </div>
            </div>
        </div>
    </section>

    <!-- TESTIMONIALS SECTION -->
    <section class="testimonials">
        <div class="section-header">
            <span class="section-badge">Ulasan Masyarakat</span>
            <h2 class="section-title">Loved by Home Cooks</h2>
            <p class="section-subtitle">Dengarkan kisah menyenangkan dari para koki rumahan yang sukses mentransformasikan kebiasaan makan mereka.</p>
        </div>

        <div class="testimonials-grid">
            <div class="testi-card">
                <div class="testi-rating">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                </div>
                <p class="testi-quote">"RasaHati sangat membantu saya yang sibuk bekerja kantoran. Resep 30 menitnya benar-benar akurat dan rasanya setara kelas restoran bintang lima!"</p>
                <div class="testi-user">
                    <div class="user-avatar">
                        <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=150&q=80" alt="Sarah Amelia">
                    </div>
                    <div>
                        <h4 class="user-name">Sarah Amelia</h4>
                        <p class="user-role">Busy Mom / Entrepreneur</p>
                    </div>
                </div>
            </div>

            <div class="testi-card">
                <div class="testi-rating">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                </div>
                <p class="testi-quote">"Sebagai food blogger pemula, saya banyak belajar teknik plating dan kombinasi bumbu otentik Nusantara dari menu-menu RasaHati. Desain webnya luar biasa premium!"</p>
                <div class="testi-user">
                    <div class="user-avatar">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&q=80" alt="Budi Santoso">
                    </div>
                    <div>
                        <h4 class="user-name">Budi Santoso</h4>
                        <p class="user-role">Food Blogger</p>
                    </div>
                </div>
            </div>

            <div class="testi-card">
                <div class="testi-rating">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star-4-5"></i>
                </div>
                <p class="testi-quote">"Semenjak berlangganan resep harian sehat di sini, kadar kolesterol saya turun stabil. Memasak sehat di rumah tidak lagi terasa hambar dan membosankan."</p>
                <div class="testi-user">
                    <div class="user-avatar">
                        <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=150&q=80" alt="Nadia Utami">
                    </div>
                    <div>
                        <h4 class="user-name">Nadia Utami</h4>
                        <p class="user-role">Home Cook / Yogi</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- NEWSLETTER SECTION -->
    <section class="newsletter">
        <div class="newsletter-card">
            <h2 class="news-title">Get Fresh Recipes in Your Inbox</h2>
            <p class="news-desc">Dapatkan kiriman kompilasi resep rahasia terpopuler, tips memasak efisien, serta menu mingguan sehat langsung ke email Anda gratis setiap hari Senin.</p>
            <form class="newsletter-form" id="newsletter-form">
                <input type="email" placeholder="Masukkan alamat email terbaik Anda..." required aria-label="Alamat Email">
                <button type="submit" class="btn btn-primary">Subscribe Now</button>
            </form>
            <p class="news-privacy"><i class="fa-solid fa-shield-halved"></i> Kami menghargai privasi Anda. Batalkan langganan kapan saja.</p>
        </div>
    </section>

    <!-- FOOTER SECTION -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-brand-column">
                <a href="#" class="footer-logo"><span class="logo-accent">Rasa</span>Hati</a>
                <p class="footer-desc">Pionir platform literasi kuliner modern yang mempersembahkan resep-resep bercita rasa mewah internasional dengan metode memasak yang disederhanakan.</p>
                <div class="social-links">
                    <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#" aria-label="Pinterest"><i class="fa-brands fa-pinterest"></i></a>
                    <a href="#" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                </div>
            </div>

            <div class="footer-links-column">
                <h4>Explore</h4>
                <ul>
                    <li><a href="recipes.php">Popular Recipes</a></li>
                    <li><a href="#categories">Food Categories</a></li>
                    <li><a href="recipes.php">Weekly Meal Plans</a></li>
                    <li><a href="recipes.php">Healthy Food Choice</a></li>
                </ul>
            </div>

            <div class="footer-links-column">
                <h4>About Us</h4>
                <ul>
                    <li><a href="#why-us">Our Culinary Story</a></li>
                    <li><a href="#">Chef Ambassadors</a></li>
                    <li><a href="#">Careers</a></li>
                    <li><a href="#">Press Kit</a></li>
                </ul>
            </div>

            <div class="footer-links-column">
                <h4>Contact & Support</h4>
                <ul>
                    <li><a href="#">FAQ Help Center</a></li>
                    <li><a href="#">Advertising</a></li>
                    <li><a href="#">Terms of Service</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 RasaHati. RasaHati Premium Culinary Experience. Didesain secara profesional.</p>
        </div>
    </footer>

    <!-- Custom Dialog Modal untuk Aksi UI -->
    <div class="custom-modal" id="custom-modal">
        <div class="modal-content">
            <span class="close-modal" id="close-modal">&times;</span>
            <div class="modal-body-icon" id="modal-icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h3 id="modal-title">Sukses!</h3>
            <p id="modal-message">Pesan Anda berhasil kami rekam.</p>
            <button class="btn btn-primary btn-close-modal" id="btn-close-modal-ok">Selesai</button>
        </div>
    </div>

    <!-- Script Utama -->
    <script src="assets/js/script.js"></script>
</body>
</html>