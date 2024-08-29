<?php
session_start();

$dbPath = '/tmp/quiz.db';
if (!file_exists($dbPath)) {
    die("Veritabanı dosyası bulunamadı.");
}

try {
    $db = new SQLite3($dbPath);
} catch (Exception $e) {
    die("Veritabanına bağlanırken bir hata oluştu: " . $e->getMessage());
}


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: index.html');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = $_POST['question'];
    $options = [$_POST['option1'], $_POST['option2'], $_POST['option3'], $_POST['option4']];
    $correctOption = $_POST['correct_option'];

    $stmt = $db->prepare('INSERT INTO questions (question, option1, option2, option3, option4, correct_option) VALUES (?, ?, ?, ?, ?, ?)');
    
    if ($stmt) {
        $stmt->bindValue(1, $question, SQLITE3_TEXT);
        for ($i = 0; $i < 4; $i++) {
            $stmt->bindValue($i + 2, $options[$i], SQLITE3_TEXT);
        }
        $stmt->bindValue(6, $correctOption, SQLITE3_INTEGER);
        
        if ($stmt->execute()) {
            echo "Soru eklendi.";
        } else {
            echo "Soru eklenirken ata oldu.";
        }
    } else {
        echo "Sorgu'da' bir hata oldu.";
    }
}

// Soru silme işlemi
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $db->prepare('DELETE FROM questions WHERE id = ?');
    
    if ($stmt) {
        $stmt->bindValue(1, $id, SQLITE3_INTEGER);
        
        if ($stmt->execute()) {
            echo "Soru silindi.";
        } else {
            echo "Soru silinirken hata oluştu.";
        }
    } else {
        echo "Sorgu hazırlanırken hata oluştu.";
    }
}

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $question = $_POST['question'] ?? null;
    $options = [
        $_POST['option1'] ?? null,
        $_POST['option2'] ?? null,
        $_POST['option3'] ?? null,
        $_POST['option4'] ?? null
    ];
    $correctOption = $_POST['correct_option'] ?? null;

    if ($question && $options && $correctOption !== null) {
        $stmt = $db->prepare('UPDATE questions SET question = ?, option1 = ?, option2 = ?, option3 = ?, option4 = ?, correct_option = ? WHERE id = ?');
        
        if ($stmt) {
            $stmt->bindValue(1, $question, SQLITE3_TEXT);
            for ($i = 0; $i < 4; $i++) {
                $stmt->bindValue($i + 2, $options[$i], SQLITE3_TEXT);
            }
            $stmt->bindValue(6, $correctOption, SQLITE3_INTEGER);
            $stmt->bindValue(7, $id, SQLITE3_INTEGER);
            
            if ($stmt->execute()) {
                echo "Soru güncellendi.";
            } else {
                echo "Soru güncellenirken hata oluştu.";
            }
        } else {
            echo "Sorgu hazırlanırken hata oluştu.";
        }
    }
}


$questions = $db->query('SELECT * FROM questions');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli</title>
</head>
<body>
    <h1>Admin Paneli</h1>
    <form action="" method="post">
        <h2>Soru Ekle</h2>
        <input type="text" name="question" placeholder="Soru" required>
        <input type="text" name="option1" placeholder="Seçenek 1" required>
        <input type="text" name="option2" placeholder="Seçenek 2" required>
        <input type="text" name="option3" placeholder="Seçenek 3" required>
        <input type="text" name="option4" placeholder="Seçenek 4" required>
        <select name="correct_option" required>
            <option value="1">Seçenek 1</option>
            <option value="2">Seçenek 2</option>
            <option value="3">Seçenek 3</option>
            <option value="4">Seçenek 4</option>
        </select>
        <button type="submit">Soru Ekle</button>
    </form>
    <h2>Mevcut Sorular</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Soru</th>
            <th>Seçenekler</th>
            <th>Doğru Seçenek</th>
            <th>İşlemler</th>
        </tr>
        <?php while ($row = $questions->fetchArray(SQLITE3_ASSOC)): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['id']); ?></td>
            <td><?php echo htmlspecialchars($row['question']); ?></td>
            <td><?php echo htmlspecialchars($row['option1']) . ', ' . htmlspecialchars($row['option2']) . ', ' . htmlspecialchars($row['option3']) . ', ' . htmlspecialchars($row['option4']); ?></td>
            <td>Seçenek <?php echo htmlspecialchars($row['correct_option']); ?></td>
            <td>
                <a href="?delete=<?php echo htmlspecialchars($row['id']); ?>">Sil</a>
                <a href="admin_panel.php?edit=<?php echo htmlspecialchars($row['id']); ?>">Düzenle</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <form action="login.php" method="get">
        <button type="submit">Quiz Yarışmasına Git</button>
    </form>
</body>
</html>

