<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


$dbFile = '/tmp/quiz.db';
$db = new SQLite3($dbFile);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kullanici_adi = $_POST['kullanici_adi'] ?? '';
    $sifre = $_POST['sifre'] ?? '';

    
    $sorgu = $db->prepare('SELECT * FROM kullanicilar WHERE kullanici_adi = :kullanici_adi AND sifre = :sifre');
    $sorgu->bindValue(':kullanici_adi', $kullanici_adi, SQLITE3_TEXT);
    $sorgu->bindValue(':sifre', $sifre, SQLITE3_TEXT);
    $sonuc = $sorgu->execute()->fetchArray(SQLITE3_ASSOC);

    if ($sonuc) {
        
        session_start();
        $_SESSION['kullanici_adi'] = $kullanici_adi;
        
        
        if ($kullanici_adi === 'admin') {
            header('Location: admin_panel.php');
            exit();
        } else {
            header('Location: quiz_yarismasi.php');
            exit();
        }
    } else {
        echo 'Kullanıcı adı veya şifre hatalı.';
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Giriş</title>
</head>
<body>
    <h1>Giriş Yap</h1>
    <form method="post">
        <label for="kullanici_adi">Kullanıcı Adı:</label>
        <input type="text" id="kullanici_adi" name="kullanici_adi" required>
        <br>
        <label for="sifre">Şifre:</label>
        <input type="password" id="sifre" name="sifre" required>
        <br>
        <input type="submit" value="Giriş Yap">
    </form>
</body>
</html>

