<?php
require_once('functions.php');

if (! hasPermission("switch-user")) die("Access denied");
setcookie('email', base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $sessionkey, $_POST['user'], MCRYPT_MODE_ECB)), time() + 60*60*24*120, '/', false, false);
echo "OK";
?>
