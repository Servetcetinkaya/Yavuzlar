<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$veritabani = "/tmp/yemek_sitesi.db";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kullanici_adi = trim($_POST['kullanici_adi']);
    $sifre = trim($_POST['sifre']);

    try {
        $baglanti = new PDO("sqlite:$veritabani");
        $baglanti->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Kullanıcıyı veritabanından al
        $stmt = $baglanti->prepare("SELECT * FROM kullanicilar WHERE kullanici_adi = :kullanici_adi");
        $stmt->bindParam(':kullanici_adi', $kullanici_adi);
        $stmt->execute();
        
        $kullanici = $stmt->fetch(PDO::FETCH_ASSOC);

        // Eğer kullanıcı bulunduysa, şifreyi kontrol et
        if ($kullanici && $kullanici['sifre'] === $sifre) {
            $_SESSION['kullanici_adi'] = $kullanici['kullanici_adi'];
            $_SESSION['rol'] = $kullanici['rol'];

            if ($kullanici['rol'] == 'admin') {
                header("Location: admin_panel.php");
            } else {
                header("Location: ana_sayfa.php");
            }
            exit();
        } else {
            $hata = "Hatalı kullanıcı adı veya şifre.";
        }
    } catch (PDOException $e) {
        echo "Veritabanı hatası: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap</title>
    <style>
        /* Stil kodları buraya gelecek */
    </style>
</head>
<body>
    <div class="container">
        <h1>Giriş Yap</h1>

        <?php if (isset($hata)): ?>
            <p class="error"><?php echo htmlspecialchars($hata); ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="kullanici_adi">Kullanıcı Adı:</label>
            <input type="text" id="kullanici_adi" name="kullanici_adi" required>
            <label for="sifre">Şifre:</label>
            <input type="password" id="sifre" name="sifre" required>
            <button type="submit">Giriş Yap</button>
        </form>
    </div>
</body>
</html>

