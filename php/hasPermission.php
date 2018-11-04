<?php
require_once('functions.php');
if (! $USER || ! hasPermission($_GET["permission"])) echo "0";
else echo "1";
?>
