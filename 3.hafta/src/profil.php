<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$database_path = "/tmp/yemek_sitesi.db";
$connection = new PDO("sqlite:$database_path");
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];

       
        $stmt = $connection->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        $user = $stmt->fetch();

        if ($user && password_verify($current_password, $user['password'])) {
            
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $connection->prepare("UPDATE users SET password = :password WHERE id = :id");
            $update_stmt->execute(['password' => $hashed_new_password, 'id' => $_SESSION['user_id']]);
            echo "Şifre başarıyla güncellendi.";
        } else {
            echo "Mevcut şifre yanlış.";
        }
    }

    
    if (isset($_FILES['profile_picture'])) {
        $target_folder = 'profil_resimleri/';
        $target_file = $target_folder . basename($_FILES['profile_picture']['name']);

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            $_SESSION['profile_picture'] = $target_file; 
        } else {
            echo "Resim yükleme hatası.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
        }
        label {
            display: block;
            margin-bottom: 10px;
        }
        input[type="file"], input[type="password"] {
            margin-bottom: 20px;
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            padding: 10px 15px;
            background-color: #333;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            width: 100%;
        }
        button:hover {
            background-color: #555;
        }
        .profile-picture {
            text-align: center;
            margin-top: 20px;
        }
        .home-button {
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Profile</h1>
    
    <form method="POST" enctype="multipart/form-data">
        <label for="current_password">Mevcut Şifre</label>
        <input type="password" name="current_password" required>

        <label for="new_password">Yeni Şifre</label>
        <input type="password" name="new_password" required>

        <button type="submit" name="update_password">Şifreyi Güncelle</button>
    </form>

    <form method="POST" enctype="multipart/form-data">
        <label for="profile_picture">Resim Yükle</label>
        <input type="file" name="profile_picture" accept="image/*" required>
        <button type="submit">Yükle</button>
    </form>

    <?php if (isset($_SESSION['profile_picture'])): ?>
        <div class="profile-picture">
            <h2>Senin Resmin:</h2>
            <img src="<?php echo htmlspecialchars($_SESSION['profile_picture']); ?>" alt="Profile Picture" width="150">
        </div>
    <?php endif; ?>

    <div class="home-button">
        <button onclick="window.location='ana_sayfa.php'">Ana Sayfa</button>
    </div>
</body>
</html>

