<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$database = "/tmp/yemek_sitesi.db";
try {
    $connection = new PDO("sqlite:$database");
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Database connection error: " . $e->getMessage();
    exit();
}


$arama_yemek = isset($_POST['arama_yemek']) ? $_POST['arama_yemek'] : '';
$min_fiyat = isset($_POST['min_fiyat']) ? $_POST['min_fiyat'] : 0;
$max_fiyat = isset($_POST['max_fiyat']) ? $_POST['max_fiyat'] : 100;


$meals = [];
try {
    $query = "SELECT * FROM meals WHERE name LIKE :arama_yemek AND price BETWEEN :min_fiyat AND :max_fiyat";
    $stmt = $connection->prepare($query);
    $arama_yemek = "%$arama_yemek%"; 
    $stmt->bindParam(':arama_yemek', $arama_yemek);
    $stmt->bindParam(':min_fiyat', $min_fiyat);
    $stmt->bindParam(':max_fiyat', $max_fiyat);
    $stmt->execute();
    $meals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching meals: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lokanta</title>
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
        .profile-icon {
            float: right;
            margin: 10px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid #333;
        }
        .profile-icon img {
            width: 100%;
            height: auto;
        }
        .cart-icon {
            float: left;
            margin: 10px;
            font-size: 20px;
        }
        .meal-card {
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
        .meal-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
        }
        .buttons a, .buttons button {
            display: inline-block;
            padding: 10px 15px;
            text-decoration: none;
            background-color: #333;
            color: #fff;
            border-radius: 4px;
            margin: 5px;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="buttons" style="float: right;">
        <a href="cikis.php" style="background-color: red;">Çıkış</a>
    </div>
    <div class="container">
        <h1>Yemekler</h1>

        <div class="profile-icon" onclick="window.location='profil.php'">
            <img src="<?php echo htmlspecialchars($_SESSION['profile_picture'] ?? 'default.png'); ?>" alt="Profile Image">
        </div>

        <div class="cart-icon">
            <a href="sepet.php">🛒 Sepet (<?php echo count($_SESSION['cart'] ?? []); ?>)</a>
        </div>

        <!-- Arama Formu -->
        <form method="post" style="margin-bottom: 20px;">
            <input type="text" name="arama_yemek" placeholder="Yemek adı" value="<?php echo htmlspecialchars($arama_yemek); ?>">
            <input type="number" name="min_fiyat" placeholder="Min Fiyat" value="<?php echo htmlspecialchars($min_fiyat); ?>">
            <input type="number" name="max_fiyat" placeholder="Max Fiyat" value="<?php echo htmlspecialchars($max_fiyat); ?>">
            <button type="submit">Ara</button>
        </form>

        <div class="meal-list">
            <?php foreach ($meals as $meal): ?>
                <div class="meal-card">
                    <img src="<?php echo htmlspecialchars($meal['image_url']); ?>" alt="<?php echo htmlspecialchars($meal['name']); ?>">
                    <div class="meal-name"><?php echo htmlspecialchars($meal['name']); ?></div>
                    <div class="meal-price">
                        <?php if (!empty($meal['discount_price'])): ?>
                            <span style="text-decoration: line-through; color: red;"><?php echo htmlspecialchars($meal['price']); ?> TL</span>
                            <span><?php echo htmlspecialchars($meal['discount_price']); ?> TL</span>
                        <?php else: ?>
                            <span><?php echo htmlspecialchars($meal['price']); ?> TL</span>
                        <?php endif; ?>
                    </div>
                    <div class="buttons">
                        <a href="yemek_detay.php?id=<?php echo htmlspecialchars($meal['id']); ?>">Detay</a>
                        
                        <form action="sepet.php" method="POST">
                            <input type="hidden" name="meal_id" value="<?= $meal['id'] ?>">
                            <input type="hidden" name="meal_name" value="<?= $meal['name'] ?>"> 
                            <input type="hidden" name="meal_price" value="<?= $meal['discount_price'] > 0 ? $meal['discount_price'] : $meal['price'] ?>"> 
                            <button type="submit" name="add_to_cart">Sepete Ekle</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>

