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

if (!isset($_SESSION['kullanici_adi']) || $_SESSION['rol'] !== 'admin') {
    header("Location: giris.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ekle_yemek'])) {
    $ad = trim($_POST['yemek_ad']);
    $fiyat = trim($_POST['yemek_fiyat']);
    $restoran = trim($_POST['yemek_restoran']);
    $resim_url = trim($_POST['yemek_resim_url']);

    try {
        $stmt = $baglanti->prepare("INSERT INTO yemekler (ad, fiyat, restoran, resim_url) VALUES (:ad, :fiyat, :restoran, :resim_url)");
        $stmt->bindParam(':ad', $ad);
        $stmt->bindParam(':fiyat', $fiyat);
        $stmt->bindParam(':restoran', $restoran);
        $stmt->bindParam(':resim_url', $resim_url);
        $stmt->execute();
        $mesaj = "Yemek eklendi.";
    } catch (PDOException $e) {
        $hata = "Yemek ekleme hatası: " . $e->getMessage();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['sil_yemek'])) {
    $id = trim($_POST['yemek_id']);

    try {
        $stmt = $baglanti->prepare("DELETE FROM yemekler WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $mesaj = "Yemek silindi.";
    } catch (PDOException $e) {
        $hata = "Yemek silme hatası: " . $e->getMessage();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['duzenle_yemek'])) {
    $id = trim($_POST['yemek_id']);
    $ad = trim($_POST['yemek_ad']);
    $fiyat = trim($_POST['yemek_fiyat']);
    $restoran = trim($_POST['yemek_restoran']);
    $resim_url = trim($_POST['yemek_resim_url']);

    try {
        $stmt = $baglanti->prepare("UPDATE yemekler SET ad = :ad, fiyat = :fiyat, restoran = :restoran, resim_url = :resim_url WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':ad', $ad);
        $stmt->bindParam(':fiyat', $fiyat);
        $stmt->bindParam(':restoran', $restoran);
        $stmt->bindParam(':resim_url', $resim_url);
        $stmt->execute();
        $mesaj = "Yemek güncellendi.";
    } catch (PDOException $e) {
        $hata = "Yemek güncelleme hatası: " . $e->getMessage();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ekle_restoran'])) {
    $ad = trim($_POST['restoran_ad']);

    try {
        $stmt = $baglanti->prepare("INSERT INTO restoranlar (ad) VALUES (:ad)");
        $stmt->bindParam(':ad', $ad);
        $stmt->execute();
        $mesaj = "Restoran eklendi.";
    } catch (PDOException $e) {
        $hata = "Restoran ekleme hatası: " . $e->getMessage();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ekle_indirim'])) {
    $yemek_id = trim($_POST['indirim_yemek_id']);
    $indirim_orani = trim($_POST['indirim_orani']);

    try {
        $stmt = $baglanti->prepare("UPDATE yemekler SET fiyat = fiyat - (fiyat * :indirim_orani / 100) WHERE id = :yemek_id");
        $stmt->bindParam(':yemek_id', $yemek_id);
        $stmt->bindParam(':indirim_orani', $indirim_orani);
        $stmt->execute();

        $stmt = $baglanti->prepare("INSERT INTO indirimler (yemek_id, indirim_orani) VALUES (:yemek_id, :indirim_orani)");
        $stmt->bindParam(':yemek_id', $yemek_id);
        $stmt->bindParam(':indirim_orani', $indirim_orani);
        $stmt->execute();
        $mesaj = "İndirim eklendi.";
    } catch (PDOException $e) {
        $hata = "İndirim ekleme hatası: " . $e->getMessage();
    }
}

try {
    $yemekler = $baglanti->query("SELECT * FROM yemekler")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $hata = "Yemekleri getirme hatası: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        header {
            background: #333;
            color: #fff;
            padding: 10px 0;
            text-align: center;
        }
        .container {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        h2 {
            margin-top: 20px;
        }
        form {
            margin-bottom: 20px;
        }
        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            padding: 10px;
            background: #28a745;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #218838;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        .message, .error {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .message {
            background: #d4edda;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
        }
        .button-container {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Admin Paneli</h1>
    </header>

    <div class="container">
        <h1>Yemek Yönetimi</h1>

        <?php if (isset($mesaj)): ?>
            <p class="message"><?php echo htmlspecialchars($mesaj); ?></p>
        <?php endif; ?>

        <?php if (isset($hata)): ?>
            <p class="error"><?php echo htmlspecialchars($hata); ?></p>
        <?php endif; ?>

        <h2>Yemek Ekle</h2>
        <form method="POST" action="">
            <label for="yemek_ad">Yemek Adı:</label>
            <input type="text" id="yemek_ad" name="yemek_ad" required>
            <label for="yemek_fiyat">Fiyat:</label>
            <input type="number" id="yemek_fiyat" name="yemek_fiyat" step="0.01" required>
            <label for="yemek_restoran">Restoran:</label>
            <input type="text" id="yemek_restoran" name="yemek_restoran" required>
            <label for="yemek_resim_url">Resim URL:</label>
            <input type="text" id="yemek_resim_url" name="yemek_resim_url" required>
            <button type="submit" name="ekle_yemek">Yemek Ekle</button>
        </form>

        <h2>Yemek Sil</h2>
        <form method="POST" action="">
            <label for="yemek_id">Yemek ID:</label>
            <input type="number" id="yemek_id" name="yemek_id" required>
            <button type="submit" name="sil_yemek">Yemek Sil</button>
        </form>

        <h2>Yemek Düzenle</h2>
        <form method="POST" action="">
            <label for="yemek_id">Yemek ID:</label>
            <input type="number" id="yemek_id" name="yemek_id" required>
            <label for="yemek_ad">Yeni Yemek Adı:</label>
            <input type="text" id="yemek_ad" name="yemek_ad">
            <label for="yemek_fiyat">Yeni Fiyat:</label>
            <input type="number" id="yemek_fiyat" name="yemek_fiyat" step="0.01">
            <label for="yemek_restoran">Yeni Restoran:</label>
            <input type="text" id="yemek_restoran" name="yemek_restoran">
            <label for="yemek_resim_url">Yeni Resim URL:</label>
            <input type="text" id="yemek_resim_url" name="yemek_resim_url">
            <button type="submit" name="duzenle_yemek">Yemek Düzenle</button>
        </form>

        <h2>Yemek Listesi</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ad</th>
                    <th>Fiyat</th>
                    <th>Restoran</th>
                    <th>Resim</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($yemekler as $yemek): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($yemek['id']); ?></td>
                        <td><?php echo htmlspecialchars($yemek['ad']); ?></td>
                        <td><?php echo htmlspecialchars($yemek['fiyat']); ?></td>
                        <td><?php echo htmlspecialchars($yemek['restoran']); ?></td>
                        <td><img src="<?php echo htmlspecialchars($yemek['resim_url']); ?>" alt="<?php echo htmlspecialchars($yemek['ad']); ?>" width="100"></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Restoran Ekle</h2>
        <form method="POST" action="">
            <label for="restoran_ad">Restoran Adı:</label>
            <input type="text" id="restoran_ad" name="restoran_ad" required>
            <button type="submit" name="ekle_restoran">Restoran Ekle</button>
        </form>

        <h2>İndirim Ekle</h2>
        <form method="POST" action="">
            <label for="indirim_yemek_id">Yemek ID:</label>
            <input type="number" id="indirim_yemek_id" name="indirim_yemek_id" required>
            <label for="indirim_orani">İndirim Oranı (%):</label>
            <input type="number" id="indirim_orani" name="indirim_orani" required>
            <button type="submit" name="ekle_indirim">İndirim Ekle</button>
        </form>
    </div>
    <div class="button-container">
        <a href="giris.php"><button>Giriş Sayfasına Dön</button></a>
    </div>
</body>
</html>

