<?php
// Hata raporlama ayarları
error_reporting(E_ALL);
ini_set('display_errors', 1);

$database_path = "/tmp/yemek_sitesi.db";

try {
    // Veritabanı bağlantısı oluşturuluyor
    $connection = new PDO("sqlite:$database_path");
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Veritabanı hatası: " . $e->getMessage();
}

// Kullanıcının bakiyesini alma
session_start();
$user_id = $_SESSION['user_id'] ?? null;
$balance = 0;

if ($user_id) {
    $stmt = $connection->prepare("SELECT balance FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $balance = $user['balance'] ?? 0;
}

// Sepet işlemleri
$cart = $_SESSION['cart'] ?? [];
$total_price = 0;

// Sepete ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $meal_id = $_POST['meal_id'] ?? null;
    $meal_name = $_POST['meal_name'] ?? null;
    $meal_price = $_POST['meal_price'] ?? null;
    $note = $_POST['note'] ?? '';

    if (!$meal_id || !$meal_name || !$meal_price) {
        echo "Yemek bilgileri eksik!";
        exit;
    }

    $cart[] = [
        'id' => $meal_id,
        'name' => $meal_name,
        'price' => $meal_price,
        'note' => $note
    ];

    $_SESSION['cart'] = $cart;

    header("Location: sepet.php");
    exit;
}

// Silme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove'])) {
    $remove_index = $_POST['remove'];
    if (isset($cart[$remove_index])) {
        unset($cart[$remove_index]);
        $_SESSION['cart'] = array_values($cart); // Diziyi yeniden indeksle
    }
}

// Not güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_note'])) {
    $note_index = $_POST['note_index'] ?? null;
    $new_note = $_POST['new_note'] ?? '';

    if ($note_index !== null && isset($cart[$note_index])) {
        $cart[$note_index]['note'] = $new_note; // Notu güncelle
        $_SESSION['cart'] = $cart; // Sepeti güncelle
    }
}

// Bakiye yükleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_balance'])) {
    $amount = $_POST['amount'] ?? 0;
    if ($amount > 0) {
        $new_balance = $balance + $amount;
        $stmt = $connection->prepare("UPDATE users SET balance = :new_balance WHERE id = :user_id");
        $stmt->bindParam(':new_balance', $new_balance);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $balance = $new_balance; // Bakiyeyi güncelle
    }
}

// Satın alma işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['purchase'])) {
    $total_price = array_sum(array_column($cart, 'price'));
    
    if ($balance >= $total_price) {
        $new_balance = $balance - $total_price;
        $stmt = $connection->prepare("UPDATE users SET balance = :new_balance WHERE id = :user_id");
        $stmt->bindParam(':new_balance', $new_balance);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        foreach ($cart as $item) {
            $stmt = $connection->prepare("INSERT INTO order_history (user_id, meal_id, meal_name, note, price, username) VALUES (:user_id, :meal_id, :meal_name, :note, :price, :username)");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':meal_id', $item['id']);
            $stmt->bindParam(':meal_name', $item['name']);
            $stmt->bindParam(':note', $item['note']);
            $stmt->bindParam(':price', $item['price']);
            $stmt->bindParam(':username', $_SESSION['username']);
            $stmt->execute();
        }

        unset($_SESSION['cart']);
        header("Location: siparis_gecmis.php");
        exit;
    } else {
        echo "Yeterli bakiye yok!";
    }
}

// Sepetteki yemekleri hesapla
foreach ($cart as $item) {
    $total_price += $item['price'];
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sepet</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
        }
        button:hover {
            background-color: #218838;
        }
        .balance-form {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sepet</h1>
        <p>Bakiyeniz: <?= $balance ?> TL</p>

        <div class="balance-form">
            <h2>Bakiye Yükle</h2>
            <form action="sepet.php" method="POST">
                <input type="number" name="amount" placeholder="Yüklemek istediğiniz tutar" required>
                <button type="submit" name="add_balance">Yükle</button>
            </form>
        </div>

        <table>
            <tr>
                <th>Yemek Adı</th>
                <th>Fiyat</th>
                <th>Not</th>
                <th>Sil</th>
            </tr>
            <?php foreach ($cart as $key => $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= htmlspecialchars($item['price']) ?> TL</td>
                <td>
                    <form action="sepet.php" method="POST">
                        <input type="hidden" name="note_index" value="<?= $key ?>">
                        <input type="text" name="new_note" value="<?= htmlspecialchars($item['note']) ?>" placeholder="Notunuzu girin" required>
                        <button type="submit" name="update_note">Güncelle</button>
                    </form>
                </td>
                <td>
                    <form action="sepet.php" method="POST">
                        <input type="hidden" name="remove" value="<?= $key ?>">
                        <button type="submit">Sil</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <h2>Toplam: <?= $total_price ?> TL</h2>

        <form action="sepet.php" method="POST">
            <button type="submit" name="purchase">Satın Al</button>
        </form>
<br>
        <form action="ana_sayfa.php">
            <button type="submit">Ana Sayfaya Dön</button>
        </form>
        <br>
        <form action="siparis_gecmis.php">
            <button type="button">Sipariş Geçmişi</button>
        </form>
    </div>
</body>
</html>

