<?php
require_once('functions.php');
if (! hasPermission("delete-user")) err("Permission denied");
if (! isset($_POST["email"])) err("Missing email parameter");
query("delete from `member` where `email` = ?", [$_POST["email"]]);
echo "OK";
?>
