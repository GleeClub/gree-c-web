<?php
require_once('functions.php');

if (! hasPermission("view-user-private-info")) die("DENIED");
echo attendance(mysql_real_escape_string($_POST['member']), 0);
?>
