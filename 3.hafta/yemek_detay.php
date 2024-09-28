<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$database = "/tmp/yemek_sitesi.db";
try {
    $connection = new PDO("sqlite:$database");
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Database connection error: " . $e->getMessage();
    exit();
}

$dish_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$dish = null;
$comments = [];
$average_rating = 0;

if ($dish_id > 0) {
    try {
        $stmt = $connection->prepare("SELECT * FROM meals WHERE id = :id");
        $stmt->bindParam(':id', $dish_id);
        $stmt->execute();
        $dish = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dish) {
            $dish = [
                'id' => 0,
                'name' => 'Not Found',
                'price' => '0',
                'image_url' => 'default.jpg',
                'description' => 'No description'
            ];
        } else {
            if (!isset($dish['description'])) {
                $dish['description'] = 'Açıklama yok. ';
            }
            if (!isset($dish['image_url'])) {
                $dish['image_url'] = 'default.jpg';
            }
        }

        // Fetch comments
        $stmt = $connection->prepare("SELECT * FROM comments WHERE meal_id = :id ORDER BY id DESC");
        $stmt->bindParam(':id', $dish_id);
        $stmt->execute();
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate average rating
        if (count($comments) > 0) {
            $total_rating = 0;
            foreach ($comments as $comment) {
                $total_rating += $comment['rating'];
            }
            $average_rating = $total_rating / count($comments);
        }
    } catch (PDOException $e) {
        echo "Error fetching dish details: " . $e->getMessage();
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_comment'])) {
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
        $rating = intval($_POST['rating']);
        $comment = htmlspecialchars($_POST['comment']);

        try {
            $add_comment = $connection->prepare("INSERT INTO comments (meal_id, username, rating, comment) VALUES (:meal_id, :username, :rating, :comment)");
            $add_comment->bindParam(':meal_id', $dish_id);
            $add_comment->bindParam(':username', $username);
            $add_comment->bindParam(':rating', $rating);
            $add_comment->bindParam(':comment', $comment);
            $add_comment->execute();
            header("Location: yemek_detay.php?id=$dish_id");
            exit();
        } catch (PDOException $e) {
            echo "Error adding comment: " . $e->getMessage();
            exit();
        }
    } else {
        header('Location: login.php');
        exit();
    }
}

if (isset($_GET['delete']) && isset($_SESSION['username'])) {
    $comment_id = intval($_GET['delete']);
    try {
        $delete = $connection->prepare("DELETE FROM comments WHERE id = :id");
        $delete->bindParam(':id', $comment_id);
        $delete->execute();
        header("Location: yemek_detay.php?id=$dish_id");
        exit();
    } catch (PDOException $e) {
        echo "Error deleting comment: " . $e->getMessage();
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yemek Detay</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }
        header {
            background-color: #333;
            color: #fff;
            padding: 10px 0;
            text-align: center;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        .dish-detail {
            margin-bottom: 20px;
        }
        .dish-detail img {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }
        .dish-name {
            font-size: 24px;
            font-weight: bold;
            margin-top: 20px;
        }
        .dish-price {
            color: #555;
            margin: 10px 0;
        }
        .dish-description {
            font-size: 16px;
            color: #666;
            margin: 20px 0;
        }
        .average-rating {
            font-size: 20px;
            color: #ffd700;
            margin: 10px 0;
        }
        .comments {
            margin-top: 30px;
        }
        .comment {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }
        .rating {
            font-size: 18px;
            color: #ffd700;
        }
        .comment-form {
            margin-top: 30px;
        }
        .comment-form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            resize: vertical;
        }
        .comment-form button {
            padding: 10px 15px;
            color: #fff;
            background-color: #333;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .comment-form button:hover {
            background-color: #555;
        }
        .message, .error {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .message {
            background-color: #e7f5ff;
            border-left: 5px solid #5bc0de;
            color: #31708f;
        }
        .error {
            background-color: #f2dede;
            border-left: 5px solid #d9534f;
            color: #a94442;
        }
        .rating-label {
            display: inline-block;
            width: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <header>
        <h1>Yemek Detay</h1>
    </header>
    <div class="container">
        <div class="dish-detail">
            <img src="<?php echo htmlspecialchars($dish['image_url']); ?>" alt="<?php echo htmlspecialchars($dish['name']); ?>">
            <div class="dish-name"><?php echo htmlspecialchars($dish['name']); ?></div>
            <div class="dish-price">Fiyat: <?php echo htmlspecialchars($dish['price']); ?> ₺</div>
            <div class="dish-description"><?php echo htmlspecialchars($dish['description']); ?></div>
            <div class="average-rating">Ortalama Puan: 
                <?php echo $average_rating > 0 ? round($average_rating, 1) . " ⭐" : "Puansız"; ?>
            </div>
        </div>

        <div class="comments">
            <h2>Yorumlar</h2>
            <?php if (count($comments) > 0): ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment">
                        <div class="rating">
                            <?php for ($i = 0; $i < $comment['rating']; $i++): ?>
                                <span class="rating-label">⭐</span>
                            <?php endfor; ?>
                        </div>
                        <div><strong><?php echo htmlspecialchars($comment['username']); ?></strong></div>
                        <div><?php echo htmlspecialchars($comment['comment']); ?></div>
                        <?php if (isset($_SESSION['username']) && $_SESSION['username'] === $comment['username']): ?>
                            <a href="?id=<?php echo $dish_id; ?>&delete=<?php echo $comment['id']; ?>" onclick="return confirm('Bu yorumu silmek istediğinize emin misiniz??');">Sil</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div>Yorum yok.</div>
            <?php endif; ?>
        </div>

        <div class="comment-form">
            <h2>Yorum Gönder</h2>
            <form method="POST" action="">
                <label for="rating">Reyting:</label>
                <select name="rating" required>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                    <option value="7">7</option>
                    <option value="8">8</option>
                    <option value="9">9</option>
                    <option value="10">10</option>
                </select>
                <br>
                <textarea name="comment" rows="4" placeholder="Düşüncelerini Yaz.." required></textarea>
                <br>
                <button type="submit" name="send_comment">Gönder</button>
                <button onclick="window.location='ana_sayfa.php'" style="padding: 10px 15px; background-color: #333; color: #fff; border: none; cursor: pointer; border-radius: 5px;">
    Ana Sayfaya Git
</button>

            </form>
        </div>
    </div>
</body>
</html>


