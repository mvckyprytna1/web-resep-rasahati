<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

if (is_admin_logged_in()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND role = 'admin' LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Autentikasi Sukses
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user_id'] = $user['id'];
            $_SESSION['admin_user_name'] = $user['name'];
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = 'Email atau password salah!';
        }
    } else {
        $error = 'Mohon lengkapi semua kolom form!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - RasaHati</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=4">
</head>
<body class="login-body">

    <div class="login-wrapper">
        <a href="../index.php" class="login-logo"><span class="logo-accent">Rasa</span>Hati</a>
        <p class="login-tagline">Silakan login untuk mengelola resep makanan RasaHati.</p>

        <?php if (!empty($error)): ?>
            <div class="alert-box alert-box-danger">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?= e($error); ?></span>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group" style="text-align: left;">
                <label class="form-label" for="email">Alamat Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Masukkan Email anda" required autofocus>
            </div>
            
            <div class="form-group" style="text-align: left;">
                <label class="form-label" for="password">Kata Sandi</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; border-radius: 12px; padding: 14px; margin-top: 10px;">
                Masuk Dashboard <i class="fa-solid fa-right-to-bracket"></i>
            </button>
        </form>

        <p style="margin-top: 25px; font-size: 0.8rem; color: var(--color-muted-text);">
            <a href="../index.php"><i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda</a>
        </p>
    </div>

</body>
</html>