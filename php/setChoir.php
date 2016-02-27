<?php
if (! isset($_GET["choir"])) die("Missing choir argument");
$choir = mysql_real_escape_string($_GET["choir"]);
if (mysql_num_rows(mysql_query("select * from `choir` where `id` = '$choir'")) < 1) die("Bad value for choir argument");
setcookie('choir', base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $sessionkey, $choir, MCRYPT_MODE_ECB)), time() + 60*60*24*120, '/', false, false);
# TODO Set last choir value in database for restoring on login
header("Location: /buzz");
?>
