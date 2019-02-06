<?php
require_once('functions.php');

if (! hasPermission("switch-user")) err("Access denied");
setcookie("email", encrypt2($_POST["user"]), time() + 60*60*24*120, "/", false, false);
echo "OK";
?>
