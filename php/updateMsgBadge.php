<?php
require_once('functions.php');

$i = getNumUnreadMessages(mysql_real_escape_string($_COOKIE['email']));
echo $i;
?>
