<?php
require_once('functions.php');

if (! isOfficer(getuser())) die("Access denied");
setcookie('email', base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $sessionkey, $_POST['user'], MCRYPT_MODE_ECB)), time() + 60*60*24*120, '/', false, false);
header("Location: /buzz");
?>

