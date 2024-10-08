<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$database_path = "/tmp/yemek_sitesi.db";

try {
    
    $connection = new PDO("sqlite:$database_path");
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    
    $stmt = $connection->prepare("SELECT * FROM order_history WHERE purchased = 0");
    $stmt->execute();
    $active_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
        $order_id = $_POST['order_id'];
        $new_status = $_POST['status'];

        $update_stmt = $connection->prepare("UPDATE order_history SET status = :status WHERE id = :order_id");
        $update_stmt->bindParam(':status', $new_status);
        $update_stmt->bindParam(':order_id', $order_id);
        $update_stmt->execute();

        header("Location: firma_panel.php"); 
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
    <title>Firma Paneli</title>
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Aktif Siparişler</h1>
        <table>
            <tr>
                <th>ID</th>
                <th>Yemek Adı</th>
                <th>Durum</th>
                <th>Güncelle</th>
            </tr>
            <?php foreach ($active_orders as $order): ?>
            <tr>
                <td><?= htmlspecialchars($order['id']) ?></td>
                <td><?= htmlspecialchars($order['meal_name']) ?></td>
                <td><?= htmlspecialchars($order['status']) ?></td>
                <td>
                    <form action="firma_panel.php" method="POST">
                        <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>">
                        <select name="status">
                            <option value="hazırlanıyor" <?= $order['status'] === 'hazırlanıyor' ? 'selected' : '' ?>>Hazırlanıyor</option>
                            <option value="yolda" <?= $order['status'] === 'yolda' ? 'selected' : '' ?>>Yolda</option>
                            <option value="ulaştı" <?= $order['status'] === 'ulaştı' ? 'selected' : '' ?>>Ulaştı</option>
                            <option value="iptal edildi" <?= $order['status'] === 'iptal edildi' ? 'selected' : '' ?>>İptal Edildi</option>
                        </select>
                        <button type="submit" name="update_status">Güncelle</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <form action="giris.php">
            <button type="submit">Çıkış Yap</button>
        </form>
    </div>
</body>
</html>

