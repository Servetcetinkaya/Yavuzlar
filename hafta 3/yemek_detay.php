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

// Yemek detayı ve yorumları getir
$yemek_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$yemek = null;
$yorumlar = [];

if ($yemek_id > 0) {
    try {
        // Yemek detayını getir
        $stmt = $baglanti->prepare("SELECT * FROM yemekler WHERE id = :id");
        $stmt->bindParam(':id', $yemek_id);
        $stmt->execute();
        $yemek = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$yemek) {
            $yemek = [
                'id' => 0,
                'ad' => 'Bulunamadı',
                'fiyat' => '0',
                'resim_url' => 'default.jpg',
                'aciklama' => 'Açıklama yok'
            ];
        } else {
            if (!isset($yemek['aciklama'])) {
                $yemek['aciklama'] = 'Açıklama yok';
            }
            if (!isset($yemek['resim_url'])) {
                $yemek['resim_url'] = 'default.jpg';
            }
        }

        // Yorumları getir
        $stmt = $baglanti->prepare("SELECT * FROM yorumlar WHERE yemek_id = :id ORDER BY id DESC");
        $stmt->bindParam(':id', $yemek_id);
        $stmt->execute();
        $yorumlar = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Yemek detayını getirme hatası: " . $e->getMessage();
        exit();
    }
}

// Yorum gönderimi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['yorum_gonder'])) {
    if (isset($_SESSION['kullanici_adi'])) {
        $kullanici_adi = $_SESSION['kullanici_adi'];
        $puan = intval($_POST['puan']);
        $yorum = htmlspecialchars($_POST['yorum']);

        try {
            $yorum_ekle = $baglanti->prepare("INSERT INTO yorumlar (yemek_id, kullanici_adi, puan, yorum) VALUES (:yemek_id, :kullanici_adi, :puan, :yorum)");
            $yorum_ekle->bindParam(':yemek_id', $yemek_id);
            $yorum_ekle->bindParam(':kullanici_adi', $kullanici_adi);
            $yorum_ekle->bindParam(':puan', $puan);
            $yorum_ekle->bindParam(':yorum', $yorum);
            $yorum_ekle->execute();
            header("Location: yemek_detay.php?id=$yemek_id");
            exit();
        } catch (PDOException $e) {
            echo "Yorum ekleme hatası: " . $e->getMessage();
            exit();
        }
    } else {
        header('Location: giris.php');
        exit();
    }
}

// Yorum silme
if (isset($_GET['sil']) && isset($_SESSION['kullanici_adi'])) {
    $yorum_id = intval($_GET['sil']);
    try {
        $sil = $baglanti->prepare("DELETE FROM yorumlar WHERE id = :id");
        $sil->bindParam(':id', $yorum_id);
        $sil->execute();
        header("Location: yemek_detay.php?id=$yemek_id");
        exit();
    } catch (PDOException $e) {
        echo "Yorum silme hatası: " . $e->getMessage();
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yemek Detay</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }

        header {
            background-color: #333;
            color: #fff;
            padding: 10px 0;
            text-align: center;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        .yemek-detay {
            margin-bottom: 20px;
        }

        .yemek-detay img {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }

        .yemek-ad {
            font-size: 24px;
            font-weight: bold;
            margin-top: 20px;
        }

        .yemek-fiyat {
            color: #555;
            margin: 10px 0;
        }

        .yemek-aciklama {
            font-size: 16px;
            color: #666;
            margin: 20px 0;
        }

        .yorumlar {
            margin-top: 30px;
        }

        .yorum {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }

        .puanlama {
            font-size: 18px;
            color: #ffd700;
        }

        .yorum-form {
            margin-top: 30px;
        }

        .yorum-form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            resize: vertical;
        }

        .yorum-form button {
            padding: 10px 15px;
            color: #fff;
            background-color: #333;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .yorum-form button:hover {
            background-color: #555;
        }

        .message, .error {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .message {
            background-color: #e7f5ff;
            border-left: 5px solid #5bc0de;
            color: #31708f;
        }

        .error {
            background-color: #f2dede;
            border-left: 5px solid #d9534f;
            color: #a94442;
        }

        .puanlama-label {
            display: inline-block;
            width: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <header>
        <h1>Yemek Detay</h1>
    </header>

    <div class="container">
        <?php if (isset($mesaj)): ?>
            <p class="message"><?php echo htmlspecialchars($mesaj); ?></p>
        <?php endif; ?>

        <?php if (isset($hata)): ?>
            <p class="error"><?php echo htmlspecialchars($hata); ?></p>
        <?php endif; ?>

        <?php if ($yemek): ?>
            <div class="yemek-detay">
                <img src="<?php echo htmlspecialchars($yemek['resim_url']); ?>" alt="<?php echo htmlspecialchars($yemek['ad']); ?>">
                <div class="yemek-ad"><?php echo htmlspecialchars($yemek['ad']); ?></div>
                <div class="yemek-fiyat"><?php echo htmlspecialchars($yemek['fiyat']); ?> TL</div>
                <div class="yemek-aciklama"><?php echo htmlspecialchars($yemek['aciklama']); ?></div>
            </div>

            <div class="yorumlar">
                <h2>Yorumlar</h2>
                <?php foreach ($yorumlar as $yorum): ?>
                    <div class="yorum">
                        <div class="puanlama">
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <?php echo ($i <= $yorum['puan']) ? '&#9733;' : '&#9734;'; ?>
                                <span class="puanlama-label"><?php echo $i; ?></span>
                            <?php endfor; ?>
                        </div>
                        <p><strong><?php echo htmlspecialchars($yorum['kullanici_adi']); ?></strong>: <?php echo htmlspecialchars($yorum['yorum']); ?></p>
                        <?php if (isset($_SESSION['kullanici_adi']) && $_SESSION['kullanici_adi'] === $yorum['kullanici_adi']): ?>
                            <a href="yemek_detay.php?id=<?php echo $yemek_id; ?>&sil=<?php echo $yorum['id']; ?>" onclick="return confirm('Bu yorumu silmek istediğinize emin misiniz?');">Sil</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (isset($_SESSION['kullanici_adi'])): ?>
                <div class="yorum-form">
                    <h2>Yorum Yap</h2>
                    <form method="POST" action="yemek_detay.php?id=<?php echo htmlspecialchars($yemek['id']); ?>">
                        <div class="puanlama">
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <input type="radio" id="puan_<?php echo $i; ?>" name="puan" value="<?php echo $i; ?>" required>
                                <label for="puan_<?php echo $i; ?>"><?php echo $i; ?></label>
                            <?php endfor; ?>
                        </div>
                        <textarea name="yorum" rows="4" required placeholder="Yorumunuzu yazın..."></textarea>
                        <button type="submit" name="yorum_gonder">Gönder</button>
                        <a href="ana_sayfa.php" style="padding: 10px; margin-left: 10px; display: inline-block; background-color: #333; color: white; text-decoration: none; border-radius: 4px;">Ana Sayfaya Dön</a>
                    </form>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <p class="error">Yemek bulunamadı.</p>
        <?php endif; ?>
    </div>
</body>
</html>

