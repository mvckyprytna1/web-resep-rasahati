<?php
if (session_status() === PHP_SESSION_NONE) {
    // Jalankan konfigurasi session yang aman untuk cPanel
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

/**
 * Memastikan pengguna sudah login sebagai admin sebelum masuk ke halaman administrasi.
 */
function require_admin_login() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header("Location: login.php");
        exit();
    }
}

/**
 * Mengecek apakah user saat ini sedang login sebagai admin.
 */
function is_admin_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}
?>