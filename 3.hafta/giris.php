<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$database = "/tmp/yemek_sitesi.db";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $hashed_password = hash('sha256', $password);  

    try {
        $connection = new PDO("sqlite:$database");
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $connection->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['password'] === $hashed_password) {
            $_SESSION['user_id'] = $user['id']; 
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == 'admin') {
                header("Location: admin_panel.php");
            } else {
                header("Location: ana_sayfa.php");
            }
            exit();
        } else {
            $error = "Hatalı giriş.";
        }
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('Başlıksız.jpeg');
            background-size: cover; /* Resmi tam ekran kaplaması için */
            background-position: center; /* Resmi ortalamak için */
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #fff; /* Yazı rengi beyaz */
        }

        .container {
            background-color: rgba(0, 0, 0, 0.7); /* Yarı şeffaf arka plan */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h1 {
            margin-bottom: 20px;
            color: #fff; /* Başlık rengi beyaz */
        }

        label {
            display: block;
            text-align: left;
            margin-bottom: 5px;
            color: #fff; /* Etiket rengi beyaz */
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
    </div>
    <br> admin:admin <br> servet:servet <br>
</body>
</html>

