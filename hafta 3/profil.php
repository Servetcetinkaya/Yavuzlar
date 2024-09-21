<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profil_resmi'])) {
    $hedef_klasor = 'profil_resimleri/';
    $hedef_dosya = $hedef_klasor . basename($_FILES['profil_resmi']['name']);

    if (move_uploaded_file($_FILES['profil_resmi']['tmp_name'], $hedef_dosya)) {
        $_SESSION['profil_resmi'] = $hedef_dosya; // Resmi oturumda sakla
    } else {
        echo "Resim yükleme hatası.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Profil</title>
</head>
<body>
    <h1>Profil</h1>
    <form method="POST" enctype="multipart/form-data">
        <label for="profil_resmi">Profil Resmi Yükle:</label>
        <input type="file" name="profil_resmi" accept="image/*" required>
        <button type="submit">Yükle</button>
    </form>

    <?php if (isset($_SESSION['profil_resmi'])): ?>
        <h2>Profil Resminiz:</h2>
        <img src="<?php echo htmlspecialchars($_SESSION['profil_resmi']); ?>" alt="Profil Resmi" width="150">
    <?php endif; ?>
</body>
</html>

