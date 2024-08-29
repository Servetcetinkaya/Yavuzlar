<?php
$dbPath = '/tmp/quiz.db';
$db = new SQLite3($dbPath);

try {
    
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL
    )");
    
    
    $db->exec("CREATE TABLE IF NOT EXISTS questions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        question TEXT NOT NULL,
        option1 TEXT NOT NULL,
        option2 TEXT NOT NULL,
        option3 TEXT NOT NULL,
        option4 TEXT NOT NULL,
        correct_option INTEGER NOT NULL
    )");
    
    
    $db->exec("CREATE TABLE IF NOT EXISTS scores (
        user_id INTEGER PRIMARY KEY,
        points INTEGER DEFAULT 0,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
    
    
    $db->exec("CREATE TABLE IF NOT EXISTS user_answers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        question_id INTEGER NOT NULL,
        answer INTEGER NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (question_id) REFERENCES questions(id)
    )");

    echo "Veritabanı ve tablolar oluşturuldu, hocam şimdi login.php ye gidip özgür ve servet isimli kullanicileri deneyebilirsiniz..";
} catch (Exception $e) {
    die("Veritabanı veya tablo oluşturulurken bir hata oluştu: " . $e->getMessage());
}
?>

