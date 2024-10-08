<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$database = "/tmp/yemek_sitesi.db";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        $connection = new PDO("sqlite:$database");
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $connection->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            
            if ($user['banned']) {
                $error = "Bu kullanıcı banlanmıştır.";
            } elseif (password_verify($password, $user['password'])) { 
                $_SESSION['user_id'] = $user['id']; 
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                
                if ($user['role'] == 'admin') {
                    header("Location: admin_panel.php");
                } elseif ($user['role'] == 'firm_user') {
                    header("Location: firma_panel.php");
                } else {
                    header("Location: ana_sayfa.php");
                }
                exit();
            } else {
                $error = "Hatalı giriş.";
            }
        } else {
            $error = "Hatalı giriş.";
        }
    } catch (PDOException $e) {
        echo "Veritabanı hatası: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Giriş</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('Başlıksız.jpeg');
            background-size: cover;
            background-position: center;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #fff;
        }

        .container {
            background-color: rgba(0, 0, 0, 0.7);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h1 {
            margin-bottom: 20px;
            color: #fff;
        }

        label {
            display: block;
            text-align: left;
            margin-bottom: 5px;
            color: #fff;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: #218838;
        }

        .error {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Giriş</h1>

        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="username">Kullanıcı adı:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Parola:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Giriş Yap</button>
        </form>
        admin:admin <br> servet:servet <br> mehmet:mehmet

    </div>
</body>
</html>

