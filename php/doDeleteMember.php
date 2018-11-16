<?php
require_once('functions.php');
if (! hasPermission("delete-user")) die("Permission denied");
if (! isset($_POST["email"])) die("Missing email parameter");
query("delete from `member` where `email` = ?", [$_POST["email"]]);
echo "OK";
?>
