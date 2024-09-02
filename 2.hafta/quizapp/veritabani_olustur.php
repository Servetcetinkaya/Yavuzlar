<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


$dbFile = '/tmp/quiz.db';


$db = new SQLite3($dbFile);


$db->exec('DROP TABLE IF EXISTS sorular');
$db->exec('DROP TABLE IF EXISTS kullanicilar');
$db->exec('DROP TABLE IF EXISTS skor_tablosu');
$db->exec('DROP TABLE IF EXISTS yanitlanan_sorular');


$db->exec('
    CREATE TABLE sorular (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        soru TEXT NOT NULL,
        secenekler TEXT NOT NULL,
        dogru_secenek INTEGER NOT NULL
    )
');


$db->exec("INSERT INTO sorular (soru, secenekler, dogru_secenek) VALUES ('Soru 1?', 'A) Seçenek 1, B) Seçenek 2, C) Seçenek 3, D) Seçenek 4', 1)");
$db->exec("INSERT INTO sorular (soru, secenekler, dogru_secenek) VALUES ('Soru 2?', 'A) Seçenek 1, B) Seçenek 2, C) Seçenek 3, D) Seçenek 4', 2)");
$db->exec("INSERT INTO sorular (soru, secenekler, dogru_secenek) VALUES ('Soru 3?', 'A) Seçenek 1, B) Seçenek 2, C) Seçenek 3, D) Seçenek 4', 3)");


$db->exec('
    CREATE TABLE kullanicilar (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        kullanici_adi TEXT NOT NULL UNIQUE,
        sifre TEXT NOT NULL
    )
');


$db->exec('
    INSERT OR IGNORE INTO kullanicilar (kullanici_adi, sifre) VALUES 
    ("admin", "12345"),
    ("servet", "12345"),
    ("ozgur", "12345")
');


$db->exec('
    CREATE TABLE skor_tablosu (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        kullanici_adi TEXT NOT NULL UNIQUE,
        puan INTEGER NOT NULL DEFAULT 0
    )
');


$db->exec('
    CREATE TABLE yanitlanan_sorular (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        kullanici_adi TEXT NOT NULL,
        soru_id INTEGER NOT NULL,
        secenek INTEGER NOT NULL,
        UNIQUE (kullanici_adi, soru_id)
    )
');

echo 'Veritabanı oluşturuldu ve tablolara veriler eklendi.';
?>

