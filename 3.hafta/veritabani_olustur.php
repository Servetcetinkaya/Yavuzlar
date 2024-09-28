<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$database_path = "/tmp/yemek_sitesi.db";

try {
    
    $connection = new PDO("sqlite:$database_path");
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    
    $connection->exec("CREATE TABLE IF NOT EXISTS firms (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL
    )");

   
    $connection->exec("CREATE TABLE IF NOT EXISTS restaurants (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        firm_id INTEGER NOT NULL,
        FOREIGN KEY (firm_id) REFERENCES firms(id)
    )");

    
    $connection->exec("CREATE TABLE IF NOT EXISTS meals (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        price REAL NOT NULL,
        restaurant_id INTEGER NOT NULL,
        image_url TEXT DEFAULT '',
        FOREIGN KEY (restaurant_id) REFERENCES restaurants(id)
    )");

    
    $connection->exec("CREATE TABLE IF NOT EXISTS comments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        meal_id INTEGER NOT NULL,
        username TEXT NOT NULL,
        comment TEXT NOT NULL,
        rating INTEGER NOT NULL CHECK (rating >= 1 AND rating <= 10), -- Rating 1-10 arasında
        FOREIGN KEY (meal_id) REFERENCES meals(id)
    )");

    
    $connection->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        role TEXT NOT NULL,
        balance REAL DEFAULT 0,
        deleted INTEGER DEFAULT 0 -- Kullanıcıyı silmek için
    )");

    
    $connection->exec("CREATE TABLE IF NOT EXISTS order_history (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        meal_id INTEGER NOT NULL,
        meal_name TEXT NOT NULL,
        note TEXT DEFAULT '',
        price REAL NOT NULL,
        date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        username TEXT NOT NULL,
        purchased INTEGER DEFAULT 0,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (meal_id) REFERENCES meals(id)
    )");

    
    $hashed_password_admin = hash('sha256', 'admin');
    $hashed_password_servet = hash('sha256', 'servet');

    $connection->exec("INSERT OR IGNORE INTO users (username, password, role) VALUES
        ('admin', '$hashed_password_admin', 'admin'),
        ('servet', '$hashed_password_servet', 'user')
    ");

    echo "Veritabanı ve tablolar başarıyla oluşturuldu.";
    
} catch (PDOException $e) {
    echo "Veritabanı hatası: " . $e->getMessage();
}
?>

