<?php
require_once('functions.php');
if (! hasPermission("delete-user")) die("Permission denied");
$email = mysql_real_escape_string($_POST['email']);
if (! mysql_query("delete from `member` where `email` = '$email'")) die("Failed to delete $email");
echo "OK";
?>
