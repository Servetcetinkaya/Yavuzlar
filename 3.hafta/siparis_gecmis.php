<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$database_path = "/tmp/yemek_sitesi.db";

try {
    
    $connection = new PDO("sqlite:$database_path");
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    
    session_start();
    $user_id = $_SESSION['user_id']; // Kullanıcı kimliğini oturumdan al

    
    $stmt = $connection->prepare("SELECT * FROM order_history WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_order'])) {
        $order_id = $_POST['delete_order'];

        
        $delete_stmt = $connection->prepare("DELETE FROM order_history WHERE id = :order_id AND user_id = :user_id");
        $delete_stmt->bindParam(':order_id', $order_id);
        $delete_stmt->bindParam(':user_id', $user_id);
        $delete_stmt->execute();

        
        $stmt->execute(['user_id' => $user_id]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <link rel="stylesheet" href="styles.css"> <!-- Varsayılan stil dosyanız -->
</head>
<body>
    <h1>Sipariş Geçmişi</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>Yemek Adı</th>
            <th>Not</th>
            <th>Fiyat</th>
            <th>Tarih</th>
            <th>Sil</th>
        </tr>
        <?php foreach ($orders as $order): ?>
        <tr>
            <td><?= htmlspecialchars($order['id']) ?></td>
            <td><?= htmlspecialchars($order['meal_name']) ?></td>
            <td><?= htmlspecialchars($order['note']) ?></td>
            <td><?= htmlspecialchars($order['price']) ?> TL</td>
            <td><?= htmlspecialchars($order['date']) ?></td>
            <td>
                <form action="siparis_gecmis.php" method="POST">
                    <input type="hidden" name="delete_order" value="<?= $order['id'] ?>">
                    <button type="submit">Sil</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <a href="ana_sayfa.php">Ana Sayfaya Dön</a> 
</body>
</html>

