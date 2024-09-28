<?php
$database_path = "/tmp/yemek_sitesi.db";

try {
   
    $connection = new PDO("sqlite:$database_path");
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

   
    $connection->exec("ALTER TABLE users ADD COLUMN deleted INTEGER DEFAULT 0");

    echo "Deleted sütunu başarıyla eklendi.";
    
} catch (PDOException $e) {
    echo "Veritabanı hatası: " . $e->getMessage();
}
?>

