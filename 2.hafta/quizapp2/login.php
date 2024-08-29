<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

$dbPath = '/tmp/quiz.db';
if (!file_exists($dbPath)) {
    die("Veritabanı dosyası bulunamadı.");
}

try {
    $db = new SQLite3($dbPath);
} catch (Exception $e) {
    die("Veritabanına bağlanırken bir hata oluştu: " . $e->getMessage());
}

if (isset($_POST['role'])) {
    $username = $_POST['role'];

    
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bindValue(1, $username, SQLITE3_TEXT);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($result) {
        $_SESSION['user_id'] = $result['id'];
        $_SESSION['role'] = $username;

        if ($username === 'Admin') {
            header('Location: admin_panel.php');
        } else if ($username === 'Özgür' || $username === 'Servet') {
            header('Location: quiz_yarismasi.php');
        } else {
            echo "Geçersiz kullanıcı rolü.";
        }
        exit();
    } else {
        echo "Kullanıcı bulunamadı.";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h1>Giriş Yap</h1>
    <form action="" method="post">
        <label for="role">Kullanıcı Adı:</label>
        <select name="role" id="role" required>
            <option value="">Seçin</option>
            <option value="Özgür">Özgür</option>
            <option value="Servet">Servet</option>
            <option value="Admin">Admin</option>
        </select>
        <button type="submit">Giriş Yap</button>
    </form>
</body>
</html>

