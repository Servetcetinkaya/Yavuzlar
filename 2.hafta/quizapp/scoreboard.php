<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


$dbFile = '/tmp/quiz.db';


$db = new SQLite3($dbFile);


$skor_sorgu = $db->prepare('
    SELECT kullanici_adi, 
           SUM(CASE WHEN secenek = dogru_secenek THEN 1 ELSE 0 END) AS puan 
    FROM yanitlanan_sorular
    JOIN sorular ON yanitlanan_sorular.soru_id = sorular.id
    GROUP BY kullanici_adi
');
$result = $skor_sorgu->execute();


if (!$result) {
    die('Sorgu çalıştırılırken bir hata oluştu: ' . $db->lastErrorMsg());
}


?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Skor Tablosu</title>
</head>
<body>
    <h1>Skor Tablosu</h1>
    <table border="1">
        <tr>
            <th>Kullanıcı Adı</th>
            <th>Puan</th>
        </tr>
        <?php 
        
        if ($result) {
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['kullanici_adi']) . "</td>";
                echo "<td>" . htmlspecialchars($row['puan']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='2'>Puan bulunamadı.</td></tr>";
        }
        ?>
    </table>
    <br><a href="quiz_yarismasi.php">Quiz'e Dön</a>
<form method="get" action="login.php" style="margin-bottom: 20px;">
        <input type="submit" value="Çıkış Yap">
    </form>
</body>
</html>
