<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$veritabani = "/tmp/yemek_sitesi.db";
try {
    $baglanti = new PDO("sqlite:$veritabani");
    $baglanti->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Veritabanı bağlantı hatası: " . $e->getMessage();
    exit();
}

// Yemekleri getir
$yemekler = [];
try {
    $stmt = $baglanti->query("SELECT * FROM yemekler");
    $yemekler = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Yemekleri getirme hatası: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yemek Alışveriş Sitesi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .profil-icon {
            float: right;
            margin: 10px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid #333;
        }
        .profil-icon img {
            width: 100%;
            height: auto;
        }
        .yemek-karti {
            display: inline-block;
            background-color: #fff;
            width: 22%;
            margin: 10px;
            padding: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            text-align: center;
            vertical-align: top;
        }
        .yemek-karti img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
        }
        .butonlar a {
            display: inline-block;
            padding: 10px 15px;
            text-decoration: none;
            background-color: #333;
            color: #fff;
            border-radius: 4px;
            margin: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Yemek İlanları</h1>

     <div class="profil-icon" onclick="window.location='profil.php'">
    <img src="<?php echo htmlspecialchars($_SESSION['profil_resmi'] ?? 'default.png'); ?>" alt="Profil Resmi">
</div>


        <div class="sepet-icon">
            <a href="sepet.php">🛒 Sepet (<?php echo count($_SESSION['sepet'] ?? []); ?>)</a>
        </div>

        <div class="yemek-listesi">
            <?php foreach ($yemekler as $yemek): ?>
                <div class="yemek-karti">
                    <img src="<?php echo htmlspecialchars($yemek['resim_url']); ?>" alt="<?php echo htmlspecialchars($yemek['ad']); ?>">
                    <div class="yemek-ad"><?php echo htmlspecialchars($yemek['ad']); ?></div>
                    <div class="yemek-fiyat"><?php echo htmlspecialchars($yemek['fiyat']); ?> TL</div>
                    <div class="butonlar">
                        <a href="yemek_detay.php?id=<?php echo htmlspecialchars($yemek['id']); ?>">Detay</a>
                        <a href="ana_sayfa.php?id=<?php echo htmlspecialchars($yemek['id']); ?>">Satın Al</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>

