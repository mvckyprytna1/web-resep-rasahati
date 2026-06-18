-- Pembuatan Database RasaVerse
-- Impor file ini langsung di phpMyAdmin cPanel Anda

CREATE TABLE IF NOT EXISTS users (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100) NOT NULL,
email VARCHAR(150) NOT NULL UNIQUE,
password VARCHAR(255) NOT NULL,
role VARCHAR(50) NOT NULL DEFAULT 'admin',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100) NOT NULL,
slug VARCHAR(150) NOT NULL UNIQUE,
description TEXT DEFAULT NULL,
image VARCHAR(255) DEFAULT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS recipes (
id INT AUTO_INCREMENT PRIMARY KEY,
category_id INT DEFAULT NULL,
title VARCHAR(200) NOT NULL,
slug VARCHAR(255) NOT NULL UNIQUE,
excerpt VARCHAR(255) DEFAULT NULL,
description TEXT DEFAULT NULL,
image VARCHAR(255) DEFAULT NULL,
cook_time INT NOT NULL COMMENT 'Durasi memasak dalam satuan menit',
servings INT NOT NULL COMMENT 'Jumlah porsi',
difficulty ENUM('Easy', 'Medium', 'Hard') NOT NULL DEFAULT 'Easy',
ingredients TEXT NOT NULL COMMENT 'Daftar bahan masakan dipisah baris baru',
steps TEXT NOT NULL COMMENT 'Langkah-langkah pembuatan dipisah baris baru',
status ENUM('published', 'draft') NOT NULL DEFAULT 'published',
is_featured TINYINT(1) NOT NULL DEFAULT 0,
created_by INT DEFAULT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Memasukkan Data Kategori Awal (Default)
INSERT INTO categories (id, name, slug, description, image) VALUES
(1, 'Breakfast', 'breakfast', 'Menu sarapan pagi sehat berenergi harian.', NULL),
(2, 'Lunch', 'lunch', 'Menu makan siang lezat, porsi mantap, anti ribet.', NULL),
(3, 'Dinner', 'dinner', 'Makan malam hangat spesial bersama keluarga.', NULL),
(4, 'Dessert', 'dessert', 'Pencuci mulut manis penggugah kebahagiaan.', NULL),
(5, 'Healthy', 'healthy', 'Formula kuliner penuh nutrisi seimbang untuk diet sehat.', NULL),
(6, 'Indonesian Food', 'indonesian-food', 'Warisan resep bumbu otentik Nusantara legendaris.', NULL)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Akun Admin Default: admin@rasaverse.test / password: admin123
-- Hash password dibuat menggunakan password_hash('admin123', PASSWORD_BCRYPT)
INSERT INTO users (id, name, email, password, role) VALUES
(1, 'Administrator RasaVerse', 'admin@rasaverse.test', '$2y$12$8uvcBiZ3GtV7ZFJ75QnpmeSM7gb3xAGDmdHj6s6VE3BU4NZmV56pC', 'admin')
ON DUPLICATE KEY UPDATE email=VALUES(email);