<?php
require_once('functions.php');

if (! hasPermission("view-user-private-info")) err("DENIED");
echo attendance($_POST['member'])["finalScore"];
?>
