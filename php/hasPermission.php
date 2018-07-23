<?php
require_once('functions.php');
if (! $USER || ! hasPermission(mysql_real_escape_string($_GET["permission"]))) echo "0";
else echo "1";
?>
