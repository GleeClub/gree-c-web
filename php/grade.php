<?php
require_once('functions.php');

if (! hasPermission("view-user-private-info")) die("DENIED");
echo attendance($_POST['member'], 0);
?>
