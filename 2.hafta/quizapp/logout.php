<?php
// Oturumu başlat
session_start();

// Oturumu yok et
session_unset();
session_destroy();

// Kullanıcıyı giriş sayfasına yönlendir
header('Location: login.php');
exit();
?>

