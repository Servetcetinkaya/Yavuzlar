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
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #000; 
            color: #fff; 
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh; 
            text-align: center;
        }
        .form-container {
            background: #333; 
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.2);
            padding: 20px;
            width: 300px;
            max-width: 100%;
        }
        h1 {
            margin-bottom: 20px;
            color: #f4f4f4; 
        }
        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
            color: #ddd; 
        }
        input[type="text"],
        input[type="password"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #555; 
            border-radius: 4px;
            background: #222; 
            color: #fff; 
        }
        input[type="submit"] {
            background-color: #007bff; 
            color: #fff; 
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        input[type="submit"]:hover {
            background-color: #0056b3; 
        }
    </style>
</head>
<body>
    <div class="">
        <h1>Giriş Yap</h1>
        <form method="post">
            <label for="kullanici_adi">Kullanıcı Adı:</label>
            <input type="text" id="kullanici_adi" name="kullanici_adi" required>
            <label for="sifre">Şifre:</label>
            <input type="password" id="sifre" name="sifre" required>
            <input type="submit" value="Giriş Yap">
        </form>
    </div>
</body>
</html>



</body>
</html>

</body>
</html>
