<?php
/**
 * Sanitasi string output untuk mencegah serangan XSS berbahaya.
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Mengubah teks string biasa menjadi format Slug URL friendly.
 */
function generate_slug($text) {
    // Ganti karakter non-alfanumerik dengan strip (-)
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    // Transliterasikan
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    // Hapus karakter yang tidak diinginkan
    $text = preg_replace('~[^-\w]+~', '', $text);
    // Trim tanda - dari awal dan akhir
    $text = trim($text, '-');
    // Hilangkan duplikasi tanda -
    $text = preg_replace('~-+~', '-', $text);
    // Ubah ke huruf kecil semua
    $text = strtolower($text);

    if (empty($text)) {
        return 'n-a';
    }
    return $text;
}

/**
 * Memformat daftar bahan baku (ingredients) / tahapan (steps) agar siap ditampilkan sebagai list.
 */
function parse_newline_to_array($text) {
    if (empty($text)) return [];
    return array_filter(array_map('trim', explode("\n", $text)));
}
?>