<?php
require_once('functions.php');
if (! hasPermission("delete-user")) die("Permission denied");
query("delete from `member` where `email` = ?", [$email]);
echo "OK";
?>
