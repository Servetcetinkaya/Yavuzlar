<?php
// Hata raporlamayı aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

$veritabani = "/tmp/yemek_sitesi.db";

try {
    $baglanti = new PDO("sqlite:$veritabani");
    $baglanti->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Yemekler tablosunu oluştur
    $baglanti->exec("CREATE TABLE IF NOT EXISTS yemekler (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ad TEXT NOT NULL,
        fiyat REAL NOT NULL,
        restoran TEXT NOT NULL,
        resim_url TEXT DEFAULT ''
    )");

    // Restoranlar tablosunu oluştur
    $baglanti->exec("CREATE TABLE IF NOT EXISTS restoranlar (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ad TEXT NOT NULL
    )");

    // Yorumlar tablosunu sil ve yeniden oluştur
    $baglanti->exec("DROP TABLE IF EXISTS yorumlar");
    $baglanti->exec("CREATE TABLE yorumlar (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        yemek_id INTEGER NOT NULL,
        kullanici_adi TEXT NOT NULL,
        yorum TEXT NOT NULL,
        puan INTEGER,
        tarih TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (yemek_id) REFERENCES yemekler(id)
    )");

    // Kullanıcılar tablosunu oluştur
    $baglanti->exec("CREATE TABLE IF NOT EXISTS kullanicilar (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        kullanici_adi TEXT NOT NULL UNIQUE,
        sifre TEXT NOT NULL,
        rol TEXT NOT NULL,
        bakiye REAL DEFAULT 0,
        profil_resmi TEXT DEFAULT ''
    )");

    // İndirimler tablosunu oluştur
    $baglanti->exec("CREATE TABLE IF NOT EXISTS indirimler (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        yemek_id INTEGER NOT NULL,
        indirim_orani REAL NOT NULL,
        FOREIGN KEY (yemek_id) REFERENCES yemekler(id)
    )");

    // Sepet notları tablosunu oluştur
    $baglanti->exec("CREATE TABLE IF NOT EXISTS sepet_notlari (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        kullanici_id INTEGER NOT NULL,
        yemek_id INTEGER NOT NULL,
        not_text TEXT,
        FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id),
        FOREIGN KEY (yemek_id) REFERENCES yemekler(id)
    )");

    // Kuponlar tablosunu oluştur
    $baglanti->exec("CREATE TABLE IF NOT EXISTS kuponlar (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        kod TEXT NOT NULL UNIQUE,
        restoran TEXT NOT NULL,
        indirim_orani REAL NOT NULL,
        aktif INTEGER DEFAULT 1
    )");

    // Admin ve Servet kullanıcılarını ekle
    $hash_sifre_admin = hash('sha256', 'admin');
    $hash_sifre_servet = hash('sha256', 'servet');

    $baglanti->exec("INSERT OR IGNORE INTO kullanicilar (kullanici_adi, sifre, rol) VALUES
        ('admin', '$hash_sifre_admin', 'admin'),
        ('servet', '$hash_sifre_servet', 'user')
    ");

    echo "Veritabanı başarıyla oluşturuldu.";
} catch (PDOException $e) {
    echo "Veritabanı oluşturma hatası: " . $e->getMessage();
}
?>

