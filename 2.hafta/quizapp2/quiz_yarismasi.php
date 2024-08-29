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

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Özgür' && $_SESSION['role'] !== 'Servet')) {
    header('Location: index.html');
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$questions = $db->query('SELECT * FROM questions');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answers = $_POST['answers'];
    
    foreach ($answers as $question_id => $answer) {
        $stmt = $db->prepare('INSERT INTO user_answers (user_id, question_id, answer) VALUES (?, ?, ?)');
        $stmt->bindValue(1, $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(2, $question_id, SQLITE3_INTEGER);
        $stmt->bindValue(3, $answer, SQLITE3_INTEGER);
        $stmt->execute();
    }

    $stmt = $db->prepare('SELECT COUNT(*) AS correct_answers FROM user_answers JOIN questions ON user_answers.question_id = questions.id WHERE user_answers.user_id = ? AND user_answers.answer = questions.correct_option');
    $stmt->bindValue(1, $user_id, SQLITE3_INTEGER);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    $correct_answers = $result['correct_answers'];

    $stmt = $db->prepare('INSERT OR REPLACE INTO scores (user_id, points) VALUES (?, ?)');
    $stmt->bindValue(1, $user_id, SQLITE3_INTEGER);
    $stmt->bindValue(2, $correct_answers, SQLITE3_INTEGER);
    $stmt->execute();

    echo "Tebrikler, sınavı tamamladınız. Puanınız: " . $correct_answers;

    exit();
}

$scores = $db->query('SELECT users.username, scores.points FROM scores JOIN users ON scores.user_id = users.id ORDER BY scores.points DESC');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Yarışması</title>
</head>
<body>
    <h1>Quiz Yarışması</h1>
    <form action="" method="post">
        <?php while ($question = $questions->fetchArray(SQLITE3_ASSOC)): ?>
        <fieldset>
            <legend><?php echo htmlspecialchars($question['question']); ?></legend>
            <label><input type="radio" name="answers[<?php echo htmlspecialchars($question['id']); ?>]" value="1" required> <?php echo htmlspecialchars($question['option1']); ?></label><br>
            <label><input type="radio" name="answers[<?php echo htmlspecialchars($question['id']); ?>]" value="2"> <?php echo htmlspecialchars($question['option2']); ?></label><br>
            <label><input type="radio" name="answers[<?php echo htmlspecialchars($question['id']); ?>]" value="3"> <?php echo htmlspecialchars($question['option3']); ?></label><br>
            <label><input type="radio" name="answers[<?php echo htmlspecialchars($question['id']); ?>]" value="4"> <?php echo htmlspecialchars($question['option4']); ?></label>
        </fieldset>
        <?php endwhile; ?>
        <button type="submit">Sınavı Tamamla</button>
    </form>

    <h2>Skor Tablosu</h2>
    <table>
        <tr>
            <th>İsim</th>
            <th>Puan</th>
        </tr>
        <?php while ($row = $scores->fetchArray(SQLITE3_ASSOC)): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['username']); ?></td>
            <td><?php echo htmlspecialchars($row['points']); ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>

