<?php
session_start();

$veritabani = "/tmp/yemek_sitesi.db";
try {
    $baglanti = new PDO("sqlite:$veritabani");
    $baglanti->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Veritabanı bağlantı hatası: " . $e->getMessage();
    exit();
}


$sepet = $_SESSION['sepet'] ?? [];
$toplam_tutar = 0;


if (isset($_GET['action']) && $_GET['action'] == 'sil' && isset($_GET['id'])) {
    $urun_id = intval($_GET['id']);
    if (isset($sepet[$urun_id])) {
        unset($sepet[$urun_id]); 
        $_SESSION['sepet'] = $sepet; 
    }
    header("Location: sepet.php"); 
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bakiye'])) {
    $yuklenecek_bakiye = floatval($_POST['bakiye']);
    $_SESSION['kullanici_bakiyesi'] = ($_SESSION['kullanici_bakiyesi'] ?? 0) + $yuklenecek_bakiye;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['not'])) {
    $_SESSION['siparis_notu'] = trim($_POST['not']);
}


if (isset($_GET['action']) && $_GET['action'] == 'satinal') {
    foreach ($sepet as $urun) {
        $toplam_tutar += $urun['fiyat'] * $urun['adet'];
    }

    $kullanici_bakiyesi = $_SESSION['kullanici_bakiyesi'] ?? 0;

    
    if ($kullanici_bakiyesi >= $toplam_tutar) {
        $_SESSION['kullanici_bakiyesi'] -= $toplam_tutar; 
        $_SESSION['sepet'] = []; 
        echo "<script>alert('Satın alma işlemi başarıyla gerçekleştirildi!'); window.location.href='ana_sayfa.php';</script>";
        exit();
    } else {
        echo "<script>alert('Yetersiz bakiye!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sepet</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
        }
        .sepet-listesi {
            margin: 20px 0;
        }
        .sepet-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        .toplam-tutar {
            margin-top: 20px;
            text-align: right;
            font-size: 18px;
            font-weight: bold;
        }
        .bakiye {
            margin-top: 20px;
            text-align: left;
            font-size: 18px;
            font-weight: bold;
        }
        .buton {
            text-align: center;
            margin-top: 20px;
        }
        .buton a {
            padding: 10px 20px;
            background-color: #333;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin: 0 5px;
        }
        .buton a:hover {
            background-color: #555;
        }
        form {
            text-align: center;
            margin-top: 20px;
        }
        form input[type="number"],
        form textarea {
            padding: 10px;
            width: calc(100% - 24px);
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        form input[type="submit"] {
            padding: 10px 20px;
            background-color: #333;
            color: #fff;
            border: none;
            border-radius: 4px;
        }
        form input[type="submit"]:hover {
            background-color: #555;
        }
        textarea {
            resize: none;
            height: 100px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sepetiniz</h1>

        <div class="bakiye">
            Mevcut Bakiyeniz: <?php echo number_format($_SESSION['kullanici_bakiyesi'] ?? 0, 2); ?> TL
        </div>

        <div class="sepet-listesi">
            <?php if (!empty($sepet)): ?>
                <?php foreach ($sepet as $id => $urun): ?>
                    <div class="sepet-item">
                        <span><?php echo htmlspecialchars($urun['ad']); ?> (<?php echo $urun['adet']; ?>)</span>
                        <span><?php echo number_format($urun['fiyat'] * $urun['adet'], 2); ?> TL</span>
                        <a href="sepet.php?action=sil&id=<?php echo $id; ?>" style="color: red;">Sil</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Sepetiniz boş.</p>
            <?php endif; ?>
        </div>

        <div class="toplam-tutar">
            Toplam Tutar: <?php echo number_format($toplam_tutar, 2); ?> TL
        </div>

        <div class="buton">
            <?php if (!empty($sepet)): ?>
                <a href="sepet.php?action=satinal">Satın Al</a>
            <?php endif; ?>
            <a href="ana_sayfa.php">Ana Sayfa</a>
        </div>

        <form action="sepet.php" method="post">
            <input type="number" name="bakiye" placeholder="Bakiye Yükle" step="0.01" min="0">
            <input type="submit" value="Bakiye Yükle">
        </form>

        
        <h2>Sipariş Notu</h2>
        <form action="sepet.php" method="post">
            <textarea name="not" placeholder="Sipariş notunuzu buraya yazın..."><?php echo htmlspecialchars($_SESSION['siparis_notu'] ?? ''); ?></textarea>
            <input type="submit" value="Notu Kaydet">
        </form>
    </div>
</body>
</html>

