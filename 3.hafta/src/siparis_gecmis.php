<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$database_path = "/tmp/yemek_sitesi.db";
session_start();
$user_id = $_SESSION['user_id'] ?? null;

try {
    $connection = new PDO("sqlite:$database_path");
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    
    $stmt = $connection->prepare("SELECT * FROM order_history WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $order_history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_order'])) {
        $order_id = $_POST['order_id'];
        $delete_stmt = $connection->prepare("DELETE FROM order_history WHERE id = :order_id");
        $delete_stmt->bindParam(':order_id', $order_id);
        $delete_stmt->execute();

        
        header("Location: siparis_gecmis.php");
        exit;
    }

    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_note'])) {
        $order_id = $_POST['order_id'];
        $new_note = $_POST['new_note'];

        $update_stmt = $connection->prepare("UPDATE order_history SET note = :note WHERE id = :order_id");
        $update_stmt->bindParam(':note', $new_note);
        $update_stmt->bindParam(':order_id', $order_id);
        $update_stmt->execute();

        
        header("Location: siparis_gecmis.php");
        exit;
    }

} catch (PDOException $e) {
    echo "Veritabanı hatası: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sipariş Geçmişi</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Sipariş Geçmişi</h1>
        
        <?php if (!empty($order_history)): ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Yemek Adı</th>
                    <th>Fiyat</th>
                    <th>Not</th>
                    <th>Durum</th>
                    <th>Tarih</th>
                    <th>Not Güncelle</th>
                    <th>Sil</th>
                </tr>
                <?php foreach ($order_history as $order): ?>
                <tr>
                    <td><?= htmlspecialchars($order['id']) ?></td>
                    <td><?= htmlspecialchars($order['meal_name']) ?></td>
                    <td><?= htmlspecialchars($order['price']) ?> TL</td>
                    <td><?= htmlspecialchars($order['note']) ?></td>
                    <td><?= htmlspecialchars($order['status']) ?></td>
                    <td><?= htmlspecialchars($order['date']) ?></td>
                    <td>
                        <form action="siparis_gecmis.php" method="POST">
                            <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>">
                            <input type="text" name="new_note" placeholder="Yeni notu girin" required>
                            <button type="submit" name="update_note">Güncelle</button>
                        </form>
                    </td>
                    <td>
                        <form action="siparis_gecmis.php" method="POST">
                            <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>">
                            <button type="submit" name="delete_order">Sil</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>Sipariş geçmişiniz bulunmamaktadır.</p>
        <?php endif; ?>

        <form action="ana_sayfa.php">
            <button type="submit">Ana Sayfaya Dön</button>
        </form>
    </div>
</body>
</html>
