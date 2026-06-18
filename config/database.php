<?php
// Pengaturan kredensial database cPanel Anda
// Sesuaikan variabel di bawah ini saat diunggah ke hosting cPanel
$host = "localhost";
$user = "vicj7142_user_example_db"; // Masukkan username database cPanel Anda
$pass = "supriyanto"; // Masukkan password database cPanel Anda
$db   = "vicj7142_example_db"; // Masukkan nama database cPanel Anda

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>