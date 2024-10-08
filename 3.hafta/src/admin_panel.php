<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$database_path = "/tmp/yemek_sitesi.db";

try {
   
    $connection = new PDO("sqlite:$database_path");
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    
    $stmt = $connection->prepare('SELECT * FROM users WHERE deleted = 0 AND role != "admin" GROUP BY username');
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    $stmt = $connection->prepare('SELECT * FROM restaurants');
    $stmt->execute();
    $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    $stmt = $connection->prepare('SELECT * FROM meals');
    $stmt->execute();
    $meals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    $stmt = $connection->prepare('SELECT * FROM firms');
    $stmt->execute();
    $firms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    $stmt = $connection->prepare('SELECT * FROM coupons');
    $stmt->execute();
    $coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Veritabanı hatası: " . $e->getMessage();
}


if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    $stmt = $connection->prepare('UPDATE users SET deleted = 1 WHERE id = :id');
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    header('Location: admin_panel.php');
    exit();
}


if (isset($_POST['ban_user'])) {
    $user_id = $_POST['user_id'];
    $stmt = $connection->prepare('UPDATE users SET banned = 1 WHERE id = :id');
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    header('Location: admin_panel.php');
    exit();
}


if (isset($_POST['undelete_user'])) {
    $user_id = $_POST['user_id'];
    $stmt = $connection->prepare('UPDATE users SET banned = 0 WHERE id = :id');
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    header('Location: admin_panel.php');
    exit();
}



if (isset($_POST['add_meal'])) {
    $meal_name = $_POST['meal_name'] ?? '';
    $meal_price = $_POST['meal_price'] ?? '';
    $discount_price = $_POST['discount_price'] ?? null; 
    $restaurant_id = $_POST['restaurant_id'] ?? '';
    $image_url = $_POST['image_url'] ?? '';

    if ($meal_name && $meal_price && $restaurant_id && $image_url) {
        $stmt = $connection->prepare('INSERT INTO meals (name, price, restaurant_id, image_url, discount_price) VALUES (:name, :price, :restaurant_id, :image_url, :discount_price)');
        $stmt->bindParam(':name', $meal_name);
        $stmt->bindParam(':price', $meal_price);
        $stmt->bindParam(':restaurant_id', $restaurant_id);
        $stmt->bindParam(':image_url', $image_url);
        $stmt->bindParam(':discount_price', $discount_price); 
        $stmt->execute();
        header('Location: admin_panel.php');
        exit();
    }
}


if (isset($_POST['edit_meal_confirm'])) {
    $meal_id = $_POST['meal_id'] ?? '';
    $meal_name = $_POST['meal_name'] ?? '';
    $meal_price = $_POST['meal_price'] ?? '';
    $restaurant_id = $_POST['restaurant_id'] ?? '';
    $image_url = $_POST['image_url'] ?? '';
    $discount_price = $_POST['discount_price'] ?? null;

    if ($meal_id && $meal_name && $meal_price && $restaurant_id && $image_url) {
        $stmt = $connection->prepare('UPDATE meals SET name = :name, price = :price, restaurant_id = :restaurant_id, image_url = :image_url, discount_price = :discount_price WHERE id = :id');
        $stmt->bindParam(':name', $meal_name);
        $stmt->bindParam(':price', $meal_price);
        $stmt->bindParam(':restaurant_id', $restaurant_id);
        $stmt->bindParam(':image_url', $image_url);
        $stmt->bindParam(':discount_price', $discount_price);
        $stmt->bindParam(':id', $meal_id);
        $stmt->execute();
        header('Location: admin_panel.php');
        exit();
    }
}



if (isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Argon2 ile şifrele
    $role = 'normal'; 

    try {
        $stmt = $connection->prepare('INSERT INTO users (username, password, role, deleted, banned) VALUES (:username, :password, :role, 0, 0)');
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':role', $role);
        $stmt->execute();
        header('Location: admin_panel.php');
        exit();
    } catch (PDOException $e) {
        echo "Kullanıcı eklenirken bir hata oluştu: " . $e->getMessage();
    }
}



if (isset($_POST['add_firm_user'])) {
    $username = $_POST['firm_username'];
    $password = password_hash($_POST['firm_password'],  PASSWORD_DEFAULT);
    $role = 'firm_user';

    $stmt = $connection->prepare('INSERT INTO users (username, password, role, deleted, banned) VALUES (:username, :password, :role, 0, 0)');
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':role', $role);
    $stmt->execute();
    header('Location: admin_panel.php');
    exit();
}


if (isset($_POST['add_restaurant'])) {
    $restaurant_name = $_POST['restaurant_name'];
    $firm_id = $_POST['firm_id'];

    $stmt = $connection->prepare('INSERT INTO restaurants (name, firm_id) VALUES (:name, :firm_id)');
    $stmt->bindParam(':name', $restaurant_name);
    $stmt->bindParam(':firm_id', $firm_id);
    $stmt->execute();
    header('Location: admin_panel.php');
    exit();
}


if (isset($_POST['add_firm'])) {
    $firm_name = $_POST['firm_name'];

    $stmt = $connection->prepare('INSERT INTO firms (name) VALUES (:name)');
    $stmt->bindParam(':name', $firm_name);
    $stmt->execute();
    header('Location: admin_panel.php');
    exit();
}


if (isset($_POST['add_coupon'])) {
    $coupon_code = $_POST['coupon_code'];
    $discount = $_POST['discount'];
    $expiration_date = $_POST['expiration_date'];

    $stmt = $connection->prepare('INSERT INTO coupons (code, discount, expiration_date) VALUES (:code, :discount, :expiration_date)');
    $stmt->bindParam(':code', $coupon_code);
    $stmt->bindParam(':discount', $discount);
    $stmt->bindParam(':expiration_date', $expiration_date);
    $stmt->execute();
    header('Location: admin_panel.php');
    exit();
}


$search_restaurant = '';
if (isset($_POST['search_restaurant'])) {
    $search_restaurant = $_POST['search_restaurant'];
    $stmt = $connection->prepare('SELECT * FROM restaurants WHERE name LIKE :search');
    $stmt->bindValue(':search', '%' . $search_restaurant . '%');
    $stmt->execute();
    $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
}


$search_meal = '';
if (isset($_POST['search_meal'])) {
    $search_meal = $_POST['search_meal'];
    $stmt = $connection->prepare('SELECT * FROM meals WHERE name LIKE :search');
    $stmt->bindValue(':search', '%' . $search_meal . '%');
    $stmt->execute();
    $meals = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Paneli</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        h2 {
            color: #555;
            margin-top: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #007BFF;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        form {
            display: inline;
        }

        button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        input[type="text"], input[type="password"], input[type="number"], input[type="date"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .logout {
            display: block;
            width: 100%;
            text-align: center;
            padding: 10px;
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }

        .logout:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin Paneli</h1>

        <h2>Kullanıcılar</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Kullanıcı Adı</th>
                <th>Rol</th>
                <th>Durum</th>
                <th>İşlem</th>
            </tr>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['role']); ?></td>
                <td><?php echo (isset($user['banned']) && $user['banned']) ? 'Banlı Üye' : 'Aktif'; ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" name="ban_user" <?php echo (isset($user['banned']) && $user['banned']) ? 'disabled' : ''; ?>>Banla</button>
                    </form>
                    <form method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" name="undelete_user" <?php echo (isset($user['banned']) && !$user['banned']) ? 'disabled' : ''; ?>>Kaldır</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <h2>Kullanıcı Ekle</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Kullanıcı Adı" required>
            <input type="password" name="password" placeholder="Parola" required>
            <button type="submit" name="add_user">Ekle</button>
        </form>

        <h2>Firma Kullanıcısı Ekle</h2>
        <form method="POST">
            <input type="text" name="firm_username" placeholder="Firma Kullanıcı Adı" required>
            <input type="password" name="firm_password" placeholder="Parola" required>
            <button type="submit" name="add_firm_user">Ekle</button>
        </form>

        <h2>Firma Ekle</h2>
        <form method="POST">
            <input type="text" name="firm_name" placeholder="Firma Adı" required>
            <button type="submit" name="add_firm">Ekle</button>
        </form>

        <h2>Restoran Ekle</h2>
        <form method="POST">
            <input type="text" name="restaurant_name" placeholder="Restoran Adı" required>
            <input type="number" name="firm_id" placeholder="Firma ID" required>
            <button type="submit" name="add_restaurant">Ekle</button>
        </form>

        <h2>Yemek Ekle</h2>
        <form method="POST">
            <input type="text" name="meal_name" placeholder="Yemek Adı" required>
            <input type="number" step="0.01" name="meal_price" placeholder="Yemek Fiyatı" required>
            <input type="number" name="restaurant_id" placeholder="Restoran ID" required>
            <input type="text" name="image_url" placeholder="Görsel URL" required>
            <input type="number" step="0.01" name="discount_price" placeholder="İndirimli Fiyat (Opsiyonel)">
            <button type="submit" name="add_meal">Ekle</button>
        </form>

      <h2>Yemekler</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Yemek Adı</th>
        <th>Fiyat</th>
        <th>İndirimli Fiyat</th>
        <th>Restoran ID</th>
        <th>Görsel</th>
        <th>İşlem</th>
    </tr>
    <?php foreach ($meals as $meal): ?>
    <tr>
        <td><?php echo $meal['id']; ?></td>
        <td><?php echo htmlspecialchars($meal['name']); ?></td>
        <td><?php echo htmlspecialchars($meal['price']); ?></td>
        <td><?php echo htmlspecialchars($meal['discount_price']); ?></td>
        <td><?php echo htmlspecialchars($meal['restaurant_id']); ?></td>
        <td><img src="<?php echo htmlspecialchars($meal['image_url']); ?>" alt="<?php echo htmlspecialchars($meal['name']); ?>" width="50"></td>
        <td>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="meal_id" value="<?php echo $meal['id']; ?>">
                <button type="button" onclick="toggleEditForm(<?php echo $meal['id']; ?>)">Düzenle</button>

            </form>
            <!-- Düzenleme Formu -->
            <form method="POST" style="display:inline;" class="edit-form" id="edit-form-<?php echo $meal['id']; ?>" style="display:none;">
                <input type="hidden" name="meal_id" value="<?php echo $meal['id']; ?>">
                <input type="text" name="meal_name" value="<?php echo htmlspecialchars($meal['name']); ?>" required>
                <input type="number" step="0.01" name="meal_price" value="<?php echo htmlspecialchars($meal['price']); ?>" required>
                <input type="number" name="restaurant_id" value="<?php echo htmlspecialchars($meal['restaurant_id']); ?>" required>
                <input type="text" name="image_url" value="<?php echo htmlspecialchars($meal['image_url']); ?>" required>
                <input type="number" step="0.01" name="discount_price" value="<?php echo htmlspecialchars($meal['discount_price']); ?>" placeholder="İndirimli Fiyat (Opsiyonel)">
                <button type="submit" name="edit_meal_confirm">Kaydet</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>




        <h2>Kupon Oluştur</h2>
        <form method="POST">
            <input type="text" name="coupon_code" placeholder="Kupon Kodu" required>
            <input type="number" step="0.01" name="discount" placeholder="İndirim Miktarı" required>
            <input type="date" name="expiration_date" placeholder="Son Kullanma Tarihi" required>
            <button type="submit" name="add_coupon">Oluştur</button>
        </form>

        <h2>Kuponlar</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Kupon Kodu</th>
                <th>İndirim Miktarı</th>
                <th>Son Kullanma Tarihi</th>
            </tr>
            <?php foreach ($coupons as $coupon): ?>
            <tr>
                <td><?php echo $coupon['id']; ?></td>
                <td><?php echo htmlspecialchars($coupon['code']); ?></td>
                <td><?php echo htmlspecialchars($coupon['discount']); ?></td>
                <td><?php echo htmlspecialchars($coupon['expiration_date']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <h2>Restoranlar</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Restoran Adı</th>
                <th>Firma ID</th>
            </tr>
            <?php foreach ($restaurants as $restaurant): ?>
            <tr>
                <td><?php echo $restaurant['id']; ?></td>
                <td><?php echo htmlspecialchars($restaurant['name']); ?></td>
                <td><?php echo htmlspecialchars($restaurant['firm_id']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <h2>Firmalar</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Firma Adı</th>
            </tr>
            <?php foreach ($firms as $firm): ?>
            <tr>
                <td><?php echo $firm['id']; ?></td>
                <td><?php echo htmlspecialchars($firm['name']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <a href="giris.php" class="logout">Çıkış</a>
    </div>
...
</table>

<!-- JavaScript kodunu buraya ekleyin -->
<script>
    function toggleEditForm(mealId) {
        const form = document.getElementById('edit-form-' + mealId);
        form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'inline' : 'none';
    }
</script>

</body>
</html>

</body>
</html>

