<?php
require_once('functions.php');
if (! $USER || ! hasPermission("officer")) echo "0";
else echo "1";
?>
